<?php require_once __DIR__ . '/../Backend/notAccess.php'; ?>
<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php
// Ověření, že uživatel je v roli Autora (role_id = 5)
$userId = $_SESSION['user']['id'] ?? null;
if ($userId) {
    $user = select('users', 'role_id', "id = $userId");
    if (empty($user) || ($user[0]['role_id'] ?? null) != 5) {
        $_SESSION['error'] = "Nemáte oprávnění vytvářet články. Musíte být v roli Autora.";
        header('Location: user.php');
        exit();
    }
}
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin: 0;">Vytvořit nový článek</h1>
    </div>
    <div class="section-body">
        <form action="../Backend/postControl.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_post">
            
            <div style="margin-bottom: 18px;">
                <label for="title">
                    Název článku <span class="req">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    required 
                    maxlength="255"
                    placeholder="Zadejte název článku"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px;"
                >
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Maximálně 255 znaků</div>
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="abstract">
                    Abstrakt <span class="req">*</span>
                </label>
                <textarea 
                    id="abstract" 
                    name="abstract" 
                    rows="5" 
                    required
                    placeholder="Stručný popis článku (abstrakt)"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; font-family: inherit;"
                ></textarea>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Krátký souhrn článku, který pomůže čtenářům rychle pochopit obsah</div>
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="keywords">
                    Klíčová slova
                </label>
                <input 
                    type="text" 
                    id="keywords" 
                    name="keywords" 
                    maxlength="500"
                    placeholder="např. věda, výzkum, analýza (oddělená čárkami)"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px;"
                >
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Klíčová slova oddělená čárkami (max. 500 znaků)</div>
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="topic">
                    Téma / Kategorie
                </label>
                <input 
                    type="text" 
                    id="topic" 
                    name="topic" 
                    maxlength="255"
                    placeholder="např. Biologie, Chemie, Fyzika"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px;"
                >
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="authors">
                    Autoři (pokud je jich více)
                </label>
                <textarea 
                    id="authors" 
                    name="authors" 
                    rows="3"
                    placeholder="Seznam dalších autorů (každý na nový řádek nebo oddělený čárkami)"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; font-family: inherit;"
                ></textarea>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Pokud je článek od více autorů, uveďte jejich jména</div>
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="body">
                    Obsah článku <span class="req">*</span>
                </label>
                <textarea 
                    id="body" 
                    name="body" 
                    rows="15" 
                    required
                    placeholder="Zadejte plný text článku"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; font-family: inherit;"
                ></textarea>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Hlavní obsah článku</div>
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="file">
                    Příloha (PDF, DOCX)
                </label>
                <input 
                    type="file" 
                    id="file" 
                    name="file" 
                    accept=".pdf,.doc,.docx"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Můžete nahrát soubor s článkem (PDF, DOC, DOCX). Maximální velikost: 10 MB</div>
            </div>
            
            <div class="actions">
                <button type="submit" style="background: var(--brand); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Odeslat článek</button>
                <a href="user.php" style="background: var(--muted); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-left: 12px; display: inline-block; font-weight: 600;">Zrušit</a>
            </div>
            
            <div style="margin-top: 16px;">
                <small style="color: var(--muted);">
                    <span class="req">*</span> Označená pole jsou povinná
                </small>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/Include/footer.php'; ?>
