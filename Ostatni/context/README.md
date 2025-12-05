# Context - Dokumentace projektu RSP_2025_ZáhonAStrom

Tato složka obsahuje kompletní dokumentaci projektu pro rychlé porozumění struktuře, architektuře a implementaci.

**Poznámka:** Tato dokumentace se nachází v `Ostatni/context/` a popisuje strukturu projektu z pohledu kořenového adresáře `RSP_2025_ZahonAStrom/`.

## 📚 Obsah

### 00_PROJEKT_OVERVIEW.md
**Základní přehled projektu**
- Účel a cíle aplikace
- Technologický stack
- Struktura projektu
- Přehled funkcionalit
- Aktuální stav implementace
- TODOs a roadmapa

**Pro:** Nové členy týmu, rychlý onboarding

---

### 01_ARCHITEKTURA_TECHNOLOGIE.md
**Architektura a technologie**
- Technologický stack (PHP, MariaDB, Bootstrap)
- Struktura aplikace (MVC, layers)
- Request flow
- Security layers
- API a komunikace
- Design patterns
- Performance considerations

**Pro:** Vývojáře, technické rozhodování

---

### 02_DATABAZE_SCHÉMA.md
**Databázové schéma**
- ERD a tabulky
- Foreign keys a relace
- Výchozí data
- SQL příklady
- Migrace a verze
- Optimalizace indexů

**Pro:** Databázové administrátory, vývojáře

---

### 03_PHP_FUNKCE.md
**PHP API a funkce**
- CRUD operace (insert, select, update, delete)
- Autentizace (validateUser, registerUser)
- Email služby
- Session management
- Helper funkce
- Best practices

**Pro:** Backend vývojáře, code review

---

### 04_FRONTEND_COMPONENTS.md
**Frontend komponenty**
- Layout system (bootstrap, header, footer)
- Hlavní stránky (index, login, user, article...)
- CSS komponenty a design system
- JavaScript funkce
- Responsive design
- TODOs

**Pro:** Frontend vývojáře, designéry

---

### 05_SECURITY_BEST_PRACTICES.md
**Bezpečnost**
- Implementované zabezpečení
- Security gaps
- Risk matrix
- Hardening checklist
- Testing guidelines
- OWASP references

**Pro:** Security audit, vulnerability assessment

---

### 06_WORKFLOW_KOMUNIKACE.md
**Workflow procesy a komunikace**
- Workflow stavy článků a automatické změny
- Role a oprávnění
- Databázové vztahy
- Komunikace mezi komponentami
- Request flow diagramy
- Bezpečnostní opatření
- Session management
- File management
- Email komunikace
- Kompletní workflow cyklus

**Pro:** Vývojáře, porozumění workflow procesům, debugování

---

### 07_CHAT_NOTIFIKACE.md
**Chat a notifikace**
- Interní chat systém mezi uživateli
- Databázové schéma (chats, chat_messages)
- Backend implementace (chatControl.php)
- Frontend implementace (chat.php)
- Systém notifikací
- API endpoint pro notifikace
- Místa vytváření notifikací
- Propojení s workflow
- Bezpečnostní opatření

**Pro:** Vývojáře, implementace komunikace, debugování chat/notifikací

---

## 🚀 Quick start

### Pro vývojáře
1. Začněte: `00_PROJEKT_OVERVIEW.md`
2. Architektura: `01_ARCHITEKTURA_TECHNOLOGIE.md`
3. Databáze: `02_DATABAZE_SCHÉMA.md`
4. API: `03_PHP_FUNKCE.md`
5. Workflow: `06_WORKFLOW_KOMUNIKACE.md`
6. Chat/Notifikace: `07_CHAT_NOTIFIKACE.md`

### Pro frontend
1. Přehled: `00_PROJEKT_OVERVIEW.md`
2. Komponenty: `04_FRONTEND_COMPONENTS.md`
3. Design system: `04_FRONTEND_COMPONENTS.md` (CSS sekce)

### Pro DB admin
1. Schéma: `02_DATABAZE_SCHÉMA.md`
2. Příklady: `02_DATABAZE_SCHÉMA.md` (SQL sekce)

