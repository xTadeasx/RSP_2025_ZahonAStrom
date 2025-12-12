<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
if (empty($_SESSION['user']['id']) || !in_array($_SESSION['user']['role_id'] ?? null, [1,2])) {
    $_SESSION['error'] = "Přístup mají jen Admin a Šéfredaktor.";
    header('Location: ./index.php');
    exit();
}

$reviewers = select('users', 'id, username, email', "role_id = 3");
$editors = select('users', 'id, username, email', "role_id = 4");

// Přehled přiřazení
$assignments = [];
$sql = "SELECT pa.id AS assignment_id, pa.post_id, pa.reviewer_id, pa.due_date, pa.status, pa.assigned_at,
               p.title, p.state, u.username AS reviewer_name
        FROM post_assignments pa
        LEFT JOIN posts p ON pa.post_id = p.id
        LEFT JOIN users u ON pa.reviewer_id = u.id
        ORDER BY pa.assigned_at DESC";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $assignments[] = $row;
    }
}

// Poslední články pro rychlé zjištění ID
$recentPosts = [];
$sqlRecent = "
    SELECT p.id, p.title, p.state, w.state AS state_name, p.updated_at
    FROM posts p
    LEFT JOIN workflow w ON p.state = w.id
    ORDER BY p.id DESC
    LIMIT 15
";
$resRecent = $conn->query($sqlRecent);
if ($resRecent && $resRecent->num_rows > 0) {
    while ($row = $resRecent->fetch_assoc()) {
        $recentPosts[] = $row;
    }
}

// Recenzenti + jejich aktivní články
$reviewerArticles = [];
$sqlReviewerArticles = "
    SELECT 
        u.id AS user_id,
        u.username,
        u.email,
        pa.post_id,
        pa.status AS assignment_status,
        p.title AS post_title,
        w.state AS workflow_state,
        pa.assigned_at
    FROM users u
    LEFT JOIN post_assignments pa ON pa.reviewer_id = u.id
    LEFT JOIN posts p ON p.id = pa.post_id
    LEFT JOIN workflow w ON p.state = w.id
    WHERE u.role_id = 3
    ORDER BY u.username ASC, pa.assigned_at DESC
";
$resRev = $conn->query($sqlReviewerArticles);
if ($resRev && $resRev->num_rows > 0) {
    while ($row = $resRev->fetch_assoc()) {
        $reviewerArticles[$row['user_id']]['user'] = [
            'username' => $row['username'],
            'email' => $row['email']
        ];
        if (!empty($row['post_id'])) {
            $reviewerArticles[$row['user_id']]['posts'][] = $row;
        }
    }
}

// Redaktoři + jejich aktivní články
$editorArticles = [];
$sqlEditorArticles = "
    SELECT 
        u.id AS user_id,
        u.username,
        u.email,
        p.id AS post_id,
        p.title AS post_title,
        w.state AS workflow_state,
        p.updated_at
    FROM users u
    LEFT JOIN posts p ON p.user_id = u.id
    LEFT JOIN workflow w ON p.state = w.id
    WHERE u.role_id = 4
    ORDER BY u.username ASC, p.updated_at DESC
";
$resEd = $conn->query($sqlEditorArticles);
if ($resEd && $resEd->num_rows > 0) {
    while ($row = $resEd->fetch_assoc()) {
        $editorArticles[$row['user_id']]['user'] = [
            'username' => $row['username'],
            'email' => $row['email']
        ];
        if (!empty($row['post_id'])) {
            $editorArticles[$row['user_id']]['posts'][] = $row;
        }
    }
}

