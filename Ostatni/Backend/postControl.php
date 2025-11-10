<?php
require_once __DIR__ . '/notAccess.php';
require_once __DIR__ . '/../Database/dataControl.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'create_post':
            $userId = $_SESSION['user']['id'] ?? null;
            
            // Ověření, že uživatel je přihlášen
            if (!$userId) {
                $_SESSION['error'] = "Musíte být přihlášeni.";
                header('Location: ../Frontend/login.php');
                exit();
            }
            
            // Ověření, že uživatel je v roli Autora (role_id = 5)
            $user = select('users', 'role_id', "id = $userId");
            if (empty($user) || ($user[0]['role_id'] ?? null) != 5) {
                $_SESSION['error'] = "Nemáte oprávnění vytvářet články. Musíte být v roli Autora.";
                header('Location: ../Frontend/user.php');
                exit();
            }
            
            // Validace vstupních dat
            $title = trim($_POST['title'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $abstract = trim($_POST['abstract'] ?? '');
            $keywords = trim($_POST['keywords'] ?? '');
            $topic = trim($_POST['topic'] ?? '');
            $authors = trim($_POST['authors'] ?? '');
            
            if (empty($title)) {
                $_SESSION['error'] = "Název článku je povinný.";
                header('Location: ../Frontend/clanek.php');
                exit();
            }
            
            if (empty($body)) {
                $_SESSION['error'] = "Obsah článku je povinný.";
                header('Location: ../Frontend/clanek.php');
                exit();
            }
            
            if (empty($abstract)) {
                $_SESSION['error'] = "Abstrakt článku je povinný.";
                header('Location: ../Frontend/clanek.php');
                exit();
            }
            
            // Nastavení výchozího stavu (Nový - workflow id = 1, pokud existuje)
            // Pokud workflow neexistuje, použijeme NULL
            $workflowState = null;
            $workflow = select('workflow', 'id', "state = 'Nový'");
            if (!empty($workflow)) {
                $workflowState = $workflow[0]['id'];
            }
            
            // Příprava dat pro vložení
            $postData = [
                'title' => $title,
                'body' => $body,
                'abstract' => $abstract,
                'keywords' => !empty($keywords) ? $keywords : null,
                'topic' => !empty($topic) ? $topic : null,
                'authors' => !empty($authors) ? $authors : null,
                'user_id' => $userId,
                'state' => $workflowState,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'created_by' => $userId,
                'updated_by' => $userId
            ];
            
            // Vložení článku do databáze
            $result = insert($postData, 'posts');
            
            if ($result) {
                $_SESSION['success'] = "Článek byl úspěšně vytvořen.";
                header('Location: ../Frontend/user.php');
            } else {
                $_SESSION['error'] = "Došlo k chybě při vytváření článku.";
                header('Location: ../Frontend/clanek.php');
            }
            break;
            
        default:
            $_SESSION['error'] = "Neznámá akce: " . ($_POST['action'] ?? '');
            header('Location: ../Frontend/user.php');
            break;
    }
} else {
    $_SESSION['error'] = "Neplatný požadavek.";
    header('Location: ../Frontend/user.php');
}
?>

