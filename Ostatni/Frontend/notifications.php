<?php require_once __DIR__ . '/../Backend/notAccess.php'; ?>
<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>
<?php require_once __DIR__ . '/../Backend/notificationService.php'; ?>

<?php
$userId = $_SESSION['user']['id'] ?? null;
$notifications = [];

if ($userId) {
    // Načtení všech notifikací uživatele
    $sql = "
        SELECT 
            n.id,
            n.type,
            n.message,
            n.created_at,
            n.read_at,
            n.related_post_id,
            p.title AS post_title
        FROM notifications n
        LEFT JOIN posts p ON p.id = n.related_post_id
        WHERE n.user_id = ?
        ORDER BY n.read_at IS NULL DESC, n.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt && (int)$conn->errno === 1146) {
        // Pokud tabulka neexistuje, vytvoříme ji
        if (notificationEnsureSchema()) {
            $stmt = $conn->prepare($sql);
        }
    }
    
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = $row;
                }
            }
        } else {
            $stmt->bind_result(
                $id,
                $type,
                $message,
                $createdAt,
                $readAt,
                $relatedPostId,
                $postTitle
            );
            while ($stmt->fetch()) {
                $notifications[] = [
                    'id' => $id,
                    'type' => $type,
                    'message' => $message,
                    'created_at' => $createdAt,
                    'read_at' => $readAt,
                    'related_post_id' => $relatedPostId,
                    'post_title' => $postTitle
                ];
            }
        }
        $stmt->close();
    }
}

// Počítání nepřečtených
$unreadCount = 0;
foreach ($notifications as $notif) {
    if (empty($notif['read_at'])) {
        $unreadCount++;
    }
}
?>

<section class="section">
    <div class="section-title">Moje notifikace</div>
    <div class="section-body">
        <?php if (empty($notifications)): ?>
            <p>Nemáte žádné notifikace.</p>
        <?php else: ?>
            <p style="margin-bottom: 16px;">
                <strong>Celkem notifikací:</strong> <?= count($notifications) ?> 
                <?php if ($unreadCount > 0): ?>
                    | <strong>Nepřečtených:</strong> <span style="color: var(--brand);"><?= $unreadCount ?></span>
                <?php endif; ?>
            </p>
            
            <div style="overflow-x: auto;">
                <table class="table table-striped" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--brand); color: white;">
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">ID</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">Typ</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">Zpráva</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">Článek</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">Vytvořeno</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">Přečteno</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--border);">Stav</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notif): ?>
                            <?php
                            $isRead = !empty($notif['read_at']);
                            $rowClass = $isRead ? '' : 'notification-row--unread';
                            $createdAt = $notif['created_at'] ?? null;
                            $readAt = $notif['read_at'] ?? null;
                            
                            // Formátování data
                            $createdAtFormatted = '';
                            if ($createdAt) {
                                try {
                                    $dt = new DateTime($createdAt);
                                    $createdAtFormatted = $dt->format('d. m. Y H:i');
                                } catch (Exception $e) {
                                    $createdAtFormatted = date('d. m. Y H:i', strtotime($createdAt));
                                }
                            }
                            
                            $readAtFormatted = '';
                            if ($readAt) {
                                try {
                                    $dt = new DateTime($readAt);
                                    $readAtFormatted = $dt->format('d. m. Y H:i');
                                } catch (Exception $e) {
                                    $readAtFormatted = date('d. m. Y H:i', strtotime($readAt));
                                }
                            } else {
                                $readAtFormatted = '-';
                            }
                            
                            // Typ notifikace - čitelný název
                            $typeLabel = $notif['type'] ?? 'Obecné';
                            $typeLabels = [
                                'assignment' => 'Přiřazení',
                                'review_submitted' => 'Recenze dokončena',
                                'article_state' => 'Změna stavu'
                            ];
                            if (isset($typeLabels[$typeLabel])) {
                                $typeLabel = $typeLabels[$typeLabel];
                            }
                            ?>
                            <tr class="<?= $rowClass ?>" style="<?= !$isRead ? 'background: rgba(78, 24, 53, 0.05);' : '' ?>">
                                <td style="padding: 10px; border: 1px solid var(--border);"><?= e((string)($notif['id'] ?? '')) ?></td>
                                <td style="padding: 10px; border: 1px solid var(--border);"><?= e($typeLabel) ?></td>
                                <td style="padding: 10px; border: 1px solid var(--border);">
                                    <p style="margin: 0; font-weight: <?= $isRead ? 'normal' : '600' ?>;"><?= nl2br(e($notif['message'] ?? '')) ?></p>
                                </td>
                                <td style="padding: 10px; border: 1px solid var(--border);">
                                    <?php if (!empty($notif['related_post_id']) && !empty($notif['post_title'])): ?>
                                        <a href="article.php?id=<?= (int)$notif['related_post_id'] ?>" style="color: var(--brand);">
                                            <?= e($notif['post_title']) ?>
                                        </a>
                                    <?php elseif (!empty($notif['related_post_id'])): ?>
                                        <a href="article.php?id=<?= (int)$notif['related_post_id'] ?>" style="color: var(--brand);">
                                            Článek #<?= (int)$notif['related_post_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px; border: 1px solid var(--border);"><?= e($createdAtFormatted) ?></td>
                                <td style="padding: 10px; border: 1px solid var(--border);"><?= e($readAtFormatted) ?></td>
                                <td style="padding: 10px; border: 1px solid var(--border);">
                                    <?php if ($isRead): ?>
                                        <span style="color: var(--success);">✓ Přečteno</span>
                                    <?php else: ?>
                                        <span style="color: var(--brand); font-weight: 600;">● Nepřečteno</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

