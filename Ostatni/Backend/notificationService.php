<?php
require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/../Database/dataControl.php';

if (!function_exists('notificationEnsureSchema')) {
    function notificationEnsureSchema(): bool
    {
        global $conn;
        if (!$conn) {
            return false;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) DEFAULT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME DEFAULT NULL,
                related_post_id INT DEFAULT NULL,
                INDEX notifications_user_idx (user_id),
                INDEX notifications_post_idx (related_post_id),
                CONSTRAINT notifications_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT notifications_post_fk FOREIGN KEY (related_post_id) REFERENCES posts(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
        ";

        $result = $conn->query($sql);
        if (!$result) {
            error_log('Create notifications table failed: ' . $conn->error);
            return false;
        }

        return true;
    }
}

if (!function_exists('createNotification')) {
    function createNotification(int $userId, string $message, ?string $type = null, ?int $relatedPostId = null): bool
    {
        global $conn;
        if (!$conn) {
            return false;
        }

        if (!notificationEnsureSchema()) {
            return false;
        }

        $data = [
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'related_post_id' => $relatedPostId
        ];

        return insert($data, 'notifications');
    }
}

