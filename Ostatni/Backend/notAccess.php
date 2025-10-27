<?php
//ověření že pokud uživatel není přihlášen nebo neposílá údaje na přihlášení, nemůže se dostat na tuto stránku

// !!! KOPÍROVAT DO KAŽDÉ CHRÁNĚNÉ STRÁNKY !!!
session_start();
if(!isset($_POST['password'])) {
    if (!isset($_SESSION['user_username'])) {
        $_SESSION['error'] = "Musíte být přihlášeni.";
        header("Location: ../Frontend/index.php");
        exit();
    }
}
// Tadeášovo králoství

?>