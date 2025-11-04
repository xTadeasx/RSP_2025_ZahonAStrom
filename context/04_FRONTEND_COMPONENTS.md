# Frontend komponenty a stránky

## 🎨 Layout system

### bootstrap.php
Centrální soubor pro inicializaci stránky

**Funkce:**
- Spuštění session
- Flash messages handling
- Escape funkce
- HTML doctype
- Bootstrap CSS/JS načítání
- Custom CSS/JS načítání

**Struktura:**
```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Flash messages
$__flashError = $_SESSION['error'] ?? null;
$__flashSuccess = $_SESSION['success'] ?? null;
// ... unset flash messages
```

---

### header.php
Hlavička stránky s navigací

**Komponenty:**
- Bootstrap navbar
- Logo/brand
- Navigační menu
- User menu (přihlášení/odhlášení)
- Session info

**Navigační položky:**
1. O časopisu → index.php
2. Redakční rada → board.php
3. Informace pro autory → authors.php
4. Archiv → archive.php

**User menu:**
```php
<?php if (!empty($_SESSION['user']['username'])): ?>
    <span>Přihlášen: <?= e($_SESSION['user']['username']) ?></span>
    <a href="./user.php">Účet</a>
    <form action="../Backend/userControl.php" method="post">
        <button type="submit" name="action" value="logOut">Odhlásit</button>
    </form>
<?php else: ?>
    <a href="./login.php">Přihlášení</a>
<?php endif; ?>
```

---

### footer.php
Patička stránky

**Obsah:**
- Kontaktní informace
- Copyright
- Grid layout (2 sloupce)

---

## 📄 Hlavní stránky

### index.php - Domovská stránka

**Sekce:**

1. **Proč číst náš časopis**
   - Feature cards (3 ks)
   - Široké spektrum oborů
   - Důsledná recenze
   - Tým zkušených editorů

2. **Nejnovější články**
   - Grid cards s články
   - Demo data
   - Metadata (author, date)

**Demo články:**
```php
$posts = [
    [
        'title' => 'Dopady digitalizace na malé a střední podniky',
        'excerpt' => 'Analýza trendů...',
        'author' => 'Ing. Jana Nováková',
        'date' => '28. 10. 2025',
        'category' => 'Ekonomika'
    ],
    // ... další články
];
```

**TODO:**
- Napojit na databázi
- Přidat obrázky
- Paginace
- Vyhledávání

---

### login.php - Přihlášení a registrace

**Formuláře:**

1. **Login**
   - Username + Password
   - POST → login.php (action: login)

2. **Password reset**
   - Email
   - POST → login.php (action: reset_password)

3. **Registrace**
   - Username, Password, Email, Phone
   - Minlength: 3 pro heslo
   - POST → login.php (action: register)

**Validace:**
- Client-side (HTML5)
- Required fields
- Email type
- Password minlength

**TODO:**
- Přidat * k required fields (JavaScript)
- Password strength meter
- Captcha

---

### user.php - Správa účtu

**Požadavky:** Přihlášený uživatel (notAccess.php)

**Formuláře:**

1. **Edit user**
   - Username, Password, Email, Phone
   - Aktualizace session po uložení
   - POST → userControl.php (action: edit_user)

2. **Writer registration**
   - Textový důvod (min. 10 znaků)
   - Email redakci
   - POST → userControl.php (action: writerRegister)

**TODO:**
- Avatar upload
- Notification preferences
- Change password separately
- Delete account

---

### article.php - Detail článku

**Layout:**
- Hero sekce (kicker, title, perex, meta)
- Body (prose typography)
- Footer (zpět link)

**Demo data:**
```php
$article = [
    'title' => 'Dopady digitalizace...',
    'perex' => 'Analýza trendů...',
    'author' => 'Ing. Jana Nováková',
    'date' => '28. 10. 2025',
    'category' => 'Ekonomika'
];
```

**Prose elements:**
- h2, h3 headings
- Paragraphs
- Lists (ul, ol)
- Blockquotes

**TODO:**
- Napojit na databázi
- Full-text content
- Comments section
- Share buttons
- Print view

---

### author.php - Články autora

**URL parametry:**
- `?name=Ing. Jana Nováková`

**Funkce:**
- Filtrování článků podle autora
- Author header (avatar, bio)
- Grid s články autora

**Demo:**
```php
$allPosts = [...]; // Všechny články
$posts = array_filter($allPosts, function($p) use ($authorName) {
    return mb_strtolower($p['author']) === mb_strtolower($authorName);
});
```

**TODO:**
- Napojit na databázi
- Author profile data
- Bio, avatar
- Statistics

---

### authors.php - Informace pro autory

**Sekce:**
1. Úvod
2. Jak probíhá odeslání článku (ordered list)
3. Požadavky na formátování (unordered list)
4. Etika publikování
5. Šablony a vzory (feature cards)
6. Kontakt redakce

**Feature cards:**
- Word/Google Docs
- LaTeX
- Práva a licence

**TODO:**
- Stažení šablon
- Online formulář
- Submission guidelines

---

### board.php - Redakční rada

**Sekce:**
1. Úvod do redakční rady
2. Tým (grid cards)

