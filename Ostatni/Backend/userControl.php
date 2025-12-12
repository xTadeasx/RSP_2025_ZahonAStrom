<?php
require_once __DIR__ . '/notAccess.php'; // !!! KOPÍROVAT DO KAŽDÉ CHRÁNĚNÉ STRÁNKY !!!
require_once __DIR__ . '/../Database/dataControl.php'; // Připojení k DB a funkce pro práci s daty 
require_once __DIR__ . '/sendEmail.php'; // Funkce pro odesílání emailů


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'edit_user':
            // Zajistíme nové sloupce, pokud chybí
            $conn->query("ALTER TABLE users ADD COLUMN bio TEXT NULL");
            $conn->query("ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL");

            $id = $_SESSION['user']['id'] ?? null;
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            if (!$id) {
                $_SESSION['error'] = "Nemáte oprávnění upravovat tento účet.";
                header('Location: ../Frontend/user.php');
                exit();
            }

            // Upload avataru (volitelné)
            $avatarPath = null;
            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $file = $_FILES['avatar'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','webp'];
                if (in_array($ext, $allowed)) {
                    $safeName = 'avatar_' . $id . '_' . uniqid() . '.' . $ext;
                    $target = $uploadDir . $safeName;
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $avatarPath = 'uploads/avatars/' . $safeName;
                    }
                }
            }

            $updateData = [
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'bio' => $bio
            ];
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            if ($avatarPath) {
                $updateData['avatar_path'] = $avatarPath;
                $_SESSION['user']['avatar_path'] = $avatarPath;
            }

            $result = update('users', $updateData, "id = $id");
            if ($result) {
                $_SESSION['success'] = "Účet byl úspěšně upraven.";
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['bio'] = $bio;
            } else {
                $_SESSION['error'] = "Došlo k chybě při úpravě účtu.";
            }
            header('Location: ../Frontend/user.php');
            break;

        case 'change_role':
            $currentUserId = $_SESSION['user']['id'] ?? null;
            $currentRole = $_SESSION['user']['role_id'] ?? null;
            $targetUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $targetRoleId = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;

            // Povoleno jen Admin (1) nebo Šéfredaktor (2)
            if (!$currentUserId || !in_array((int)$currentRole, [1, 2])) {
                $_SESSION['error'] = "Nemáte oprávnění měnit role.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }

            if ($targetUserId <= 0 || $targetRoleId <= 0) {
                $_SESSION['error'] = "Chybí uživatel nebo role.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }

            // Zjisti původní roli
            $existing = select('users', 'role_id, username', "id = {$targetUserId}");
            $oldRoleId = !empty($existing) ? (int)$existing[0]['role_id'] : null;
            $targetUsername = !empty($existing) ? $existing[0]['username'] : 'uživatel';

            // Aktualizace role
            $ok = update('users', ['role_id' => $targetRoleId], "id = {$targetUserId}");
            if ($ok) {
                $_SESSION['success'] = "Role byla upravena.";
                // Pokud měníme vlastní roli, upravíme session
                if ($targetUserId === $currentUserId) {
                    $_SESSION['user']['role_id'] = $targetRoleId;
                }

                // Log do system_logs
                $msg = sprintf(
                    'Uživatel ID %d změnil roli uživateli %s (ID %d) z %s na %s',
                    (int)$currentUserId,
                    $targetUsername,
                    (int)$targetUserId,
                    $oldRoleId === null ? 'neznámá' : (string)$oldRoleId,
                    (string)$targetRoleId
                );
                insert([
                    'user_id' => $currentUserId,
                    'event_type' => 'role_change',
                    'level' => 'info',
                    'message' => $msg
                ], 'system_logs');
            } else {
                $_SESSION['error'] = "Nepodařilo se uložit roli.";
            }
            header('Location: ../Frontend/staff_management.php');
            break;
        case 'logOut':
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['success'] = "Účet byl úspěšně odhlášen.";
            header('Location: ../Frontend/index.php');
            break;
        case 'writerRegister':
            $text = trim($_POST['text'] ?? '');
            if ($text === '' || mb_strlen($text) < 10) {
                $_SESSION['error'] = 'Zadejte prosím důvod (min. 10 znaků).';
                header('Location: ../Frontend/user.php');
                break;
            }
            // Předáme ID uživatele, aby se do emailu přidal tracking odkaz
            $userId = $_SESSION['user']['id'] ?? null;
            $ok = sendEmail('rspzahonastrom@gmail.com', 'Žádost o pozici autora', $text, $userId);
            if ($ok) {
                $_SESSION['success'] = 'Žádost o pozici autora byla odeslána.';
            } else {
                $_SESSION['error'] = 'Nepodařilo se odeslat e‑mail. Zkuste to prosím později.';
            }
            header('Location: ../Frontend/user.php');
            break;
        default:
            $_SESSION['error'] = "Neznámá akce: " . $_POST['action'];
            header('');
            break;
    }


}
?>