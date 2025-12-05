<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
// Ověření přihlášení
if (empty($_SESSION['user']['id'])) {
    $_SESSION['error'] = "Musíte být přihlášeni.";
    header('Location: ./login.php');
    exit();
}

$userId = $_SESSION['user']['id'];
$userRoleId = $_SESSION['user']['role_id'] ?? null;

// Ověření oprávnění - pouze Admin (1), Šéfredaktor (2), Redaktor (4) mohou editovat
if (empty($userRoleId) || !in_array($userRoleId, [1, 2, 4])) {
    $_SESSION['error'] = "Nemáte oprávnění editovat články.";
    header('Location: ./articles_overview.php');
    exit();
}

// Získání ID článku z URL
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($articleId <= 0) {
    $_SESSION['error'] = "Článek nebyl nalezen.";
    header('Location: ./articles_overview.php');
    exit();
}

// Načtení článku z databáze
$article = null;
try {
    $sql = "SELECT 
                p.id,
                p.title,
                p.body,
                p.abstract,
                p.keywords,
                p.topic,
                p.authors,
                p.file_path,
                p.created_at,
                p.published_at,
                p.state as post_state,
                p.user_id as author_id,
                p.final_decision,
                p.final_note,
                p.final_decided_at,
                p.final_decided_by,
                u.username as author_username,
                w.state as workflow_state
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN workflow w ON p.state = w.id
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $articleId);
        $stmt->execute();
        
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $article = $result->fetch_assoc();
            }
        } else {
            // Fallback
            $stmt->bind_result($id, $title, $body, $abstract, $keywords, $topic, $authors, $file_path, $created_at, $published_at, $post_state, $author_id, $author_username, $workflow_state);
            if ($stmt->fetch()) {
                $article = [
                    'id' => $id,
                    'title' => $title,
                    'body' => $body,
                    'abstract' => $abstract,
                    'keywords' => $keywords,
                    'topic' => $topic,
                    'authors' => $authors,
                    'file_path' => $file_path,
                    'created_at' => $created_at,
                    'published_at' => $published_at,
                    'post_state' => $post_state,
                    'author_id' => $author_id,
                    'author_username' => $author_username,
                    'workflow_state' => $workflow_state
                ];
            }
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Chyba při načítání článku: " . $e->getMessage());
}

// Pokud článek nebyl nalezen
if (!$article) {
    $_SESSION['error'] = "Článek nebyl nalezen.";
    header('Location: ./articles_overview.php');
    exit();
}

// Načtení workflow stavů
$workflowStates = [];
try {
    $statesQuery = "SELECT id, state FROM workflow ORDER BY id";
    $statesResult = $conn->query($statesQuery);
    if ($statesResult && $statesResult->num_rows > 0) {
        while ($stateRow = $statesResult->fetch_assoc()) {
            $workflowStates[] = $stateRow;
        }
    }
} catch (Exception $e) {
    error_log("Chyba při načítání stavů workflow: " . $e->getMessage());
}

// Načtení recenzentů (role_id = 3)
$reviewers = [];
try {
    $reviewersQuery = "SELECT id, username, email FROM users WHERE role_id = 3 ORDER BY username";
    $reviewersResult = $conn->query($reviewersQuery);
    if ($reviewersResult && $reviewersResult->num_rows > 0) {
        while ($reviewerRow = $reviewersResult->fetch_assoc()) {
            $reviewers[] = $reviewerRow;
        }
    }
} catch (Exception $e) {
    error_log("Chyba při načítání recenzentů: " . $e->getMessage());
}

