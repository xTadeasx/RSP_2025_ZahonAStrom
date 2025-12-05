# Databázové schéma

## 📊 Přehled databáze

**Název**: `rsp`  
**Encoding**: utf8mb4_czech_ci  
**Engine**: InnoDB  
**Server**: MariaDB 10.4.32

## 🗂️ Tabulky

### 1. `users` - Uživatelé systému

| Sloupec | Typ | Null | Klíč | Default | Popis |
|---------|-----|------|------|---------|-------|
| id | int(11) | NO | PRIMARY | AUTO_INCREMENT | Primární klíč |
| username | varchar(255) | YES | - | NULL | Uživatelské jméno (unique) |
| password | varchar(255) | YES | - | NULL | Hash hesla (bcrypt) |
| email | varchar(255) | NO | - | NULL | Email adresa |
| phone | varchar(255) | NO | - | NULL | Telefonní číslo |
| role_id | int(11) | YES | FK | NULL | Odkaz na users_roles |
| created_at | datetime | YES | - | NULL | Datum vytvoření |
| updated_at | datetime | YES | - | NULL | Datum poslední úpravy |
| created_by | int(11) | YES | FK | NULL | Vytvořil (user_id) |
| updated_by | int(11) | YES | FK | NULL | Upravil (user_id) |

**Foreign Keys:**
- `role_id` → `users_roles.id`
- `created_by` → `users.id`
- `updated_by` → `users.id`

**Indexy:**
- PRIMARY (id)
- KEY (role_id)

**Constraints:**
- Email required
- Phone required

---

### 2. `users_roles` - Role uživatelů

| Sloupec | Typ | Null | Klíč | Default | Popis |
|---------|-----|------|------|---------|-------|
| id | int(11) | NO | PRIMARY | AUTO_INCREMENT | Primární klíč |
| role | varchar(255) | YES | - | NULL | Název role |
| created_at | datetime | YES | - | NULL | Datum vytvoření |
| updated_at | datetime | YES | - | NULL | Datum poslední úpravy |
| created_by | int(11) | YES | FK | NULL | Vytvořil (user_id) |
| updated_by | int(11) | YES | FK | NULL | Upravil (user_id) |

**Foreign Keys:**
- `created_by` → `users.id`
- `updated_by` → `users.id`

**Indexy:**
- PRIMARY (id)
- KEY (updated_by)
- KEY (created_by)

**Přednastavené role:**
1. Administrátor
2. Šéfredaktor
3. Recenzent
4. Redaktor
5. Autor
6. Čtenář

---

### 3. `posts` - Články

| Sloupec | Typ | Null | Klíč | Default | Popis |
|---------|-----|------|------|---------|-------|
| id | int(11) | NO | PRIMARY | AUTO_INCREMENT | Primární klíč |
| title | varchar(255) | YES | - | NULL | Název článku |
| body | text | YES | - | NULL | Obsah článku |
| user_id | int(11) | YES | FK | NULL | Autor článku |
| state | int(11) | YES | FK | NULL | Stav ve workflow |
| created_at | datetime | YES | - | NULL | Datum vytvoření |
| updated_at | datetime | YES | - | NULL | Datum poslední úpravy |
| created_by | int(11) | YES | FK | NULL | Vytvořil (user_id) |
| updated_by | int(11) | YES | FK | NULL | Upravil (user_id) |

**Foreign Keys:**
- `state` → `workflow.id`
- `user_id` → `users.id`
- `created_by` → `users.id`
- `updated_by` → `users.id`

**Indexy:**
- PRIMARY (id)
- KEY (state)
- KEY (user_id)
- KEY (created_by)
- KEY (updated_by)

---

### 4. `workflow` - Workflow stavy

| Sloupec | Typ | Null | Klíč | Default | Popis |
|---------|-----|------|------|---------|-------|
| id | int(11) | NO | PRIMARY | AUTO_INCREMENT | Primární klíč |
| state | varchar(255) | YES | - | NULL | Název stavu |
| created_at | datetime | YES | - | NULL | Datum vytvoření |
| updated_at | datetime | YES | - | NULL | Datum poslední úpravy |
| created_by | int(11) | YES | FK | NULL | Vytvořil (user_id) |
| updated_by | int(11) | YES | FK | NULL | Upravil (user_id) |

**Foreign Keys:**
- `created_by` → `users.id`
- `updated_by` → `users.id`

**Indexy:**
- PRIMARY (id)
- KEY (created_by)
- KEY (updated_by)

**Navrhované stavy:**
- Koncept
- Na recenzi
- V recenzi
- Požaduje úpravy
- Schváleno
- Publikováno
- Zamítnuto
- Archivováno

---

### 5. `chats` - Soukromé konverzace

| Sloupec | Typ | Null | Klíč | Default | Popis |
|---------|-----|------|------|---------|-------|
| id | int(11) | NO | PRIMARY | AUTO_INCREMENT | Primární klíč |
| user_one_id | int(11) | NO | FK | - | Jeden z účastníků (menší id) |
| user_two_id | int(11) | NO | FK | - | Druhý účastník (větší id) |
| created_at | datetime | YES | - | CURRENT_TIMESTAMP | Datum založení konverzace |

**Foreign Keys:**
- `user_one_id` → `users.id` (ON DELETE CASCADE)
- `user_two_id` → `users.id` (ON DELETE CASCADE)

**Indexy:**
- PRIMARY (id)
- UNIQUE (`user_one_id`, `user_two_id`) – zajišťuje jednu konverzaci mezi dvojicí

