<?php
// Společný bootstrap pro všechny stránky ve `Frontend/`
// - spustí session
// - vykreslí flash zprávy do pravého horního rohu

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funkce pro bezpečný výpis
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Připrav flash zprávy a po zobrazení smaž
$__flashError = $_SESSION['error'] ?? null;
$__flashSuccess = $_SESSION['success'] ?? null;
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSP - Záhon a Strom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./Assets/CSS/main.css">
</head>
<body>
    <?php if ($__flashError): ?>
        <div class="alert alert-error" data-auto-hide><?= e($__flashError) ?></div>
    <?php endif; ?>
    <?php if ($__flashSuccess): ?>
        <div class="alert alert-success" data-auto-hide><?= e($__flashSuccess) ?></div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script defer src="./Assets/main.js"></script>

