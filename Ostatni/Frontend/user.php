<?php require_once __DIR__ . '/../Backend/notAccess.php'; ?>
<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
    <h1>Účet uživatele</h1>
    <form action="../Backend/userControl.php" method="POST">
        <input type="hidden" name="action" value="edit_user">

        <input type="hidden" name="id" value="<?php echo htmlspecialchars($_SESSION['user']['id'] ?? ''); ?>">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" minlength="3" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" required>
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>" required>
        <div class="actions">
            <button class="btn" type="submit">Uložit změny</button>
        </div>
    </form>
    <div style="height:16px"></div>
    <form action="../Backend/userControl.php" method="POST">
        <input type="hidden" name="action" value="writerRegister">
        <input type="text" id="text" name="text" placeholder="Zadejte důvod proč chcete být autorem" minlength="10" required>
        <div class="actions">
            <button class="btn" type="submit">Přihlásit se k pozici autora</button>
        </div>
    </form>
<?php require_once __DIR__ . '/Include/footer.php'; ?>