// Načtení aktuálně přiřazených recenzentů
$assignedReviewers = [];
try {
    $assignedQuery = "SELECT reviewer_id, assigned_at, due_date, status FROM post_assignments WHERE post_id = ?";
    $assignedStmt = $conn->prepare($assignedQuery);
    if ($assignedStmt) {
        $assignedStmt->bind_param("i", $articleId);
        $assignedStmt->execute();
        
        if (method_exists($assignedStmt, 'get_result')) {
            $assignedResult = $assignedStmt->get_result();
            if ($assignedResult && $assignedResult->num_rows > 0) {
                while ($assignedRow = $assignedResult->fetch_assoc()) {
                    $assignedReviewers[] = $assignedRow;
                }
            }
        } else {
            $assignedStmt->bind_result($reviewer_id, $assigned_at, $due_date, $status);
            while ($assignedStmt->fetch()) {
                $assignedReviewers[] = [
                    'reviewer_id' => $reviewer_id,
                    'assigned_at' => $assigned_at,
                    'due_date' => $due_date,
                    'status' => $status
                ];
            }
        }
        $assignedStmt->close();
    }
} catch (Exception $e) {
    error_log("Chyba při načítání přiřazených recenzentů: " . $e->getMessage());
}
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin: 0;">Editovat článek</h1>
    </div>
    <div class="section-body">
        <form action="../Backend/postControl.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_post">
            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
            
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
                    value="<?= e($article['title'] ?? '') ?>"
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
                ><?= e($article['abstract'] ?? '') ?></textarea>
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
                    value="<?= e($article['keywords'] ?? '') ?>"
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
                    value="<?= e($article['topic'] ?? '') ?>"
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
                ><?= e($article['authors'] ?? '') ?></textarea>
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
                ><?= e($article['body'] ?? '') ?></textarea>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Hlavní obsah článku</div>
            </div>
            
            <div style="margin-bottom: 18px;">
                <label for="state">
                    Stav workflow <span class="req">*</span>
                </label>
                <select 
                    id="state" 
                    name="state" 
                    required
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                    <option value="">-- Vyberte stav --</option>
                    <?php foreach ($workflowStates as $state): ?>
                        <option value="<?= $state['id'] ?>" <?= ($article['post_state'] == $state['id']) ? 'selected' : '' ?>>
                            <?= e($state['state']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Aktuální stav: <?= e($article['workflow_state'] ?? 'Nezadáno') ?></div>
            </div>

            <?php if (in_array($userRoleId, [1,2])): ?>
            <div style="margin-bottom: 18px; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg);">
                <label for="final_decision" style="font-weight: 600;">Finální rozhodnutí</label>
                <select 
                    id="final_decision" 
                    name="final_decision" 
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                    <option value="">-- Nechat beze změny --</option>
                    <option value="approve" <?= ($article['final_decision'] ?? '') === 'approve' ? 'selected' : '' ?>>Schválit k publikaci</option>
                    <option value="reject" <?= ($article['final_decision'] ?? '') === 'reject' ? 'selected' : '' ?>>Zamítnout</option>
                </select>
                <div style="margin-top: 10px;">
                    <label for="final_note">Poznámka k rozhodnutí</label>
                    <textarea 
                        id="final_note" 
                        name="final_note" 
                        rows="3" 
                        placeholder="Důvod rozhodnutí, podmínky, další kroky"
                        style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; font-family: inherit;"
                    ><?= e($article['final_note'] ?? '') ?></textarea>
                </div>
                <?php if (!empty($article['final_decided_at'])): ?>
                    <div style="color: var(--muted); font-size: 0.9rem; margin-top: 6px;">
                        Rozhodnuto: <?= date('d. m. Y H:i', strtotime($article['final_decided_at'])) ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 18px;">
                <label for="file">
                    Příloha (PDF, DOCX)
                </label>
                <?php if (!empty($article['file_path'])): ?>
                    <div style="padding: 8px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; margin-bottom: 8px;">
                        <strong>Aktuální soubor:</strong> 
                        <a href="./download.php?id=<?= $article['id'] ?>" target="_blank" style="color: var(--brand); text-decoration: underline;">
                            <?= e(basename($article['file_path'])) ?>
                        </a>
                        <label style="margin-left: 12px; font-weight: normal;">
                            <input type="checkbox" name="remove_file" value="1" style="margin-right: 4px;">
                            Odstranit současný soubor
                        </label>
                    </div>
                <?php endif; ?>
                <input 
                    type="file" 
                    id="file" 
                    name="file" 
                    accept=".pdf,.doc,.docx"
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                    <?php if (!empty($article['file_path'])): ?>
                        Můžete nahrát nový soubor (nahradí současný) nebo odstranit stávající soubor.
                    <?php else: ?>
                        Můžete nahrát soubor s článkem (PDF, DOC, DOCX). Maximální velikost: 10 MB
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sekce pro přiřazení recenzentů -->
            <div style="margin-bottom: 18px; padding: 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;">
                <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.1rem;">Přiřazení recenzentů</h3>
                
                <?php if (!empty($assignedReviewers)): ?>
                    <div style="margin-bottom: 16px;">
                        <strong>Aktuálně přiřazení recenzenti:</strong>
                        <ul style="margin: 8px 0; padding-left: 20px;">
                            <?php foreach ($assignedReviewers as $assigned): ?>
                                <?php
                                // Najít jméno recenzenta
                                $reviewerName = 'Neznámý';
                                foreach ($reviewers as $reviewer) {
                                    if ($reviewer['id'] == $assigned['reviewer_id']) {
                                        $reviewerName = $reviewer['username'];
                                        break;
                                    }
                                }
                                ?>
                                <li style="margin: 4px 0;">
                                    <?= e($reviewerName) ?>
                                    <?php if (!empty($assigned['due_date'])): ?>
                                        (Termín: <?= date('d. m. Y', strtotime($assigned['due_date'])) ?>)
                                    <?php endif; ?>
                                    <?php if (!empty($assigned['status'])): ?>
                                        - <?= e($assigned['status']) ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                        Přidat recenzenty:
                    </label>
                    <?php if (empty($reviewers)): ?>
                        <p style="color: var(--muted); font-style: italic;">Žádní recenzenti nejsou k dispozici</p>
                    <?php else: ?>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--border); border-radius: 6px; padding: 8px; background: white;">
                            <?php foreach ($reviewers as $reviewer): ?>
                                <?php
                                $isAssigned = false;
                                foreach ($assignedReviewers as $assigned) {
                                    if ($assigned['reviewer_id'] == $reviewer['id']) {
                                        $isAssigned = true;
                                        break;
                                    }
                                }
                                ?>
                                <label style="display: block; padding: 6px; margin: 4px 0; border-radius: 4px; cursor: <?= $isAssigned ? 'not-allowed' : 'pointer' ?>; background: <?= $isAssigned ? 'var(--bg)' : 'transparent' ?>;" onmouseover="this.style.background='<?= $isAssigned ? 'var(--bg)' : 'var(--bg)' ?>'" onmouseout="this.style.background='<?= $isAssigned ? 'var(--bg)' : 'transparent' ?>'">
                                    <input 
                                        type="checkbox" 
                                        name="reviewer_id[]" 
                                        value="<?= $reviewer['id'] ?>" 
                                        <?= $isAssigned ? 'disabled' : '' ?>
                                        style="margin-right: 8px;"
                                    >
                                    <strong><?= e($reviewer['username']) ?></strong>
                                    <span style="color: var(--muted); font-size: 0.9rem;"> (<?= e($reviewer['email']) ?>)</span>
                                    <?php if ($isAssigned): ?>
                                        <span style="color: var(--brand); font-size: 0.85rem; margin-left: 8px;">✓ již přiřazeno</span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                            Vyberte recenzenty, které chcete přidat k recenzi tohoto článku. Již přiřazení recenzenti jsou označeni a nelze je znovu vybrat.
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label for="review_due_date">
                        Termín recenze (volitelné):
                    </label>
                    <input 
                        type="date" 
                        id="review_due_date" 
                        name="review_due_date" 
                        style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px;"
                    >
                    <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">Datum, do kdy by měla být recenze dokončena</div>
                </div>
            </div>
            
            <div class="actions">
                <button type="submit" style="background: var(--brand); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Uložit změny</button>
                <a href="./articles_overview.php" style="background: var(--muted); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-left: 12px; display: inline-block; font-weight: 600;">Zrušit</a>
                <a href="./article.php?id=<?= $article['id'] ?>" style="background: var(--brand-2); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-left: 12px; display: inline-block; font-weight: 600;">Zobrazit článek</a>
            </div>
            
            <div style="margin-top: 16px;">
                <small style="color: var(--muted);">
                    <span class="req">*</span> Označená pole jsou povinná
                </small>
            </div>
        </form>
        
        <!-- Sekce recenzí a reakcí - pouze pro redaktory/šéfredaktory -->
        <?php if (in_array($userRoleId, [1, 2, 4])): ?>
            <?php
            // Načtení všech recenzí s reakcemi autora
            $allReviews = [];
            try {
                $reviewsSql = "SELECT pr.*, u.username AS reviewer_name, u2.username AS author_name
                              FROM post_reviews pr
                              LEFT JOIN users u ON pr.reviewer_id = u.id
                              LEFT JOIN users u2 ON (SELECT user_id FROM posts WHERE id = pr.post_id) = u2.id
                              WHERE pr.post_id = ?
                              ORDER BY pr.created_at DESC";
                $reviewsStmt = $conn->prepare($reviewsSql);
                if ($reviewsStmt) {
                    $reviewsStmt->bind_param("i", $articleId);
                    $reviewsStmt->execute();
                    if (method_exists($reviewsStmt, 'get_result')) {
                        $reviewsResult = $reviewsStmt->get_result();
                        if ($reviewsResult) {
                            while ($row = $reviewsResult->fetch_assoc()) {
                                $allReviews[] = $row;
                            }
                        }
                    }
                    $reviewsStmt->close();
                }
            } catch (Exception $e) {
                error_log("Chyba při načítání recenzí: " . $e->getMessage());
            }
            ?>
            <div style="margin-top: 32px; padding: 20px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;">
                <h3 style="margin-top: 0;">Recenze a reakce (celá konverzace)</h3>
                <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 16px;">
                    Jako redaktor vidíte všechny recenze a reakce autora.
                </p>
                
                <?php if (empty($allReviews)): ?>
                    <p style="color: var(--muted); font-style: italic;">Zatím nejsou žádné recenze.</p>
                <?php else: ?>
                    <div style="display: grid; gap: 16px;">
                        <?php foreach ($allReviews as $rev): ?>
                            <div style="padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap;">
                                    <div>
                                        <strong>Recenzent:</strong> <?= e($rev['reviewer_name'] ?? 'Neznámý') ?>
                                        <span style="color: var(--muted); font-size: 0.9rem; margin-left: 8px;">
                                            (<?= date('d. m. Y H:i', strtotime($rev['created_at'])) ?>)
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="margin-bottom: 12px; display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px; font-size: 0.9rem;">
                                    <div>Aktualita: <strong><?= (int)$rev['score_actuality'] ?>/5</strong></div>
                                    <div>Originalita: <strong><?= (int)$rev['score_originality'] ?>/5</strong></div>
                                    <div>Jazyk: <strong><?= (int)$rev['score_language'] ?>/5</strong></div>
                                    <div>Odbornost: <strong><?= (int)$rev['score_expertise'] ?>/5</strong></div>
                                </div>
                                
                                <?php if (!empty($rev['comment'])): ?>
                                    <div style="margin-bottom: 12px; padding: 12px; background: var(--bg); border-radius: 6px;">
                                        <strong>Komentář recenzenta:</strong>
                                        <div style="margin-top: 6px; white-space: pre-wrap;"><?= e($rev['comment']) ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($rev['author_comment'])): ?>
                                    <div style="margin-top: 12px; padding: 12px; background: #e8f5e9; border-left: 4px solid var(--brand); border-radius: 6px;">
                                        <strong>Reakce autora:</strong>
                                        <div style="margin-top: 6px; white-space: pre-wrap;"><?= e($rev['author_comment']) ?></div>
                                        <?php if (!empty($rev['author_comment_at'])): ?>
                                            <div style="color: var(--muted); font-size: 0.85rem; margin-top: 6px;">
                                                Odesláno: <?= date('d. m. Y H:i', strtotime($rev['author_comment_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

