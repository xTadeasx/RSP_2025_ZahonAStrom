<?php
/**
 * Handler pro stažení souborů článků
 */

require_once __DIR__ . '/../Database/dataControl.php';
require_once __DIR__ . '/../Database/db.php';

// Získání ID článku z URL
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($articleId <= 0) {
    die('Neplatné ID článku.');
}

// Načtení článku z databáze
$article = null;
try {
    $sql = "SELECT id, title, file_path FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $articleId);
        $stmt->execute();
        
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $article = $result->fetch_assoc();
            }
        } else {
            // Fallback
            $stmt->bind_result($id, $title, $file_path);
            if ($stmt->fetch()) {
                $article = [
                    'id' => $id,
                    'title' => $title,
                    'file_path' => $file_path
                ];
            }
        }
        $stmt->close();
    }
} catch (Exception $e) {
    die('Chyba při načítání článku: ' . $e->getMessage());
}

// Ověření, že článek existuje a má soubor
if (!$article || empty($article['file_path'])) {
    die('Soubor k tomuto článku neexistuje.');
}

$filePath = $article['file_path'];
$fileName = basename($filePath);

// Cesta k souboru - soubory jsou v uploads/ složce
$uploadDir = __DIR__ . '/../uploads/';
$fullPath = __DIR__ . '/../' . $filePath;

// Ověření, že soubor existuje
if (!file_exists($fullPath)) {
    die('Soubor nebyl nalezen na serveru.');
}

// Ověření, že se nejedná o path traversal útok
$realPath = realpath($fullPath);
$realUploadDir = realpath($uploadDir);

// Ověření, že cesta k souboru je v uploads/ složce
if (!$realPath) {
    die('Soubor nebyl nalezen.');
}

if (!$realUploadDir) {
    // Pokud uploads/ složka neexistuje, vytvoříme ji
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $realUploadDir = realpath($uploadDir);
}

if (!$realUploadDir || strpos($realPath, $realUploadDir) !== 0) {
    die('Neplatná cesta k souboru. Path traversal útok detekován.');
}

// Bezpečný název souboru pro stažení (použijeme původní název z článku)
$downloadFileName = $article['title'] . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
$downloadFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $downloadFileName);

// Určení MIME typu
$mimeType = mime_content_type($fullPath);
if (!$mimeType) {
    // Fallback podle přípony
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
}

// Nastavení hlaviček pro stažení
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Vyprázdnění výstupního bufferu
if (ob_get_level()) {
    ob_end_clean();
}

// Čtení a výstup souboru
readfile($fullPath);
exit();
?>

