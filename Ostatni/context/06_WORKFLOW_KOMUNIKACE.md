# Workflow procesy a komunikace v systému

## 📋 Přehled

Tento dokument popisuje kompletní workflow procesy, komunikaci mezi komponentami a automatizované změny stavů v systému pro správu vědeckého časopisu.

**Poslední aktualizace:** 2025-01-17

---

## 🔄 Workflow stavy článků

### Definice stavů (tabulka `workflow`)

| ID | Název | Popis |
|----|-------|-------|
| 1 | Nový | Článek byl vytvořen autorem |
| 2 | Odeslaný | Článek byl odeslán k recenzi |
| 3 | V recenzi | Článek má přiřazené recenzenty |
| 4 | Schváleno recenzenty | Článek prošel recenzním procesem |
| 5 | Vrácen k úpravám | Článek byl vrácen autorovi k úpravám |
| 6 | Schválen | Článek byl schválen a publikován |
| 7 | Zamítnut | Článek byl zamítnut |

### Automatické změny stavů

#### 1. Vytvoření článku → "Nový" (id = 1)

**Kdy:** Autor vytvoří nový článek (`Frontend/clanek.php` → `Backend/postControl.php`)

**Proces:**
1. Autor vyplní formulář (název, abstrakt, obsah, soubor)
2. Validace: název, obsah a abstrakt jsou povinné
3. Validace souboru: PDF, DOC, DOCX, max 10 MB
4. Upload souboru: bezpečné pojmenování (`uniqid() + sanitize`)
5. Vložení do DB: `posts` tabulka
6. **Automaticky nastaven stav:** `state = 1` ("Nový")

**Kód:**
```php
// Backend/postControl.php - create_post
$workflow = select('workflow', 'id', "state = 'Nový'");
$workflowState = $workflow[0]['id'] ?? null;
$postData['state'] = $workflowState; // = 1
```

---

#### 2. Přiřazení recenzenta → "V recenzi" (id = 3)

**Kdy:** Admin/Šéfredaktor/Redaktor přiřadí recenzenta k článku (`Frontend/edit_article.php` → `Backend/postControl.php`)

**Proces:**
1. Editor otevře formulář pro úpravu článku
2. Vybere recenzenty (checkboxy)
3. Nastaví termín recenze (due_date)
4. Odeslání formuláře
5. Kontrola: zda recenzent už není přiřazen
6. Vložení do `post_assignments`: `status = 'Přiděleno'`
7. **AUTOMATICKY:** Pokud byl přidán nový recenzent, článek se přepne na `state = 3` ("V recenzi")

**Kód:**
```php
// Backend/postControl.php - update_post
if ($newReviewersAdded) {
    // Najdi workflow id pro "V recenzi"
    $workflowStateSql = "SELECT id FROM workflow WHERE state = 'V recenzi'";
    // Aktualizuj posts.state = 3
    $updatePostStateSql = "UPDATE posts SET state = ?, updated_at = ?, updated_by = ? WHERE id = ?";
}
```

**Důležité:**
- Změna stavu se provede **pouze pokud** byl přidán **nový** recenzent
- Pokud recenzent už je přiřazen, stav se nemění
- Aktualizuje se `updated_at` a `updated_by`

---

#### 3. Odeslání recenze → "Vrácen k úpravám" (id = 5)

**Kdy:** Recenzent odešle recenzi (`Frontend/review_article.php` → `Backend/reviewControl.php`)

**Proces:**
1. Recenzent otevře článek k recenzi (pouze přiřazené články)
2. Vyplní hodnocení: aktualita, originalita, jazyk, odbornost (1-5)
3. Přidá komentář
4. Odeslání recenze
5. Validace: všechny skóre 1-5, komentář povinný
6. Vložení do `post_reviews`
7. Aktualizace `post_assignments.status = 'Recenzováno'`
8. **AUTOMATICKY:** Článek se přepne na `state = 5` ("Vrácen k úpravám")

**Kód:**
```php
// Backend/reviewControl.php - create_review
// Po úspěšném vložení recenze:
$workflowStateSql = "SELECT id FROM workflow WHERE state = 'Vrácen k úpravám'";
$updatePostStateSql = "UPDATE posts SET state = ?, updated_at = ?, updated_by = ? WHERE id = ?";
// state = 5
```

