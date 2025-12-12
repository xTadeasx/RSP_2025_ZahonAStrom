<?php require_once __DIR__ . '/../Backend/notAccess.php'; ?>
<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php
// Načtení role uživatele z databáze
$userId = $_SESSION['user']['id'] ?? null;
$userRole = null;
if ($userId) {
    $user = select('users', 'role_id', "id = $userId");
    if (!empty($user)) {
        $userRole = $user[0]['role_id'] ?? null;
    }
}
?>
<h1>Účet uživatele</h1>
<div style="margin-bottom: 16px; display:flex; gap:12px; flex-wrap:wrap;">
    <a href="notifications.php" style="text-decoration:none; color:var(--brand);">🔔 Moje notifikace</a>
    <?php if (in_array($userRole, [1, 2, 4, 5])): // Administrátor, Šéfredaktor, Redaktor, Autor ?>
        <a href="clanek.php" style="text-decoration:none; color:var(--brand);">Vytvořit nový článek</a>
    <?php endif; ?>
</div>
<form action="../Backend/userControl.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="edit_user">

    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_SESSION['user']['id'] ?? ''); ?>">

    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>" required>
    <label for="password">Password (ponechte prázdné, pokud nechcete měnit):</label>
    <input type="password" id="password" name="password" minlength="3">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" required>
    <label for="phone">Telefon:</label>
    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user']['phone'] ?? ''); ?>">

    <label for="bio">Jedna věta o mně:</label>
    <input type="text" id="bio" name="bio" maxlength="200" value="<?php echo htmlspecialchars($_SESSION['user']['bio'] ?? ''); ?>" placeholder="Krátké info, zobrazí se v týmu">

    <label for="avatar">Profilová fotka (jpg, png, webp):</label>
    <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp">
    <?php if (!empty($_SESSION['user']['avatar_path'])): ?>
        <div style="margin:8px 0;">
            <img src="../<?php echo htmlspecialchars($_SESSION['user']['avatar_path']); ?>" alt="Avatar" style="height:80px; width:80px; object-fit:cover; border-radius:50%;">
        </div>
    <?php endif; ?>
    <div class="actions">
        <button class="btn" type="submit">Uložit změny</button>
    </div>
</form>
<div style="height:16px"></div>
<form action="../Backend/userControl.php" method="POST">
    <input type="hidden" name="action" value="writerRegister">
    <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>">
    <input type="text" id="text" name="text" placeholder="Zadejte důvod proč chcete být autorem" minlength="10" required>
    <div class="actions">
        <button class="btn" type="submit">Přihlásit se k pozici autora</button>
    </div>
</form>
<?php require_once __DIR__ . '/Include/footer.php'; ?>