<?php
// Hlavička s Bootstrap navbar, zachovává stávající funkcionalitu
?>
<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--brand)">
        <div class="container">
            <a class="navbar-brand" href="./index.php">Vědecký časopis</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="./index.php">O časopisu</a></li>
                    <li class="nav-item"><a class="nav-link" href="./board.php">Redakční rada</a></li>
                    <li class="nav-item"><a class="nav-link" href="./authors.php">Informace pro autory</a></li>
                    <li class="nav-item"><a class="nav-link" href="./archive.php">Archiv</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($_SESSION['user']['username'])): ?>
                        <span class="text-white-50 small">Přihlášen: <?= e($_SESSION['user']['username']) ?></span>
                        <a class="btn btn-light btn-sm" href="./user.php">Účet</a>
                        <form class="inline" action="../Backend/userControl.php" method="post">
                            <button class="btn btn-outline-light btn-sm" type="submit" name="action" value="logOut">Odhlásit</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-light btn-sm" href="./login.php">Přihlášení</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
<main class="container">

