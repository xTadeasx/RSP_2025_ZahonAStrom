<?php require_once __DIR__ . '/../Backend/notAccess.php'; ?>
<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php
// Ověření, že uživatel je v roli Autora (role_id = 5)
$userId = $_SESSION['user']['id'] ?? null;
if ($userId) {
    $user = select('users', 'role_id', "id = $userId");
    if (empty($user) || ($user[0]['role_id'] ?? null) != 5) {
        $_SESSION['error'] = "Nemáte oprávnění vytvářet články. Musíte být v roli Autora.";
        header('Location: user.php');
        exit();
    }
}
?>
<h1>Vytvořit nový článek</h1>
<form action="../Backend/postControl.php" method="POST">
    <input type="hidden" name="action" value="create_post">
    
    <label for="title">Název článku:</label>
    <input type="text" id="title" name="title" required>
    
    <label for="body">Obsah článku:</label>
    <textarea id="body" name="body" rows="15" required></textarea>
    
    <div class="actions">
        <button type="submit">Odeslat článek</button>
    </div>
</form>
<?php require_once __DIR__ . '/Include/footer.php'; ?>

