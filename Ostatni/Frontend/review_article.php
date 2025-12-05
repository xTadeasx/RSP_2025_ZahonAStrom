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

// Ověření, že uživatel je recenzent (role_id = 3)
if (empty($userRoleId) || $userRoleId != 3) {
    $_SESSION['error'] = "Nemáte oprávnění psát recenze. Musíte být v roli Recenzenta.";
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

// Ověření, zda je recenzent přiřazen k tomuto článku
$isAssigned = false;
try {
    $assignmentCheckSql = "SELECT id FROM post_assignments WHERE post_id = ? AND reviewer_id = ?";
    $assignmentCheckStmt = $conn->prepare($assignmentCheckSql);
    if ($assignmentCheckStmt) {
        $assignmentCheckStmt->bind_param("ii", $articleId, $userId);
        $assignmentCheckStmt->execute();
        if (method_exists($assignmentCheckStmt, 'get_result')) {
            $assignmentCheckResult = $assignmentCheckStmt->get_result();
            $isAssigned = $assignmentCheckResult && $assignmentCheckResult->num_rows > 0;
        } else {
            $assignmentCheckStmt->bind_result($assignmentId);
            $isAssigned = $assignmentCheckStmt->fetch();
        }
        $assignmentCheckStmt->close();
    }
} catch (Exception $e) {
    error_log("Chyba při kontrole přiřazení: " . $e->getMessage());
}

if (!$isAssigned) {
    $_SESSION['error'] = "Nejste přiřazen k recenzi tohoto článku.";
    header('Location: ./articles_overview.php');
    exit();
}

// Načtení článku z databáze
$article = null;
try {
    $sql = "SELECT 
                p.id,
                p.title,
                p.abstract,
                p.body,
                p.topic,
                p.authors,
                p.created_at,
                p.file_path,
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
            $stmt->bind_result($id, $title, $abstract, $body, $topic, $authors, $created_at, $file_path, $author_username, $workflow_state);
            if ($stmt->fetch()) {
                $article = [
                    'id' => $id,
                    'title' => $title,
                    'abstract' => $abstract,
                    'body' => $body,
                    'topic' => $topic,
                    'authors' => $authors,
                    'created_at' => $created_at,
                    'file_path' => $file_path,
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

// Načtení existující recenze, pokud existuje
$existingReview = null;
try {
    $reviewSql = "SELECT 
                    id,
                    score_actuality,
                    score_originality,
                    score_language,
                    score_expertise,
                    comment,
                    author_comment,
                    author_comment_at,
                    created_at,
                    updated_at
                FROM post_reviews
                WHERE post_id = ? AND reviewer_id = ?";
    
    $reviewStmt = $conn->prepare($reviewSql);
    if ($reviewStmt) {
        $reviewStmt->bind_param("ii", $articleId, $userId);
        $reviewStmt->execute();
        
        if (method_exists($reviewStmt, 'get_result')) {
            $reviewResult = $reviewStmt->get_result();
            if ($reviewResult && $reviewResult->num_rows > 0) {
                $existingReview = $reviewResult->fetch_assoc();
            }
        } else {
            // Fallback
            $reviewStmt->bind_result($reviewId, $score_actuality, $score_originality, $score_language, $score_expertise, $comment, $author_comment, $author_comment_at, $created_at, $updated_at);
            if ($reviewStmt->fetch()) {
                $existingReview = [
                    'id' => $reviewId,
                    'score_actuality' => $score_actuality,
                    'score_originality' => $score_originality,
                    'score_language' => $score_language,
                    'score_expertise' => $score_expertise,
                    'comment' => $comment,
                    'author_comment' => $author_comment,
                    'author_comment_at' => $author_comment_at,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at
                ];
            }
        }
        $reviewStmt->close();
    }
} catch (Exception $e) {
    error_log("Chyba při načítání recenze: " . $e->getMessage());
}

$isEditMode = !empty($existingReview);
$pageTitle = $isEditMode ? "Upravit recenzi" : "Napsat recenzi";
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin: 0;"><?= e($pageTitle) ?></h1>
    </div>
    <div class="section-body">
        <!-- Informace o článku -->
        <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <h2 style="margin-top: 0; margin-bottom: 12px; font-size: 1.2rem;">Informace o článku</h2>
            <p><strong>Název:</strong> <?= e($article['title'] ?? 'Bez názvu') ?></p>
            <p><strong>Autor:</strong> <?= e($article['author_username'] ?? 'Neznámý autor') ?></p>
            <?php if (!empty($article['topic'])): ?>
                <p><strong>Téma:</strong> <?= e($article['topic']) ?></p>
            <?php endif; ?>
            <?php if (!empty($article['abstract'])): ?>
                <p><strong>Abstrakt:</strong> <?= e($article['abstract']) ?></p>
            <?php endif; ?>
            <?php if (!empty($article['file_path'])): ?>
                <p>
                    <strong>Dokument:</strong> 
                    <a href="./download.php?id=<?= $article['id'] ?>" target="_blank" style="color: var(--brand); text-decoration: underline;">
                        Stáhnout dokument
                    </a>
                </p>
            <?php endif; ?>
            <p style="margin-top: 12px;">
                <a href="./article.php?id=<?= $article['id'] ?>" target="_blank" class="btn btn-small" style="text-decoration: none;">
                    Zobrazit celý článek
                </a>
            </p>
        </div>

        <!-- Formulář pro recenzi -->
        <form action="../Backend/reviewControl.php" method="POST">
            <input type="hidden" name="action" value="<?= $isEditMode ? 'update_review' : 'create_review' ?>">
            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="review_id" value="<?= $existingReview['id'] ?>">
            <?php endif; ?>

            <h3 style="margin-top: 0; margin-bottom: 16px;">Hodnocení článku</h3>
            <p style="color: var(--muted); margin-bottom: 20px; font-size: 0.9rem;">
                Vyhodnoťte článek v následujících kritériích (1 = nejhorší, 5 = nejlepší):
            </p>

            <!-- Score: Aktualita -->
            <div style="margin-bottom: 20px;">
                <label for="score_actuality" style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Aktualita <span class="req">*</span>
                </label>
                <select 
                    id="score_actuality" 
                    name="score_actuality" 
                    required
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                    <option value="">-- Vyberte hodnocení --</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($existingReview && $existingReview['score_actuality'] == $i) ? 'selected' : '' ?>>
                            <?= $i ?> <?= $i == 1 ? '(velmi nízká)' : ($i == 5 ? '(velmi vysoká)' : '') ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                    Jak aktuální je téma a obsah článku?
                </div>
            </div>

            <!-- Score: Originalita -->
            <div style="margin-bottom: 20px;">
                <label for="score_originality" style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Originalita <span class="req">*</span>
                </label>
                <select 
                    id="score_originality" 
                    name="score_originality" 
                    required
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                    <option value="">-- Vyberte hodnocení --</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($existingReview && $existingReview['score_originality'] == $i) ? 'selected' : '' ?>>
                            <?= $i ?> <?= $i == 1 ? '(bez originality)' : ($i == 5 ? '(velmi originální)' : '') ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                    Jak originální je přístup a obsah článku?
                </div>
            </div>

            <!-- Score: Jazyk -->
            <div style="margin-bottom: 20px;">
                <label for="score_language" style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Jazyková úroveň <span class="req">*</span>
                </label>
                <select 
                    id="score_language" 
                    name="score_language" 
                    required
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                    <option value="">-- Vyberte hodnocení --</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($existingReview && $existingReview['score_language'] == $i) ? 'selected' : '' ?>>
                            <?= $i ?> <?= $i == 1 ? '(velmi špatná)' : ($i == 5 ? '(výborná)' : '') ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                    Jaká je jazyková úroveň a stylistika článku?
                </div>
            </div>

            <!-- Score: Odbornost -->
            <div style="margin-bottom: 20px;">
                <label for="score_expertise" style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Odborná úroveň <span class="req">*</span>
                </label>
                <select 
                    id="score_expertise" 
                    name="score_expertise" 
                    required
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; background: white;"
                >
                    <option value="">-- Vyberte hodnocení --</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($existingReview && $existingReview['score_expertise'] == $i) ? 'selected' : '' ?>>
                            <?= $i ?> <?= $i == 1 ? '(nízká)' : ($i == 5 ? '(vysoká)' : '') ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                    Jaká je odborná úroveň a hloubka zpracování tématu?
                </div>
            </div>

            <!-- Komentář -->
            <div style="margin-bottom: 20px;">
                <label for="comment" style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Komentář k recenzi
                </label>
                <textarea 
                    id="comment" 
                    name="comment" 
                    rows="10"
                    placeholder="Napište podrobný komentář k článku, jeho silné a slabé stránky, návrhy na zlepšení..."
                    style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-top: 6px; font-family: inherit;"
                ><?= e($existingReview['comment'] ?? '') ?></textarea>
                <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                    Podrobný komentář k článku, jeho silné a slabé stránky, návrhy na zlepšení atd.
                </div>
            </div>

            <?php if ($isEditMode): ?>
                <div style="padding: 12px; background: var(--bg); border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; color: var(--muted);">
                    <strong>Recenze byla vytvořena:</strong> <?= date('d. m. Y H:i', strtotime($existingReview['created_at'])) ?>
                    <?php if (!empty($existingReview['updated_at'])): ?>
                        <br><strong>Poslední úprava:</strong> <?= date('d. m. Y H:i', strtotime($existingReview['updated_at'])) ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($existingReview['author_comment'])): ?>
                    <div style="padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); margin-bottom: 20px;">
                        <strong>Reakce autora:</strong>
                        <div style="margin-top: 8px; white-space: pre-wrap;"><?= e($existingReview['author_comment']) ?></div>
                        <?php if (!empty($existingReview['author_comment_at'])): ?>
                            <div style="color: var(--muted); font-size: 0.85rem; margin-top: 6px;">Odesláno: <?= date('d. m. Y H:i', strtotime($existingReview['author_comment_at'])) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="actions">
                <button type="submit" style="background: var(--brand); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <?= $isEditMode ? 'Uložit změny' : 'Odeslat recenzi' ?>
                </button>
                <a href="./articles_overview.php" style="background: var(--muted); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-left: 12px; display: inline-block; font-weight: 600;">
                    Zrušit
                </a>
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

