# Chat a Notifikace - Dokumentace

## 📋 Přehled

Tento dokument popisuje implementaci interního chatu mezi uživateli a systému notifikací v aplikaci pro vědecký časopis.

**Poslední aktualizace:** 2025-01-17

---

## 💬 Chat systém

### Přehled

Interní chat umožňuje přihlášeným uživatelům posílat si soukromé zprávy. Každá dvojice uživatelů má jednu konverzaci (chat), která obsahuje všechny zprávy mezi nimi.

### Databázové schéma

#### Tabulka `chats`
```sql
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_one_id INT NOT NULL,          -- Menší ID z dvojice
    user_two_id INT NOT NULL,          -- Větší ID z dvojice
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_one_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_two_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_chat (user_one_id, user_two_id)
);
```

**Důležité:**
- `user_one_id` a `user_two_id` jsou vždy seřazeny (min/max), aby unikátní index fungoval
- Před vložením se ID seřadí: `$userOne = min($currentUserId, $recipientId);`

#### Tabulka `chat_messages`
```sql
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,              -- Odkaz na chats.id
    sender_id INT NOT NULL,            -- Odesílatel
    receiver_id INT NOT NULL,          -- Příjemce
    message TEXT NOT NULL,             -- Obsah zprávy
    is_read TINYINT(1) DEFAULT 0,      -- 0 = nepřečteno, 1 = přečteno
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Indexy:**
- PRIMARY (id)
- KEY (chat_id)
- KEY (sender_id)
- KEY (receiver_id)

### Backend implementace

#### Backend/chatControl.php

**Akce:** `send_message`

**Proces odeslání zprávy:**

1. **Validace:**
   - Kontrola přihlášení (`notAccess.php`)
   - Kontrola existence `recipient_id`
   - Kontrola, že příjemce není stejný jako odesílatel
   - Kontrola, že zpráva není prázdná
   - Ověření existence příjemce v databázi

2. **Vytvoření nebo nalezení chatu:**
   ```php
   $userOne = min($currentUserId, $recipientId);
   $userTwo = max($currentUserId, $recipientId);
   
   // Hledání existujícího chatu
   SELECT id FROM chats WHERE user_one_id = ? AND user_two_id = ? LIMIT 1
   
   // Pokud neexistuje, vytvoří se nový
   INSERT INTO chats (user_one_id, user_two_id) VALUES (?, ?)
   ```

3. **Vložení zprávy:**
   ```php
   insert([
       'chat_id' => $chatId,
       'sender_id' => $currentUserId,
       'receiver_id' => $recipientId,
       'message' => $message,
       'is_read' => 0,
       'created_at' => date('Y-m-d H:i:s')
   ], 'chat_messages');
   ```

4. **Přesměrování:**
   - Úspěch: `Frontend/chat.php?chat_with={recipient_id}`
   - Chyba: Flash message + zpět na chat

**Bezpečnost:**
- Použití prepared statements
- Validace vstupů
- Kontrola existence uživatele
- XSS protection (escaping v frontendu)

### Frontend implementace

#### Frontend/chat.php

**Struktura stránky (3 sloupce):**

1. **Levý sloupec - Seznam uživatelů:**
   - Filtrování podle jména (GET `filter_name`)
   - Filtrování podle role (GET `filter_role`)
   - Zobrazení nepřečtených zpráv (badge s počtem)
   - Aktivní uživatel (highlight)

2. **Prostřední sloupec - Zprávy:**
   - Zobrazení historie konverzace
   - Rozlišení vlastních/cizích zpráv (CSS třídy `me`/`them`)
   - Formátování času (d.m. H:i)
   - Auto-scroll na konec při načtení

3. **Pravý sloupec - Formulář:**
   - Textarea pro novou zprávu
   - Odeslání přes POST na `Backend/chatControl.php`

**Načítání dat:**

1. **Nepřečtené zprávy:**
   ```php
   SELECT sender_id, COUNT(*) as unread_total 
   FROM chat_messages 
   WHERE receiver_id = ? AND is_read = 0 
   GROUP BY sender_id
   ```

2. **Seznam uživatelů:**
   ```php
   SELECT u.id, u.username, ur.role 
   FROM users u 
   LEFT JOIN users_roles ur ON u.role_id = ur.id
   WHERE u.id != ? [AND filtrování]
   ORDER BY u.username ASC
   ```

3. **Zprávy konverzace:**
   ```php
   // Najít chat_id
   SELECT id FROM chats WHERE user_one_id = ? AND user_two_id = ? LIMIT 1
   
   // Načíst zprávy
   SELECT id, sender_id, receiver_id, message, created_at
   FROM chat_messages
   WHERE chat_id = ?
   ORDER BY created_at ASC
   ```

4. **Označení jako přečtené:**
   ```php
   UPDATE chat_messages 
   SET is_read = 1 
   WHERE chat_id = ? AND receiver_id = ? AND is_read = 0
   ```

**URL parametry:**
- `?chat_with={user_id}` - Otevření konverzace s konkrétním uživatelem
- `?filter_name={text}` - Filtrování podle jména
- `?filter_role={role_id}` - Filtrování podle role

**CSS třídy:**
- `.chat-layout` - Grid layout (3 sloupce)
- `.chat-column` - Sloupec (left, middle, right)
- `.chat-user` - Položka v seznamu uživatelů
- `.chat-bubble` - Bublina zprávy (`.me` / `.them`)
- `.chat-badge` - Badge s počtem nepřečtených

### Request flow

```
Frontend/chat.php (GET)
    ↓
