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
    function createNotification(int $userId, string $message, ?string $type = null, ?int $relatedPostId = null, ?int $senderId = null): bool
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

        $notificationResult = insert($data, 'notifications');
        
        // Vytvoření záznamu do system_logs
        if ($notificationResult && $senderId !== null && $relatedPostId !== null) {
            // Získání jmen uživatelů a názvu článku pro log
            $senderName = 'Neznámý uživatel';
            $recipientName = 'Neznámý uživatel';
            $postTitle = 'Neznámý článek';
            
            // Získání jména odesílatele
            $sender = select('users', 'username', "id = $senderId");
            if (!empty($sender)) {
                $senderName = $sender[0]['username'] ?? 'Neznámý uživatel';
            }
            
            // Získání jména příjemce
            $recipient = select('users', 'username', "id = $userId");
            if (!empty($recipient)) {
                $recipientName = $recipient[0]['username'] ?? 'Neznámý uživatel';
            }
            
            // Získání názvu článku
            $post = select('posts', 'title', "id = $relatedPostId");
            if (!empty($post)) {
                $postTitle = $post[0]['title'] ?? 'Neznámý článek';
            }
            
            // Vytvoření zprávy pro log
            $logMessage = sprintf(
                'Uživatel %s (ID: %d) přidal notifikaci uživateli %s (ID: %d) o článku "%s" (ID: %d). Typ: %s',
                $senderName,
                $senderId,
                $recipientName,
                $userId,
                $postTitle,
                $relatedPostId,
                $type ?? 'Obecné'
            );
            
            $logData = [
                'user_id' => $senderId,
                'event_type' => 'notification_created',
                'level' => 'info',
                'message' => $logMessage
            ];
            
            insert($logData, 'system_logs');
        }
        
        return $notificationResult;
    }
}