**Poznámky:**
- Před vložením se id účastníků seřadí (`min/max`), aby unikátní index fungoval.

---

### 6. `chat_messages` - Zprávy v konverzacích

| Sloupec | Typ | Null | Klíč | Default | Popis |
|---------|-----|------|------|---------|-------|
| id | int(11) | NO | PRIMARY | AUTO_INCREMENT | Primární klíč |
| chat_id | int(11) | NO | FK | - | Odkaz na tabulku `chats` |
| sender_id | int(11) | NO | FK | - | Odesílatel zprávy |
| receiver_id | int(11) | NO | FK | - | Příjemce zprávy |
| message | text | NO | - | - | Obsah zprávy |
| is_read | tinyint(1) | YES | - | 0 | Příznak přečtení |
| created_at | datetime | YES | - | CURRENT_TIMESTAMP | Datum odeslání |

**Foreign Keys:**
- `chat_id` → `chats.id` (ON DELETE CASCADE)
- `sender_id` → `users.id` (ON DELETE CASCADE)
- `receiver_id` → `users.id` (ON DELETE CASCADE)

**Indexy:**
- PRIMARY (id)
- KEY (`chat_id`)
- KEY (`sender_id`)
- KEY (`receiver_id`)

---

## 🔗 ERD vztahy

```
users
  ├─→ users_roles (role_id)
  ├─→ users (created_by)
  └─→ users (updated_by)

users_roles
  ├─→ users (created_by)
  └─→ users (updated_by)

posts
  ├─→ workflow (state)
  ├─→ users (user_id)
  ├─→ users (created_by)
  └─→ users (updated_by)

chats
  ├─→ users (user_one_id)
  └─→ users (user_two_id)

chat_messages
  ├─→ chats (chat_id)
  ├─→ users (sender_id)
  └─→ users (receiver_id)

workflow
  ├─→ users (created_by)
  └─→ users (updated_by)
```

## 📝 Audit columns

Všechny tabulky obsahují audit sloupce:
- `created_at` - Kdy byl záznam vytvořen
- `updated_at` - Kdy byl naposled upraven
- `created_by` - Kdo záznam vytvořil
- `updated_by` - Kdo naposled upravil

## 🔐 Bezpečnost

### Hashování hesel
```php
password_hash($password, PASSWORD_BCRYPT)
password_verify($password, $hashedPassword)
```

### SQL Injection prevention
```php
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
```

### XSS prevention
```php
htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
```

## 📊 Příklady dotazů

### Select všechny uživatele
```sql
SELECT * FROM users;
```

### Select uživatele s rolí
```sql
SELECT u.*, ur.role 
FROM users u 
LEFT JOIN users_roles ur ON u.role_id = ur.id;
```

### Select články s autorem
```sql
SELECT p.*, u.username as author 
FROM posts p 
LEFT JOIN users u ON p.user_id = u.id;
```

### Select články podle stavu
```sql
SELECT p.*, w.state 
FROM posts p 
LEFT JOIN workflow w ON p.state = w.id 
WHERE w.state = 'Schváleno';
```

### Count článků podle autora
```sql
SELECT u.username, COUNT(p.id) as article_count 
FROM users u 
LEFT JOIN posts p ON u.id = p.user_id 
GROUP BY u.id;
```

## 🚀 Inicializace databáze

### Kroky k nastavení
1. Spustit SQL dump: `1,db.sql`
2. Vytvořit databázi
3. Importovat strukturu
4. Vložit výchozí role (pokud nejsou)
5. Vytvořit první admin účet

### SQL pro vytvoření rolí (při prázdné tabulce)
```sql
INSERT INTO users_roles (role) VALUES 
('Administrátor'),
('Šéfredaktor'),
('Recenzent'),
('Redaktor'),
('Autor'),
('Čtenář');
```

### SQL pro vytvoření workflow stavů
```sql
INSERT INTO workflow (state) VALUES 
('Koncept'),
('Na recenzi'),
('V recenzi'),
('Požaduje úpravy'),
('Schváleno'),
('Publikováno'),
('Zamítnuto'),
('Archivováno');
```

## 🔄 Migrace

### Aktuální stav
- Verze: 1.1
- Poslední změna: 18. 11. 2025

### TODO migrace
- Přidat sloupec `category_id` do `posts`
- Přidat tabulku `categories`
- Přidat tabulku `reviews`
- Přidat sloupec `abstract` do `posts`
- Přidat sloupec `keywords` do `posts`
- Přidat tabulku `comments`

## 📈 Optimalizace

### Indexy
- `users.username` (unique - TODO)
- `users.email` (unique - TODO)
- `posts.title` (fulltext - TODO)
- `posts.created_at` (index - TODO)

### Constraints
- `users.username` UNIQUE (TODO)
- `users.email` UNIQUE (TODO)
- `users.password` NOT NULL (TODO)
- `posts.title` NOT NULL (TODO)

## 🧪 Testovací data

### Výchozí uživatelé
```sql
INSERT INTO users (id, username, password, email, phone, role_id) VALUES
(2, 'jahoda', '$2y$10$fEofEot/Ql.I484Sz6GTt.BN2MHP6OugteXcLBGL5aHVPURe6RlNK', '', '', NULL),
(4, 'tadeas', '$2y$10$UUPMB2jRJtoXhH6DLgyNDuBMeL9kqT8IhhN/ck.aGUO04JtAqpU4u', 'jahoda.tadeas@gmail.com', '123123', 1);
```

**Poznámka**: Hesla jsou bcrypt hashované

