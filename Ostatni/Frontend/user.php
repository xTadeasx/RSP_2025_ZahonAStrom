<?php
    // !!! TOTO PŘIDAT VŽDY VŠUDE - MŮŽEŠ SI VYTVOŘIT SOUBOR A PAK JEN VŠUDE DÁT REQUIRE_ONCE NEBO TO PŘEPISOVAT, TO NECHÁM NA TOBĚ
    // !!! OSOBNĚ BYCH NA TO VYTVOŘIL TEN SOUBOR, DAL TAM PŘÍMO CSS A JS AŤ TO NEMUSÍŠ FŮRT KOPÍROVAT
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
    <title>RSP - Záhon a Strom</title>
</head>
<body>
    <H1>EDIT Uzivatele ucet</H1>
    <form action="../Backend/userControl.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($_SESSION['user']['id'] ?? ''); ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>"  required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" required>
        <br><br>
        <label for="phone">phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" required>
        <br><br>
        <input type="hidden" name="action" value="edit_user">
        <button type="submit">Uložit změny</button>
    </form>
</body>
</html>