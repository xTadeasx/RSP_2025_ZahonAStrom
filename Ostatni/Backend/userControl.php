<?php
require_once __DIR__ . '/notAccess.php'; // !!! KOPÍROVAT DO KAŽDÉ CHRÁNĚNÉ STRÁNKY !!!
require_once __DIR__ . '/../Database/dataControl.php'; // Připojení k DB a funkce pro práci s daty 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($_POST['action'] === 'edit_user') {
        $id = $_SESSION['user_id'];
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        // Ověření, že uživatel je přihlášen a má právo upravovat účet
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== $id) {
            $_SESSION['error'] = "Nemáte oprávnění upravovat tento účet.";
            return false;
        }

        // Aktualizace uživatelských údajů v databázi
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $result = update('users', ['username' => $username, 'password' => $hashedPassword], "id = $id");
        if ($result) {
            $_SESSION['success'] = "Účet byl úspěšně upraven.";
            return true;
        } else {
            $_SESSION['error'] = "Došlo k chybě při úpravě účtu.";
            return false;
        }
    }

    if($_POST['action'] === 'logOut') {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['success'] = "Účet byl úspěšně odhlášen.";
        header('Location: ../Frontend/index.php');
    }

}
?>