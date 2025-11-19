# PHP funkce a API

## 📚 Database operations (dataControl.php)

### insert($data, $table)
Vložení nového záznamu do databáze

**Parametry:**
- `$data` (array): Associativní pole sloupec => hodnota
- `$table` (string): Název tabulky

**Návratová hodnota:**
- `bool`: true při úspěchu, false při chybě

**Příklad:**
```php
$success = insert([
    'username' => 'testuser',
    'password' => password_hash('heslo123', PASSWORD_BCRYPT),
    'email' => 'test@example.com',
    'phone' => '123456789',
    'role_id' => 6
], 'users');
```

**Implementace:**
- Používá prepared statements
- Automatické escapování hodnot
- Podporuje všechny datové typy jako string

---

### select($table, $columns = "*", $where = "")
Výběr záznamů z databáze

**Parametry:**
- `$table` (string): Název tabulky
- `$columns` (string): Sloupce k výběru (default: "*")
- `$where` (string): WHERE podmínka (optional)

**Návratová hodnota:**
- `array`: Pole asociativních polí s řádky

**Příklad:**
```php
// Všechny uživatele
$users = select('users');

// Uživatelé s rolí
$editors = select('users', '*', "role_id = 4");

// Konkrétní sloupce
$usernames = select('users', 'username, email');

// Specifický uživatel
$user = select('users', '*', "username = 'jahoda'")[0];
```

**Poznámka:**
- Nevrací prázdné pole při 0 výsledcích
- Používá SQL WHERE syntaxi
- SQL injection prevention zajištěno databází

---

### update($table, $data, $where)
Aktualizace záznamů v databázi

**Parametry:**
- `$table` (string): Název tabulky
- `$data` (array): Associativní pole sloupec => nová hodnota
- `$where` (string): WHERE podmínka

**Návratová hodnota:**
- `bool`: true při úspěchu

**Příklad:**
```php
$success = update('users', [
    'email' => 'novy@email.com',
    'phone' => '987654321'
], "username = 'jahoda'");
```

**Implementace:**
- Prepared statements s bind_param
- Podporuje více sloupců najednou

---

### delete($table, $where)
Smazání záznamů z databáze

**Parametry:**
- `$table` (string): Název tabulky
- `$where` (string): WHERE podmínka

**Návratová hodnota:**
- `bool`: true při úspěchu

**Příklad:**
```php
$success = delete('posts', "id = 5");
```

**Poznámka:**
- POZOR: Není ochrana před smazáním všech řádků
- Vždy zkontrolovat WHERE podmínku

---

### validateUser($username, $password)
Ověření přihlašovacích údajů

**Parametry:**
- `$username` (string): Uživatelské jméno
- `$password` (string): Heslo (plain text)

**Návratová hodnota:**
- `bool`: true pokud údaje platné

**Příklad:**
```php
if (validateUser('jahoda', 'heslo123')) {
    // Přihlášení úspěšné
}
```

**Implementace:**
- Prepared statements
- `password_verify()` pro bezpečné ověření
- Neznalost existence uživatele kvůli security

---

### registerUser($username, $password, $email = null, $phone = null)
Registrace nového uživatele

**Parametry:**
- `$username` (string): Uživatelské jméno
- `$password` (string): Heslo (plain text)
- `$email` (string|null): Email
- `$phone` (string|null): Telefon

**Návratová hodnota:**
- `bool`: false pokud username existuje, true při úspěchu

**Příklad:**
```php
if (registerUser('novyuser', 'heslo456', 'novy@email.com', '123456')) {
    // Registrace úspěšná
} else {
    // Username již existuje
}
```

**Implementace:**
- Kontrola existence username
- Hashování hesla pomocí bcrypt
- Default role_id = 1 (Čtenář)
- Optional email a phone

---

### createUserRoles()
Vytvoření základních rolí v systému

**Parametry:** Žádné

**Návratová hodnota:**
- Žádná (vkládá data do DB)

**Příklad:**
```php
createUserRoles(); // Vytvoří 6 rolí
```

**Vytvořené role:**
1. Administrátor
2. Šéfredaktor
3. Recenzent
4. Redaktor
5. Autor
6. Čtenář

---

## 📧 Email functions (sendEmail.php)

### sendEmail($to, $subject, $text)
Odeslání obecného emailu

**Parametry:**
- `$to` (string): Email příjemce
- `$subject` (string): Předmět
- `$text` (string): Text zprávy

**Návratová hodnota:**
- `bool`: true při úspěchu

**Příklad:**
```php
sendEmail('admin@example.com', 'Nový článek', 'Byl odevzdán nový článek.');
```

**Konfigurace:**
- SMTP: Gmail
- From: rspzahonastrom@gmail.com
- Port: 587 (STARTTLS)
- HTML: false (plain text)

---

### sendEmailResetPassword($to)
Odeslání emailu s novým heslem

**Parametry:**
- `$to` (string): Email uživatele

**Návratová hodnota:**
- Žádná (echo/print místo return)

**Příklad:**
```php
sendEmailResetPassword('user@example.com');
// Vygeneruje 4-místný kód a pošle na email
```

