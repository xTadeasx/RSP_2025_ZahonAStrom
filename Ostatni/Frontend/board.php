<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>

<section class="section">
  <div class="section-title">Redakční rada</div>
  <div class="section-body">
    <p>Tým autorů, editorů a recenzentů z databáze. Kliknutím zobrazíte jejich články.</p>
  </div>
</section>

<?php
$team = [];
$roleLabels = [
  1 => 'Administrátor',
  2 => 'Šéfredaktor',
  3 => 'Recenzent',
  4 => 'Redaktor',
  5 => 'Autor'
];

$sql = "
    SELECT 
      u.id, u.username, u.email, u.role_id,
      u.bio AS bio,
      u.avatar_path AS avatar_path,
      (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS posts_count
    FROM users u
    WHERE u.role_id IN (1,2,3,4,5)
    ORDER BY u.role_id ASC, u.email ASC
";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
  while ($row = $res->fetch_assoc()) {
    $team[] = $row;
  }
}
?>

<section class="section">
  <div class="section-title">Tým</div>
  <div class="section-body">
    <?php if (empty($team)): ?>
      <p style="color: var(--muted);">Zatím zde nejsou žádní uživatelé s rolí Autor/Redaktor/Recenzent/Šéfredaktor.</p>
    <?php else: ?>
      <div class="team">
        <?php foreach ($team as $m): ?>
          <?php
            // Určení zobrazovaného jména
            $displayName = 'Neznámý uživatel';
            if (!empty($m['email'])) {
                // Extrahuj jméno z emailu: jmeno.prijmeni@rsp.cz -> Jméno Příjmení
                $emailParts = explode('@', $m['email']);
                if (!empty($emailParts[0])) {
                    $nameParts = explode('.', $emailParts[0]);
                    $displayName = '';
                    foreach ($nameParts as $part) {
                        $displayName .= ucfirst($part) . ' ';
                    }
                    $displayName = trim($displayName);
                }
            } elseif (!empty($m['username'])) {
                // Fallback: převeď username na hezké jméno
                $username = str_replace('_', ' ', $m['username']);
                $parts = explode(' ', $username);
                $displayName = '';
                foreach ($parts as $part) {
                    $displayName .= ucfirst($part) . ' ';
                }
                $displayName = trim($displayName);
            }
            
            // Iniciály pro avatar
            $initials = '';
            if (!empty($displayName)) {
                $nameParts = explode(' ', $displayName);
                if (count($nameParts) >= 2) {
                    $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1, 'UTF-8') . mb_substr($nameParts[1], 0, 1, 'UTF-8'), 'UTF-8');
                } else {
                    $initials = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'), 'UTF-8');
                }
            }
            
            $roleName = $roleLabels[$m['role_id']] ?? 'Uživatel';
            $postsCount = (int)($m['posts_count'] ?? 0);
            $bio = trim($m['bio'] ?? '');
            $avatar = trim($m['avatar_path'] ?? '');
          ?>
          <div class="member-card">
            <?php if ($avatar !== ''): ?>
              <div class="avatar" aria-hidden="true" style="overflow:hidden;">
                <img src="../<?= e($avatar) ?>" alt="Avatar <?= e($displayName) ?>" style="width:100%; height:100%; object-fit:cover;">
              </div>
            <?php else: ?>
              <div class="avatar" aria-hidden="true" style="display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.4rem;">
                <?= e($initials) ?>
              </div>
            <?php endif; ?>
            <div class="member-body">
              <h3 class="member-name"><?= e($displayName) ?></h3>
              <div class="member-role"><?= e($roleName) ?></div>
              <?php if ($bio !== ''): ?>
                <p class="member-bio" style="margin-bottom:6px;"><?= e($bio) ?></p>
              <?php endif; ?>
              <p class="member-bio" style="margin-bottom:8px;">Článků: <?= $postsCount ?></p>
              <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="feature-link" href="./articles_overview.php?author_id=<?= (int)$m['id'] ?>">Všechny články</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>
