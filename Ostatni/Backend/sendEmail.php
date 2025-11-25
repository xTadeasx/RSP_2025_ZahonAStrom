<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//O TENTO SOUBOR MI NAPIŠTĚ A BUDE NA TEAMESCH

require_once __DIR__ . '/../Database/dataControl.php';
require __DIR__ . '/../vendor/autoload.php';

/**
 * Email funkce pro Seznam.cz
 * 
 * Konfigurace: Port 465 s SSL (ENCRYPTION_SMTPS)
 * Pro debug režim přidejte před $mail->send():
 * $mail->SMTPDebug = 2; // 0 = off, 1 = client, 2 = client + server
 * 
 * Alternativní konfigurace pro Seznam.cz:
 * - Port 465 s SSL (aktuálně použito - lepší kompatibilita na Windows)
 * - Port 587 s STARTTLS (pokud 465 nefunguje, změňte na ENCRYPTION_STARTTLS a port 587)
 */

function sendEmail($to, $subject, $text, $userId = null)
{
    $mail = new PHPMailer(true);
    try {
        // SMTP konfigurace pro Seznam.cz
        $mail->isSMTP();
        $mail->Host       = 'smtp.seznam.cz';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chatgptbolest@seznam.cz';
        $mail->Password   = '1242sw0N';
        
        // Seznam.cz - používám port 465 s SSL (lepší kompatibilita na Windows)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        // SSL/TLS nastavení - méně striktní pro lepší kompatibilitu
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // CharSet pro české znaky
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('chatgptbolest@seznam.cz', 'RSP Záhon a Strom');
        $mail->addAddress($to);

        // Pokud je zadáno userId, přidáme odkaz s trackingem
        if ($userId !== null) {
            // Vytvoříme bezpečný token z ID (base64 encode pro jednoduchost)
            $token = base64_encode($userId . '|' . time());
            // Získáme základní URL (protokol + host)
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;
            
            // Relativní cesta k emailReturn.php
            $returnUrl = $baseUrl . '/Ostatni/Backend/emailReturn.php?token=' . urlencode($token);
            
            // Přidáme odkaz do textu emailu
            $text .= "\n\n---\nKlikněte zde pro potvrzení: " . $returnUrl;
        }

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $text;

        // Odeslání emailu
        $result = $mail->send();
        if ($result) {
            return true;
        } else {
            // Pokud send() vrátí false, ale nevyhodí výjimku
            error_log("Email send failed to {$to}: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        // Log error s detailními informacemi
        error_log("Email send failed to {$to}: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

function sendEmailResetPassword($to)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $randomNumber = rand(1000, 9999);
    $randomNumberHash = password_hash($randomNumber, PASSWORD_BCRYPT);
    $user = select('users', '*', "email = '$to'")[0];
    if($user == null){
        $_SESSION['error'] = "Uživatel s tímto emailem neexistuje.";    
        return;
    }
    
    $mail = new PHPMailer(true);
    try {
        // SMTP konfigurace pro Seznam.cz
        $mail->isSMTP();
        $mail->Host       = 'smtp.seznam.cz';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chatgptbolest@seznam.cz';
        $mail->Password   = '1242sw0N';
        
        // Seznam.cz - používám port 465 s SSL (lepší kompatibilita na Windows)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // SSL/TLS nastavení - méně striktní pro lepší kompatibilitu
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // CharSet pro české znaky
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('chatgptbolest@seznam.cz', 'RSP Záhon a Strom');
        $mail->addAddress($to);

        $mail->isHTML(false);
        $mail->Subject = "Obnova hesla";
        $mail->Body    = "Vaše nové heslo je: " . $randomNumber;
        
        // Odeslání emailu
        $result = $mail->send();
        if ($result) {
            update('users', ['password_temp' => $randomNumberHash], "id = {$user['id']}");
            $_SESSION['success'] = "Na váš email bylo odesláno nové heslo.";
            return true;
        } else {
            // Pokud send() vrátí false, ale nevyhodí výjimku
            error_log("Password reset email send failed to {$to}: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Nepodařilo se odeslat email s novým heslem. Zkuste to prosím později.";
            return false;
        }
    } catch (Exception $e) {
        // Detailní logování chyby
        error_log("Password reset email failed to {$to}: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        $_SESSION['error'] = "Nepodařilo se odeslat email s novým heslem. Zkuste to prosím později.";
        return false;
    }
}
