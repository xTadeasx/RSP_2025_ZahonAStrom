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
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin:0;">Správa redaktorů a recenzentů</h1>
        <p style="margin:6px 0 0; color:var(--muted);">Role: Admin, Šéfredaktor</p>
    </div>
    <div class="section-body" style="display:grid; gap:16px;">
        <div style="padding:14px; border:1px solid var(--border); border-radius:8px;">
            <h3 style="margin-top:0;">Přiřadit recenzenta k článku</h3>
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
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

