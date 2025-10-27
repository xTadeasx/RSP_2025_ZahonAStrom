<?php
    session_start();
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-success">' . $_SESSION['error'] . '</div>';
        // To do: ten div stylovat, ideálně pravej horní roh stránky, červenej čtverec, po pár sekundách aby zmizel.
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        // To do: ten div stylovat, ideálně pravej horní roh stránky, zelený nebo modrý čtverec, po pár sekundách aby zmizel.
        unset($_SESSION['success']);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Login</h1>
    <form action="../Backend/login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <input type="hidden" name="action" value="login">
        <button type="submit">Login</button>
    </form>

    <br><br>

    <h1>Register</h1>
    <form action="../Backend/login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <input type="hidden" name="action" value="register">
        <button type="submit">Register</button>
    </form>
</body>
</html>