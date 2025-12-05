<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
// Získání ID článku z URL
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($articleId <= 0) {
    $_SESSION['error'] = "Článek nebyl nalezen.";
    header('Location: ./index.php');
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
                u.username as author_username,
                u.id as author_id,
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
            // Fallback pro starší PHP
            $stmt->bind_result($id, $title, $body, $abstract, $keywords, $topic, $authors, $file_path, $created_at, $published_at, $post_state, $author_username, $author_id, $workflow_state);
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
                    'author_username' => $author_username,
                    'author_id' => $author_id,
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
    header('Location: ./index.php');
    exit();
}

// Formátování data
$date = $article['published_at'] ?? $article['created_at'];
if ($date) {
    try {
        $dateObj = new DateTime($date);
        $formattedDate = $dateObj->format('d. m. Y');
    } catch (Exception $e) {
        $formattedDate = date('d. m. Y', strtotime($date));
    }
} else {
    $formattedDate = 'Datum nezadáno';
}

// Určení autora
$author = $article['author_username'] ?? 'Neznámý autor';
if (!empty($article['authors'])) {
    $author .= ', ' . $article['authors'];
}

// Kategorie/téma
$category = $article['topic'] ?? 'Obecné';

// Klíčová slova
$keywords = $article['keywords'] ?? '';
?>

<article class="article">
  <header class="article-hero">
    <?php if (!empty($category)): ?>
      <div class="article-kicker"><?= e($category) ?></div>
    <?php endif; ?>
    <h1 class="article-title"><?= e($article['title'] ?? 'Bez názvu') ?></h1>
    <?php if (!empty($article['abstract'])): ?>
      <p class="article-perex"><?= e($article['abstract']) ?></p>
    <?php endif; ?>
    <div class="article-meta">
      <span>
        <?php if (!empty($article['author_username'])): ?>
          <a class="feature-link" href="./author.php?name=<?= urlencode($article['author_username']) ?>"><?= e($article['author_username']) ?></a>
          <?php if (!empty($article['authors'])): ?>
            , <?= e($article['authors']) ?>
          <?php endif; ?>
        <?php else: ?>
          <?= e($author) ?>
        <?php endif; ?>
      </span>
      <span>·</span>
      <span><?= e($formattedDate) ?></span>
      <?php if (!empty($article['workflow_state']) && $article['post_state'] != 5): ?>
        <span>·</span>
        <span style="color: #ff9800;"><?= e($article['workflow_state']) ?></span>
      <?php endif; ?>
    </div>
    
    <?php if (!empty($keywords)): ?>
      <div style="margin-top: 12px;">
        <strong>Klíčová slova:</strong>
        <span style="color: var(--muted);"><?= e($keywords) ?></span>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($article['file_path'])): ?>
      <div style="margin-top: 16px;">
        <a href="./download.php?id=<?= $article['id'] ?>" class="btn" style="background: var(--brand); color: white; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 8px;">
          📥 Stáhnout dokument
        </a>
      </div>
    <?php endif; ?>
  </header>

  <div class="article-body prose">
    <?php if (!empty($article['body'])): ?>
      <?php 
      // Zobrazení těla článku
      // Použijeme e() pro escapování HTML a nl2br pro zachování řádků
      $body = e($article['body']);
      // Převod nových řádků na <br>
      echo nl2br($body);
      ?>
    <?php else: ?>
      <p style="color: var(--muted); font-style: italic;">Článek nemá obsah.</p>
    <?php endif; ?>
  </div>

  <!-- Sekce komentářů -->
  <?php
  // Komentáře jsou zobrazeny jen u publikovaných článků
  $workflowState = $article['workflow_state'] ?? '';
  $showComments = in_array($workflowState, ['Schválen', 'Publikováno']);
  
  if ($showComments):
    // Načtení komentářů
    $comments = [];
    try {
        $commentsSql = "SELECT c.*, u.username AS author_name
                       FROM comments c
                       LEFT JOIN users u ON c.author_id = u.id
                       WHERE c.post_id = ? AND c.visibility = 'public'
                       ORDER BY c.created_at ASC";
        $commentsStmt = $conn->prepare($commentsSql);
        if ($commentsStmt) {
            $commentsStmt->bind_param("i", $articleId);
            $commentsStmt->execute();
            if (method_exists($commentsStmt, 'get_result')) {
                $commentsResult = $commentsStmt->get_result();
                if ($commentsResult) {
                    while ($row = $commentsResult->fetch_assoc()) {
                        $comments[] = $row;
                    }
                }
            }
            $commentsStmt->close();
        }
    } catch (Exception $e) {
        error_log("Chyba při načítání komentářů: " . $e->getMessage());
    }
  ?>
  <div class="article-comments" style="margin-top: 48px; padding-top: 32px; border-top: 2px solid var(--border);">
    <h2 style="margin-bottom: 20px;">Komentáře (<?= count($comments) ?>)</h2>
    
    <?php if (!empty($_SESSION['user']['id'])): ?>
      <!-- Formulář pro přidání komentáře -->
      <form action="../Backend/commentControl.php" method="POST" style="margin-bottom: 32px; padding: 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;">
        <input type="hidden" name="action" value="add_comment">
        <input type="hidden" name="post_id" value="<?= $articleId ?>">
        <label for="comment_content" style="display: block; margin-bottom: 8px; font-weight: 600;">Přidat komentář:</label>
        <textarea 
          id="comment_content" 
          name="content" 
          rows="4" 
          required
          placeholder="Napište svůj komentář..."
          style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; box-sizing: border-box;"
        ></textarea>
        <button type="submit" style="margin-top: 12px; background: var(--brand); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Odeslat komentář
        </button>
      </form>
    <?php else: ?>
      <p style="color: var(--muted); margin-bottom: 24px;">
        <a href="./login.php" style="color: var(--brand); text-decoration: underline;">Přihlaste se</a> pro přidání komentáře.
      </p>
    <?php endif; ?>
    
    <!-- Seznam komentářů -->
    <?php if (empty($comments)): ?>
      <p style="color: var(--muted); font-style: italic;">Zatím nejsou žádné komentáře.</p>
    <?php else: ?>
      <div style="display: grid; gap: 16px;">
        <?php foreach ($comments as $comment): ?>
          <div style="padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <strong><?= e($comment['author_name'] ?? 'Anonymní') ?></strong>
              <span style="color: var(--muted); font-size: 0.9rem;">
                <?= date('d. m. Y H:i', strtotime($comment['created_at'])) ?>
              </span>
            </div>
            <div style="white-space: pre-wrap; color: var(--text);"><?= e($comment['content']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <footer class="article-footer" style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
    <a class="btn btn-small" href="./index.php">← Zpět na domovskou stránku</a>
    <?php if (!empty($article['file_path'])): ?>
      <a href="./download.php?id=<?= $article['id'] ?>" class="btn btn-small" style="background: var(--brand); color: white; margin-left: 12px;">
        📥 Stáhnout dokument
      </a>
    <?php endif; ?>
  </footer>
</article>

<?php require_once __DIR__ . '/Include/footer.php'; ?>
