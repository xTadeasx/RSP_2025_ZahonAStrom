<?php
require_once __DIR__ . '/notAccess.php';
require_once __DIR__ . '/../Database/dataControl.php';
require_once __DIR__ . '/../Database/db.php';
require_once __DIR__ . '/sendEmail.php';
require_once __DIR__ . '/notificationService.php';
require_once __DIR__ . '/appServices.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'create_review':
        case 'update_review':
            $userId = $_SESSION['user']['id'] ?? null;
            $articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
            
            // Ověření, že uživatel je přihlášen
            if (!$userId) {
                $_SESSION['error'] = "Musíte být přihlášeni.";
                header('Location: ../Frontend/login.php');
                exit();
            }
            
            // Ověření, že uživatel je recenzent (role_id = 3)
            $userSql = "SELECT role_id FROM users WHERE id = ?";
            $userStmt = $conn->prepare($userSql);
            $user = [];
            if ($userStmt) {
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                if (method_exists($userStmt, 'get_result')) {
                    $userResult = $userStmt->get_result();
                    if ($userResult && $userResult->num_rows > 0) {
                        while ($row = $userResult->fetch_assoc()) {
                            $user[] = $row;
                        }
                    }
                } else {
                    $userStmt->bind_result($roleId);
                    if ($userStmt->fetch()) {
                        $user[] = ['role_id' => $roleId];
                    }
                }
                $userStmt->close();
            }
            
            if (empty($user) || ($user[0]['role_id'] ?? null) != 3) {
                $_SESSION['error'] = "Nemáte oprávnění psát recenze. Musíte být v roli Recenzenta.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            
            if ($articleId <= 0) {
                $_SESSION['error'] = "Neplatné ID článku.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            
            // Ověření, zda je recenzent přiřazen k tomuto článku
            $assignmentCheckSql = "SELECT id FROM post_assignments WHERE post_id = ? AND reviewer_id = ?";
            $assignmentCheckStmt = $conn->prepare($assignmentCheckSql);
            $assignmentCheck = [];
            if ($assignmentCheckStmt) {
                $assignmentCheckStmt->bind_param("ii", $articleId, $userId);
                $assignmentCheckStmt->execute();
                if (method_exists($assignmentCheckStmt, 'get_result')) {
                    $assignmentCheckResult = $assignmentCheckStmt->get_result();
                    if ($assignmentCheckResult && $assignmentCheckResult->num_rows > 0) {
                        while ($row = $assignmentCheckResult->fetch_assoc()) {
                            $assignmentCheck[] = $row;
                        }
                    }
                } else {
                    $assignmentCheckStmt->bind_result($assignmentId);
                    if ($assignmentCheckStmt->fetch()) {
                        $assignmentCheck[] = ['id' => $assignmentId];
                    }
                }
                $assignmentCheckStmt->close();
            }
            
            if (empty($assignmentCheck)) {
                $_SESSION['error'] = "Nejste přiřazen k recenzi tohoto článku.";
                header('Location: ../Frontend/articles_overview.php');
                exit();
            }
            
            // Validace vstupních dat
            $scoreActuality = isset($_POST['score_actuality']) ? (int)$_POST['score_actuality'] : 0;
            $scoreOriginality = isset($_POST['score_originality']) ? (int)$_POST['score_originality'] : 0;
            $scoreLanguage = isset($_POST['score_language']) ? (int)$_POST['score_language'] : 0;
            $scoreExpertise = isset($_POST['score_expertise']) ? (int)$_POST['score_expertise'] : 0;
            $comment = trim($_POST['comment'] ?? '');
            
            // Validace skóre (1-5)
            if ($scoreActuality < 1 || $scoreActuality > 5) {
                $_SESSION['error'] = "Hodnocení aktuality musí být mezi 1 a 5.";
                header("Location: ../Frontend/review_article.php?id=$articleId");
                exit();
            }
            
            if ($scoreOriginality < 1 || $scoreOriginality > 5) {
                $_SESSION['error'] = "Hodnocení originality musí být mezi 1 a 5.";
                header("Location: ../Frontend/review_article.php?id=$articleId");
                exit();
            }
            
            if ($scoreLanguage < 1 || $scoreLanguage > 5) {
                $_SESSION['error'] = "Hodnocení jazykové úrovně musí být mezi 1 a 5.";
                header("Location: ../Frontend/review_article.php?id=$articleId");
                exit();
            }
            
            if ($scoreExpertise < 1 || $scoreExpertise > 5) {
                $_SESSION['error'] = "Hodnocení odborné úrovně musí být mezi 1 a 5.";
                header("Location: ../Frontend/review_article.php?id=$articleId");
                exit();
            }
            
            $isUpdate = $_POST['action'] === 'update_review' && isset($_POST['review_id']);
            
            if ($isUpdate) {
                // Aktualizace existující recenze
                $reviewId = (int)$_POST['review_id'];
                
                // Ověření, že recenze existuje a patří aktuálnímu recenzentovi
                $existingReviewSql = "SELECT id FROM post_reviews WHERE id = ? AND reviewer_id = ? AND post_id = ?";
                $existingReviewStmt = $conn->prepare($existingReviewSql);
                $existingReview = [];
                if ($existingReviewStmt) {
                    $existingReviewStmt->bind_param("iii", $reviewId, $userId, $articleId);
                    $existingReviewStmt->execute();
                    if (method_exists($existingReviewStmt, 'get_result')) {
                        $existingReviewResult = $existingReviewStmt->get_result();
                        if ($existingReviewResult && $existingReviewResult->num_rows > 0) {
                            while ($row = $existingReviewResult->fetch_assoc()) {
                                $existingReview[] = $row;
                            }
                        }
                    } else {
                        $existingReviewStmt->bind_result($reviewIdCheck);
                        if ($existingReviewStmt->fetch()) {
                            $existingReview[] = ['id' => $reviewIdCheck];
                        }
                    }
                    $existingReviewStmt->close();
                }
                
                if (empty($existingReview)) {
                    $_SESSION['error'] = "Recenze nebyla nalezena nebo k ní nemáte přístup.";
                    header("Location: ../Frontend/review_article.php?id=$articleId");
                    exit();
                }
                
                // Příprava dat pro aktualizaci
                $updateData = [
                    'score_actuality' => $scoreActuality,
                    'score_originality' => $scoreOriginality,
                    'score_language' => $scoreLanguage,
                    'score_expertise' => $scoreExpertise,
                    'comment' => !empty($comment) ? $comment : null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Aktualizace recenze pomocí prepared statement
                $setParts = [];
                $params = [];
                $types = '';
                
                // Definice typů pro jednotlivé sloupce
                $columnTypes = [
                    'score_actuality' => 'i',
                    'score_originality' => 'i',
                    'score_language' => 'i',
                    'score_expertise' => 'i',
                    'comment' => 's',
                    'updated_at' => 's'
                ];
                
                foreach ($updateData as $key => $value) {
                    $setParts[] = "$key = ?";
                    $params[] = $value;
                    // Použijeme definovaný typ nebo defaultně string
                    $types .= $columnTypes[$key] ?? 's';
                }
                
                $sql = "UPDATE post_reviews SET " . implode(", ", $setParts) . " WHERE id = ?";
                $params[] = $reviewId;
                $types .= 'i';
                
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param($types, ...$params);
                    $result = $stmt->execute();
                    if (!$result) {
                        error_log("Chyba při aktualizaci recenze: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $result = false;
                    error_log("Chyba při přípravě SQL dotazu: " . $conn->error);
                }
                
                if ($result) {
                    $_SESSION['success'] = "Recenze byla úspěšně aktualizována.";
                    header("Location: ../Frontend/review_article.php?id=$articleId");
                } else {
                    $_SESSION['error'] = "Došlo k chybě při aktualizaci recenze.";
                    header("Location: ../Frontend/review_article.php?id=$articleId");
                }
            } else {
                // Vytvoření nové recenze
                // Kontrola, zda recenzent už recenzi nenapsal
                $existingReviewSql = "SELECT id FROM post_reviews WHERE post_id = ? AND reviewer_id = ?";
                $existingReviewStmt = $conn->prepare($existingReviewSql);
                $existingReview = [];
                if ($existingReviewStmt) {
                    $existingReviewStmt->bind_param("ii", $articleId, $userId);
                    $existingReviewStmt->execute();
                    if (method_exists($existingReviewStmt, 'get_result')) {
                        $existingReviewResult = $existingReviewStmt->get_result();
                        if ($existingReviewResult && $existingReviewResult->num_rows > 0) {
                            while ($row = $existingReviewResult->fetch_assoc()) {
                                $existingReview[] = $row;
                            }
                        }
                    } else {
                        $existingReviewStmt->bind_result($reviewIdCheck);
                        if ($existingReviewStmt->fetch()) {
                            $existingReview[] = ['id' => $reviewIdCheck];
                        }
                    }
                    $existingReviewStmt->close();
                }
                
                if (!empty($existingReview)) {
                    $_SESSION['error'] = "Recenzi k tomuto článku jste již napsal. Můžete ji upravit.";
                    header("Location: ../Frontend/review_article.php?id=$articleId");
                    exit();
                }
                
                // Příprava dat pro vložení
                $reviewData = [
                    'post_id' => $articleId,
                    'reviewer_id' => $userId,
                    'score_actuality' => $scoreActuality,
                    'score_originality' => $scoreOriginality,
                    'score_language' => $scoreLanguage,
                    'score_expertise' => $scoreExpertise,
                    'comment' => !empty($comment) ? $comment : null
                ];
                
                // Vložení recenze do databáze
                $result = insert($reviewData, 'post_reviews');
                
                if ($result) {
                        // Log
                        insert([
                            'user_id' => $userId,
                            'event_type' => 'review_create',
                            'level' => 'info',
                            'message' => sprintf('Recenzent %d vytvořil recenzi k článku ID %d', $userId, $articleId)
                        ], 'system_logs');

                    // Aktualizace statusu přiřazení na "Recenzováno"
                    $updateAssignmentSql = "UPDATE post_assignments SET status = ? WHERE post_id = ? AND reviewer_id = ?";
                    $updateAssignmentStmt = $conn->prepare($updateAssignmentSql);
                    if ($updateAssignmentStmt) {
                        $status = 'Recenzováno';
                        $updateAssignmentStmt->bind_param("sii", $status, $articleId, $userId);
                        $updateAssignmentStmt->execute();
                        $updateAssignmentStmt->close();
                    }
                    
                    // Změna stavu článku na "Vrácen k úpravám" (workflow id = 4)
                    $workflowStateSql = "SELECT id FROM workflow WHERE state = ?";
                    $workflowStateStmt = $conn->prepare($workflowStateSql);
                    $workflowStateId = null;
                    if ($workflowStateStmt) {
                        $workflowStateName = 'Vrácen k úpravám';
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
                        // Aktualizace stavu článku
                        $updatePostStateSql = "UPDATE posts SET state = ?, updated_at = ?, updated_by = ? WHERE id = ?";
                        $updatePostStateStmt = $conn->prepare($updatePostStateSql);
                        if ($updatePostStateStmt) {
                            $updatedAt = date('Y-m-d H:i:s');
                            $updatePostStateStmt->bind_param("isii", $workflowStateId, $updatedAt, $userId, $articleId);
                            $updateResult = $updatePostStateStmt->execute();
                            if (!$updateResult) {
                                error_log("Chyba při aktualizaci stavu článku: " . $updatePostStateStmt->error);
                            }
                            $updatePostStateStmt->close();
                        }
                    }
                    
                    $assignmentOwnerId = fetchAssignmentOwnerId($articleId, $userId);
                    $articleSummary = fetchPostSummary($articleId);
                    $articleTitle = $articleSummary['title'] ?? ("Článek #$articleId");
                    $reviewerName = $_SESSION['user']['username'] ?? 'Recenzent';

                    if ($assignmentOwnerId) {
                        $message = sprintf('Recenzent %s dokončil recenzi článku "%s".', $reviewerName, $articleTitle);
                        createNotification($assignmentOwnerId, $message, 'review_submitted', $articleId, $userId);

                        $ownerContact = fetchUserContact($assignmentOwnerId);
                        if ($ownerContact && !empty($ownerContact['email'])) {
                            $link = buildFrontendUrl("/Frontend/edit_article.php?id={$articleId}");
                            $emailBody = "Dobrý den {$ownerContact['username']},\n\n".
                                "recenzent {$reviewerName} právě odeslal recenzi článku \"{$articleTitle}\".\n".
                                "Článek můžete zkontrolovat a rozhodnout o dalším postupu zde: {$link}\n\n".
                                "Redakce RSP";
                            sendEmail($ownerContact['email'], 'Recenzent dokončil posudek', $emailBody, $assignmentOwnerId);
                        }
                    }

                    $_SESSION['success'] = "Recenze byla úspěšně odeslána. Článek byl přepnut do stavu 'Vrácen k úpravám'.";
                    header("Location: ../Frontend/review_article.php?id=$articleId");
                } else {
                    $_SESSION['error'] = "Došlo k chybě při ukládání recenze.";
                    header("Location: ../Frontend/review_article.php?id=$articleId");
                }
            }
            break;
            
        default:
            $_SESSION['error'] = "Neznámá akce: " . ($_POST['action'] ?? '');
            header('Location: ../Frontend/articles_overview.php');
            break;
    }
} else {
    $_SESSION['error'] = "Neplatný požadavek.";
    header('Location: ../Frontend/articles_overview.php');
}
?>

