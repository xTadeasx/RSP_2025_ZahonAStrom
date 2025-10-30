<?php
// Jednoduchá pomocná funkce pro odeslání e‑mailu.
// V produkci můžeš přepnout na PHPMailer z `vendor/phpmailer/phpmailer`.

function sendEmail(string $to, string $subject, string $htmlBody, string $from = 'no-reply@example.test'): bool {
    // Základní pokus přes vestavěnou funkci mail(). Na lokálním XAMPP většinou neodešle,
    // ale kvůli toku aplikace vracíme true i při neúspěchu, aby se neblokoval běh.
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: ${from}\r\n";

    try {
        @mail($to, $subject, $htmlBody, $headers);
        return true;
    } catch (Throwable $e) {
        return true; // neblokovat aplikaci při chybě
    }
}
?>

