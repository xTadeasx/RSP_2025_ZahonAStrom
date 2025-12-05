<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
if (empty($_SESSION['user']['id'])) {
    $_SESSION['error'] = "Musíte být přihlášeni.";
    header('Location: ./login.php');
    exit();
}

$userId = (int)($_SESSION['user']['id'] ?? 0);
$userRoleId = $_SESSION['user']['role_id'] ?? null;
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($articleId <= 0) {
    $_SESSION['error'] = "Článek nebyl nalezen.";
    header('Location: ./articles_overview.php');
    exit();
}

// Načtení článku a kontrola vlastnictví
$article = select('posts', '*', "id = $articleId");
if (empty($article)) {
    $_SESSION['error'] = "Článek nebyl nalezen.";
    header('Location: ./articles_overview.php');
    exit();
}
$article = $article[0];

if ((int)$article['user_id'] !== $userId) {
    $_SESSION['error'] = "Nemáte oprávnění reagovat na tento článek.";
    header('Location: ./articles_overview.php');
    exit();
}

// Načtení recenzí
$reviewsSql = "SELECT pr.*, u.username AS reviewer_name
               FROM post_reviews pr
               LEFT JOIN users u ON pr.reviewer_id = u.id
               WHERE pr.post_id = ?
               ORDER BY pr.created_at DESC";
$reviews = [];
$stmt = $conn->prepare($reviewsSql);
if ($stmt) {
    $stmt->bind_param("i", $articleId);
    $stmt->execute();
    if (method_exists($stmt, 'get_result')) {
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $reviews[] = $row;
            }
        }
    }
    $stmt->close();
}
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin: 0;">Reakce na recenze</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">Článek: <strong><?= e($article['title'] ?? 'Bez názvu') ?></strong></p>
    </div>
    <div class="section-body" style="display: grid; gap: 16px;">
        <div style="padding: 14px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg);">
            <h3 style="margin-top: 0;">Nahrát novou verzi</h3>
            <form action="../Backend/postControl.php" method="POST" enctype="multipart/form-data" style="display: grid; gap: 8px;">
                <input type="hidden" name="action" value="author_update_version">
                <input type="hidden" name="article_id" value="<?= $articleId ?>">
                <label>Soubor (PDF, DOC, DOCX) *</label>
                <input type="file" name="file" accept=".pdf,.doc,.docx" required>
                <label>Poznámka pro redakci (volitelné)</label>
                <textarea name="author_note" rows="3" placeholder="Shrňte změny oproti předchozí verzi"><?= e($article['final_note'] ?? '') ?></textarea>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="btn" type="submit" style="background: var(--brand); color: white;">Nahrát novou verzi</button>
                    <a class="btn" href="./articles_overview.php" style="background: var(--muted); color: white;">Zpět</a>
                </div>
            </form>
            <?php if (!empty($article['file_path'])): ?>
                <p style="margin-top: 8px; color: var(--muted);">Aktuální soubor: <a href="./download.php?id=<?= $articleId ?>" target="_blank"><?= e(basename($article['file_path'])) ?></a></p>
            <?php endif; ?>
        </div>

        <div style="padding: 14px; border: 1px solid var(--border); border-radius: 8px;">
            <h3 style="margin-top: 0;">Posudky</h3>
            <?php if (empty($reviews)): ?>
                <p style="color: var(--muted);">Zatím nejsou k dispozici žádné recenze.</p>
            <?php else: ?>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($reviews as $rev): ?>
                        <div style="border: 1px solid var(--border); border-radius: 8px; padding: 12px;">
                            <div style="display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                                <div><strong>Recenzent:</strong> <?= e($rev['reviewer_name'] ?? 'Recenzent') ?></div>
                                <div style="color: var(--muted);"><?= date('d. m. Y H:i', strtotime($rev['created_at'])) ?></div>
                            </div>
                            <div style="margin-top: 8px; display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; font-size: 0.9rem;">
                                <div>Aktualita: <strong><?= (int)$rev['score_actuality'] ?>/5</strong></div>
                                <div>Originalita: <strong><?= (int)$rev['score_originality'] ?>/5</strong></div>
                                <div>Jazyk: <strong><?= (int)$rev['score_language'] ?>/5</strong></div>
                                <div>Odbornost: <strong><?= (int)$rev['score_expertise'] ?>/5</strong></div>
                            </div>
                            <?php if (!empty($rev['comment'])): ?>
                                <div style="margin-top: 10px; white-space: pre-wrap;"><?= e($rev['comment']) ?></div>
                            <?php endif; ?>

                            <div style="margin-top: 12px; padding: 10px; background: var(--bg); border-radius: 6px;">
                                <form action="../Backend/postControl.php" method="POST" style="display: grid; gap: 8px;">
                                    <input type="hidden" name="action" value="author_reply_review">
                                    <input type="hidden" name="review_id" value="<?= (int)$rev['id'] ?>">
                                    <label>Vaše reakce na recenzi</label>
                                    <textarea name="reply" rows="4" required placeholder="Vaše odpověď pro recenzenta"><?= e($rev['author_comment'] ?? '') ?></textarea>
                                    <?php if (!empty($rev['author_comment_at'])): ?>
                                        <div style="color: var(--muted); font-size: 0.85rem;">Poslední reakce: <?= date('d. m. Y H:i', strtotime($rev['author_comment_at'])) ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <button class="btn btn-small" type="submit" style="background: var(--brand); color: white;">Odeslat reakci</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

