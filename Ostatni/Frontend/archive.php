<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>

<?php
$query = trim($_GET['q'] ?? '');

// Publikované články: podle published_at nebo stavu "Schválen"
$publishedStateId = null;
$wf = select('workflow', 'id', "state = 'Schválen'");
if (!empty($wf)) {
    $publishedStateId = (int)$wf[0]['id'];
}

$sql = "SELECT p.id, p.title, p.abstract, p.topic, p.authors, p.published_at, p.file_path, u.username AS author
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
  <div class="section-title">Archiv</div>
  <div class="section-body">
    <p>Prohlížejte schválené/publikované články, stáhněte PDF nebo zobrazte detail. Můžete hledat podle názvu.</p>
    <form method="GET" action="./archive.php" style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
      <input type="text" name="q" value="<?= e($query) ?>" placeholder="Hledat název..." style="flex:1; min-width:240px; padding:10px; border:1px solid var(--border); border-radius:8px;">
      <button class="btn" type="submit" style="background:var(--brand); color:white;">Hledat</button>
      <?php if ($query !== ''): ?>
        <a class="btn" href="./archive.php" style="background:var(--muted); color:white;">Reset</a>
      <?php endif; ?>
    </form>
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
                <span><?= e($a['topic'] ?? 'Obecné') ?></span>
                <?php if (!empty($a['authors'])): ?>
                  <span><?= e($a['authors']) ?></span>
                <?php elseif (!empty($a['author'])): ?>
                  <span><?= e($a['author']) ?></span>
                <?php endif; ?>
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

