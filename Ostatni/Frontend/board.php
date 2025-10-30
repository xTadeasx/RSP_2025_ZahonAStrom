<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<section class="section">
  <div class="section-title">Redakční rada</div>
  <div class="section-body">
    <p>Tým autorů a editorů s dlouholetou zkušeností. Dbáme na odbornou kvalitu textů, srozumitelnost i transparentní recenzní řízení.</p>
  </div>
</section>

<?php
$team = [
  [ 'name' => 'Petr Novák', 'role' => 'Project Manager', 'bio' => 'Publikuje od roku 2005, zaměření na odhalování online podvodů a bezpečnost.', 'link' => '#'],
  [ 'name' => 'Hynek Bárta', 'role' => 'Šéfredaktor', 'bio' => 'Zodpovědný za obsah a metodiku recenzí, vede redakční tým.', 'link' => '#'],
  [ 'name' => 'Ladislav Šlapal', 'role' => 'Redaktor', 'bio' => 'Technologie a automobilový průmysl, dlouholetá praxe v IT.', 'link' => '#'],
  [ 'name' => 'Petr Lippert', 'role' => 'Redaktor', 'bio' => 'Zkušenosti z vývoje, zaměření na testy a analytické články.', 'link' => '#'],
  [ 'name' => 'Vít Nováček', 'role' => 'Autor', 'bio' => 'IT bezpečnost, testy a srovnání. Důraz na metodiku a přesnost.', 'link' => '#'],
  [ 'name' => 'Daniel Bartoš', 'role' => 'Autor', 'bio' => 'Inovativní podnikání, technologie a popularizace vědy.', 'link' => '#'],
];
?>

<section class="section">
  <div class="section-title">Tým</div>
  <div class="section-body">
    <div class="team">
      <?php foreach ($team as $m): ?>
        <div class="member-card">
          <div class="avatar" aria-hidden="true"></div>
          <div class="member-body">
            <h3 class="member-name"><?= e($m['name']) ?></h3>
            <div class="member-role"><?= e($m['role']) ?></div>
            <p class="member-bio"><?= e($m['bio']) ?></p>
            <a class="feature-link" href="./author.php?name=<?= urlencode($m['name']) ?>">Všechny články autora</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>


