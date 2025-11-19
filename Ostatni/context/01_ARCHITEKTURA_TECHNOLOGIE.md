# Architektura a technologie

## 📋 Technologický stack

### Backend
- **PHP**: 8.2.12
- **Framework**: Vlastní MVC/MVP architektura
- **Pattern**: Procedurální s objekty (kompatibilita s Composer)
- **Autoloading**: Composer PSR-4

### Databáze
- **RDBMS**: MariaDB 10.4.32
- **Encoding**: utf8mb4_czech_ci
- **Connection**: mysqli (procedurální wrapper)

### Frontend
- **HTML**: HTML5
- **CSS**: Custom CSS + Bootstrap 5.3.3
- **JavaScript**: Vanilla ES6+
- **Icons**: Bootstrap Icons (CDN)
- **Responsive**: Mobile-first design

### Dependencies (Composer)
- **PHPMailer**: ^7.0 (Odesílání emailů)

## 🏛️ Architektura aplikace

### Struktura složek

```
Ostatni/
├── Backend/               # Business logika
│   ├── login.php         # Autentizace
│   ├── userControl.php   # Správa uživatele
│   ├── sendEmail.php     # Email služba
│   └── notAccess.php     # Middleware
├── Database/             # Databázová vrstva
│   ├── db.php           # Připojení k DB
│   ├── dataControl.php  # CRUD operace
│   └── 1,db.sql        # Schema
├── Frontend/            # Prezentační vrstva
│   ├── Include/        # Layout components
│   │   ├── bootstrap.php
│   │   ├── header.php
│   │   └── footer.php
│   ├── Assets/         # Statické soubory
│   │   ├── CSS/
│   │   └── main.js
│   └── *.php          # Views
├── context/            # Dokumentace projektu
├── vendor/            # Composer packages
└── Example/          # Dokumentace/kódy
```

### Databázové vrstvy

#### 1. Připojení (db.php)
```php
$conn = new mysqli($servername, $username, $password, $database);
```

#### 2. CRUD abstrakce (dataControl.php)
- `insert()` - Vložení s prepared statements
- `select()` - Výběr s WHERE podmínkou
- `update()` - Aktualizace
- `delete()` - Smazání

#### 3. Business logika (Backend/*.php)
- Uživatelské operace
- Validace
- Session management
- Email odesílání

#### 4. Prezentační vrstva (Frontend/*.php)
- HTML rendering
- Formuláře
- Flash messages
- Bootstrap komponenty

## 🔄 Request flow

### Přihlášení uživatele
```
Frontend/login.php (Form)
  ↓ POST
Backend/login.php (Validation)
  ↓
Database/dataControl.php (validateUser)
  ↓
Session setup
  ↓
Frontend/index.php (Redirect)
```

### Zobrazení článku
```
Frontend/article.php (Request)
  ↓
Include/bootstrap.php (Session check)
  ↓
Include/header.php (Layout)
  ↓
Database/db.php (Connection)
  ↓
Frontend/article.php (Load from DB)
  ↓
Frontend/article.php (Render data)
  ↓
Include/footer.php (Close layout)
```

### Vytvoření článku
```
Frontend/clanek.php (Form)
  ↓ POST
Backend/postControl.php (action=create_post)
  ↓ (Validation)
Database/dataControl.php (insert)
  ↓ (File upload)
downloads/ (Save file)
  ↓ (DB insert)
posts table (state = 1 "Nový")
  ↓ (Redirect)
Frontend/user.php (Success message)
```

### Editace článku a přiřazení recenzenta
```
Frontend/articles_overview.php
  ↓ (Click "Editovat")
Frontend/edit_article.php (GET id)
  ↓ (Load article from DB)
  ↓ (Display form)
  ↓ POST
Backend/postControl.php (action=update_post)
  ↓ (Validation)
  ↓ (Update posts)
  ↓ (Assign reviewers → post_assignments)
  ↓ (Auto change state → 3 "V recenzi")
Frontend/edit_article.php (Redirect)
```

### Recenze článku
```
Frontend/articles_overview.php (Recenzent)
  ↓ (Click "Napsat recenzi")
Frontend/review_article.php (GET id)
  ↓ (Check assignment)
  ↓ (Display form)
  ↓ POST
Backend/reviewControl.php (action=create_review)
  ↓ (Validation)
  ↓ (Insert post_reviews)
  ↓ (Update post_assignments.status = 'Recenzováno')
  ↓ (Auto change state → 5 "Vrácen k úpravám")
Frontend/review_article.php (Redirect)
```