**Důležité:**
- Změna stavu se provede **pouze při vytvoření nové recenze** (ne při úpravě)
- Autor vidí, že článek potřebuje úpravy
- Autor může článek upravit a proces se opakuje

---

## 🔐 Role a oprávnění

### Přehled rolí

| ID | Role | Oprávnění |
|----|------|-----------|
| 1 | Administrátor | Všechny články, editace, přiřazování recenzentů |
| 2 | Šéfredaktor | Všechny články, editace, přiřazování recenzentů |
| 3 | Recenzent | Pouze přiřazené články, psaní recenzí |
| 4 | Redaktor | Všechny články, editace, přiřazování recenzentů |
| 5 | Autor | Vlastní články, vytváření článků |
| 6 | Čtenář | Pouze čtení článků |

### Přístup k funkcím

#### Vytváření článků (`Frontend/clanek.php`)
- **Přístup:** Pouze Autor (role_id = 5)
- **Kontrola:** `Backend/postControl.php` - `create_post`
- **Viditelnost tlačítka:** Všechny role kromě Čtenáře (header.php)

#### Přehled článků (`Frontend/articles_overview.php`)
- **Admin, Šéfredaktor, Redaktor:** Všechny články
- **Recenzent:** Pouze přiřazené články (INNER JOIN post_assignments)
- **Autor:** Pouze vlastní články (WHERE user_id = ?)
- **Čtenář:** Bez přístupu

#### Editace článků (`Frontend/edit_article.php`)
- **Přístup:** Admin (1), Šéfredaktor (2), Redaktor (4)
- **Funkce:**
  - Editace všech polí článku
  - Změna workflow stavu
  - Přiřazování recenzentů
  - Správa souborů (odstranění, nahrazení)

#### Recenze článků (`Frontend/review_article.php`)
- **Přístup:** Pouze Recenzent (role_id = 3)
- **Kontrola:** Recenzent musí být přiřazen k článku (`post_assignments`)
- **Funkce:**
  - Hodnocení: aktualita, originalita, jazyk, odbornost (1-5)
  - Komentář
  - Editace existující recenze

---

## 📊 Databázové vztahy

### Tabulka `posts`
- `user_id` → `users.id` (autor)
- `state` → `workflow.id` (aktuální stav)
- `created_by` → `users.id` (kdo vytvořil)
- `updated_by` → `users.id` (kdo naposledy upravil)

### Tabulka `post_assignments`
- `post_id` → `posts.id` (článek)
- `reviewer_id` → `users.id` (recenzent)
- `assigned_by` → `users.id` (kdo přiřadil)
- `status`: 'Přiděleno', 'Recenzováno'

### Tabulka `post_reviews`
- `post_id` → `posts.id` (článek)
- `reviewer_id` → `users.id` (recenzent)
- Skóre: `score_actuality`, `score_originality`, `score_language`, `score_expertise` (1-5)
- `comment` (TEXT)

---

## 🔄 Komunikace mezi komponentami

### Request Flow

#### 1. Vytvoření článku

```
Frontend/clanek.php
    ↓ (POST form)
Backend/postControl.php (action=create_post)
    ↓ (validace)
Database/dataControl.php (insert)
    ↓ (file upload)
uploads/ (soubor)
    ↓ (vložení do DB)
posts table (state = 1)
    ↓ (redirect)
Frontend/user.php (success message)
```

#### 2. Editace článku a přiřazení recenzenta

```
Frontend/articles_overview.php
    ↓ (klik na "Editovat")
Frontend/edit_article.php (GET id)
    ↓ (načtení článku z DB)
    ↓ (zobrazení formuláře)
    ↓ (POST form)
Backend/postControl.php (action=update_post)
    ↓ (validace)
    ↓ (update posts)
    ↓ (přiřazení recenzentů → post_assignments)
    ↓ (automatická změna stavu → state = 3)
Frontend/edit_article.php (redirect s success message)
```

#### 3. Recenze článku

```
Frontend/articles_overview.php (Recenzent)
    ↓ (klik na "Napsat recenzi")
Frontend/review_article.php (GET id)
    ↓ (kontrola přiřazení)
    ↓ (zobrazení článku a formuláře)
    ↓ (POST form)
Backend/reviewControl.php (action=create_review)
    ↓ (validace)
    ↓ (insert post_reviews)
    ↓ (update post_assignments.status = 'Recenzováno')
    ↓ (automatická změna stavu → state = 5)
Frontend/review_article.php (redirect s success message)
```

