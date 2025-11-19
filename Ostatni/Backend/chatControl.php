<?php
require_once __DIR__ . '/notAccess.php';
require_once __DIR__ . '/../Database/dataControl.php';
require_once __DIR__ . '/../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Neplatný požadavek.";
    header('Location: ../Frontend/index.php');
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send_message':
        $currentUserId = $_SESSION['user']['id'] ?? null;
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        $message = trim($_POST['message'] ?? '');

        $redirectUrl = '../Frontend/chat.php';
        if ($recipientId > 0) {
            $redirectUrl .= '?chat_with=' . $recipientId;
        }

        if (!$currentUserId) {
            $_SESSION['error'] = "Musíte být přihlášeni.";
            header('Location: ../Frontend/login.php');
            exit();
        }

        if ($recipientId <= 0) {
            $_SESSION['error'] = "Musíte vybrat příjemce.";
            header('Location: ' . $redirectUrl);
            exit();
        }

        if ($recipientId === $currentUserId) {
            $_SESSION['error'] = "Nemůžete posílat zprávy sami sobě.";
            header('Location: ' . $redirectUrl);
            exit();
        }

        if (empty($message)) {
            $_SESSION['error'] = "Zpráva nesmí být prázdná.";
            header('Location: ' . $redirectUrl);
            exit();
        }

        // Ověření, že příjemce existuje
        $recipient = select('users', 'id', "id = $recipientId");
        if (empty($recipient)) {
            $_SESSION['error'] = "Vybraný uživatel neexistuje.";
            header('Location: ../Frontend/index.php');
            exit();
        }

        $userOne = min($currentUserId, $recipientId);
        $userTwo = max($currentUserId, $recipientId);

        $chatId = null;
        $chatSql = "SELECT id FROM chats WHERE user_one_id = ? AND user_two_id = ? LIMIT 1";
        $chatStmt = $conn->prepare($chatSql);
        if ($chatStmt) {
            $chatStmt->bind_param("ii", $userOne, $userTwo);
            $chatStmt->execute();
            $chatStmt->bind_result($existingChatId);
            if ($chatStmt->fetch()) {
                $chatId = $existingChatId;
            }
            $chatStmt->close();
        }

        if (!$chatId) {
            $insertChatSql = "INSERT INTO chats (user_one_id, user_two_id) VALUES (?, ?)";
            $insertChatStmt = $conn->prepare($insertChatSql);
            if ($insertChatStmt) {
                $insertChatStmt->bind_param("ii", $userOne, $userTwo);
                if ($insertChatStmt->execute()) {
                    $chatId = $conn->insert_id;
                }
                $insertChatStmt->close();
            }
        }

        if (!$chatId) {
            $_SESSION['error'] = "Nepodařilo se vytvořit chat. Zkuste to prosím znovu.";
            header('Location: ' . $redirectUrl);
            exit();
        }

        $insertResult = insert([
            'chat_id' => $chatId,
            'sender_id' => $currentUserId,
            'receiver_id' => $recipientId,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ], 'chat_messages');

        if ($insertResult) {
            $_SESSION['success'] = "Zpráva byla odeslána.";
        } else {
            $_SESSION['error'] = "Zprávu se nepodařilo odeslat.";
        }

        header('Location: ' . $redirectUrl);
        exit();
        break;

    default:
        $_SESSION['error'] = "Neznámá akce.";
        header('Location: ../Frontend/index.php');
        break;
}

exit();

