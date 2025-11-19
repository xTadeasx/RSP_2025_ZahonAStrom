# Přehled projektu RSP_2025_ZáhonAStrom

## 📝 Základní informace
- **Název**: Záhon a Strom - Vědecký časopis
- **Předmět**: RSP (Rizika související s programováním)
- **Instituce**: VŠPJ (Vysoká škola polytechnická Jihlava)
- **Semestr**: 3. semestr
- **Rok**: 2025

## 🎯 Účel projektu
Vývoj webové aplikace pro vědecký časopis s následujícími funkcemi:
- Publikování recenzovaných článků
- Systém uživatelských rolí (Administrátor, Šéfredaktor, Recenzent, Redaktor, Autor, Čtenář)
- Workflow pro recenzní řízení
- Registrace a přihlášení uživatelů
- Archiv článků

## 🏗️ Architektura projektu

### Frontend
- **Framework**: Čistý PHP + Bootstrap 5
- **Styling**: Vlastní CSS (main.css)
- **JavaScript**: Vanilla JS (main.js)
- **Hlavní stránka**: `Frontend/index.php`
- **Entry point**: `index.php` (přesměrování)

### Backend
- **Technologie**: PHP 8.2.12
- **Databáze**: MariaDB 10.4.32
- **ORM**: Vlastní abstrakce (dataControl.php)
- **Email**: PHPMailer 7.0 (Gmail SMTP)

### Databáze
- **Název**: `rsp`
- **Collation**: utf8mb4_czech_ci
- **Tabulky**:
  - `users` - Uživatelé
  - `users_roles` - Role uživatelů
  - `posts` - Články
  - `workflow` - Workflow stavy

## 📁 Struktura projektu

```
RSP_2025_ZahonAStrom/
├── Dokumenty/              # Obchodní dokumentace
├── Grafika/                # Wireframy, ERD, BMC
├── Ostatni/                # Hlavní kód
│   ├── Backend/            # Backend logika
│   ├── Database/           # DB schema a funkce
│   ├── Frontend/           # Frontend stránky
│   ├── Example/            # Příklady použití
│   ├── context/            # Dokumentace projektu
│   ├── vendor/             # Composer závislosti
│   ├── composer.json       # PHP závislosti
│   ├── index.php          # Entry point
│   └── hesla.php          # Citlivé údaje
└── README.md               # Dokumentace projektu
```

## 🔐 Autentizace a autorizace

### Role
1. **Administrátor** (ID: 1)
2. **Šéfredaktor** (ID: 2)
3. **Recenzent** (ID: 3)
4. **Redaktor** (ID: 4)
5. **Autor** (ID: 5)
6. **Čtenář** (ID: 6) - výchozí role

### Session management
- Session spouštěna v `bootstrap.php`
- Flash messages: `$_SESSION['success']`, `$_SESSION['error']`
- Uživatelské data: `$_SESSION['user']`

## 🗄️ Databázové schéma

### Hlavní tabulky

#### `users` - Uživatelé
```sql
- id (PK, AUTO_INCREMENT)
- username (varchar 255)
- password (varchar 255, hashed)
- email (varchar 255, NOT NULL)
- phone (varchar 255, NOT NULL)
- role_id (FK -> users_roles.id)
- created_at, updated_at, created_by, updated_by
```

#### `users_roles` - Role uživatelů
```sql
- id (PK, AUTO_INCREMENT)
- role (varchar 255)
- created_at, updated_at, created_by, updated_by
```
**Role:** Administrátor, Šéfredaktor, Recenzent, Redaktor, Autor, Čtenář

#### `posts` - Články
```sql
- id (PK, AUTO_INCREMENT)
- title (varchar 255)
- body (text)
- abstract (text)
- keywords (varchar 500)
- topic (varchar 255)
- authors (text)
- file_path (varchar 500)
- user_id (FK -> users.id)
- state (FK -> workflow.id)
- created_at, updated_at, created_by, updated_by
- published_at (datetime)
```

#### `workflow` - Workflow stavy
```sql
- id (PK, AUTO_INCREMENT)
- state (varchar 255)
- created_at, updated_at, created_by, updated_by
```
**Stavy:** Nový, Odeslaný, V recenzi, Schváleno recenzenty, Vrácen k úpravám, Schválen, Zamítnut

#### `post_assignments` - Přiřazení recenzentů
```sql
- id (PK, AUTO_INCREMENT)
- post_id (FK -> posts.id)
- reviewer_id (FK -> users.id)
- assigned_by (FK -> users.id)
- assigned_at (datetime)
- due_date (date)
- status (varchar 50)
```

#### `post_reviews` - Recenze článků
```sql
- id (PK, AUTO_INCREMENT)
- post_id (FK -> posts.id)
- reviewer_id (FK -> users.id)
- score_actuality (tinyint 1-5)
- score_originality (tinyint 1-5)
- score_language (tinyint 1-5)
- score_expertise (tinyint 1-5)
- comment (text)
- created_at, updated_at
```

#### `notifications` - Notifikace
```sql
- id (PK, AUTO_INCREMENT)
- user_id (FK -> users.id)
- type (varchar 50)
- message (text)
- created_at, read_at
- related_post_id (FK -> posts.id)
```

#### `system_logs` - Systémové logy
```sql
- id (PK, AUTO_INCREMENT)
- user_id (FK -> users.id)
- event_type (varchar 50)
- level (varchar 20)
- message (text)
- created_at
```

**Detailní schéma:** Viz `02_DATABAZE_SCHÉMA.md`