---

## 🔒 Bezpečnostní opatření

### 1. Autentizace
- **Middleware:** `Backend/notAccess.php`
- **Kontrola:** Session ověření před přístupem k chráněným stránkám
- **Použití:** Všechny backend soubory a chráněné frontend stránky

### 2. Autorizace
- **Role-based access control (RBAC)**
- **Kontrola role:** Před každou akcí se ověří `role_id` z session
- **Příklady:**
  - Vytváření článků: pouze Autor (5)
  - Editace: pouze Admin (1), Šéfredaktor (2), Redaktor (4)
  - Recenze: pouze Recenzent (3) a musí být přiřazen

### 3. SQL Injection Prevention
- **Prepared statements:** Všechny SQL dotazy používají prepared statements
- **Escape string:** Pro jednoduché dotazy (filtry) se používá `mysqli_real_escape_string`
- **Typ kontrola:** Parametry se typově ověřují před vložením do dotazu

### 4. XSS Protection
- **HTML escaping:** Funkce `e()` v `bootstrap.php`
- **Použití:** Všechny výstupy z databáze se escapují pomocí `htmlspecialchars()`

### 5. File Upload Security
- **Validace typu:** Pouze PDF, DOC, DOCX
- **Validace velikosti:** Max 10 MB
- **Bezpečné pojmenování:** `uniqid() + preg_replace()` pro sanitizaci
- **Path traversal protection:** `realpath()` kontrola v `download.php`
- **Rollback:** Pokud selže DB operace, soubor se smaže

---

## 📝 Session Management

### Uložená data v session

```php
$_SESSION['user'] = [
    'id' => int,           // User ID
    'username' => string,  // Uživatelské jméno
    'email' => string,     // Email
    'phone' => string,     // Telefon
    'role_id' => int       // Role ID (1-6)
];
```

### Flash messages

```php
$_SESSION['success'] = "Zpráva o úspěchu";
$_SESSION['error'] = "Chybová zpráva";
```

**Zobrazení:** Automaticky v `bootstrap.php` jako alerty v pravém horním rohu

**Mazání:** Po zobrazení se automaticky smažou z session

---

## 🗂️ File Management

### Upload proces

1. **Validace:**
   - Typ: PDF, DOC, DOCX
   - Velikost: max 10 MB
   - Chyba: přesměrování s error message

2. **Uložení:**
   - Adresář: `uploads/`
   - Název: `uniqid('article_', true) + '_' + sanitized_filename`
   - Cesta v DB: `uploads/filename.pdf`

3. **Rollback:**
   - Pokud selže DB operace, soubor se smaže
   - Použití: `unlink()` v error handleru

### Download proces

1. **Načtení z DB:**
   - `SELECT file_path FROM posts WHERE id = ?`
   - Kontrola existence článku

2. **Bezpečnost:**
   - Path traversal protection: `realpath()`
   - Kontrola existence souboru
   - MIME type detection

3. **Výstup:**
   - Headers: `Content-Type`, `Content-Disposition`
   - Název souboru: název článku + přípona
   - Čtení a výstup: `readfile()`

---

## 🔍 Filtrování a vyhledávání

### Přehled článků (`articles_overview.php`)

**Filtry:**
- **Podle stavu:** Dropdown s workflow stavy
- **Podle názvu:** Textové vyhledávání (LIKE)

**Implementace:**
- GET parametry: `?stav=3&nazev=článek`
- SQL: `WHERE p.state = ? AND p.title LIKE '%?%'`
- Escape: `mysqli_real_escape_string()` pro název
- Cast: `(int)` pro stav

**Reset:** Tlačítko "Reset" vymaže filtry

---

## 📧 Email komunikace

### Funkce

1. **sendEmail($to, $subject, $text, $userId = null)**
   - Obecné odeslání emailu
   - Pokud je `$userId`, přidá se tracking odkaz

2. **sendEmailResetPassword($to)**
   - Obnova hesla
   - Generuje náhodné heslo (1000-9999)
   - Hashuje a uloží do DB
   - Odešle email s novým heslem

### Konfigurace