**Team demo:**
```php
$team = [
    [
        'name' => 'Petr Novák',
        'role' => 'Project Manager',
        'bio' => 'Publikuje od roku 2005...',
        'link' => '#'
    ],
    // ... další členové
];
```

**Member card:**
- Avatar (placeholder)
- Name, role
- Bio
- Link na články

**TODO:**
- Avatar images
- Real team data
- Member pages
- Contact info

---

### archive.php - Archiv

**Layout:**
- Sekce podle roku
- Grid cards pro každé číslo

**Demo:**
```php
$archive = [
    '2025' => [
        ['issue' => '1/2025', 'theme' => 'Digitalizace a AI', 'articles' => 8],
        ['issue' => '2/2025', 'theme' => 'Zdravotnictví', 'articles' => 7]
    ],
    '2024' => [...]
];
```

**TODO:**
- Napojit na databázi
- Filter by issue
- Download PDF
- Search archive

---

## 🎨 CSS komponenty

### Cards
```css
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
```

**Struktura:**
- .thumb (obrázek)
- .body (title, excerpt, meta)
- .actions (tlačítka)

### Feature cards
```css
.feature-card {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.feature-ico {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(...);
}
```

### Member cards
```css
.member-card {
    display: flex;
    gap: 12px;
    border: ...;
    padding: 14px;
    align-items: flex-start;
}
.avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
}
```

### Forms
```css
form:not(.inline) {
    background: var(--surface);
    border: ...;
    border-radius: 12px;
    padding: 18px;
}
input, textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
}
```

### Alerts
```css
.alert {
    position: fixed;
    top: 16px;
    right: 16px;
    padding: 12px 14px;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,.12);
}
.alert-success { background: var(--success); }
.alert-error { background: var(--error); }
.alert.hide { opacity: 0; }
```

### Prose typography
```css
.prose h2 { margin-top: 14px; }
.prose p { margin: 8px 0; }
.prose ul { padding-left: 20px; }
.prose blockquote {
    border-left: 4px solid var(--brand-2);
    background: #fcfbfd;
}
```

---

## ⚡ JavaScript funkce

### Auto-hide alerts
```javascript
document.querySelectorAll('.alert[data-auto-hide]')
    .forEach((el) => setTimeout(() => el.classList.add('hide'), 2600));
```

### Required fields star
```javascript
const requiredInputs = document.querySelectorAll('input[required]');
requiredInputs.forEach((input) => {
    const label = input.form.querySelector(`label[for="${id}"]`);
    if (label && !label.querySelector('.req')) {
        const star = document.createElement('span');
        star.className = 'req';
        star.textContent = ' *';
        label.appendChild(star);
    }
});
```

---

## 🎨 Design system

### Barvy
```css
:root {
    --bg: #f7f6f8;          /* Background */
    --text: #1e1b20;        /* Primary text */
    --muted: #6b6570;       /* Secondary text */
    --brand: #4e1835;       /* Primary brand */
    --brand-2: #6d2a4c;     /* Brand variant */
    --surface: #ffffff;     /* Cards, forms */
    --border: #e6e2e8;      /* Borders */
    --success: #2e7d32;     /* Success */
    --error: #b00020;       /* Error */
}
```

### Typography
- Font: system-ui, -apple-system, Segoe UI
- Size: 16px / 1.5 line-height
- Responsive: Mobile-first

### Spacing
- Container: max-width 1100px, padding 24px
- Section: margin 28px 0
- Card gap: 16px
- Form padding: 18px

### Border radius
- Buttons: 6px
- Cards: 12px
- Alerts: 10px
- Inputs: 8px

---

## 📱 Responsive design

### Breakpoints
```css
@media (max-width: 720px) {
    .footer-grid { grid-template-columns: 1fr; }
    .features { grid-template-columns: 1fr; }
}

@media (max-width: 400px) {
    .container { padding: 0 12px; }
    .cards { grid-template-columns: 1fr; }
}
```

### Grid adaptivity
- Cards: `repeat(auto-fit, minmax(260px, 1fr))`
- Features: 3 columns → 1 column
- Team: `repeat(auto-fit, minmax(240px, 1fr))`

---

## 🔧 Utility classes

### Buttons
```css
.btn              /* Base button */
.btn-small        /* Smaller variant */
.btn-outline      /* Outline style */
```

### Layout
```css
.container        /* Max-width container */
.section          /* Section spacing */
.section-title    /* Brand colored title */
.section-body     /* White background body */
```

### Components
```css
.alert            /* Flash message */
.card             /* Article card */
.feature-card     /* Feature box */
.member-card      /* Team member */
```

### Typography
```css
.prose            /* Article body */
.list             /* ul/ol styling */
```

---

## 📋 TODOs

### Short-term
- [ ] Přidat obrázky k článkům
- [ ] Implementovat databázové volání
- [ ] Paginace pro seznamy
- [ ] Vyhledávání
- [ ] Filter podle kategorií

### Long-term
- [ ] Theme switcher (dark mode)
- [ ] Internationalization (i18n)
- [ ] Accessibility (a11y)
- [ ] SEO optimization
- [ ] Progressive Web App
- [ ] Offline support