Načtení seznamu uživatelů
Načtení nepřečtených zpráv
[Pokud ?chat_with] → Načtení konverzace
    ↓
Zobrazení UI (3 sloupce)
    ↓
[Uživatel odešle zprávu]
    ↓
POST → Backend/chatControl.php (action=send_message)
    ↓
Validace → Vytvoření/nalezení chatu → Vložení zprávy
    ↓
Redirect → Frontend/chat.php?chat_with={id}
    ↓
Označení zpráv jako přečtené
```

### Oprávnění

- **Přístup:** Pouze přihlášení uživatelé (`notAccess.php`)
- **Funkce:** Všichni přihlášení uživatelé mohou posílat zprávy všem ostatním
- **Omezení:** Nelze posílat zprávy sami sobě

---

## 🔔 Notifikace

### Přehled

Systém notifikací informuje uživatele o důležitých událostech v systému. Aktuálně je implementován pro recenzenty (role_id = 3), kteří dostávají upozornění o přiřazení článků k recenzi a o dokončení recenzí.

### Databázové schéma

#### Tabulka `notifications`
```sql
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,              -- Příjemce notifikace
    type VARCHAR(50) DEFAULT NULL,     -- Typ notifikace (assignment, review_submitted, article_state)
    message TEXT NOT NULL,             -- Text zprávy
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME DEFAULT NULL,     -- Kdy byla přečtena (NULL = nepřečtená)
    related_post_id INT DEFAULT NULL,  -- Odkaz na související článek
    INDEX notifications_user_idx (user_id),
    INDEX notifications_post_idx (related_post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_post_id) REFERENCES posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
```

**Typy notifikací:**
- `assignment` - Přiřazení článku k recenzi
- `review_submitted` - Recenzent dokončil recenzi
- `article_state` - Změna stavu článku (vrácení k úpravám, schválení)

### Backend implementace

#### Backend/notificationService.php

**Funkce `notificationEnsureSchema()`:**
- Vytvoří tabulku `notifications`, pokud neexistuje
- Automatické vytvoření při prvním použití
- Vrací `true` při úspěchu

**Funkce `createNotification()`:**
```php
function createNotification(
    int $userId, 
    string $message, 
    ?string $type = null, 
    ?int $relatedPostId = null
): bool
```

**Parametry:**
- `$userId` - ID příjemce notifikace
- `$message` - Text zprávy
- `$type` - Typ notifikace (volitelné)
- `$relatedPostId` - ID souvisejícího článku (volitelné)

**Implementace:**
```php
$data = [
    'user_id' => $userId,
    'type' => $type,
    'message' => $message,
    'related_post_id' => $relatedPostId
];

return insert($data, 'notifications');
```

#### Backend/notificationControl.php

**API endpoint pro načtení notifikací (JSON):**

**Oprávnění:**
- Pouze přihlášení uživatelé
- Pouze recenzenti (role_id = 3)

**Response formát:**
```json
{
    "status": "ok",
    "data": [
        {
            "id": 1,
            "type": "assignment",
            "message": "Byl vám přidělen článek \"Název\" k recenzi.",
            "created_at": "2025-01-17 10:30:00",
            "created_at_human": "17. 1. 2025 10:30",
            "read_at": null,
            "is_read": false,
            "related_post_id": 5,
            "post_title": "Název článku"
        }
    ]
}
```

**SQL dotaz:**
```sql
SELECT 
    n.id,
    n.type,
    n.message,
    n.created_at,
    n.read_at,
    n.related_post_id,
    p.title AS post_title
