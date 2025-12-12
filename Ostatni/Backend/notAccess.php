<?php
// Původní soubor blokoval přístup i k veřejným stránkám.
// V projektu už používáme selektivní kontroly přihlášení v konkrétních skriptech,
// takže zde nebudeme bránit přístupu. Necháváme jen start session.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}