**Implementace:**
- Generuje náhodný 4-místný kód (1000-9999)
- Hashuje kód pomocí bcrypt
- Aktualizuje heslo v databázi
- Odesílá plain text email

**Bezpečnostní upozornění:**
- Kód není case-sensitive
- Kód je čistě číselný
- Žádná expirace kódu

---

## 🔒 Authentication functions (login.php)

### Login flow
```php
// POST action = 'login'
if (validateUser($username, $password)) {
    $_SESSION['user'] = [
        'username' => $username,
        'id' => $userId,
        'email' => $email,
        'phone' => $phone
    ];
    $_SESSION['success'] = "Přihlášení úspěšné.";
    header('Location: ../Frontend/index.php');
}
```

### Register flow
```php
// POST action = 'register'
if (registerUser($username, $password, $email, $phone)) {
    $_SESSION['success'] = "Registrace úspěšná.";
    header('Location: ../Frontend/login.php');
}
```

### Password reset flow
```php
// POST action = 'reset_password'
sendEmailResetPassword($email);
header('Location: ../Frontend/login.php');
```

---

## 👤 User control functions (userControl.php)

### Edit user
```php
// POST action = 'edit_user'
update('users', [
    'username' => $username,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'email' => $email,
    'phone' => $phone
], "id = $id");

$_SESSION['user'] = [...]; // Aktualizace session
```

### Logout
```php
// POST action = 'logOut'
session_unset();
session_destroy();
session_start();
$_SESSION['success'] = "Odhlášení úspěšné.";
header('Location: ../Frontend/index.php');
```

### Writer registration request
```php
// POST action = 'writerRegister'
sendEmail('rspzahonastrom@gmail.com', 'Žádost o autora', $text);
// Žádost o pozici autora
```

---

## 🎨 Helper functions (bootstrap.php)

### e($value)
Escape HTML entities (XSS prevention)

**Parametry:**
- `$value` (string): Hodnota k escapování

**Návratová hodnota:**
- `string`: Escapovaná hodnota

**Příklad:**
```php
echo e($userInput); // <script> → &lt;script&gt;
```

**Implementace:**
```php
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

---

## 🛡️ Security functions (notAccess.php)
## 💬 Chat funkce (chatControl.php)

### Odeslání zprávy
```php
// POST action = 'send_message'
require_once __DIR__ . '/../Backend/chatControl.php';
```

**Logika:**
- Ověří přihlášení (`notAccess.php`)
- Validuje, že příjemce existuje a není shodný s odesílatelem
- Najde nebo založí záznam v `chats` (unikátní dvojice uživatelů)
- Vloží zprávu do `chat_messages` (prepared statements)
- Přesměruje zpět na `Frontend/index.php?chat_with={id}`

**Data:**
- `chat_messages.chat_id` – reference na konverzaci
- `sender_id` / `receiver_id`
- `message` – text zprávy
- `is_read` – nastaveno na `0`, značí nepřečtené

### Access control
```php
session_start();
if (!isset($_POST['password'])) {
    if (!isset($_POST['email'])) {
        if (!isset($_SESSION['user']['username'])) {
            $_SESSION['error'] = "Musíte být přihlášeni.";
            header("Location: ../Frontend/index.php");
            exit();
        }
    }
}
```

**Použití:**
```php
require_once __DIR__ . '/../Backend/notAccess.php';
```

**Logika:**
1. Kontrola POST dat (login/register/forgot)
2. Kontrola session
3. Redirect pokud není přihlášen

---

## 📊 Database connection (db.php)

### Global connection
```php
$conn = new mysqli($servername, $username, $password, $database);
```

**Konfigurace:**
- Server: localhost
- User: root
- Password: "" (prázdné)
- Database: RSP

**Poznámka:**
- Neshoda: SQL používá `rsp`, connection `RSP`

---

## 🔄 Session management

### Flash messages
```php
// Nastavení
$_SESSION['success'] = "Operace úspěšná.";
$_SESSION['error'] = "Chyba v operaci.";

// Zobrazení (bootstrap.php)
$__flashError = $_SESSION['error'] ?? null;
$__flashSuccess = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

// HTML
<div class="alert alert-success"><?= e($__flashSuccess) ?></div>
<div class="alert alert-error"><?= e($__flashError) ?></div>
```

### Auto-hide (JavaScript)
```javascript
document.querySelectorAll('.alert[data-auto-hide]')
    .forEach((el) => setTimeout(() => el.classList.add('hide'), 2600));
```

---

## 📝 Best practices

### Prepared statements
Vždy používat prepared statements pro SQL dotazy
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

### Input validation
```php
$username = $_POST['username'] ?? '';
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
```

### Error handling
```php
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
```

### Security headers
```php
header('Location: ../Frontend/index.php');
exit();
```

---

## 🧪 Testing functions

### Example queries (Example/index.php)
```php
// Insert
insert(['name' => 'John', 'surname' => 'Doe'], 'users');

// Select
$users = select('users');
$readers = select('users_roles', '*', "role = 'Čtenář'");

// Update
update('users', ['surname' => 'Smith'], "name = 'John'");

// Delete
delete('users', "name = 'John'");
```

