# Bezpečnost a best practices

## 🔐 Implementované zabezpečení

### 1. Password handling

#### ✅ Hashování hesel
```php
// Při registraci
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Při ověření
password_verify($password, $hashedPassword);
```

**Implementace:**
- Bcrypt algoritmus (default PASSWORD_DEFAULT)
- Salt automaticky
- Cost factor: 10 (default)
- One-way hash

---

### 2. SQL Injection Prevention

#### ✅ Prepared statements
```php
// SELECT
$sql = "SELECT password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);

// INSERT
$columns = implode(", ", array_keys($data));
$placeholders = implode(", ", array_fill(0, count($data), "?"));
$values = array_values($data);
$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
$stmt = $conn->prepare($sql);
$types = str_repeat("s", count($values));
$stmt->bind_param($types, ...$values);
```

**Implementace:**
- Všechny user inputs přes prepared statements
- Bind parametrů podle typu
- Escapování automatické

---

### 3. XSS Protection

#### ✅ Output encoding
```php
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Použití
echo e($userInput);
```

**Implementace:**
- `ENT_QUOTES`: Escapuje single a double quotes
- `ENT_SUBSTITUTE`: Substituuje invalid characters
- UTF-8 encoding
- Použití všude kde user input

---

### 4. Session Security

#### ✅ Session management
```php
session_start();

// Nastavení dat
$_SESSION['user'] = [...];

// Validace
if (!isset($_SESSION['user']['username'])) {
    header("Location: ../Frontend/index.php");
    exit();
}

// Zrušení
session_unset();
session_destroy();
```

**Best practices:**
- Session start vždy na začátku
- Flash messages cleanup
- Exit po redirect
- Destroy při logout

---

### 5. Access Control

#### ✅ Middleware
```php
// notAccess.php
session_start();
if (!isset($_SESSION['user']['username'])) {
    $_SESSION['error'] = "Musíte být přihlášeni.";
    header("Location: ../Frontend/index.php");
    exit();
}
```

**Implementace:**
- Session check
- Redirect nepovolených uživatelů
- Flash message
- Exit po redirect

---

## ⚠️ Security gaps a TODOs

### 1. CSRF Protection

#### ❌ Missing
```php
// TODO: Implementovat
// Generování tokenu
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validace
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token mismatch');
}
```

**Riziko:**
- Cross-Site Request Forgery
- Manipulace požadavků
- Zneužití session

**Řešení:**
- Token generation
- Token validation
- SameSite cookies

---

### 2. Rate Limiting

#### ❌ Missing
```php
// TODO: Implementovat
function rateLimit($action, $maxAttempts, $window) {
    $key = "rate_limit_{$action}";
    $attempts = $_SESSION[$key] ?? 0;
    if ($attempts >= $maxAttempts) {
        die('Too many attempts');
    }
    $_SESSION[$key] = $attempts + 1;
}
```

**Risks:**
- Brute force login
- Spam registrací
- Email abuse
- Denial of Service

**Řešení:**
- Attempt tracking
- Time windows
- CAPTCHA po N pokusech
- IP blocking

---

### 3. Password Reset Security

#### ⚠️ Weak implementation
```php
// Současná implementace
$randomNumber = rand(1000, 9999);
$randomNumberHash = password_hash($randomNumber, PASSWORD_BCRYPT);
```

**Problémy:**
- 4-místný numerický kód
- Žádná expirace
- Jednorázový token pouze v emailu
- Plain text v emailu

**Vylepšení:**
```php
// TODO: Vylepšená verze
$token = bin2hex(random_bytes(32));
$expiry = time() + 3600; // 1 hodina

INSERT INTO password_resets (email, token, expires_at) VALUES ...;

// Validace
WHERE token = ? AND expires_at > NOW() AND used = 0;
```

---

### 4. Input Validation

#### ⚠️ Minimal validation
```php
// Current
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? null;

// TODO: Rozšířená validace
function validateUsername($username) {
    if (strlen($username) < 3 || strlen($username) > 20) {
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return false;
    }
    return true;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
```

**Problémy:**
- Bez kontrolní délky
- Speciální znaky v username
- Weak email validation
- No sanitization

