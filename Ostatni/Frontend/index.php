<?php
    session_start();
    if (isset($_SESSION['flash'])) {
        echo '<div class="alert alert-success">' . $_SESSION['flash'] . '</div>';
        // To do: ten div stylovat, ideálně pravej horní roh stránky, červenej čtverec, po pár sekundách aby zmizel.
        unset($_SESSION['flash']);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSP - Záhon a Strom</title>
</head>
<body>
    <!-- Tomášovo králoství -->
     
</body>
</html>