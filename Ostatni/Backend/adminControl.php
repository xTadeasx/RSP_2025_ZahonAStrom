<?php
require_once __DIR__ . '/notAccess.php';
require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/../Database/dataControl.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUserId = $_SESSION['user']['id'] ?? null;
$currentRoleId = $_SESSION['user']['role_id'] ?? null;

// Povoleno jen Admin (1) nebo Šéfredaktor (2)
if (!$currentUserId || !in_array((int)$currentRoleId, [1, 2])) {
    $_SESSION['error'] = "Nemáte oprávnění k této akci.";
    header('Location: ../Frontend/staff_management.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Neplatný požadavek.";
    header('Location: ../Frontend/staff_management.php');
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create_user':
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        if ($username === '' || $email === '' || $roleId <= 0 || $password === '') {
            $_SESSION['error'] = "Vyplňte username, email, heslo a roli.";
            break;
        }
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $ok = insert([
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'role_id' => $roleId,
            'password' => $hashed
        ], 'users');
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Uživatel vytvořen." : "Uživatele se nepodařilo vytvořit.";
        break;

    case 'delete_user':
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Chybí ID uživatele.";
            break;
        }
        if ($id === (int)$currentUserId) {
            $_SESSION['error'] = "Nemůžete smazat sám sebe.";
            break;
        }
        $ok = delete('users', "id = {$id}");
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Uživatel smazán." : "Smazání selhalo.";
        break;

    case 'create_role':
        $role = trim($_POST['role'] ?? '');
        if ($role === '') {
            $_SESSION['error'] = "Zadejte název role.";
            break;
        }
        $ok = insert(['role' => $role], 'users_roles');
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Role přidána." : "Přidání role selhalo.";
        break;

    case 'delete_role':
        $roleId = (int)($_POST['role_id'] ?? 0);
        if ($roleId <= 0) {
            $_SESSION['error'] = "Chybí role.";
            break;
        }
        $used = select('users', 'id', "role_id = {$roleId}");
        if (!empty($used)) {
            $_SESSION['error'] = "Role je přiřazena uživatelům, nejde smazat.";
            break;
        }
        $ok = delete('users_roles', "id = {$roleId}");
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Role smazána." : "Smazání role selhalo.";
        break;

    case 'create_issue':
        $year = (int)($_POST['year'] ?? 0);
        $number = (int)($_POST['number'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $published = $_POST['published_at'] ?? null;
        $ok = insert([
            'year' => $year,
            'number' => $number,
            'title' => $title,
            'published_at' => $published ?: null
        ], 'issues');
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Vydání přidáno." : "Přidání vydání selhalo.";
        break;

    case 'update_issue':
        $id = (int)($_POST['issue_id'] ?? 0);
        $year = (int)($_POST['year'] ?? 0);
        $number = (int)($_POST['number'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $published = $_POST['published_at'] ?? null;
        if ($id <= 0) { $_SESSION['error'] = "Chybí ID vydání."; break; }
        $ok = update('issues', [
            'year' => $year,
            'number' => $number,
            'title' => $title,
            'published_at' => $published ?: null
        ], "id = {$id}");
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Vydání upraveno." : "Úprava selhala.";
        break;

    case 'delete_issue':
        $id = (int)($_POST['issue_id'] ?? 0);
        if ($id <= 0) { $_SESSION['error'] = "Chybí ID vydání."; break; }
        $ok = delete('issues', "id = {$id}");
        $_SESSION[$ok ? 'success' : 'error'] = $ok ? "Vydání smazáno." : "Smazání vydání selhalo.";
        break;

    default:
        $_SESSION['error'] = "Neznámá akce.";
}

header('Location: ../Frontend/staff_management.php');
exit();

