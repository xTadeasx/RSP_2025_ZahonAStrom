<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>

<?php
$query = trim($_GET['q'] ?? '');
$filterAuthorId = isset($_GET['author_id']) ? (int)$_GET['author_id'] : null;

// Publikované články: podle published_at nebo stavu "Schválen"
$publishedStateId = null;
$wf = select('workflow', 'id', "state = 'Schválen'");
if (!empty($wf)) {
    $publishedStateId = (int)$wf[0]['id'];
}

// Načtení jména autora, pokud je filtrováno podle autora
$authorName = null;
if ($filterAuthorId !== null && $filterAuthorId > 0) {
    try {
        $authorQuery = "SELECT username, email FROM users WHERE id = " . (int)$filterAuthorId;
        $authorResult = $conn->query($authorQuery);
        if ($authorResult && $authorResult->num_rows > 0) {
            $authorRow = $authorResult->fetch_assoc();
            // Určení zobrazovaného jména
            if (!empty($authorRow['email'])) {
                $emailParts = explode('@', $authorRow['email']);
                if (!empty($emailParts[0])) {
                    $nameParts = explode('.', $emailParts[0]);
                    $authorName = '';
                    foreach ($nameParts as $part) {
                        $authorName .= ucfirst($part) . ' ';
                    }
                    $authorName = trim($authorName);
                }
            }
            if (empty($authorName) && !empty($authorRow['username'])) {
                $username = str_replace('_', ' ', $authorRow['username']);
                $parts = explode(' ', $username);
                $authorName = '';
                foreach ($parts as $part) {
                    $authorName .= ucfirst($part) . ' ';
                }
                $authorName = trim($authorName);
            }
        }
    } catch (Exception $e) {
        error_log("Chyba při načítání jména autora: " . $e->getMessage());
    }
}

// Načtení seznamu autorů pro filtr
$authorsList = [];
try {
    $authorsSql = "SELECT DISTINCT u.id, u.username, u.email
                   FROM users u
                   INNER JOIN posts p ON u.id = p.user_id
                   WHERE p.state = " . (int)$publishedStateId . " OR p.published_at IS NOT NULL
                   ORDER BY u.username";
    $authorsResult = $conn->query($authorsSql);
    if ($authorsResult && $authorsResult->num_rows > 0) {
        while ($authorRow = $authorsResult->fetch_assoc()) {
            $authorsList[] = $authorRow;
        }
    }
} catch (Exception $e) {
    error_log("Chyba při načítání autorů: " . $e->getMessage());
}

$sql = "SELECT p.id, p.title, p.abstract, p.topic, p.authors, p.published_at, p.file_path, u.username AS author, u.id AS author_id, u.email AS author_email
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE 1=1";

if ($publishedStateId !== null) {
    $sql .= " AND p.state = " . (int)$publishedStateId;
} else {
    $sql .= " AND p.published_at IS NOT NULL";
}

if ($query !== '') {
    $escaped = $conn->real_escape_string($query);
    $sql .= " AND p.title LIKE '%" . $escaped . "%'";
}

if ($filterAuthorId !== null && $filterAuthorId > 0) {
    $sql .= " AND p.user_id = " . (int)$filterAuthorId;
}

$sql .= " ORDER BY COALESCE(p.published_at, p.created_at) DESC";

$articles = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}
?>

