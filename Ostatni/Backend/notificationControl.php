<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user']['id'] ?? null;
$userRoleId = $_SESSION['user']['role_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nepřihlášený uživatel.'
    ]);
    exit;
}

// Recenzentské UI – zobrazuj pouze recenzentům
if ((int)$userRoleId !== 3) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nemáte oprávnění zobrazit notifikace.'
    ]);
    exit;
}

require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/notificationService.php';

$notifications = [];

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
    LIMIT 20
";

$stmt = $conn->prepare($sql);
if (!$stmt && (int)$conn->errno === 1146) {
    if (notificationEnsureSchema()) {
        $stmt = $conn->prepare($sql);
    }
}
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = formatNotificationRow($row);
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
            $row = [
                'id' => $id,
                'type' => $type,
                'message' => $message,
                'created_at' => $createdAt,
                'read_at' => $readAt,
                'related_post_id' => $relatedPostId,
                'post_title' => $postTitle
            ];
            $notifications[] = formatNotificationRow($row);
        }
    }
    $stmt->close();
} else {
    http_response_code(500);
    error_log('Notification endpoint error: ' . $conn->error);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nepodařilo se načíst notifikace.'
    ]);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'data' => $notifications
]);

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function formatNotificationRow(array $row): array
{
    $createdAt = $row['created_at'] ?? null;
    $createdAtHuman = null;
    if (!empty($createdAt)) {
        try {
            $dt = new DateTime($createdAt);
            $createdAtHuman = $dt->format('d. m. Y H:i');
        } catch (Exception $e) {
            $createdAtHuman = date('d. m. Y H:i', strtotime((string)$createdAt));
        }
    }

    return [
        'id' => (int)($row['id'] ?? 0),
        'type' => $row['type'] ?? null,
        'message' => $row['message'] ?? '',
        'created_at' => $createdAt,
        'created_at_human' => $createdAtHuman,
        'read_at' => $row['read_at'] ?? null,
        'is_read' => !empty($row['read_at']),
        'related_post_id' => $row['related_post_id'] ?? null,
        'post_title' => $row['post_title'] ?? null
    ];
}

