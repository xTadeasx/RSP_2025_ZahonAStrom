<?php
require_once __DIR__ . '/notAccess.php';
require_once __DIR__ . '/../Database/dataControl.php';
require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/sendEmail.php';
require_once __DIR__ . '/notificationService.php';
require_once __DIR__ . '/appServices.php';

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
            
            // Ověření, že uživatel má oprávnění vytvářet články (Administrátor, Šéfredaktor, Redaktor, Autor)
            $user = select('users', 'role_id', "id = $userId");
            $userRole = $user[0]['role_id'] ?? null;
            if (empty($user) || !in_array($userRole, [1, 2, 4, 5])) {
                $_SESSION['error'] = "Nemáte oprávnění vytvářet články. Musíte být Administrátor, Šéfredaktor, Redaktor nebo Autor.";
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
            
            // Zpracování nahrání souboru
            $filePath = null;
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../downloads/';
                
                // Vytvoření složky pro downloady, pokud neexistuje
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $file = $_FILES['file'];
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileError = $file['error'];
                
                // Validace typu souboru
                $allowedTypes = ['application/pdf', 'application/msword', 
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $allowedExtensions = ['pdf', 'doc', 'docx'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Kontrola přípony
                if (!in_array($fileExtension, $allowedExtensions)) {
                    $_SESSION['error'] = "Neplatný typ souboru. Povolené formáty: PDF, DOC, DOCX";
                    header('Location: ../Frontend/clanek.php');
                    exit();
                }
                
                // Kontrola velikosti (max 10 MB)
                $maxSize = 10 * 1024 * 1024; // 10 MB
                if ($fileSize > $maxSize) {
                    $_SESSION['error'] = "Soubor je příliš velký. Maximální velikost: 10 MB";
                    header('Location: ../Frontend/clanek.php');
                    exit();
                }
                
                // Generování bezpečného názvu souboru
                $safeFileName = uniqid('article_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                $targetPath = $uploadDir . $safeFileName;
                
                // Přesunutí souboru
                if (move_uploaded_file($fileTmpName, $targetPath)) {
                    // Uložení relativní cesty do databáze
                    $filePath = 'downloads/' . $safeFileName;
                } else {
                    $_SESSION['error'] = "Nepodařilo se nahrát soubor. Zkuste to prosím znovu.";
                    header('Location: ../Frontend/clanek.php');
                    exit();
                }
            }
            
            // Příprava dat pro vložení
            $postData = [
                'title' => $title,
                'body' => $body,
                'abstract' => $abstract,
                'keywords' => !empty($keywords) ? $keywords : null,
                'topic' => !empty($topic) ? $topic : null,
                'authors' => !empty($authors) ? $authors : null,
                'file_path' => $filePath,
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
                $_SESSION['success'] = "Článek byl úspěšně vytvořen." . ($filePath ? " Soubor byl nahrán." : "");
                header('Location: ../Frontend/user.php');
            } else {
                // Pokud se vložení nezdařilo a soubor byl nahrán, smažeme ho
                if ($filePath && file_exists(__DIR__ . '/../' . $filePath)) {
                    unlink(__DIR__ . '/../' . $filePath);
                }
                $_SESSION['error'] = "Došlo k chybě při vytváření článku.";
                header('Location: ../Frontend/clanek.php');
            }
            break;
            
        case 'update_post':
            $userId = $_SESSION['user']['id'] ?? null;
            $articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
            
            // Ověření, že uživatel je přihlášen
            if (!$userId) {
                $_SESSION['error'] = "Musíte být přihlášeni.";
                header('Location: ../Frontend/login.php');
                exit();
            }
            
            // Ověření, že uživatel má oprávnění editovat (Admin, Šéfredaktor, Redaktor)
            $user = select('users', 'role_id', "id = $userId");
            if (empty($user) || !in_array($user[0]['role_id'] ?? null, [1, 2, 4])) {
                $_SESSION['error'] = "Nemáte oprávnění editovat články.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            
            if ($articleId <= 0) {
                $_SESSION['error'] = "Neplatné ID článku.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            
            // Ověření, že článek existuje
            $existingArticle = select('posts', 'id, title, file_path, user_id, state', "id = $articleId");
            if (empty($existingArticle)) {
                $_SESSION['error'] = "Článek nebyl nalezen.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            $articleTitle = $existingArticle[0]['title'] ?? ("Článek #$articleId");
            $articleAuthorId = (int)($existingArticle[0]['user_id'] ?? 0);
            $previousState = (int)($existingArticle[0]['state'] ?? 0);
            
            // Validace vstupních dat
            $title = trim($_POST['title'] ?? '');
            $body = trim($_POST['body'] ?? '');
            $abstract = trim($_POST['abstract'] ?? '');
            $keywords = trim($_POST['keywords'] ?? '');
            $topic = trim($_POST['topic'] ?? '');
            $authors = trim($_POST['authors'] ?? '');
            $state = isset($_POST['state']) ? (int)$_POST['state'] : null;
            $finalDecision = $_POST['final_decision'] ?? null;
            $finalNote = trim($_POST['final_note'] ?? '');
            
            if (empty($title)) {
                $_SESSION['error'] = "Název článku je povinný.";
                header("Location: ../Frontend/edit_article.php?id=$articleId");
                exit();
            }
            
            if (empty($body)) {
                $_SESSION['error'] = "Obsah článku je povinný.";
                header("Location: ../Frontend/edit_article.php?id=$articleId");
                exit();
            }
            
            if (empty($abstract)) {
                $_SESSION['error'] = "Abstrakt článku je povinný.";
                header("Location: ../Frontend/edit_article.php?id=$articleId");
                exit();
            }
            
            if ($state === null || $state <= 0) {
                $_SESSION['error'] = "Stav workflow je povinný.";
                header("Location: ../Frontend/edit_article.php?id=$articleId");
                exit();
            }
            
            // Zpracování souboru
            $currentFilePath = $existingArticle[0]['file_path'] ?? null;
            $removeFile = isset($_POST['remove_file']) && $_POST['remove_file'] == '1';
            $fileChanged = false;
            $newFilePath = $currentFilePath;
            
            // Odstranění souboru, pokud je požadováno
            if ($removeFile && $currentFilePath) {
                $fullPath = __DIR__ . '/../' . $currentFilePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                $newFilePath = null;
                $fileChanged = true;
            }
            
            // Nahrání nového souboru
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../downloads/';
                
                // Vytvoření složky pro downloady, pokud neexistuje
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $file = $_FILES['file'];
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                
                // Validace typu souboru
                $allowedExtensions = ['pdf', 'doc', 'docx'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Kontrola přípony
                if (!in_array($fileExtension, $allowedExtensions)) {
                    $_SESSION['error'] = "Neplatný typ souboru. Povolené formáty: PDF, DOC, DOCX";
                    header("Location: ../Frontend/edit_article.php?id=$articleId");
                    exit();
                }
                
                // Kontrola velikosti (max 10 MB)
                $maxSize = 10 * 1024 * 1024; // 10 MB
                if ($fileSize > $maxSize) {
                    $_SESSION['error'] = "Soubor je příliš velký. Maximální velikost: 10 MB";
                    header("Location: ../Frontend/edit_article.php?id=$articleId");
                    exit();
                }
                
                // Smazání starého souboru, pokud existuje
                if ($currentFilePath) {
                    $oldFullPath = __DIR__ . '/../' . $currentFilePath;
                    if (file_exists($oldFullPath)) {
                        unlink($oldFullPath);
                    }
                }
                
                // Generování bezpečného názvu souboru
                $safeFileName = uniqid('article_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                $targetPath = $uploadDir . $safeFileName;
                
                // Přesunutí souboru
                if (move_uploaded_file($fileTmpName, $targetPath)) {
                    $newFilePath = 'downloads/' . $safeFileName;
                    $fileChanged = true;
                } else {
                    $_SESSION['error'] = "Nepodařilo se nahrát soubor. Zkuste to prosím znovu.";
                    header("Location: ../Frontend/edit_article.php?id=$articleId");
                    exit();
                }
            }
            
            // Mapování finálního rozhodnutí na workflow stav (jen Admin/Šéfredaktor)
            if (in_array($user[0]['role_id'] ?? null, [1, 2]) && !empty($finalDecision)) {
                $decisionStateName = null;
                if ($finalDecision === 'approve') {
                    $decisionStateName = 'Schválen';
                } elseif ($finalDecision === 'reject') {
                    $decisionStateName = 'Zamítnut';
                }

                if ($decisionStateName !== null) {
                    $workflowState = select('workflow', 'id', "state = '" . $conn->real_escape_string($decisionStateName) . "'");
                    if (!empty($workflowState)) {
                        $state = (int)$workflowState[0]['id'];
                    }
                }
            }

            // Příprava dat pro aktualizaci
            $updateData = [
                'title' => $title,
                'body' => $body,
                'abstract' => $abstract,
                'keywords' => !empty($keywords) ? $keywords : null,
                'topic' => !empty($topic) ? $topic : null,
                'authors' => !empty($authors) ? $authors : null,
                'state' => $state,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId
            ];

            if (in_array($user[0]['role_id'] ?? null, [1, 2])) {
                if (!empty($finalDecision)) {
                    $updateData['final_decision'] = $finalDecision;
                    $updateData['final_decided_at'] = date('Y-m-d H:i:s');
                    $updateData['final_decided_by'] = $userId;
                }
                $updateData['final_note'] = !empty($finalNote) ? $finalNote : null;
            }
            
            // Přidání file_path pouze pokud se změnil
            if ($fileChanged) {
                $updateData['file_path'] = $newFilePath;
            }
            
            // Aktualizace článku - použijeme přímý SQL dotaz s prepared statement pro bezpečnost
            $setParts = [];
            $params = [];
            $types = '';
            
            // Definice typů pro jednotlivé sloupce
            $columnTypes = [
                'title' => 's',
                'body' => 's',
                'abstract' => 's',
                'keywords' => 's',
                'topic' => 's',
                'authors' => 's',
                'file_path' => 's',
                'state' => 'i',
                'updated_at' => 's',
                'updated_by' => 'i'
            ];
            
            foreach ($updateData as $key => $value) {
                $setParts[] = "$key = ?";
                $params[] = $value;
                // Použijeme definovaný typ nebo defaultně string
                $types .= $columnTypes[$key] ?? 's';
            }
            
            $sql = "UPDATE posts SET " . implode(", ", $setParts) . " WHERE id = ?";
            $params[] = $articleId;
            $types .= 'i';
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                $result = $stmt->execute();
                if (!$result) {
                    error_log("Chyba při aktualizaci článku: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $result = false;
                error_log("Chyba při přípravě SQL dotazu: " . $conn->error);
            }
            
            if ($result) {
                // Zpracování přiřazení recenzentů
                $reviewerIds = isset($_POST['reviewer_id']) ? $_POST['reviewer_id'] : [];
                $dueDate = !empty($_POST['review_due_date']) ? $_POST['review_due_date'] : null;
                
                $newReviewersAdded = false;
                
                // Přidání nových recenzentů
                if (!empty($reviewerIds) && is_array($reviewerIds)) {
                    foreach ($reviewerIds as $reviewerId) {
                        $reviewerId = (int)$reviewerId;
                        if ($reviewerId > 0) {
                            // Kontrola, zda už není přiřazen - použijeme prepared statement
                            $existingAssignmentSql = "SELECT id FROM post_assignments WHERE post_id = ? AND reviewer_id = ?";
                            $existingAssignmentStmt = $conn->prepare($existingAssignmentSql);
                            $existingAssignment = [];
                            if ($existingAssignmentStmt) {
                                $existingAssignmentStmt->bind_param("ii", $articleId, $reviewerId);
                                $existingAssignmentStmt->execute();
                                if (method_exists($existingAssignmentStmt, 'get_result')) {
                                    $existingAssignmentResult = $existingAssignmentStmt->get_result();
                                    if ($existingAssignmentResult && $existingAssignmentResult->num_rows > 0) {
                                        while ($row = $existingAssignmentResult->fetch_assoc()) {
                                            $existingAssignment[] = $row;
                                        }
                                    }
                                } else {
                                    $existingAssignmentStmt->bind_result($assignmentId);
                                    if ($existingAssignmentStmt->fetch()) {
                                        $existingAssignment[] = ['id' => $assignmentId];
                                    }
                                }
                                $existingAssignmentStmt->close();
                            }
                            
                            if (empty($existingAssignment)) {
                                // Přidání nového přiřazení
                                $assignmentData = [
                                    'post_id' => $articleId,
                                    'reviewer_id' => $reviewerId,
                                    'assigned_by' => $userId,
                                    'assigned_at' => date('Y-m-d H:i:s'),
                                    'due_date' => $dueDate ? date('Y-m-d', strtotime($dueDate)) : null,
                                    'status' => 'Přiděleno'
                                ];
                                $insertResult = insert($assignmentData, 'post_assignments');
                                if ($insertResult) {
                                    $newReviewersAdded = true;
                                    $message = sprintf('Byl vám přidělen článek "%s" k recenzi.', $articleTitle);
                                    createNotification($reviewerId, $message, 'assignment', $articleId, $userId);

                                    $reviewerContact = fetchUserContact($reviewerId);
                                    if ($reviewerContact && !empty($reviewerContact['email'])) {
                                        $assignerName = $_SESSION['user']['username'] ?? 'Redakce';
                                        $link = buildFrontendUrl("/Frontend/review_article.php?id={$articleId}");
                                        $emailBody = "Dobrý den {$reviewerContact['username']},\n\n".
                                            "Byl vám přidělen článek \"{$articleTitle}\" k recenzi.\n".
                                            "Přidělil vás {$assignerName}.\n\n".
                                            "Na článek se můžete podívat zde: {$link}\n\n".
                                            "Děkujeme,\nRedakce RSP";
                                        sendEmail($reviewerContact['email'], 'Nový článek k recenzi', $emailBody, $reviewerId);
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Pokud byl přidán nový recenzent, změňme stav článku na "V recenzi" (workflow id = 3)
                if ($newReviewersAdded) {
                    $workflowStateSql = "SELECT id FROM workflow WHERE state = ?";
                    $workflowStateStmt = $conn->prepare($workflowStateSql);
                    $workflowStateId = null;
                    if ($workflowStateStmt) {
                        $workflowStateName = 'V recenzi';
                        $workflowStateStmt->bind_param("s", $workflowStateName);
                        $workflowStateStmt->execute();
                        
                        if (method_exists($workflowStateStmt, 'get_result')) {
                            $workflowStateResult = $workflowStateStmt->get_result();
                            if ($workflowStateResult && $workflowStateResult->num_rows > 0) {
                                $workflowRow = $workflowStateResult->fetch_assoc();
                                $workflowStateId = $workflowRow['id'];
                            }
                        } else {
                            $workflowStateStmt->bind_result($workflowId);
                            if ($workflowStateStmt->fetch()) {
                                $workflowStateId = $workflowId;
                            }
                        }
                        $workflowStateStmt->close();
                    }
                    
                    if ($workflowStateId !== null) {
                        // Aktualizace stavu článku na "V recenzi"
                        $updatePostStateSql = "UPDATE posts SET state = ?, updated_at = ?, updated_by = ? WHERE id = ?";
                        $updatePostStateStmt = $conn->prepare($updatePostStateSql);
                        if ($updatePostStateStmt) {
                            $updatedAt = date('Y-m-d H:i:s');
                            $updatePostStateStmt->bind_param("isii", $workflowStateId, $updatedAt, $userId, $articleId);
                            $updateStateResult = $updatePostStateStmt->execute();
                            if (!$updateStateResult) {
                                error_log("Chyba při aktualizaci stavu článku na 'V recenzi': " . $updatePostStateStmt->error);
                            }
                            $updatePostStateStmt->close();
                        }
                    }
                }
                
                if ($state !== null && $state !== $previousState) {
                    handleArticleStateChange($articleId, $articleAuthorId, $previousState, $state, $articleTitle, $userId);
                }

                $_SESSION['success'] = "Článek byl úspěšně aktualizován." . ($newReviewersAdded ? " Recenzenti byli přiřazeni a článek byl přepnut do stavu 'V recenzi'." : "");
                header("Location: ../Frontend/edit_article.php?id=$articleId");
            } else {
                $_SESSION['error'] = "Došlo k chybě při aktualizaci článku.";
                header("Location: ../Frontend/edit_article.php?id=$articleId");
            }
            break;

        case 'author_update_version':
            $userId = $_SESSION['user']['id'] ?? null;
            $articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
            $authorNote = trim($_POST['author_note'] ?? '');

            if (!$userId) {
                $_SESSION['error'] = "Musíte být přihlášeni.";
                header('Location: ../Frontend/login.php');
                exit();
            }

            // Jen autor svého článku
            $articleRow = select('posts', 'id, user_id, state, file_path', "id = $articleId");
            if (empty($articleRow) || (int)$articleRow[0]['user_id'] !== (int)$userId) {
                $_SESSION['error'] = "Nemáte oprávnění nahrát novou verzi tohoto článku.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Soubor je povinný.";
                header("Location: ../Frontend/article_feedback.php?id=$articleId");
                exit();
            }

            $uploadDir = __DIR__ . '/../downloads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $_FILES['file'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $_SESSION['error'] = "Neplatný typ souboru. Povolené formáty: PDF, DOC, DOCX.";
                header("Location: ../Frontend/article_feedback.php?id=$articleId");
                exit();
            }

            $maxSize = 10 * 1024 * 1024; // 10 MB
            if ($fileSize > $maxSize) {
                $_SESSION['error'] = "Soubor je příliš velký. Maximální velikost: 10 MB.";
                header("Location: ../Frontend/article_feedback.php?id=$articleId");
                exit();
            }

            // Smazat starý soubor
            $currentPath = $articleRow[0]['file_path'] ?? null;
            if ($currentPath && file_exists(__DIR__ . '/../' . $currentPath)) {
                unlink(__DIR__ . '/../' . $currentPath);
            }

            $safeFileName = uniqid('article_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            $targetPath = $uploadDir . $safeFileName;
            if (!move_uploaded_file($fileTmpName, $targetPath)) {
                $_SESSION['error'] = "Nepodařilo se nahrát soubor.";
                header("Location: ../Frontend/article_feedback.php?id=$articleId");
                exit();
            }

            // Nastavit stav na "Odeslaný" pokud existuje
            $newStateId = $articleRow[0]['state'];
            $wf = select('workflow', 'id', "state = 'Odeslaný'");
            if (!empty($wf)) {
                $newStateId = (int)$wf[0]['id'];
            }

            $updateData = [
                'file_path' => 'downloads/' . $safeFileName,
                'state' => $newStateId,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $userId
            ];
            if (!empty($authorNote)) {
                $updateData['final_note'] = $authorNote; // využijeme final_note jako poznámku autora k revizi
            }

            $result = update('posts', $updateData, "id = $articleId");
            if ($result) {
                // Notifikace pro zadavatele a recenzenty
                $assignments = select('post_assignments', 'reviewer_id', "post_id = $articleId");
                $articleTitle = select('posts', 'title', "id = $articleId")[0]['title'] ?? ("Článek #$articleId");
                foreach ($assignments as $assign) {
                    $rid = (int)$assign['reviewer_id'];
                    $msg = sprintf('Autor nahrál novou verzi článku "%s".', $articleTitle);
                    createNotification($rid, $msg, 'article_updated', $articleId, $userId);
                }
                $_SESSION['success'] = "Nová verze byla nahrána a článek byl znovu odeslán.";
            } else {
                $_SESSION['error'] = "Nepodařilo se uložit novou verzi.";
            }
            header("Location: ../Frontend/article_feedback.php?id=$articleId");
            break;

        case 'author_reply_review':
            $userId = $_SESSION['user']['id'] ?? null;
            $reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
            $replyText = trim($_POST['reply'] ?? '');

            if (!$userId) {
                $_SESSION['error'] = "Musíte být přihlášeni.";
                header('Location: ../Frontend/login.php');
                exit();
            }
            if ($reviewId <= 0 || empty($replyText)) {
                $_SESSION['error'] = "Chybí odpověď nebo ID recenze.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }

            $review = select('post_reviews', 'id, post_id, reviewer_id', "id = $reviewId");
            if (empty($review)) {
                $_SESSION['error'] = "Recenze nenalezena.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            $postId = (int)$review[0]['post_id'];
            $post = select('posts', 'user_id, title', "id = $postId");
            if (empty($post) || (int)$post[0]['user_id'] !== (int)$userId) {
                $_SESSION['error'] = "Nemáte oprávnění reagovat na tuto recenzi.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }

            $updateData = [
                'author_comment' => $replyText,
                'author_comment_at' => date('Y-m-d H:i:s')
            ];
            $updated = update('post_reviews', $updateData, "id = $reviewId");

            if ($updated) {
                $articleTitle = $post[0]['title'] ?? ("Článek #$postId");
                $reviewerId = (int)$review[0]['reviewer_id'];
                $msg = sprintf('Autor přidal reakci k vaší recenzi článku "%s".', $articleTitle);
                createNotification($reviewerId, $msg, 'author_reply', $postId, $userId);

                // Informovat zadavatele (assigned_by) pokud existuje
                $assignmentOwner = select('post_assignments', 'assigned_by', "post_id = $postId AND reviewer_id = $reviewerId");
                if (!empty($assignmentOwner) && !empty($assignmentOwner[0]['assigned_by'])) {
                    $ownerId = (int)$assignmentOwner[0]['assigned_by'];
                    $msgOwner = sprintf('Autor reagoval na recenzi článku "%s".', $articleTitle);
                    createNotification($ownerId, $msgOwner, 'author_reply', $postId, $userId);
                }

                $reviewerContact = select('users', 'email, username', "id = $reviewerId");
                if (!empty($reviewerContact) && !empty($reviewerContact[0]['email'])) {
                    $link = buildFrontendUrl("/Frontend/review_article.php?id={$postId}");
                    $body = "Dobrý den,\n\nautor přidal reakci k vaší recenzi článku \"{$articleTitle}\".\nReakci si můžete přečíst zde: {$link}\n\nRedakce RSP";
                    sendEmail($reviewerContact[0]['email'], 'Autor reagoval na vaši recenzi', $body, $reviewerId);
                }
                $_SESSION['success'] = "Reakce byla uložena a recenzent byl upozorněn.";
            } else {
                $_SESSION['error'] = "Nepodařilo se uložit reakci.";
            }
            header("Location: ../Frontend/article_feedback.php?id=$postId");
            break;

        case 'assign_reviewer_direct':
            $userId = $_SESSION['user']['id'] ?? null;
            $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
            $reviewerId = isset($_POST['reviewer_id']) ? (int)$_POST['reviewer_id'] : 0;
            $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

            if (!$userId || !in_array($_SESSION['user']['role_id'] ?? null, [1, 2])) {
                $_SESSION['error'] = "Nemáte oprávnění přiřazovat recenzenty.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }

            if ($postId <= 0 || $reviewerId <= 0) {
                $_SESSION['error'] = "Chybí článek nebo recenzent.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }

            // Zabránit duplicitám
            $existing = select('post_assignments', 'id', "post_id = $postId AND reviewer_id = $reviewerId");
            if (!empty($existing)) {
                $_SESSION['error'] = "Recenzent už je k článku přiřazen.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }

            $assignmentData = [
                'post_id' => $postId,
                'reviewer_id' => $reviewerId,
                'assigned_by' => $userId,
                'assigned_at' => date('Y-m-d H:i:s'),
                'due_date' => $dueDate ? date('Y-m-d', strtotime($dueDate)) : null,
                'status' => 'Přiděleno'
            ];
            $inserted = insert($assignmentData, 'post_assignments');

            if ($inserted) {
                $articleTitle = select('posts', 'title', "id = $postId")[0]['title'] ?? ("Článek #$postId");
                $msg = sprintf('Byl vám přidělen článek "%s" k recenzi.', $articleTitle);
                createNotification($reviewerId, $msg, 'assignment', $postId, $userId);
                $_SESSION['success'] = "Recenzent byl přiřazen.";
            } else {
                $_SESSION['error'] = "Nepodařilo se přiřadit recenzenta.";
            }
            header('Location: ../Frontend/staff_management.php');
            break;

        case 'remove_reviewer_assignment':
            $userId = $_SESSION['user']['id'] ?? null;
            $assignmentId = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;

            if (!$userId || !in_array($_SESSION['user']['role_id'] ?? null, [1, 2])) {
                $_SESSION['error'] = "Nemáte oprávnění mazat přiřazení.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }
            if ($assignmentId <= 0) {
                $_SESSION['error'] = "Chybí ID přiřazení.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }
            $assignment = select('post_assignments', '*', "id = $assignmentId");
            if (empty($assignment)) {
                $_SESSION['error'] = "Přiřazení nenalezeno.";
                header('Location: ../Frontend/staff_management.php');
                exit();
            }

            $deleted = delete('post_assignments', "id = $assignmentId");
            if ($deleted) {
                $_SESSION['success'] = "Přiřazení bylo odstraněno.";
            } else {
                $_SESSION['error'] = "Nepodařilo se odstranit přiřazení.";
            }
            header('Location: ../Frontend/staff_management.php');
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

if (!function_exists('handleArticleStateChange')) {
    function handleArticleStateChange(int $articleId, int $authorId, int $oldState, int $newState, string $articleTitle, ?int $senderId = null): void
    {
        if (!$authorId || $oldState === $newState) {
            return;
        }

        $stateName = getWorkflowStateName($newState);
        if (!$stateName) {
            return;
        }

        $authorContact = fetchUserContact($authorId);
        if (!$authorContact) {
            return;
        }

        // Pokud není zadán senderId, zkusíme ho získat z session
        if ($senderId === null && session_status() === PHP_SESSION_ACTIVE) {
            $senderId = $_SESSION['user']['id'] ?? null;
        }

        $messages = [
            'Vrácen k úpravám' => [
                'notification' => sprintf('Článek "%s" byl vrácen k úpravám. Prosíme, zapracujte připomínky.', $articleTitle),
                'subject' => 'Článek vrácen k úpravám',
                'body' => "Dobrý den {$authorContact['username']},\n\n".
                    "článek \"{$articleTitle}\" byl vrácen k úpravám na základě recenzního řízení.\n".
                    "Přihlaste se prosím do systému a zapracujte změny.\n\n".
                    "Děkujeme,\nRedakce RSP"
            ],
            'Schválen' => [
                'notification' => sprintf('Článek "%s" byl schválen k publikaci. Gratulujeme!', $articleTitle),
                'subject' => 'Článek schválen',
                'body' => "Dobrý den {$authorContact['username']},\n\n".
                    "článek \"{$articleTitle}\" byl schválen k publikaci. Děkujeme za spolupráci.\n\n".
                    "Redakce RSP"
            ]
        ];

        if (!isset($messages[$stateName])) {
            return;
        }

        $payload = $messages[$stateName];
        createNotification($authorId, $payload['notification'], 'article_state', $articleId, $senderId);

        if (!empty($authorContact['email'])) {
            sendEmail($authorContact['email'], $payload['subject'], $payload['body'], $authorId);
        }
    }
}

