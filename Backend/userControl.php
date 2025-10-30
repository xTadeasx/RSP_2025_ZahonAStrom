<?php
require_once __DIR__ . '/notAccess.php'; // !!! KOPÍROVAT DO KAŽDÉ CHRÁNĚNÉ STRÁNKY !!!
require_once __DIR__ . '/../Database/dataControl.php'; // Připojení k DB a funkce pro práci s daty 
require_once __DIR__ . '/sendEmail.php'; // Funkce pro odesílání emailů


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'edit_user':
            $id = $_SESSION['user']['id'];
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            // Ověření, že uživatel je přihlášen a má právo upravovat účet
            if (!isset($_SESSION['user']['id']) || $_SESSION['user']['id'] !== $id) {
                $_SESSION['error'] = "Nemáte oprávnění upravovat tento účet.";
            }

            // Aktualizace uživatelských údajů v databázi
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $result = update('users', ['username' => $username, 'password' => $hashedPassword, 'email' => $email, 'phone' => $phone], "id = $id");
            if ($result) {
                $_SESSION['success'] = "Účet byl úspěšně upraven.";
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
            } else {
                $_SESSION['error'] = "Došlo k chybě při úpravě účtu.";
            }
            header('Location: ../Frontend/user.php');
            break;
        case 'logOut':
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['success'] = "Účet byl úspěšně odhlášen.";
            header('Location: ../Frontend/index.php');
            break;
        case 'writerRegister':
            $text = trim($_POST['text'] ?? '');
            if ($text === '' || mb_strlen($text) < 10) {
                $_SESSION['error'] = 'Zadejte prosím důvod (min. 10 znaků).';
                header('Location: ../Frontend/user.php');
                break;
            }
            $ok = sendEmail('rspzahonastrom@gmail.com', 'Žádost o pozici autora', nl2br($text));
            if ($ok) {
                $_SESSION['success'] = 'Žádost o pozici autora byla odeslána.';
            } else {
                $_SESSION['error'] = 'Nepodařilo se odeslat e‑mail. Zkuste to prosím později.';
            }
            header('Location: ../Frontend/user.php');
            break;
        default:
            $_SESSION['error'] = "Neznámá akce: " . $_POST['action'];
            header('');
            break;
    }


}
?>