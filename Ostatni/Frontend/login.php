<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

    <h1>Přihlášení</h1>
    <form action="../Backend/login.php" method="POST">
        <input type="hidden" name="action" value="login">
        
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <div class="actions">
            <button class="btn border" type="submit">Login</button>
        </div>
    </form>

    <div style="height:16px"></div>
     <!-- Nějak udělat aby u každého inputu co má required tak ať se přidá '*' mělo by to jít nějak přes javascript -->
    <h1>Registrace</h1>
    <form action="../Backend/login.php" method="POST">
        <input type="hidden" name="action" value="register">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" minlength="3" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>
        
        <div class="actions">
            <button class="btn border" type="submit">Register</button>
        </div>
    </form>
<?php require_once __DIR__ . '/Include/footer.php'; ?>