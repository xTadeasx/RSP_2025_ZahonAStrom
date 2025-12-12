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
                p.image_path,
                p.created_at,
                p.published_at,
                p.state as post_state,
                u.username as author_username,
                u.email as author_email,
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
            $stmt->bind_result($id, $title, $body, $abstract, $keywords, $topic, $authors, $file_path, $image_path, $created_at, $published_at, $post_state, $author_username, $author_email, $author_id, $workflow_state);
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
                    'author_email' => $author_email,
                    'author_id' => $author_id,
                    'workflow_state' => $workflow_state,
                    'image_path' => $image_path
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

// Určení zobrazovaného jména autora
function getDisplayName($article) {
    // Pokud existuje pole authors, použij ho
    if (!empty($article['authors'])) {
        return $article['authors'];
    }
    // Pokud ne, zkus extrahovat jméno z emailu
    if (!empty($article['author_email'])) {
        $email = $article['author_email'];
        // Formát: jmeno.prijmeni@rsp.cz -> Jméno Příjmení
        $emailParts = explode('@', $email);
        if (!empty($emailParts[0])) {
            $nameParts = explode('.', $emailParts[0]);
            $displayName = '';
            foreach ($nameParts as $part) {
                $displayName .= ucfirst($part) . ' ';
            }
            return trim($displayName);
        }
    }
    // Fallback na username (ale převedeme podtržítka na mezery a kapitalizujeme)
    if (!empty($article['author_username'])) {
        $username = str_replace('_', ' ', $article['author_username']);
        $parts = explode(' ', $username);
        $displayName = '';
        foreach ($parts as $part) {
            $displayName .= ucfirst($part) . ' ';
        }
        return trim($displayName);
    }
    return 'Neznámý autor';
}

$authorDisplayName = getDisplayName($article);

// Kategorie/téma
$category = $article['topic'] ?? 'Obecné';

// Klíčová slova
$keywords = $article['keywords'] ?? '';
?>

