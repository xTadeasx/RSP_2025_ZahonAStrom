# Kontext projektu RSP_2025_ZáhonAStrom

Tento souhrn slouží jako rychlá orientace, co se kde děje.

## Struktura a účel složek
- `Backend/` – business logika, kontrolery, middleware, emaily.
- `Database/` – připojení k DB (`db.php`), CRUD wrappery (`dataControl.php`), dumpy/schema.
- `Frontend/` – stránky (views), společné include soubory (`Include/`), statika (`Assets/`).
- `context/` – dokumentace (detail viz existující 00–07 soubory).
- `downloads/` – nahrané soubory článků.
- `vendor/` – Composer závislosti (PHPMailer).

## Databáze (MariaDB, utf8mb4_czech_ci)
Hlavní tabulky: `users` (má i `password_temp`), `users_roles`, `posts`, `workflow`, `post_assignments`, `post_reviews`, `notifications`, `chats`, `chat_messages`, `system_logs`.
- Výchozí role ID 1–6: Admin, Šéfredaktor, Recenzent, Redaktor, Autor, Čtenář.
- Login ověřuje `password`, při resetu se ukládá dočasný hash do `password_temp` a login ho také přijme.

## Klíčové backend soubory
- `Backend/login.php` – obsluhuje POST `action`:
  - `login`: volá `validateUser`, nastaví session a role.
  - `register`: volá `registerUser`.
  - `reset_password`: volá `sendEmailResetPassword`.
- `Backend/sendEmail.php` – PHPMailer; `sendEmailResetPassword` generuje 4místný kód, hash do `users.password_temp`, pošle na email.
- `Backend/notAccess.php` – middleware kontroly přihlášení; používán na chráněných stránkách.
- `Backend/userControl.php` – editace uživatele, logout, žádost o autora.
- `Backend/postControl.php` – CRUD článků, přiřazení recenzentů, workflow změny.
- `Backend/reviewControl.php` – vytváření/úprava recenzí článků.
- `Backend/chatControl.php` – soukromé zprávy, vytváření konverzací a ukládání zpráv.
- `Backend/notificationControl.php` – čtení/mazání notifikací, join na `notifications` + `posts`.

## Databázová vrstva
- `Database/db.php` – mysqli připojení (dbname `RSP`), globální `$conn`.
- `Database/dataControl.php` – CRUD wrappery `insert`, `select`, `update`, `delete`, validace uživatele `validateUser`, registrace `registerUser`, seed rolí `createUserRoles`.

## Frontend vrstva
- `Frontend/Include/bootstrap.php` – start session, flash messages, funkce `e()` pro escape, základ HTML.
- `Frontend/Include/header.php` / `footer.php` – layout, navigace, user menu.
- Hlavní stránky: `Frontend/index.php` (chat + novinky), `login.php` (login/register/reset), `user.php` (správa účtu), `article.php`, `authors.php`, `board.php`, `archive.php`, `articles_overview.php`, `edit_article.php`, `review_article.php`, `notifications.php`, `chat.php`.
- Statika: `Frontend/Assets/CSS/main.css`, `Frontend/Assets/main.js` (např. auto-hide alertů).

## Bezpečnost – stav
- Implementováno: bcrypt hesla, prepared statements, XSS escape, základní session check.
- Chybí/zlepšit: CSRF, rate limiting, silnější reset (token + expirace), pevnější validace vstupů, bezpečné DB cred (.env), HTTPS/CSP/session headers, bezpečný upload souborů.

## Typické toky
- Přihlášení: Frontend `login.php` form → Backend `login.php` (action=login) → `validateUser` → session → redirect `Frontend/index.php`.
- Reset hesla: Frontend `login.php` form (email) → Backend `login.php` (action=reset_password) → `sendEmailResetPassword` (uloží hash do `password_temp`, pošle kód).
- Vytvoření článku: Frontend `clanek.php` → Backend `postControl.php` (action=create_post) → `insert` do `posts`, uložení souboru do `downloads/`.
- Recenze: Frontend `review_article.php` → Backend `reviewControl.php` → `post_reviews`, změny stavu a assignment.
- Chat: Frontend `chat.php`/`index.php` → Backend `chatControl.php` → `chats` + `chat_messages`.
- Notifikace: `notificationControl.php` joinuje `notifications` s `posts`, umí označit/přečíst/smazat.

## Důležité poznámky
- Připojení DB používá jméno `RSP`, dump je `rsp` (pozor na case).
- Hesla pro mail jsou v `hesla.php`/`sendEmail.php` (citlivé).
- Uploadované soubory jsou v `downloads/`.
- Při práci s chráněnými stránkami vždy includovat `Backend/notAccess.php`.