## 🎨 Stylování

### Barvy (CSS variables)
- `--bg`: #f7f6f8
- `--text`: #1e1b20
- `--muted`: #6b6570
- `--brand`: #4e1835 (tmavě vínová)
- `--brand-2`: #6d2a4c (světlejší)
- `--surface`: #ffffff
- `--border`: #e6e2e8
- `--success`: #2e7d32
- `--error`: #b00020

### Komponenty
- Navbar (Bootstrap)
- Cards (Grid layout)
- Forms (Custom styling)
- Alerts (Auto-hide flash messages)
- Feature cards
- Member cards
- Prose typography

## 📧 Email funkce

### Konfigurace
- **SMTP**: Gmail
- **Port**: 587 (STARTTLS)
- **From**: rspzahonastrom@gmail.com
- **Heslo**: uloženo v `hesla.php`

### Funkce
- `sendEmail($to, $subject, $text)` - Obecné odeslání
- `sendEmailResetPassword($to)` - Obnova hesla

## 🛠️ Základní funkce (dataControl.php)

- `insert($data, $table)` - Vložení záznamu
- `select($table, $columns, $where)` - Výběr záznamů
- `update($table, $data, $where)` - Aktualizace
- `delete($table, $where)` - Smazání
- `validateUser($username, $password)` - Ověření přihlášení
- `registerUser($username, $password, $email, $phone)` - Registrace
- `createUserRoles()` - Vytvoření rolí

## 🔒 Bezpečnost

### Implementováno
- Password hashing (bcrypt)
- Prepared statements (SQL injection prevention)
- Session management
- Input validation
- XSS protection (htmlspecialchars)

### V `notAccess.php`
- Middleware pro přihlášení
- Přesměrování nepřihlášených uživatelů
- Session ověření

## 📄 Hlavní stránky Frontend

1. **index.php** - Domovská stránka s nejnovějšími články
2. **login.php** - Přihlášení/Registrace/Obnova hesla
3. **user.php** - Správa účtu uživatele
4. **article.php** - Detail článku
5. **author.php** - Články autora
6. **authors.php** - Informace pro autory
7. **board.php** - Redakční rada
8. **archive.php** - Archiv článků

## 🚀 Spuštění projektu

### Požadavky
- PHP 8.2+
- MariaDB 10.4+
- Composer

### Instalace
1. Importovat databázi: `Database/1,db.sql`
2. Nastavit přístupové údaje v `Database/db.php`
3. Spustit: `composer install`
4. Otevřít: `http://localhost/Ostatni/` nebo `http://localhost/Ostatni/Frontend/`

## 📌 TODOs a poznámky

### Implementováno ✅
- ✅ Systém autentizace
- ✅ Registrace uživatelů
- ✅ Flash messages
- ✅ Bootstrap integrace
- ✅ Email odesílání
- ✅ Databázové funkce
- ✅ Role management
- ✅ Obnova hesla
- ✅ Recenzní workflow systém
- ✅ Správa článků (CRUD)
- ✅ Nahrávání souborů (PDF, DOC, DOCX)
- ✅ Přiřazování recenzentů k článkům
- ✅ Automatické změny workflow stavů
- ✅ Přehled článků podle rolí
- ✅ Filtrování článků (stav, název)
- ✅ Editace článků
- ✅ Recenze článků (hodnocení 1-5, komentáře)
- ✅ Stahování souborů článků
- ✅ File upload security
- ✅ Input validation
- ✅ Role-based access control (RBAC)
- ✅ Interní soukromé zprávy mezi uživateli (chat)

### TODO 🔴
- [ ] CSRF tokeny
- [ ] Rate limiting
- [ ] Kategorie článků
- [ ] Vyhledávání (full-text)
- [ ] Paginace
- [ ] Admin panel
- [ ] Avatary uživatelů
- [ ] Notifikace uživatelů
- [ ] Statistiky a reporty
- [ ] Export článků (PDF)
- [ ] Vylepšit workflow (schválení po recenzi)
- [ ] Database credentials do .env

## 👥 Tým
- Petr Novák - Project Manager
- Hynek Bárta - Šéfredaktor
- Ladislav Šlapal - Redaktor
- Petr Lippert - Redaktor
- Vít Nováček - Autor
- Daniel Bartoš - Autor
- Tadeáš Jahoda - Developer

## 📚 Odkazy
- Moodle: https://moodle.vspj.cz/course/view.php?id=203424
- Teams: https://teams.microsoft.com/
- ScrumDesk: https://app.scrumdesk.com/#/projects
- Excel: SharePoint

## 🔗 Důležité poznámky
- Heslo email je uloženo v `hesla.php` a `sendEmail.php` (citlivé - Seznam.cz)
- Databáze se jmenuje `rsp` ale připojení je na `RSP`
- Články jsou načítány z databáze (`posts` tabulka)
- Workflow stavy se automaticky mění při určitých akcích
- Recenzní systém je plně funkční
- Soubory článků se ukládají do `downloads/` adresáře

## 📚 Dokumentace
- **Workflow a komunikace:** `context/06_WORKFLOW_KOMUNIKACE.md`
- **Databázové schéma:** `context/02_DATABAZE_SCHÉMA.md`
- **Architektura:** `context/01_ARCHITEKTURA_TECHNOLOGIE.md`
- **PHP funkce:** `context/03_PHP_FUNKCE.md`
- **Frontend komponenty:** `context/04_FRONTEND_COMPONENTS.md`
- **Bezpečnost:** `context/05_SECURITY_BEST_PRACTICES.md`

