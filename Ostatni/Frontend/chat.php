<?php require_once __DIR__ . '/../Backend/notAccess.php'; ?>
<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>
<?php
$loggedUserId = $_SESSION['user']['id'] ?? null;
$filterName = trim($_GET['filter_name'] ?? '');
$filterRole = isset($_GET['filter_role']) && $_GET['filter_role'] !== '' ? (int)$_GET['filter_role'] : null;
$selectedChatUserId = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : null;
if ($selectedChatUserId === $loggedUserId) {
    $selectedChatUserId = null;
}

$roles = [];
$rolesSql = "SELECT id, role FROM users_roles ORDER BY role";
if ($rolesResult = $conn->query($rolesSql)) {
    while ($roleRow = $rolesResult->fetch_assoc()) {
        $roles[] = $roleRow;
    }
    $rolesResult->free();
}

$unreadCounts = [];
$chatUsers = [];
$chatMessages = [];
$selectedChatUser = null;

try {
    $unreadSql = "SELECT sender_id, COUNT(*) as unread_total 
                  FROM chat_messages 
                  WHERE receiver_id = ? AND is_read = 0 
                  GROUP BY sender_id";
    $unreadStmt = $conn->prepare($unreadSql);
    if ($unreadStmt) {
        $unreadStmt->bind_param("i", $loggedUserId);
        $unreadStmt->execute();
        if (method_exists($unreadStmt, 'get_result')) {
            $unreadResult = $unreadStmt->get_result();
            if ($unreadResult) {
                while ($row = $unreadResult->fetch_assoc()) {
                    $unreadCounts[(int)$row['sender_id']] = (int)$row['unread_total'];
                }
            }
        } else {
            $unreadStmt->bind_result($senderId, $unreadTotal);
            while ($unreadStmt->fetch()) {
                $unreadCounts[(int)$senderId] = (int)$unreadTotal;
            }
        }
        $unreadStmt->close();
    }

    $whereClauses = ["u.id != ?"];
    $params = [$loggedUserId];
    $types = "i";

    if ($filterName !== '') {
        $whereClauses[] = "u.username LIKE ?";
        $params[] = '%' . $filterName . '%';
        $types .= "s";
    }

    if (!empty($filterRole)) {
        $whereClauses[] = "u.role_id = ?";
        $params[] = $filterRole;
        $types .= "i";
    }

    $whereSql = implode(' AND ', $whereClauses);
    $usersSql = "SELECT u.id, u.username, ur.role 
                 FROM users u 
                 LEFT JOIN users_roles ur ON u.role_id = ur.id
                 WHERE $whereSql
                 ORDER BY u.username ASC";

    $usersStmt = $conn->prepare($usersSql);
    if ($usersStmt) {
        $usersStmt->bind_param($types, ...$params);
        $usersStmt->execute();
        if (method_exists($usersStmt, 'get_result')) {
            $usersResult = $usersStmt->get_result();
            if ($usersResult) {
                while ($row = $usersResult->fetch_assoc()) {
                    $chatUsers[] = [
                        'id' => (int)$row['id'],
                        'username' => $row['username'] ?? 'Uživatel ' . $row['id'],
                        'role' => $row['role'] ?? 'Uživatel',
                        'unread' => $unreadCounts[(int)$row['id']] ?? 0
                    ];
                }
            }
        } else {
            $usersStmt->bind_result($userId, $username, $roleName);
            while ($usersStmt->fetch()) {
                $chatUsers[] = [
                    'id' => (int)$userId,
                    'username' => $username ?? ('Uživatel ' . $userId),
                    'role' => $roleName ?? 'Uživatel',
                    'unread' => $unreadCounts[(int)$userId] ?? 0
                ];
            }
        }
        $usersStmt->close();
    }

    if ($selectedChatUserId) {
        foreach ($chatUsers as $user) {
            if ($user['id'] === $selectedChatUserId) {
                $selectedChatUser = $user;
                break;
            }
        }

        if (!$selectedChatUser) {
            $selectedChatUserId = null;
        } else {
            $userOne = min($loggedUserId, $selectedChatUserId);
            $userTwo = max($loggedUserId, $selectedChatUserId);

            $chatId = null;
            $chatSql = "SELECT id FROM chats WHERE user_one_id = ? AND user_two_id = ? LIMIT 1";
            $chatStmt = $conn->prepare($chatSql);
            if ($chatStmt) {
                $chatStmt->bind_param("ii", $userOne, $userTwo);
                $chatStmt->execute();
                $chatStmt->bind_result($foundChatId);
                if ($chatStmt->fetch()) {
                    $chatId = (int)$foundChatId;
                }
                $chatStmt->close();
            }

            if ($chatId) {
                $messagesSql = "SELECT id, sender_id, receiver_id, message, created_at
                                FROM chat_messages
                                WHERE chat_id = ?
                                ORDER BY created_at ASC";
                $messagesStmt = $conn->prepare($messagesSql);
                if ($messagesStmt) {
                    $messagesStmt->bind_param("i", $chatId);
                    $messagesStmt->execute();
                    if (method_exists($messagesStmt, 'get_result')) {
                        $messagesResult = $messagesStmt->get_result();
                        if ($messagesResult) {
                            while ($row = $messagesResult->fetch_assoc()) {
                                $createdAt = $row['created_at'] ?? null;
                                $formattedTime = $createdAt ? date('d.m. H:i', strtotime($createdAt)) : '';
                                $chatMessages[] = [
                                    'id' => (int)$row['id'],
                                    'message' => $row['message'],
                                    'is_own' => ((int)$row['sender_id'] === $loggedUserId),
                                    'created_at' => $formattedTime
                                ];
                            }
                        }
                    } else {
                        $messagesStmt->bind_result($messageId, $senderId, $receiverId, $messageText, $createdAt);
                        while ($messagesStmt->fetch()) {
                            $chatMessages[] = [
                                'id' => (int)$messageId,
                                'message' => $messageText,
                                'is_own' => ((int)$senderId === $loggedUserId),
                                'created_at' => $createdAt ? date('d.m. H:i', strtotime($createdAt)) : ''
                            ];
                        }
                    }
                    $messagesStmt->close();
                }

                $updateReadSql = "UPDATE chat_messages SET is_read = 1 WHERE chat_id = ? AND receiver_id = ? AND is_read = 0";
                $updateReadStmt = $conn->prepare($updateReadSql);
                if ($updateReadStmt) {
                    $updateReadStmt->bind_param("ii", $chatId, $loggedUserId);
                    $updateReadStmt->execute();
                    $updateReadStmt->close();
                }
            }
        }
    }
} catch (Exception $chatException) {
    error_log('Chyba při načítání chatu: ' . $chatException->getMessage());
}