- **SMTP:** Seznam.cz (smtp.seznam.cz)
- **Port:** 465 (SSL)
- **Autentizace:** Username + Password
- **CharSet:** UTF-8
- **Encoding:** base64

### Použití

- **Žádost o pozici autora:** `userControl.php` → `writerRegister`
- **Obnova hesla:** `login.php` → `resetPassword`

---

## 🎨 UI komponenty

### Header (`Frontend/Include/header.php`)

**Tlačítka podle role:**
- **"Nový článek":** Všechny role kromě Čtenáře (role_id != 6)
- **"Přehled článků":** Admin, Šéfredaktor, Recenzent, Redaktor, Autor (role_id in [1,2,3,4,5])

**Session data:**
- Zobrazení přihlášeného uživatele
- Tlačítko "Účet"
- Tlačítko "Odhlásit"

### Flash messages (`bootstrap.php`)

- **Success:** Zelená alert
- **Error:** Červená alert
- **Auto-hide:** Po 5 sekundách (JavaScript)
- **Umístění:** Pravý horní roh

---

## 🔄 Kompletní workflow cyklus

### Scénář: Autor vytvoří článek → Recenze → Úpravy

1. **Autor vytvoří článek:**
   - `Frontend/clanek.php` → formulář
   - `Backend/postControl.php` → `create_post`
   - **Stav:** `state = 1` ("Nový")

2. **Admin přiřadí recenzenta:**
   - `Frontend/articles_overview.php` → "Editovat"
   - `Frontend/edit_article.php` → vybere recenzenta
   - `Backend/postControl.php` → `update_post`
   - **Automaticky:** `state = 3` ("V recenzi")
   - **Vložení:** `post_assignments` (status = 'Přiděleno')

3. **Recenzent napíše recenzi:**
   - `Frontend/articles_overview.php` (Recenzent) → "Napsat recenzi"
   - `Frontend/review_article.php` → vyplní hodnocení
   - `Backend/reviewControl.php` → `create_review`
   - **Automaticky:** `state = 5` ("Vrácen k úpravám")
   - **Aktualizace:** `post_assignments.status = 'Recenzováno'`
   - **Vložení:** `post_reviews`

4. **Autor upraví článek:**
   - Autor vidí článek ve stavu "Vrácen k úpravám"
   - Může upravit článek (pokud má oprávnění)
   - Proces se opakuje od kroku 2

---

## 🐛 Error Handling

### Logování chyb

- **error_log():** Všechny chyby se logují
- **Místa:**
  - SQL chyby
  - File upload chyby
  - Email chyby
  - Validace chyby

### Zobrazení chyb uživateli

- **Flash messages:** `$_SESSION['error']`
- **Přesměrování:** Po chybě se uživatel přesměruje na původní stránku
- **Error handling:** Try-catch bloky v kritických místech

---

## 📌 Důležité poznámky

### Fallback pro starší PHP

- **get_result():** Některé PHP verze nepodporují `get_result()`
- **Řešení:** Fallback na `bind_result()` + manuální sestavení pole
- **Použití:** Všechny SQL dotazy s prepared statements

### NULL hodnoty v databázi

- **insert():** Filtruje NULL hodnoty (použije se DEFAULT z DB)
- **Důvod:** Umožňuje databázi použít výchozí hodnoty
- **Použití:** Při vytváření článků (některá pole jsou volitelná)

### Workflow stav "Schválen"

- **ID:** 6 (podle databáze)
- **Poznámka:** V databázi je duplikát ID 5 pro "Schválen" a "Vrácen k úpravám"
- **Řešení:** Zkontrolovat a opravit databázi (měl by být pouze jeden stav s ID 5)

---

## 🔗 Související dokumenty

- `00_PROJEKT_OVERVIEW.md` - Přehled projektu
- `01_ARCHITEKTURA_TECHNOLOGIE.md` - Architektura
- `02_DATABAZE_SCHÉMA.md` - Databázové schéma
- `03_PHP_FUNKCE.md` - PHP funkce
- `04_FRONTEND_COMPONENTS.md` - Frontend komponenty
- `05_SECURITY_BEST_PRACTICES.md` - Bezpečnost

---

**Dokument vytvořen:** 2025-01-17  
**Autor:** AI Assistant (na základě analýzy kódu)  
**Verze:** 1.0