// Zajistit existenci tabulky issues (pro starší databáze)
$conn->query("
CREATE TABLE IF NOT EXISTS issues (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  year INT NOT NULL,
  number INT NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  published_at DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
");
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin:0;">Správa redaktorů a recenzentů</h1>
        <p style="margin:6px 0 0; color:var(--muted);">Role: Admin, Šéfredaktor</p>
    </div>
    <div class="section-body" style="display:grid; gap:16px;">
        <div style="padding:14px; border:1px solid var(--border); border-radius:8px;">
            <h3 style="margin-top:0;">Přiřadit recenzenta k článku</h3>
            <p style="margin:0 0 8px; color:var(--muted); font-size:0.95rem;">
                ID článku najdeš níže v sekci „Poslední články (ID)“ nebo v menu v „Přehled článků“.
            </p>
            <form action="../Backend/postControl.php" method="POST" style="display:grid; gap:8px;">
                <input type="hidden" name="action" value="assign_reviewer_direct">
                <label>ID článku *</label>
                <input type="number" name="post_id" required min="1">
                <label>Recenzent *</label>
                <select name="reviewer_id" required>
                    <option value="">-- vyberte recenzenta --</option>
                    <?php foreach ($reviewers as $r): ?>
                        <option value="<?= (int)$r['id'] ?>"><?= e($r['username']) ?> (<?= e($r['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <label>Termín (volitelný)</label>
                <input type="date" name="due_date">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button class="btn" type="submit" style="background:var(--brand); color:white;">Přiřadit</button>
                </div>
            </form>
        </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Seznam recenzentů</h3>
            <?php if (empty($reviewers)): ?>
                <p style="color:var(--muted);">Žádní recenzenti nejsou k dispozici.</p>
            <?php else: ?>
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($reviewers as $r): ?>
                        <li><?= e($r['username']) ?> (<?= e($r['email']) ?>)</li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px;">
            <h3 style="margin-top:0;">Aktivní přiřazení</h3>
            <?php if (empty($assignments)): ?>
                <p style="color:var(--muted);">Žádná přiřazení.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg); border-bottom:1px solid var(--border);">
                                <th style="padding:10px; text-align:left;">Článek</th>
                                <th style="padding:10px; text-align:left;">Recenzent</th>
                                <th style="padding:10px; text-align:left;">Termín</th>
                                <th style="padding:10px; text-align:left;">Stav</th>
                                <th style="padding:10px; text-align:left;">Akce</th>
                                <th style="padding:10px; text-align:left;">Přerozdělit</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($assignments as $a): ?>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:10px;">
                                    <strong><?= e($a['title'] ?? ('#'.$a['post_id'])) ?></strong><br>
                                    <span style="color:var(--muted); font-size:0.9rem;">ID: <?= (int)$a['post_id'] ?></span>
                                </td>
                                <td style="padding:10px;"><?= e($a['reviewer_name'] ?? 'Recenzent') ?></td>
                                <td style="padding:10px;"><?= !empty($a['due_date']) ? date('d. m. Y', strtotime($a['due_date'])) : '—' ?></td>
                                <td style="padding:10px;"><?= e($a['status'] ?? '-') ?></td>
                                <td style="padding:10px;">
                                    <form action="../Backend/postControl.php" method="POST" onsubmit="return confirm('Odebrat přiřazení?');">
                                        <input type="hidden" name="action" value="remove_reviewer_assignment">
                                        <input type="hidden" name="assignment_id" value="<?= (int)$a['assignment_id'] ?>">
                                        <button class="btn btn-small" type="submit" style="background:#f44336; color:white;">Odebrat</button>
                                    </form>
                                </td>
                                <td style="padding:10px;">
                                    <form action="../Backend/postControl.php" method="POST" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                        <input type="hidden" name="action" value="reassign_reviewer">
                                        <input type="hidden" name="assignment_id" value="<?= (int)$a['assignment_id'] ?>">
                                        <select name="new_reviewer_id" required style="padding:6px 8px; min-width:150px;">
                                            <option value="">-- nový recenzent --</option>
                                            <?php foreach ($reviewers as $r): ?>
                                                <option value="<?= (int)$r['id'] ?>" <?= ((int)$r['id'] === (int)$a['reviewer_id']) ? 'disabled' : '' ?>>
                                                    <?= e($r['username']) ?> (<?= e($r['email']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-small" type="submit" style="background:var(--brand-2); color:white;">Přerozdělit</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Poslední články (ID)</h3>
            <?php if (empty($recentPosts)): ?>
                <p style="color:var(--muted);">Zatím žádné články.</p>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--bg); border-bottom:1px solid var(--border);">
                                <th style="padding:8px; text-align:left; width:70px;">ID</th>
                                <th style="padding:8px; text-align:left;">Název</th>
                                <th style="padding:8px; text-align:left; width:180px;">Stav</th>
                                <th style="padding:8px; text-align:left; width:160px;">Aktualizováno</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentPosts as $p): ?>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:8px;"><?= (int)$p['id'] ?></td>
                                <td style="padding:8px;"><?= e($p['title'] ?? '(bez názvu)') ?></td>
                                <td style="padding:8px; color:var(--muted);"><?= e($p['state_name'] ?? ('ID ' . ($p['state'] ?? '-'))) ?></td>
                                <td style="padding:8px; color:var(--muted);"><?= !empty($p['updated_at']) ? date('d. m. Y H:i', strtotime($p['updated_at'])) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Recenzenti a jejich aktivní články</h3>
            <?php if (empty($reviewerArticles)): ?>
                <p style="color:var(--muted);">Žádní recenzenti nebo žádné přiřazené články.</p>
            <?php else: ?>
                <div style="display:grid; gap:10px;">
                    <?php foreach ($reviewerArticles as $revId => $data): ?>
                        <div style="border:1px solid var(--border); border-radius:6px; padding:10px;">
                            <strong><?= e($data['user']['username'] ?? 'Recenzent') ?></strong>
                            <div style="color:var(--muted); font-size:0.9rem;"><?= e($data['user']['email'] ?? '') ?></div>
                            <?php if (empty($data['posts'])): ?>
                                <p style="margin:8px 0 0; color:var(--muted);">Bez aktivních přiřazení.</p>
                            <?php else: ?>
                                <ul style="margin:8px 0 0 18px; padding:0;">
                                    <?php foreach ($data['posts'] as $post): ?>
                                        <li>
                                            <?= e($post['post_title'] ?? ('#'.$post['post_id'])) ?>
                                            <?php if (!empty($post['workflow_state'])): ?>
                                                <span style="color:var(--muted);">— <?= e($post['workflow_state']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($post['assignment_status'])): ?>
                                                <span style="color:var(--muted); font-size:0.9rem;">(<?= e($post['assignment_status']) ?>)</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px;">
            <h3 style="margin-top:0;">Redaktoři a jejich aktivní články</h3>
            <?php if (empty($editorArticles)): ?>
                <p style="color:var(--muted);">Žádní redaktoři nebo žádné články.</p>
            <?php else: ?>
                <div style="display:grid; gap:10px;">
                    <?php foreach ($editorArticles as $edId => $data): ?>
                        <div style="border:1px solid var(--border); border-radius:6px; padding:10px;">
                            <strong><?= e($data['user']['username'] ?? 'Redaktor') ?></strong>
                            <div style="color:var(--muted); font-size:0.9rem;"><?= e($data['user']['email'] ?? '') ?></div>
                            <?php if (empty($data['posts'])): ?>
                                <p style="margin:8px 0 0; color:var(--muted);">Bez aktivních článků.</p>
                            <?php else: ?>
                                <ul style="margin:8px 0 0 18px; padding:0;">
                                    <?php foreach ($data['posts'] as $post): ?>
                                        <li>
                                            <?= e($post['post_title'] ?? ('#'.$post['post_id'])) ?>
                                            <?php if (!empty($post['workflow_state'])): ?>
                                                <span style="color:var(--muted);">— <?= e($post['workflow_state']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Správa rolí</h3>
            <?php
              $allUsers = select('users', 'id, username, role_id, email', "1=1");
              $roles = select('users_roles', '*', "1=1");
              $roleMap = [];
              foreach ($roles as $r) { $roleMap[$r['id']] = $r['role']; }
            ?>
            <?php if (empty($allUsers)): ?>
              <p style="color:var(--muted);">Žádní uživatelé.</p>
            <?php else: ?>
              <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr style="background:var(--surface); border-bottom:1px solid var(--border);">
                      <th style="padding:8px; text-align:left;">Uživatel</th>
                      <th style="padding:8px; text-align:left;">Email</th>
                      <th style="padding:8px; text-align:left;">Role</th>
                      <th style="padding:8px; text-align:left;">Akce</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($allUsers as $u): ?>
                      <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:8px;"><?= e($u['username']) ?></td>
                        <td style="padding:8px;"><?= e($u['email'] ?? '') ?></td>
                        <td style="padding:8px;"><?= e($roleMap[$u['role_id']] ?? '—') ?></td>
                        <td style="padding:8px;">
                          <form action="../Backend/userControl.php" method="POST" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                            <input type="hidden" name="action" value="change_role">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <select name="role_id" required style="padding:6px 8px; min-width:140px;">
                              <option value="">-- vyberte roli --</option>
                              <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id'] ?>" <?= ((int)$r['id'] === (int)$u['role_id']) ? 'selected' : '' ?>>
                                  <?= e($r['role']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <button class="btn btn-small" type="submit" style="background:var(--brand); color:white;">Uložit</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
            <h3 style="margin-top:0;">Systémový log (posledních 50)</h3>
            <?php
              $logs = [];
              $logSql = "SELECT l.created_at, l.event_type, l.level, l.message, u.username AS user_name
                         FROM system_logs l
                         LEFT JOIN users u ON u.id = l.user_id
                         ORDER BY l.id DESC
                         LIMIT 50";
              $logRes = $conn->query($logSql);
              if ($logRes && $logRes->num_rows > 0) {
                  while ($row = $logRes->fetch_assoc()) {
                      $logs[] = $row;
                  }
              }
            ?>
            <?php if (empty($logs)): ?>
              <p style="color:var(--muted);">Žádné záznamy.</p>
            <?php else: ?>
              <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                  <thead>
                    <tr style="background:var(--bg); border-bottom:1px solid var(--border);">
                      <th style="padding:8px; text-align:left; width:120px;">Datum</th>
                      <th style="padding:8px; text-align:left; width:90px;">Uživatel</th>
                      <th style="padding:8px; text-align:left; width:120px;">Typ</th>
                      <th style="padding:8px; text-align:left; width:80px;">Úroveň</th>
                      <th style="padding:8px; text-align:left;">Zpráva</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($logs as $log): ?>
                      <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:8px; color:var(--muted);"><?= e(date('d. m. Y H:i', strtotime($log['created_at']))) ?></td>
                        <td style="padding:8px;"><?= e($log['user_name'] ?? '—') ?></td>
                        <td style="padding:8px;"><?= e($log['event_type'] ?? '') ?></td>
                        <td style="padding:8px;"><?= e($log['level'] ?? '') ?></td>
                        <td style="padding:8px;"><?= e($log['message'] ?? '') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
            <h3 style="margin-top:0;">Uživatelé (CRUD)</h3>
            <form action="../Backend/adminControl.php" method="POST" style="display:grid; gap:8px; margin-bottom:12px;">
                <input type="hidden" name="action" value="create_user">
                <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap:8px;">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Heslo" required>
                    <input type="text" name="phone" placeholder="Telefon">
                    <select name="role_id" required>
                        <option value="">-- Role --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id'] ?>"><?= e($r['role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-small" type="submit" style="width:max-content;">Přidat uživatele</button>
            </form>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--bg); border-bottom:1px solid var(--border);">
                            <th style="padding:8px; text-align:left;">Username</th>
                            <th style="padding:8px; text-align:left;">Email</th>
                            <th style="padding:8px; text-align:left;">Role</th>
                            <th style="padding:8px; text-align:left;">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $u): ?>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:8px;"><?= e($u['username']) ?></td>
                                <td style="padding:8px;"><?= e($u['email'] ?? '') ?></td>
                                <td style="padding:8px;"><?= e($roleMap[$u['role_id']] ?? '—') ?></td>
                                <td style="padding:8px;">
                                    <?php if ((int)$u['id'] !== (int)($_SESSION['user']['id'] ?? 0)): ?>
                                        <form action="../Backend/adminControl.php" method="POST" onsubmit="return confirm('Smazat uživatele?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <button class="btn btn-small" type="submit" style="background:#f44336; color:white;">Smazat</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:var(--muted); font-size:0.9rem;">Nelze smazat vlastní účet.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
          </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Role (CRUD)</h3>
            <form action="../Backend/adminControl.php" method="POST" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
                <input type="hidden" name="action" value="create_role">
                <input type="text" name="role" placeholder="Název role" required>
                <button class="btn btn-small" type="submit">Přidat roli</button>
            </form>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ($roles as $r): ?>
                    <form action="../Backend/adminControl.php" method="POST" onsubmit="return confirm('Smazat roli?');" style="display:flex; gap:6px; align-items:center;">
                        <input type="hidden" name="action" value="delete_role">
                        <input type="hidden" name="role_id" value="<?= (int)$r['id'] ?>">
                        <span style="padding:6px 10px; border:1px solid var(--border); border-radius:6px; background:var(--surface);"><?= e($r['role']) ?></span>
                        <button class="btn btn-small" type="submit" style="background:#f44336; color:white;">Smazat</button>
                    </form>
                <?php endforeach; ?>
            </div>
          </div>

        <div style="padding:14px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
            <h3 style="margin-top:0;">Vydání (issues) CRUD</h3>
            <?php
              $issues = select('issues', '*', "1=1 ORDER BY year DESC, number DESC");
            ?>
            <form action="../Backend/adminControl.php" method="POST" style="display:grid; gap:8px; margin-bottom:12px;">
                <input type="hidden" name="action" value="create_issue">
                <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap:8px;">
                    <input type="number" name="year" placeholder="Rok" required>
                    <input type="number" name="number" placeholder="Číslo" required>
                    <input type="text" name="title" placeholder="Název vydání">
                    <input type="date" name="published_at" placeholder="Datum publikace">
                </div>
                <button class="btn btn-small" type="submit" style="width:max-content;">Přidat vydání</button>
            </form>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--bg); border-bottom:1px solid var(--border);">
                            <th style="padding:8px; text-align:left;">Rok</th>
                            <th style="padding:8px; text-align:left;">Číslo</th>
                            <th style="padding:8px; text-align:left;">Název</th>
                            <th style="padding:8px; text-align:left;">Publikováno</th>
                            <th style="padding:8px; text-align:left;">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($issues as $iss): ?>
                            <tr style="border-bottom:1px solid var(--border);">
                                <td style="padding:8px;"><?= e($iss['year'] ?? '') ?></td>
                                <td style="padding:8px;"><?= e($iss['number'] ?? '') ?></td>
                                <td style="padding:8px;"><?= e($iss['title'] ?? '') ?></td>
                                <td style="padding:8px;"><?= !empty($iss['published_at']) ? e(date('d. m. Y', strtotime($iss['published_at']))) : '—' ?></td>
                                <td style="padding:8px;">
                                    <form action="../Backend/adminControl.php" method="POST" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                                        <input type="hidden" name="action" value="update_issue">
                                        <input type="hidden" name="issue_id" value="<?= (int)$iss['id'] ?>">
                                        <input type="number" name="year" value="<?= e($iss['year']) ?>" style="width:90px;">
                                        <input type="number" name="number" value="<?= e($iss['number']) ?>" style="width:70px;">
                                        <input type="text" name="title" value="<?= e($iss['title']) ?>" style="width:160px;">
                                        <input type="date" name="published_at" value="<?= !empty($iss['published_at']) ? e(date('Y-m-d', strtotime($iss['published_at']))) : '' ?>">
                                        <button class="btn btn-small" type="submit" style="background:var(--brand); color:white;">Uložit</button>
                                    </form>
                                    <form action="../Backend/adminControl.php" method="POST" onsubmit="return confirm('Smazat vydání?');" style="margin-top:6px;">
                                        <input type="hidden" name="action" value="delete_issue">
                                        <input type="hidden" name="issue_id" value="<?= (int)$iss['id'] ?>">
                                        <button class="btn btn-small" type="submit" style="background:#f44336; color:white;">Smazat</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
          </div>
    </div>
</div>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