FROM notifications n
LEFT JOIN posts p ON p.id = n.related_post_id
WHERE n.user_id = ?
ORDER BY n.read_at IS NULL DESC, n.created_at DESC
LIMIT 20
```

**Řazení:**
- Nepřečtené notifikace první (`read_at IS NULL DESC`)
- Pak podle data vytvoření (`created_at DESC`)

### Místa vytváření notifikací

#### 1. Přiřazení recenzenta (Backend/postControl.php)

**Kdy:** Admin/Šéfredaktor/Redaktor přiřadí recenzenta k článku

**Kód:**
```php
$message = sprintf('Byl vám přidělen článek "%s" k recenzi.', $articleTitle);
createNotification($reviewerId, $message, 'assignment', $articleId);
```

**Kontext:**
- Při vložení záznamu do `post_assignments`
- Současně se odešle email recenzentovi

#### 2. Dokončení recenze (Backend/reviewControl.php)

**Kdy:** Recenzent odešle recenzi článku

**Kód:**
```php
$message = sprintf('Recenzent %s dokončil recenzi článku "%s".', $reviewerName, $articleTitle);
createNotification($assignmentOwnerId, $message, 'review_submitted', $articleId);
```

**Kontext:**
- Při vložení recenze do `post_reviews`
- Notifikace se odešle osobě, která recenzenta přiřadila
- Současně se odešle email

#### 3. Změna stavu článku (Backend/postControl.php)

**Kdy:** Článek je vrácen k úpravám nebo schválen

**Kód:**
```php
createNotification($authorId, $payload['notification'], 'article_state', $articleId);
```

**Typy zpráv:**
- `"Článek \"%s\" byl vrácen k úpravám. Prosíme, zapracujte připomínky."`
- `"Článek \"%s\" byl schválen k publikaci. Gratulujeme!"`

### Frontend implementace

#### Frontend/Include/header.php

**Zobrazení notifikací:**
- Pouze pro recenzenty (`role_id === 3`)
- Tlačítko s ikonou 🔔 v navbaru
- Badge s počtem nepřečtených notifikací

**HTML struktura:**
```html
<div 
    class="notification-center" 
    data-notifications-root 
    data-endpoint="../Backend/notificationControl.php"
>
    <button data-notifications-toggle>
        <span class="notification-icon">🔔</span>
        <span class="notification-badge" data-notifications-badge>0</span>
    </button>
    <div class="notification-dropdown" data-notifications-dropdown>
        <div class="notification-dropdown__header">
            <strong>Upozornění</strong>
            <span data-notifications-status>Načítám...</span>
        </div>
        <div class="notification-dropdown__body" data-notifications-list>
            <!-- Dynamicky naplněno JavaScriptem -->
        </div>
    </div>
</div>
```

#### Frontend/Assets/main.js

**JavaScript funkce:**

1. **fetchNotifications():**
   - Asynchronní načtení z API endpointu
   - Použití Fetch API
   - Zpracování JSON response
   - Volání `renderList()` s daty

2. **renderList(items):**
   - Vykreslení seznamu notifikací
   - Počítání nepřečtených (badge)
   - Formátování: datum, zpráva, název článku
   - CSS třída `notification-row--unread` pro nepřečtené

3. **Toggle dropdown:**
   - Otevření/zavření při kliknutí na tlačítko
   - Načtení dat při prvním otevření (lazy loading)
   - Zavření při kliknutí mimo

**Formát zobrazení:**
```html
<table class="notification-table">
    <tbody>
        <tr class="notification-row--unread">
            <td>17. 1. 2025 10:30</td>
            <td>
                <p class="notification-message">Byl vám přidělen článek "Název" k recenzi.</p>
                <p class="notification-meta">Článek: Název článku</p>
            </td>
        </tr>
    </tbody>
</table>
```

**CSS třídy:**
- `.notification-center` - Kontejner
- `.notification-toggle` - Tlačítko
- `.notification-badge` - Badge s počtem
- `.notification-dropdown` - Dropdown menu
- `.notification-row--unread` - Nepřečtená notifikace (světle fialové pozadí)

### Request flow

```
[Recenzent otevře dropdown]
    ↓
Frontend/Assets/main.js → fetchNotifications()
    ↓
GET → Backend/notificationControl.php
    ↓
