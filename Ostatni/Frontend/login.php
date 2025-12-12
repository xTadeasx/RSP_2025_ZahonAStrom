<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<h1>Přihlášení</h1>
<form action="../Backend/login.php" method="POST">
    <input type="hidden" name="action" value="login">

    <label for="username">Uživatelské jméno: *</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Heslo: *</label>
    <input type="password" id="password" name="password" required>

    <div class="actions">
        <button class="btn" type="submit">Přihlásit se</button>
    </div>
</form>
<h5>Obnova hesla</h5>
<form action="../Backend/login.php" method="POST">
    <input type="hidden" name="action" value="reset_password">

    <label for="email">E-mail: *</label>
    <input type="email" id="email" name="email" required>

    <div class="actions">
        <button class="btn" type="submit">Obnovit heslo</button>
    </div>
</form>
<div style="height:16px"></div>
<h1>Registrace</h1>
<form action="../Backend/login.php" method="POST">
    <input type="hidden" name="action" value="register">

    <label for="username_reg">Uživatelské jméno: *</label>
    <input type="text" id="username_reg" name="username" required>
    <label for="password_reg">Heslo: *</label>
    <input type="password" id="password_reg" name="password" minlength="3" required>
    <label for="email_reg">E-mail: *</label>
    <input type="email" id="email_reg" name="email" required>
    <label for="phone_reg">Telefon: *</label>
    <input type="text" id="phone_reg" name="phone" required>

    <div class="actions">
        <button class="btn" type="submit">Registrovat</button>
    </div>
</form>
<?php require_once __DIR__ . '/Include/footer.php'; ?>