## 🛡️ Security layers

### 1. Authentication (notAccess.php)
```php
if (!isset($_SESSION['user']['username'])) {
    $_SESSION['error'] = "Musíte být přihlášeni.";
    header("Location: ../Frontend/index.php");
    exit();
}
```

### 2. Authorization
- Role-based access control
- Session validation
- CSRF (TODO)

### 3. Input sanitization
- `htmlspecialchars()` pro XSS
- Prepared statements pro SQL injection
- Password hashing (bcrypt)

### 4. Email security
- STARTTLS encryption
- Gmail OAuth2 (TODO)
- Rate limiting (TODO)

## 📡 API a komunikace

### Session management
```php
$_SESSION['user'] = [
    'id' => $userId,
    'username' => $username,
    'email' => $email,
    'phone' => $phone
];
```

### Flash messages
```php
$_SESSION['success'] = "Přihlášení bylo úspěšné.";
$_SESSION['error'] = "Neplatné přihlašovací údaje.";
```

### Email service
```php
sendEmail($to, $subject, $text)
sendEmailResetPassword($to)
```

## 🎨 Frontend architektura

### Layout system
- **bootstrap.php**: Spuštění session, flash messages
- **header.php**: Navbar, navigation
- **footer.php**: Patička, copyright

### CSS architektura
- CSS variables pro theming
- Mobile-first responzivní design
- Grid layout pro cards
- Utility classes

### JavaScript
- DOMContentLoaded listeners
- Auto-hide alerts
- Form validace (client-side)
- Dynamic content (TODO)

## 🔌 Databázové připojení

### Konfigurace
```php
$servername = "localhost";
$username = "root";
$password = "";
$database = "RSP";
```

### Connection pooling
- Připojení v `db.php`
- Global `$conn` variable
- Prepared statements vždy

## 📦 Composer workflow

### Installation
```bash
composer install
```

### Dependencies
- **phpmailer/phpmailer**: ^7.0

### Autoloading
- PSR-4 standard
- Vendor autoload v `vendor/autoload.php`

## 🔄 Data flow

### Čtení (Read)
```
SELECT → select() → fetch_assoc() → Array
```

### Zápis (Create)
```
INSERT → insert() → prepared statement → execute()
```

### Aktualizace (Update)
```
UPDATE → update() → prepared statement → execute()
```

### Mazání (Delete)
```
DELETE → delete() → query() → result
```

## 🧩 Komponenty

### Reusable components
- Alert system (flash messages)
- Form builder (TODO)
- Card component
- Member card
- Article card

### Partials
- header.php
- footer.php
- bootstrap.php

## 🔐 Middleware system

### notAccess.php
Kontrola přihlášení pro chráněné stránky

### Použití
```php
require_once __DIR__ . '/../Backend/notAccess.php';
```

### Validace
1. Session start
2. Username check
3. Redirect pokud neplatné

## 📊 State management

### Session storage
- User data
- Flash messages
- Temporary data

### Server state
- Database connection
- Global variables
- Environment config

## 🎯 Design patterns

### Implementované
- **Repository pattern**: dataControl.php
- **Middleware pattern**: notAccess.php
- **MVC**: Separation of concerns
- **Template method**: header/footer

### TODO
- Factory pattern (Email service)
- Observer pattern (Events)
- Strategy pattern (Validation)

## 🚀 Deployment

### Requirements
- PHP 8.2+
- MariaDB 10.4+
- Web server (Apache/Nginx)
- Composer

### Configuration
- Database credentials (db.php)
- Email settings (hesla.php, sendEmail.php)
- Session storage
- Error reporting

### Environment
- Development: localhost
- Production: TODO

## 📈 Performance

### Implementované
- Database indexing
- Prepared statements
- CSS minification (TODO)
- JS minification (TODO)

### TODO
- Caching
- CDN
- Database query optimization
- Lazy loading

## 🔍 Logging & debugging

### Current
- PHP error reporting
- MySQL error messages
- Flash messages

### TODO
- Log files
- Error tracking
- Debug mode
- Performance monitoring