<section class="section">
  <div class="section-title">
    <h1 style="margin: 0;">
      Archiv
      <?php if ($authorName !== null): ?>
        - <?= e($authorName) ?>
      <?php endif; ?>
    </h1>
  </div>
  <div class="section-body">
    <p>Prohlížejte schválené/publikované články, stáhněte PDF nebo zobrazte detail. Můžete hledat podle názvu nebo filtrovat podle autora.</p>
    <form method="GET" action="./archive.php" class="filter-form-grid" style="margin-top:12px; display:grid; grid-template-columns: 1fr 1fr auto; gap:8px; align-items:end;">
      <?php if ($filterAuthorId !== null && $filterAuthorId > 0): ?>
        <input type="hidden" name="author_id" value="<?= (int)$filterAuthorId ?>">
      <?php endif; ?>
      <div>
        <label for="q" style="display:block; margin-bottom:6px; font-weight:600; font-size:0.9rem;">Filtr podle názvu:</label>
        <input type="text" id="q" name="q" value="<?= e($query) ?>" placeholder="Hledat název..." style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;">
      </div>
      <div>
        <label for="author_id" style="display:block; margin-bottom:6px; font-weight:600; font-size:0.9rem;">Filtr podle autora:</label>
        <select id="author_id" name="author_id" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; background:white;">
          <option value="">Všichni autoři</option>
          <?php foreach ($authorsList as $author): ?>
            <?php
              // Určení zobrazovaného jména
              $displayAuthorName = '';
              if (!empty($author['email'])) {
                  $emailParts = explode('@', $author['email']);
                  if (!empty($emailParts[0])) {
                      $nameParts = explode('.', $emailParts[0]);
                      foreach ($nameParts as $part) {
                          $displayAuthorName .= ucfirst($part) . ' ';
                      }
                      $displayAuthorName = trim($displayAuthorName);
                  }
              }
              if (empty($displayAuthorName) && !empty($author['username'])) {
                  $username = str_replace('_', ' ', $author['username']);
                  $parts = explode(' ', $username);
                  foreach ($parts as $part) {
                      $displayAuthorName .= ucfirst($part) . ' ';
                  }
                  $displayAuthorName = trim($displayAuthorName);
              }
            ?>
            <option value="<?= (int)$author['id'] ?>" <?= ($filterAuthorId === (int)$author['id']) ? 'selected' : '' ?>>
              <?= e($displayAuthorName ?: $author['username']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-buttons" style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="btn" type="submit" style="background:var(--brand); color:white; white-space:nowrap;">🔍 Filtrovat</button>
        <a class="btn" href="./archive.php<?= ($filterAuthorId !== null && $filterAuthorId > 0) ? '?author_id=' . (int)$filterAuthorId : '' ?>" style="background:var(--muted); color:white; white-space:nowrap;">🔄 Reset názvu</a>
      </div>
    </form>
    <?php if ($query !== '' || ($filterAuthorId !== null && $filterAuthorId > 0)): ?>
      <div style="margin-top:12px; padding:8px 12px; background:var(--bg); border-radius:6px; font-size:0.875rem; color:var(--muted);">
        <strong>Aktivní filtry:</strong>
        <?php if ($filterAuthorId !== null && $filterAuthorId > 0 && $authorName !== null): ?>
          <span style="background:var(--brand); color:white; padding:2px 8px; border-radius:4px; margin-left:6px;">
            Autor: <?= e($authorName) ?>
          </span>
        <?php endif; ?>
        <?php if ($query !== ''): ?>
          <span style="background:var(--brand); color:white; padding:2px 8px; border-radius:4px; margin-left:6px;">
            Název: "<?= e($query) ?>"
          </span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="section-body">
    <?php if (empty($articles)): ?>
      <p style="color:var(--muted);">Nenalezeny žádné články.</p>
    <?php else: ?>
      <div class="cards">
        <?php foreach ($articles as $a): ?>
          <article class="card">
            <div class="thumb"></div>
            <div class="body">
              <h3><?= e($a['title'] ?? 'Bez názvu') ?></h3>
              <?php if (!empty($a['abstract'])): ?>
                <p class="feature-text"><?= e(mb_substr($a['abstract'], 0, 160, 'UTF-8')) ?><?= strlen($a['abstract']) > 160 ? '…' : '' ?></p>
              <?php endif; ?>
              <div class="meta">
                <span>
                  <?php
                    // Určení zobrazovaného jména autora
                    $authorDisplayName = 'Neznámý autor';
                    if (!empty($a['authors'])) {
                        $authorDisplayName = $a['authors'];
                    } elseif (!empty($a['author_email'])) {
                        $emailParts = explode('@', $a['author_email']);
                        if (!empty($emailParts[0])) {
                            $nameParts = explode('.', $emailParts[0]);
                            $displayName = '';
                            foreach ($nameParts as $part) {
                                $displayName .= ucfirst($part) . ' ';
                            }
                            $authorDisplayName = trim($displayName);
                        }
                    } elseif (!empty($a['author'])) {
                        $username = str_replace('_', ' ', $a['author']);
                        $parts = explode(' ', $username);
                        $displayName = '';
                        foreach ($parts as $part) {
                            $displayName .= ucfirst($part) . ' ';
                        }
                        $authorDisplayName = trim($displayName);
                    }
                    
                    if (!empty($a['author_id'])) {
                        echo '<a class="feature-link" href="./archive.php?author_id=' . (int)$a['author_id'] . '" style="text-decoration:none; color:inherit;">' . e($authorDisplayName) . '</a>';
                    } else {
                        echo e($authorDisplayName);
                    }
                  ?>
                </span>
                <span><?= e($a['topic'] ?? 'Obecné') ?></span>
                <?php if (!empty($a['published_at'])): ?>
                  <span><?= date('d. m. Y', strtotime($a['published_at'])) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="actions">
              <a class="btn btn-small" href="./article.php?id=<?= (int)$a['id'] ?>">Zobrazit</a>
              <?php if (!empty($a['file_path'])): ?>
                <a class="btn btn-small" href="./download.php?id=<?= (int)$a['id'] ?>" target="_blank">Stáhnout</a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