$baseQuery = $_GET;
unset($baseQuery['chat_with']);
$filterQueryString = http_build_query(array_filter($baseQuery, fn($value) => $value !== ''));
?>

<section class="section">
    <div class="section-title">Interní zprávy</div>
    <div class="section-body chat-layout">
        <div class="chat-column chat-left">
            <form method="get" class="chat-filter">
                <label for="filter_name">Filtrovat podle jména</label>
                <input type="text" id="filter_name" name="filter_name" value="<?= e($filterName) ?>" placeholder="např. Novák">

                <label for="filter_role">Role</label>
                <select id="filter_role" name="filter_role">
                    <option value="">Všechny role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int)$role['id'] ?>" <?= ($filterRole == $role['id']) ? 'selected' : '' ?>>
                            <?= e($role['role']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="actions">
                    <button class="btn" type="submit">Filtrovat</button>
                    <a class="btn btn-outline" href="./chat.php">Reset</a>
                </div>
            </form>

            <div class="chat-user-list">
                <?php if (empty($chatUsers)): ?>
                    <p class="chat-empty">Nenalezeni žádní uživatelé.</p>
                <?php else: ?>
                    <?php foreach ($chatUsers as $user): ?>
                        <?php
                            $linkQuery = $baseQuery;
                            $linkQuery['chat_with'] = $user['id'];
                            $link = './chat.php?' . http_build_query(array_filter($linkQuery, fn($value) => $value !== ''));
                            if ($filterQueryString === '' && empty($linkQuery)) {
                                $link = './chat.php?chat_with=' . $user['id'];
                            }
                        ?>
                        <a class="chat-user<?= ($selectedChatUserId === $user['id']) ? ' active' : '' ?>" href="<?= e($link) ?>">
                            <div>
                                <strong><?= e($user['username']) ?></strong>
                                <small><?= e($user['role']) ?></small>
                            </div>
                            <?php if (!empty($user['unread'])): ?>
                                <span class="chat-badge"><?= (int)$user['unread'] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="chat-column chat-middle">
            <?php if ($selectedChatUser && !empty($chatMessages)): ?>
                <div class="chat-header">
                    <strong><?= e($selectedChatUser['username']) ?></strong>
                    <span><?= e($selectedChatUser['role']) ?></span>
                </div>
                <div class="chat-messages scrollable">
                    <?php foreach ($chatMessages as $message): ?>
                        <div class="chat-bubble <?= $message['is_own'] ? 'me' : 'them' ?>">
                            <div><?= nl2br(e($message['message'])) ?></div>
                            <div class="chat-bubble__meta"><?= e($message['created_at']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($selectedChatUser): ?>
                <div class="chat-header">
                    <strong><?= e($selectedChatUser['username']) ?></strong>
                    <span><?= e($selectedChatUser['role']) ?></span>
                </div>
                <div class="chat-messages scrollable">
                    <p class="chat-empty">Zatím zde nejsou žádné zprávy.</p>
                </div>
            <?php else: ?>
                <div class="chat-messages scrollable">
                    <p class="chat-empty">Vyberte uživatele vlevo a zobrazí se zde konverzace.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="chat-column chat-right">
            <?php if ($selectedChatUser): ?>
                <div class="chat-header">
                    <strong>Nová zpráva</strong>
                    <span>Adresát: <?= e($selectedChatUser['username']) ?></span>
                </div>
                <form action="../Backend/chatControl.php" method="POST" class="chat-form">
                    <input type="hidden" name="action" value="send_message">
                    <input type="hidden" name="recipient_id" value="<?= $selectedChatUser['id'] ?>">
                    <label for="chat-message">Zpráva</label>
                    <textarea id="chat-message" name="message" placeholder="Napište zprávu..." required></textarea>
                    <div class="actions">
                        <button class="btn" type="submit">Odeslat</button>
                    </div>
                </form>
            <?php else: ?>
                <p class="chat-empty">Vyberte příjemce a poté můžete psát zprávu.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

<?php if ($selectedChatUser && !empty($chatMessages)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var chatMessages = document.querySelector('.chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});
</script>
<?php endif; ?>