<article class="article">
  <header class="article-hero">
    <?php if (!empty($article['image_path'])): ?>
      <div style="margin-bottom:16px;">
        <img src="../<?= e($article['image_path']) ?>" alt="Obrázek článku" style="width:100%; max-height:360px; object-fit:cover; border-radius:12px;">
      </div>
    <?php endif; ?>
    <?php if (!empty($category)): ?>
      <div class="article-kicker"><?= e($category) ?></div>
    <?php endif; ?>
    <h1 class="article-title"><?= e($article['title'] ?? 'Bez názvu') ?></h1>
    <?php if (!empty($article['abstract'])): ?>
      <p class="article-perex"><?= e($article['abstract']) ?></p>
    <?php endif; ?>
    <div class="article-meta">
      <span>
        <?php if (!empty($article['author_id'])): ?>
          <a class="feature-link" href="./articles_overview.php?author_id=<?= (int)$article['author_id'] ?>"><?= e($authorDisplayName) ?></a>
        <?php else: ?>
          <?= e($authorDisplayName) ?>
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

  <!-- Recenze a komentáře -->
  <?php
  // Komentáře/recenze jsou zobrazeny jen u publikovaných článků
  $workflowState = $article['workflow_state'] ?? '';
  $showComments = in_array($workflowState, ['Schválen', 'Publikováno']);
  
  if ($showComments):
    // Načtení oficiálních recenzí (recenzenti)
    $officialReviews = [];
    try {
        $sqlOff = "SELECT pr.*, u.username AS reviewer_name
                   FROM post_reviews pr
                   LEFT JOIN users u ON pr.reviewer_id = u.id
                   WHERE pr.post_id = ?
                   ORDER BY pr.created_at DESC";
        $stmtOff = $conn->prepare($sqlOff);
        if ($stmtOff) {
            $stmtOff->bind_param("i", $articleId);
            $stmtOff->execute();
            if (method_exists($stmtOff, 'get_result')) {
                $resOff = $stmtOff->get_result();
                if ($resOff) {
                    while ($row = $resOff->fetch_assoc()) {
                        $officialReviews[] = $row;
                    }
                }
            }
            $stmtOff->close();
        }
    } catch (Exception $e) {
        error_log("Chyba při načítání oficiálních recenzí: " . $e->getMessage());
    }

    // Načtení uživatelských recenzí (kdokoli přihlášený)
    $userReviews = [];
    $userReviewExisting = null;
    if ($conn) {
        // Zajistit tabulku user_reviews (pokud chybí, vytvořit)
        try {
            $conn->query("
                CREATE TABLE IF NOT EXISTS user_reviews (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    user_id INT NOT NULL,
                    rating TINYINT NOT NULL,
                    comment TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT NULL,
                    INDEX user_reviews_post_idx (post_id),
                    INDEX user_reviews_user_idx (user_id),
                    CONSTRAINT user_reviews_post_fk FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                    CONSTRAINT user_reviews_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
            ");
        } catch (Exception $e) {
            error_log("Chyba při vytváření tabulky user_reviews: " . $e->getMessage());
        }
    }
    try {
        $sqlUser = "SELECT ur.*, u.username AS reviewer_name
                    FROM user_reviews ur
                    LEFT JOIN users u ON ur.user_id = u.id
                    WHERE ur.post_id = ?
                    ORDER BY ur.created_at DESC";
        $stmtUser = $conn->prepare($sqlUser);
        if ($stmtUser) {
            $stmtUser->bind_param("i", $articleId);
            $stmtUser->execute();
            if (method_exists($stmtUser, 'get_result')) {
                $resUser = $stmtUser->get_result();
                if ($resUser) {
                    while ($row = $resUser->fetch_assoc()) {
                        $userReviews[] = $row;
                        if (!empty($_SESSION['user']['id']) && (int)$row['user_id'] === (int)$_SESSION['user']['id']) {
                            $userReviewExisting = $row;
                        }
                    }
                }
            }
            $stmtUser->close();
        }
    } catch (Exception $e) {
        error_log("Chyba při načítání uživatelských recenzí: " . $e->getMessage());
    }

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

  <!-- Oficiální recenze (Recenzenti) -->
  <div class="article-comments" style="margin-top: 48px; padding-top: 32px; border-top: 2px solid var(--border);">
    <h2 style="margin-bottom: 20px;">Oficiální recenze (Recenzenti) (<?= count($officialReviews) ?>)</h2>
    <?php if (empty($officialReviews)): ?>
      <p style="color: var(--muted); font-style: italic;">Zatím nejsou oficiální recenze.</p>
    <?php else: ?>
      <div style="display:grid; gap:12px;">
        <?php foreach ($officialReviews as $rev): ?>
          <div style="padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
              <strong><?= e($rev['reviewer_name'] ?? 'Recenzent') ?></strong>
              <?php if (!empty($rev['updated_at'])): ?>
                <span style="color:var(--muted); font-size:0.9rem;"><?= date('d. m. Y H:i', strtotime($rev['updated_at'])) ?></span>
              <?php else: ?>
                <span style="color:var(--muted); font-size:0.9rem;"><?= date('d. m. Y H:i', strtotime($rev['created_at'])) ?></span>
              <?php endif; ?>
            </div>
            <div style="color:var(--muted); font-size:0.9rem; margin-bottom:6px;">
              Aktualita: <?= (int)$rev['score_actuality'] ?> · Originalita: <?= (int)$rev['score_originality'] ?> · Jazyk: <?= (int)$rev['score_language'] ?> · Odbornost: <?= (int)$rev['score_expertise'] ?>
            </div>
            <?php if (!empty($rev['comment'])): ?>
              <div style="white-space: pre-wrap;"><?= e($rev['comment']) ?></div>
            <?php endif; ?>
            <?php if (!empty($rev['author_comment'])): ?>
              <div style="margin-top:10px; padding:8px; background:var(--bg); border-radius:6px;">
                <strong>Reakce autora:</strong><br>
                <div style="white-space: pre-wrap; margin-top:4px;"><?= e($rev['author_comment']) ?></div>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Uživatelské recenze (kdokoli přihlášený) -->
  <div class="article-comments" style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
    <h2 style="margin-bottom: 16px;">Uživatelské recenze (<?= count($userReviews) ?>)</h2>

    <?php if (!empty($_SESSION['user']['id'])): ?>
      <form action="../Backend/userReviewControl.php" method="POST" style="margin-bottom: 24px; padding: 16px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;">
        <input type="hidden" name="action" value="add_user_review">
        <input type="hidden" name="post_id" value="<?= $articleId ?>">
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:10px;">
          <label for="user_rating" style="font-weight:600;">Hodnocení</label>
          <select id="user_rating" name="rating" required style="padding:8px 10px; border:1px solid var(--border); border-radius:6px;">
            <option value="">-- Vyberte --</option>
            <?php for ($i=1; $i<=5; $i++): ?>
              <option value="<?= $i ?>" <?= (!empty($userReviewExisting) && (int)$userReviewExisting['rating'] === $i) ? 'selected' : '' ?>>
                <?= $i ?> <?= $i==1 ? '(velmi špatné)' : ($i==5 ? '(výborné)' : '') ?>
              </option>
            <?php endfor; ?>
          </select>
          <span style="color:var(--muted); font-size:0.9rem;">1 = nejhorší, 5 = nejlepší</span>
        </div>
        <label for="user_comment" style="display:block; margin-bottom:6px; font-weight:600;">Komentář</label>
        <textarea 
          id="user_comment"
          name="comment"
          rows="5"
          required
          placeholder="Napište svou recenzi / zkušenost..."
          style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; font-family:inherit;"
        ><?= e($userReviewExisting['comment'] ?? '') ?></textarea>
        <button type="submit" style="margin-top:12px; background: var(--brand); color:white; padding:10px 18px; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
          <?= $userReviewExisting ? 'Uložit úpravu recenze' : 'Odeslat recenzi' ?>
        </button>
      </form>
    <?php else: ?>
      <p style="color: var(--muted); margin-bottom: 12px;">
        <a href="./login.php" style="color: var(--brand); text-decoration: underline;">Přihlaste se</a> a napište vlastní recenzi.
      </p>
    <?php endif; ?>

    <?php if (empty($userReviews)): ?>
      <p style="color: var(--muted); font-style: italic;">Zatím žádné uživatelské recenze.</p>
    <?php else: ?>
      <div style="display:grid; gap:12px;">
        <?php foreach ($userReviews as $urev): ?>
          <div style="padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
              <strong><?= e($urev['reviewer_name'] ?? 'Uživatel') ?></strong>
              <span style="color:var(--muted); font-size:0.9rem;"><?= date('d. m. Y H:i', strtotime($urev['created_at'])) ?></span>
            </div>
            <div style="color:#ffa000; font-weight:600; margin-bottom:6px;">Hodnocení: <?= (int)$urev['rating'] ?>/5</div>
            <?php if (!empty($urev['comment'])): ?>
              <div style="white-space: pre-wrap;"><?= e($urev['comment']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Sekce komentářů -->
  <div class="article-comments" style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
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
