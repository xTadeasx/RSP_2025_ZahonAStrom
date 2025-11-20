<?php
// Hlavička s Bootstrap navbar, zachovává stávající funkcionalitu

// Načtení role uživatele pro zobrazení tlačítka "Nový článek"
// Role_id je uložena v session při přihlášení (Backend/login.php)
$userRoleId = $_SESSION['user']['role_id'] ?? null;

// Zobrazení tlačítka pro role: Administrátor, Šéfredaktor, Redaktor, Autor (1, 2, 4, 5)
// Role: 1=Admin, 2=Šéfredaktor, 3=Recenzent, 4=Redaktor, 5=Autor, 6=Čtenář
$showNewArticleButton = !empty($userRoleId) && in_array($userRoleId, [1, 2, 4, 5]);

// Zobrazení tlačítka "Přehled článků" pro role: Admin, Šéfredaktor, Recenzent, Redaktor, Autor (ne Čtenář)
$showArticlesOverviewButton = !empty($userRoleId) && in_array($userRoleId, [1, 2, 3, 4, 5]);
$showReviewerNotifications = !empty($userRoleId) && (int)$userRoleId === 3;
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
                    <?php if ($showNewArticleButton): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./clanek.php" style="color: #ffd700; font-weight: 600; background: rgba(255, 215, 0, 0.1); border-radius: 4px; padding: 4px 12px !important; margin: 0 4px;">
                                ✏️ Nový článek
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($showArticlesOverviewButton): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./articles_overview.php" style="color: #4CAF50; font-weight: 600; background: rgba(76, 175, 80, 0.1); border-radius: 4px; padding: 4px 12px !important; margin: 0 4px;">
                                📋 Přehled článků
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['user']['username'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="./chat.php" style="color: #5bc0de; font-weight: 600; background: rgba(91, 192, 222, 0.15); border-radius: 4px; padding: 4px 12px !important; margin: 0 4px;">
                                💬 Zprávy
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./notifications.php" style="color: #ff9800; font-weight: 600; background: rgba(255, 152, 0, 0.15); border-radius: 4px; padding: 4px 12px !important; margin: 0 4px;">
                                🔔 Notifikace
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-3 header-actions">
                    <?php if ($showReviewerNotifications): ?>
                        <div 
                            class="notification-center" 
                            data-notifications-root 
                            data-endpoint="../Backend/notificationControl.php"
                        >
                            <button 
                                type="button" 
                                class="notification-toggle" 
                                aria-expanded="false"
                                aria-controls="notificationDropdown"
                                data-notifications-toggle
                            >
                                <span class="notification-icon" aria-hidden="true">🔔</span>
                                <span class="notification-label">Upozornění</span>
                                <span class="notification-badge" data-notifications-badge>0</span>
                            </button>
                            <div class="notification-dropdown" id="notificationDropdown" data-notifications-dropdown>
                                <div class="notification-dropdown__header">
                                    <strong>Upozornění</strong>
                                    <span data-notifications-status>Načítám...</span>
                                </div>
                                <div class="notification-dropdown__body" data-notifications-list>
                                    <div class="notification-empty">Žádná upozornění</div>
                                </div>
                                <div class="notification-dropdown__footer">
                                    Přehled upozornění pro recenzenta
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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