### Pro security audit
1. Security: `05_SECURITY_BEST_PRACTICES.md`
2. Gaps: `05_SECURITY_BEST_PRACTICES.md` (TODO sekce)
3. Risk matrix: `05_SECURITY_BEST_PRACTICES.md`

---

## 📋 Hlavní poznámky

### Implementováno ✅
- ✅ Autentizace (login, register)
- ✅ CRUD operace pro uživatele
- ✅ Databázové schéma (8 tabulek: users, users_roles, posts, workflow, post_assignments, post_reviews, notifications, system_logs)
- ✅ Session management
- ✅ Flash messages
- ✅ Email odesílání (PHPMailer)
- ✅ Bootstrap integrace
- ✅ Responsive design
- ✅ Password hashing (bcrypt)
- ✅ XSS protection (escaping)
- ✅ SQL injection prevention (prepared statements)
- ✅ Recenzní workflow systém
- ✅ Správa článků (CRUD)
- ✅ Nahrávání souborů (PDF, DOC, DOCX)
- ✅ Přiřazování recenzentů
- ✅ Automatické změny workflow stavů
- ✅ Role-based access control (RBAC)
- ✅ Přehled článků podle rolí
- ✅ Filtrování článků (stav, název)
- ✅ Editace článků
- ✅ Recenze článků (hodnocení, komentáře)
- ✅ Stahování souborů článků
- ✅ Interní soukromé zprávy mezi uživateli (chat)
- ✅ Notifikace pro recenzenty

### Důležité TODOs 🔴
- 🔴 CSRF protection
- 🔴 Rate limiting
- 🔴 Vylepšit password reset (token-based)
- 🔴 Database credentials do .env
- 🔴 Paginace článků
- 🔴 Admin panel
- 🔴 Avatary uživatelů
- 🟡 Rozšířit notifikace na všechny role (aktuálně jen recenzenti)
- 🔴 Statistiky a reporty
- 🔴 Export článků (PDF)
- 🔴 Vylepšit workflow (schválení po recenzi)

### Technologie
- PHP 8.2.12
- MariaDB 10.4.32
- Bootstrap 5.3.3
- PHPMailer 7.0
- Composer (autoloading)

### Struktura
```
RSP_2025_ZahonAStrom/
├── Dokumenty/          # Obchodní dokumentace
├── Grafika/            # Wireframy, ERD
└── Ostatni/            # Hlavní kód
    ├── Backend/        # Backend logika
    ├── Database/       # DB schema a funkce
    ├── Frontend/       # Frontend stránky
    ├── context/        # Tato dokumentace (zde)
    ├── vendor/         # Composer závislosti
    └── index.php       # Entry point
```

---

## 🔗 Důležité odkazy

- **Moodle**: https://moodle.vspj.cz/course/view.php?id=203424
- **Teams**: Microsoft Teams kanál
- **ScrumDesk**: https://app.scrumdesk.com/#/projects
- **Zadání**: Moodle → RSP zadání projektu 2025

---

## 📞 Kontakt

**Projekt**: Záhon a Strom - Vědecký časopis  
**Tým**: RSP 2025 - tým zahonastrom  
**Instituce**: VŠPJ  
**Semestr**: 3. semestr

---

## 📝 Aktualizace

**Poslední update**: 2025-01-17  
**Verze dokumentace**: 2.1  
**Autor**: AI Assistant (na základě analýzy kódu)  
**Nové dokumenty**: 
- `06_WORKFLOW_KOMUNIKACE.md` - Kompletní dokumentace workflow procesů
- `07_CHAT_NOTIFIKACE.md` - Dokumentace chat systému a notifikací

---

## 🤝 Jak použít tuto dokumentaci

1. **Rychlý onboarding**: Začněte `00_PROJEKT_OVERVIEW.md`
2. **Implementace feature**: Viz příslušný soubor podle vrstvy
3. **Code review**: Použijte `03_PHP_FUNKCE.md` a `04_FRONTEND_COMPONENTS.md`
4. **Security audit**: `05_SECURITY_BEST_PRACTICES.md`
5. **Database issues**: `02_DATABAZE_SCHÉMA.md`
6. **Chat/Notifikace**: `07_CHAT_NOTIFIKACE.md`

---

**Dokumentace byla generována automaticky na základě analýzy celého projektu.**