---

### 5. File Upload Security

#### ❌ Not implemented
```php
// TODO: Implementovat
function uploadFile($file, $allowedTypes, $maxSize) {
    // Kontrola typu
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        die('Invalid file type');
    }
    
    // Kontrola velikosti
    if ($file['size'] > $maxSize) {
        die('File too large');
    }
    
    // Bezpečné jméno
    $filename = uniqid() . '_' . basename($file['name']);
    
    // Přesun mimo webroot
    move_uploaded_file($file['tmp_name'], "/var/uploads/{$filename}");
}
```

**Risks:**
- Malicious files
- Path traversal
- PHP execution
- Storage abuse

---

### 6. Database Security

#### ⚠️ Weak credentials
```php
// db.php
$username = "root";
$password = "";
```

**Problémy:**
- Default root user
- Empty password
- No user-specific DB user
- Hardcoded credentials

**Best practices:**
```php
// TODO: Environment variables
$username = getenv('DB_USER') ?: 'app_user';
$password = getenv('DB_PASS');
$database = getenv('DB_NAME');
```

---

### 7. HTTPS Enforcement

#### ❌ Not enforced
```php
// TODO: Implementovat
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}
```

---

### 8. Content Security Policy

#### ❌ Missing
```html
<!-- TODO: Implementovat -->
<meta http-equiv="Content-Security-Policy" 
      content="default-src 'self'; 
               script-src 'self' https://cdn.jsdelivr.net; 
               style-src 'self' https://cdn.jsdelivr.net;">
```

---

### 9. Session Security Headers

#### ❌ Not configured
```php
// TODO: Implementovat
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);
```

---

### 10. SQL Query Audit Trail

#### ⚠️ Basic logging
```php
// TODO: Implementovat
function logQuery($table, $action, $user_id) {
    insert([
        'table_name' => $table,
        'action' => $action,
        'user_id' => $user_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ], 'audit_log');
}
```

---

## 🛡️ Security hardening checklist

### Immediate
- [ ] Implementovat CSRF protection
- [ ] Přidat rate limiting
- [ ] Vylepšit password reset
- [ ] Validace inputů
- [ ] Database credentials do .env

### Short-term
- [ ] HTTPS enforcement
- [ ] CSP headers
- [ ] Session security headers
- [ ] Input sanitization
- [ ] File upload security

### Long-term
- [ ] Audit logging
- [ ] Intrusion detection
- [ ] Regular security audits
- [ ] Penetration testing
- [ ] Security monitoring

---

## 📊 Security risk matrix

| Riziko | Pravděpodobnost | Dopad | Priorita |
|--------|----------------|-------|----------|
| SQL Injection | Nízká (prepared) | Kritický | ✅ V řešeno |
| XSS | Nízká (escaping) | Vysoký | ✅ V řešeno |
| CSRF | Střední | Vysoký | 🔴 Vysoká |
| Brute Force | Vysoká | Střední | 🔴 Vysoká |
| Session Fixation | Střední | Střední | 🟡 Střední |
| Password Reset | Vysoká | Kritický | 🔴 Vysoká |
| File Upload | Nízká (není) | Kritický | 🟡 Střední |
| Credential Leak | Střední | Kritický | 🔴 Vysoká |

---

## 🔒 Security headers

### Recommended
```php
// TODO: Přidat do .htaccess nebo PHP
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=()');
```

---

## 🧪 Security testing

### Manual testing
1. SQL injection attempts
2. XSS payloads
3. CSRF forms
4. Session hijacking
5. Brute force login
6. File upload exploit

### Automated tools
- OWASP ZAP
- Burp Suite
- Nikto
- PHPStan security

---

## 📚 Resources

### OWASP Top 10
1. Broken Access Control
2. Cryptographic Failures
3. Injection
4. Insecure Design
5. Security Misconfiguration
6. Vulnerable Components
7. Authentication Failures
8. Software/Data Integrity
9. Logging Failures
10. SSRF

### References
- OWASP: https://owasp.org/
- PHP Security: https://www.php.net/manual/en/security.php
- CWE: https://cwe.mitre.org/

