<?php
/**
 * Email Return Handler
 * Zpracovává kliknutí na odkaz v emailu a odešle zpět email uživatele
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../Database/dataControl.php';
require __DIR__ . '/../vendor/autoload.php';

// Získání tokenu z URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Neplatný odkaz. Chybí token.');
}

// Dekódování tokenu
$decoded = base64_decode($token, true);
if ($decoded === false) {
    die('Neplatný odkaz. Token není ve správném formátu.');
}

// Rozdělení tokenu na ID a timestamp
$parts = explode('|', $decoded);
if (count($parts) !== 2) {
    die('Neplatný odkaz. Špatný formát tokenu.');
}

$userId = (int)$parts[0];
$timestamp = (int)$parts[1];

// Ověření, že token není starší než 30 dní (volitelné)
$maxAge = 30 * 24 * 60 * 60; // 30 dní v sekundách
if (time() - $timestamp > $maxAge) {
    die('Odkaz vypršel. Kontaktujte prosím administrátora.');
}

// Načtení uživatele z databáze
$user = select('users', '*', "id = $userId");

if (empty($user)) {
    die('Uživatel nebyl nalezen v databázi.');
}

$user = $user[0];

// Získání emailu uživatele
$userEmail = $user['email'] ?? '';
$username = $user['username'] ?? '';

if (empty($userEmail)) {
    die('Uživatel nemá nastavený email.');
}

// Změna role uživatele na Autor (role_id = 5)
$roleChanged = false;
$currentRoleId = $user['role_id'] ?? null;
if ($currentRoleId != 5) {
    $updateResult = update('users', ['role_id' => 5], "id = $userId");
    if ($updateResult) {
        $roleChanged = true;
    }
}

// Odeslání emailu zpět na systémový email
$adminEmail = 'chatgptbolest@seznam.cz'; // Váš systémový email
$subject = 'Email Return - Kliknutí na odkaz v emailu';
$message = "Uživatel kliknul na odkaz v emailu:\n\n";
$message .= "ID uživatele: {$userId}\n";
$message .= "Uživatelské jméno: {$username}\n";
$message .= "Email uživatele: {$userEmail}\n";
$message .= "Čas kliknutí: " . date('Y-m-d H:i:s') . "\n";
$message .= "IP adresa: " . ($_SERVER['REMOTE_ADDR'] ?? 'Neznámá') . "\n";
if ($roleChanged) {
    $message .= "✓ Role byla změněna na Autor (role_id: 5)\n";
    $message .= "Předchozí role_id: " . ($currentRoleId ?? 'null') . "\n";
} else {
    $message .= "Role uživatele: " . ($currentRoleId ?? 'null') . " (nebyla změněna)\n";
}

// Odeslání emailu pomocí sendEmail funkce (bez userId, aby se nezačala rekurze)
require_once __DIR__ . '/sendEmail.php';

// Použijeme přímý mail bez tokenu, aby se nezačala rekurze
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.seznam.cz';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'chatgptbolest@seznam.cz';
    $mail->Password   = '1242sw0N';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setFrom('chatgptbolest@seznam.cz', 'RSP Záhon a Strom');
    $mail->addAddress($adminEmail);
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->send();
    
    // Zobrazení potvrzovací stránky
    ?>
    <!DOCTYPE html>
    <html lang="cs">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email potvrzen</title>
        <style>
            body {
                font-family: system-ui, -apple-system, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: #f7f6f8;
            }
            .container {
                background: white;
                padding: 2rem;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 500px;
            }
            h1 {
                color: #4e1835;
                margin-bottom: 1rem;
            }
            p {
                color: #6b6570;
                line-height: 1.6;
            }
            .success {
                color: #2e7d32;
                font-weight: bold;
                margin-top: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>✓ Email potvrzen</h1>
            <p>Vaše kliknutí na odkaz bylo zaznamenáno.</p>
            <?php if ($roleChanged): ?>
                <p class="success">Vaše role byla úspěšně změněna na Autor!</p>
            <?php else: ?>
                <p class="success">Děkujeme za potvrzení!</p>
            <?php endif; ?>
            <p style="margin-top: 2rem; font-size: 0.9rem; color: #6b6570;">
                ID uživatele: <?= htmlspecialchars($userId) ?><br>
                Email: <?= htmlspecialchars($userEmail) ?><br>
                <?php if ($roleChanged): ?>
                    <strong style="color: #2e7d32;">Role: Autor</strong>
                <?php endif; ?>
            </p>
        </div>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    // Pokud se nepodaří odeslat email, zobrazíme chybu
    die('Nepodařilo se odeslat potvrzovací email. Chyba: ' . $e->getMessage());
}

