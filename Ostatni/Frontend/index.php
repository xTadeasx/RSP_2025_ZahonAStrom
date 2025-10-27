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
    <!-- Tomášovo králoství -->
     <h1>
        <?php
        if(isset($_SESSION['user']['username'])) {
            echo "Přihlášen jako: " . $_SESSION['user']['username'];
            ?>
                <a href="../Frontend/user.php">Účet uživatele</a>
                <br>
                <form action="../Backend/userControl.php" method="post">
                    <button type="submit" name="action" value="logOut">Odhlásit se</button>
                </form>
            <?php

        } 

        ?>
        <br>
        <a href="../Frontend/login.php">Přihlášení/registrace</a>

     </h1>
</body>
</html>