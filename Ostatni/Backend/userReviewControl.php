<?php
require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/../Database/dataControl.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Neplatný požadavek.";
    header('Location: ../Frontend/index.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add_user_review') {
    $userId = $_SESSION['user']['id'] ?? null;
    $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = trim($_POST['comment'] ?? '');

    if (!$userId) {
        $_SESSION['error'] = "Musíte být přihlášeni.";
        header('Location: ../Frontend/login.php');
        exit();
    }

    if ($postId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
        $_SESSION['error'] = "Vyplňte prosím hodnocení (1-5) i komentář.";
        header("Location: ../Frontend/article.php?id={$postId}");
        exit();
    }

    // Ověřit, že článek je publikovaný/schválený
    $isPublished = false;
    $wf = select('posts p LEFT JOIN workflow w ON p.state = w.id', 'w.state', "p.id = {$postId}");
    if (!empty($wf)) {
        $stateName = $wf[0]['state'] ?? '';
        $isPublished = in_array($stateName, ['Schválen', 'Publikováno']);
    }
    if (!$isPublished) {
        $_SESSION['error'] = "Recenze lze přidat jen k publikovaným článkům.";
        header("Location: ../Frontend/article.php?id={$postId}");
        exit();
    }

    // Zajistit tabulku user_reviews
    $createSql = "
        CREATE TABLE IF NOT EXISTS user_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            comment TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT NULL,
            INDEX user_reviews_post_idx (post_id),
            INDEX user_reviews_user_idx (user_id),
            CONSTRAINT user_reviews_post_fk FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            CONSTRAINT user_reviews_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
    ";
    $conn->query($createSql);

    // Zjistit, zda uživatel už recenzi má – upsert
    $existing = select('user_reviews', 'id', "post_id = {$postId} AND user_id = {$userId}");
    if (!empty($existing)) {
        $rid = (int)$existing[0]['id'];
        $updated = update('user_reviews', [
            'rating' => $rating,
            'comment' => $comment,
            'updated_at' => date('Y-m-d H:i:s')
        ], "id = {$rid}");
        $_SESSION['success'] = $updated ? "Recenze byla aktualizována." : "Nepodařilo se uložit recenzi.";
        if ($updated) {
            insert([
                'user_id' => $userId,
                'event_type' => 'user_review_update',
                'level' => 'info',
                'message' => sprintf('Uživatel %d upravil uživatelskou recenzi k článku ID %d', $userId, $postId)
            ], 'system_logs');
        }
    } else {
        $inserted = insert([
            'post_id' => $postId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment
        ], 'user_reviews');
        $_SESSION['success'] = $inserted ? "Recenze byla uložena." : "Nepodařilo se uložit recenzi.";
        if ($inserted) {
            insert([
                'user_id' => $userId,
                'event_type' => 'user_review_create',
                'level' => 'info',
                'message' => sprintf('Uživatel %d přidal uživatelskou recenzi k článku ID %d', $userId, $postId)
            ], 'system_logs');
        }
    }

    header("Location: ../Frontend/article.php?id={$postId}");
    exit();
}

$_SESSION['error'] = "Neznámá akce.";
header('Location: ../Frontend/index.php');
exit();

