<?php
require_once __DIR__ . '/notAccess.php'; // !!! KOPÍROVAT DO KAŽDÉ CHRÁNĚNÉ STRÁNKY !!!
require_once __DIR__ . '/../Database/dataControl.php'; // Připojení k DB a funkce pro práci s daty 

// To do: Vytvořit způsob přihlašování uživatele

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if($_POST['action'] === 'login') {
        // Přihlášení uživatele
        if (validateUser($username, $password)) {
            // Úspěšné přihlášení
            session_start();
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "Přihlášení bylo úspěšné.";
            header('Location: ../Frontend/index.php'); // Přesměrování na index
            exit();
        } else {
            // Neuspěšné přihlášení
            $_SESSION['error'] = "Neplatné uživatelské jméno nebo heslo.";
            header('Location: ../Frontend/login.php'); // Přesměrování zpátky na login
        }
    } elseif($_POST['action'] === 'register') {
        // Registrace uživatele
        if (registerUser($username, $password)) {
            // Úspěšná registrace
            $_SESSION['success'] = "Registrace byla úspěšná.";
            header('Location: ../Frontend/login.php'); // Přesměrování zpátky na login
        } else {
            $_SESSION['error'] = "Uživatelské jméno již existuje.";
            header('Location: ../Frontend/login.php'); // Přesměrování zpátky na login
        }
    }
}
?>