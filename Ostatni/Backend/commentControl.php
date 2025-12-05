<?php
require_once __DIR__ . '/notAccess.php';
require_once __DIR__ . '/../Database/dataControl.php';
require_once __DIR__ . '/../Database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_comment') {
        $userId = $_SESSION['user']['id'] ?? null;
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        $content = trim($_POST['content'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] > 0 ? (int)$_POST['parent_id'] : null;
        
        if (!$userId) {
            $_SESSION['error'] = "Musíte být přihlášeni.";
            header('Location: ../Frontend/login.php');
            exit();
        }
        
        if ($postId <= 0 || empty($content)) {
            $_SESSION['error'] = "Chybí obsah komentáře nebo ID článku.";
            header("Location: ../Frontend/article.php?id=$postId");
            exit();
        }
        
        // Ověření, že článek existuje a je publikovaný
        $post = select('posts', 'id, state', "id = $postId");
        if (empty($post)) {
            $_SESSION['error'] = "Článek nebyl nalezen.";
            header('Location: ../Frontend/index.php');
            exit();
        }
        
        // Komentáře jsou povoleny jen u publikovaných článků (stav "Schválen" nebo "Publikováno")
        $workflow = select('workflow', 'state', "id = " . (int)$post[0]['state']);
        $workflowState = !empty($workflow) ? $workflow[0]['state'] : '';
        if (!in_array($workflowState, ['Schválen', 'Publikováno'])) {
            $_SESSION['error'] = "Komentáře jsou povoleny jen u publikovaných článků.";
            header("Location: ../Frontend/article.php?id=$postId");
            exit();
        }
        
        $commentData = [
            'post_id' => $postId,
            'author_id' => $userId,
            'parent_id' => $parentId,
            'type' => 'public',
            'visibility' => 'public',
            'content' => $content
        ];
        
        $result = insert($commentData, 'comments');
        
        if ($result) {
            $_SESSION['success'] = "Komentář byl přidán.";
        } else {
            $_SESSION['error'] = "Nepodařilo se přidat komentář.";
        }
        
        header("Location: ../Frontend/article.php?id=$postId");
        exit();
    }
} else {
    $_SESSION['error'] = "Neplatný požadavek.";
    header('Location: ../Frontend/index.php');
    exit();
}
?>