[Kontrola oprávnění: role_id === 3]
    ↓
SQL dotaz → notifications table
    ↓
JSON response
    ↓
renderList() → Zobrazení v dropdownu
    ↓
Aktualizace badge s počtem nepřečtených
```

### Oprávnění

- **Vytváření notifikací:** Všichni oprávnění uživatelé (při akcích)
- **Zobrazení notifikací:** Pouze recenzenti (role_id = 3)
- **API endpoint:** Pouze recenzenti

---

## 🔗 Propojení s workflow

### Chat
- **Nezávislý systém** - nezávislý na workflow stavech článků
- **Použití:** Komunikace mezi uživateli o článcích, dotazy, diskuse

### Notifikace
- **Integrovaný s workflow:**
  - Přiřazení recenzenta → notifikace typu `assignment`
  - Dokončení recenze → notifikace typu `review_submitted`
  - Změna stavu článku → notifikace typu `article_state`

---

## 📊 Statistiky a dotazy

### Počet nepřečtených zpráv (chat)
```sql
SELECT sender_id, COUNT(*) as unread_total 
FROM chat_messages 
WHERE receiver_id = ? AND is_read = 0 
GROUP BY sender_id
```

### Počet nepřečtených notifikací
```sql
SELECT COUNT(*) 
FROM notifications 
WHERE user_id = ? AND read_at IS NULL
```

### Historie konverzace
```sql
SELECT id, sender_id, receiver_id, message, created_at
FROM chat_messages
WHERE chat_id = ?
ORDER BY created_at ASC
```

### Notifikace uživatele
```sql
SELECT n.*, p.title AS post_title
FROM notifications n
LEFT JOIN posts p ON p.id = n.related_post_id
WHERE n.user_id = ?
ORDER BY n.read_at IS NULL DESC, n.created_at DESC
LIMIT 20
```

---

## 🐛 Známé problémy a TODO

### Chat
- [ ] Real-time aktualizace (WebSocket nebo polling)
- [ ] Oznámení o nových zprávách (browser notifications)
- [ ] Možnost smazat zprávy
- [ ] Možnost upravit zprávy
- [ ] Přílohy k zprávám
- [ ] Vyhledávání v konverzacích

### Notifikace
- [ ] Označení jako přečtené (aktuálně jen zobrazení)
- [ ] Real-time aktualizace
- [ ] Browser notifications
- [ ] Filtrování podle typu
- [ ] Možnost smazat notifikace
- [ ] Rozšíření na všechny role (ne jen recenzenty)
- [ ] Email notifikace jako alternativa k interním

---

## 🔒 Bezpečnost

### Chat
- ✅ Prepared statements (SQL injection prevention)
- ✅ XSS protection (escaping v frontendu)
- ✅ Kontrola přihlášení (`notAccess.php`)
- ✅ Validace existence uživatele
- ✅ Kontrola, že nelze posílat zprávy sami sobě

### Notifikace
- ✅ Prepared statements
- ✅ Role-based access control (pouze recenzenti)
- ✅ Session validation
- ✅ XSS protection (escaping v JavaScriptu)

---

## 📝 Příklady použití

### Vytvoření notifikace při přiřazení recenzenta
```php
require_once __DIR__ . '/notificationService.php';

$reviewerId = 5; // ID recenzenta
$articleTitle = "Název článku";
$articleId = 10;

$message = sprintf('Byl vám přidělen článek "%s" k recenzi.', $articleTitle);
createNotification($reviewerId, $message, 'assignment', $articleId);
```

### Odeslání zprávy v chatu
```php
// Frontend/chat.php - Formulář
<form action="../Backend/chatControl.php" method="POST">
    <input type="hidden" name="action" value="send_message">
    <input type="hidden" name="recipient_id" value="<?= $selectedChatUser['id'] ?>">
    <textarea name="message" required></textarea>
    <button type="submit">Odeslat</button>
</form>
```

---

## 🔗 Související dokumenty

- `02_DATABAZE_SCHÉMA.md` - Databázové schéma (chats, chat_messages, notifications)
- `03_PHP_FUNKCE.md` - PHP funkce (createNotification)
- `04_FRONTEND_COMPONENTS.md` - Frontend komponenty
- `06_WORKFLOW_KOMUNIKACE.md` - Workflow procesy (kde se vytvářejí notifikace)

---

**Dokument vytvořen:** 2025-01-17  
**Autor:** AI Assistant (na základě analýzy kódu)  
**Verze:** 1.0

