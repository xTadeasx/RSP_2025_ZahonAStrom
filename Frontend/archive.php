<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<?php
// Demo data archivu (frontend-only)
$archive = [
  '2025' => [
    [ 'issue' => '1/2025', 'theme' => 'Digitalizace a AI v praxi', 'articles' => 8 ],
    [ 'issue' => '2/2025', 'theme' => 'Zdravotnictví a inovace', 'articles' => 7 ],
  ],
  '2024' => [
    [ 'issue' => '1/2024', 'theme' => 'Ekonomika a management', 'articles' => 9 ],
    [ 'issue' => '2/2024', 'theme' => 'Sociální vědy a pedagogika', 'articles' => 6 ],
  ],
];
?>

<section class="section">
  <div class="section-title">Archiv</div>
  <div class="section-body">
    <p>Procházejte starší čísla našeho časopisu podle roku vydání. Každé číslo je tematicky zaměřené a obsahuje recenzované články.</p>
  </div>
</section>

<?php foreach ($archive as $year => $issues): ?>
  <section class="section">
    <div class="section-title">Rok <?= e($year) ?></div>
    <div class="section-body">
      <div class="cards">
        <?php foreach ($issues as $i): ?>
          <article class="card">
            <div class="thumb"></div>
            <div class="body">
              <h3>Číslo <?= e($i['issue']) ?></h3>
              <p class="feature-text">Téma: <?= e($i['theme']) ?></p>
              <div class="meta">
                <span><?= e($i['articles']) ?> článků</span>
                <span><?= e($year) ?></span>
              </div>
            </div>
            <div class="actions">
              <a class="btn btn-small" href="./index.php">Zobrazit číslo</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endforeach; ?>

<?php require_once __DIR__ . '/Include/footer.php'; ?>


