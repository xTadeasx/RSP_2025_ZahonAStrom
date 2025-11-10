<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
// Načtení článků z databáze
// Zobrazujeme články se stavy: Nový (1), Odeslaný (2), V recenzi (3), Schválen (5)
$posts = [];

try {
    // SQL dotaz s JOIN pro získání článků s autory
    // Zobrazujeme články, které by měly být viditelné:
    // - Nový (1), Odeslaný (2), V recenzi (3), Schválen (5)
    // NEZobrazujeme: Vrácen k úpravám (4), Zamítnut (6)
    // Pro produkci můžete změnit na: WHERE p.state = 5 (jen schválené)
    
    // Povolené stavy pro zobrazení na indexu
    // 1 = Nový, 2 = Odeslaný, 3 = V recenzi, 5 = Schválen
    // NEZobrazujeme: 4 = Vrácen k úpravám, 6 = Zamítnut
    $allowedStates = [1, 2, 3, 5];
    
    // Vytvoření placeholders pro IN klauzuli
    $placeholders = implode(',', array_fill(0, count($allowedStates), '?'));
    
    $sql = "SELECT 
                p.id,
                p.title,
                p.abstract,
                p.topic,
                p.authors,
                p.created_at,
                p.published_at,
                p.state as post_state,
                u.username as author_username,
                u.id as author_id,
                w.state as workflow_state
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN workflow w ON p.state = w.id
            WHERE p.state IN ($placeholders)
            ORDER BY COALESCE(p.published_at, p.created_at) DESC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Bind parametrů pro IN klauzuli
        $types = str_repeat('i', count($allowedStates));
        $stmt->bind_param($types, ...$allowedStates);
        $stmt->execute();
        
        // Získání výsledků - použijeme get_result() pokud je dostupné, jinak bind_result()
        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                // Formátování data
                $date = $row['published_at'] ?? $row['created_at'];
                if ($date) {
                    try {
                        $dateObj = new DateTime($date);
                        $formattedDate = $dateObj->format('d. m. Y');
                    } catch (Exception $e) {
                        $formattedDate = date('d. m. Y', strtotime($date));
                    }
                } else {
                    $formattedDate = 'Datum nezadáno';
                }
                
                // Určení autora
                $author = $row['author_username'] ?? 'Neznámý autor';
                if (!empty($row['authors'])) {
                    // Pokud jsou uvedeni další autoři, přidáme je
                    $author .= ', ' . $row['authors'];
                }
                
                // Zkrácení abstraktu pro excerpt
                $excerpt = $row['abstract'] ?? '';
                if (strlen($excerpt) > 150) {
                    $excerpt = mb_substr($excerpt, 0, 147, 'UTF-8') . '...';
                }
                
                // Zobrazení badge pro stav (pokud není schválen)
                $stateBadge = '';
                $workflowState = $row['workflow_state'] ?? '';
                if ($row['post_state'] != 5) {
                    $stateBadge = $workflowState;
                }
                
                $posts[] = [
                    'id' => $row['id'],
                    'title' => $row['title'] ?? 'Bez názvu',
                    'excerpt' => $excerpt ?: 'Článek bez abstraktu.',
                    'author' => $author,
                    'date' => $formattedDate,
                    'category' => $row['topic'] ?? 'Obecné',
                    'author_id' => $row['author_id'],
                    'state' => $workflowState,
                    'state_badge' => $stateBadge
                ];
                }
            }
        } else {
            // Fallback pro starší verze PHP bez mysqlnd - použijeme jednodušší dotaz
            // Pro fallback použijeme přímý dotaz bez IN klauzule
            $stateList = implode(',', $allowedStates);
            $sqlFallback = "SELECT 
                p.id,
                p.title,
                p.abstract,
                p.topic,
                p.authors,
                p.created_at,
                p.published_at,
                p.state as post_state,
                u.username as author_username,
                u.id as author_id,
                w.state as workflow_state
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN workflow w ON p.state = w.id
            WHERE p.state IN ($stateList)
            ORDER BY COALESCE(p.published_at, p.created_at) DESC
            LIMIT 10";
            
            $resultFallback = $conn->query($sqlFallback);
            if ($resultFallback && $resultFallback->num_rows > 0) {
                while ($row = $resultFallback->fetch_assoc()) {
                    // Formátování data
                    $date = $row['published_at'] ?? $row['created_at'];
                    if ($date) {
                        try {
                            $dateObj = new DateTime($date);
                            $formattedDate = $dateObj->format('d. m. Y');
                        } catch (Exception $e) {
                            $formattedDate = date('d. m. Y', strtotime($date));
                        }
                    } else {
                        $formattedDate = 'Datum nezadáno';
                    }
                    
                    // Určení autora
                    $author = $row['author_username'] ?? 'Neznámý autor';
                    if (!empty($row['authors'])) {
                        $author .= ', ' . $row['authors'];
                    }
                    
                    // Zkrácení abstraktu pro excerpt
                    $excerpt = $row['abstract'] ?? '';
                    if (strlen($excerpt) > 150) {
                        $excerpt = mb_substr($excerpt, 0, 147, 'UTF-8') . '...';
                    }
                    
                    // Zobrazení badge pro stav (pokud není schválen)
                    $stateBadge = '';
                    $workflowState = $row['workflow_state'] ?? '';
                    if ($row['post_state'] != 5) {
                        $stateBadge = $workflowState;
                    }
                    
                    $posts[] = [
                        'id' => $row['id'],
                        'title' => $row['title'] ?? 'Bez názvu',
                        'excerpt' => $excerpt ?: 'Článek bez abstraktu.',
                        'author' => $author,
                        'date' => $formattedDate,
                        'category' => $row['topic'] ?? 'Obecné',
                        'author_id' => $row['author_id'],
                        'state' => $workflowState,
                        'state_badge' => $stateBadge
                    ];
                }
            }
        }
        if (isset($stmt)) {
            $stmt->close();
        }
    }
} catch (Exception $e) {
    // V případě chyby použijeme prázdné pole
    $posts = [];
    error_log("Chyba při načítání článků: " . $e->getMessage());
}
?>

<section class="section">
    <div class="section-title">Proč číst náš časopis</div>
    <div class="section-body">
        <div class="features">
            <div class="feature-card">
                <span class="feature-ico"></span>
                <div>
                    <h3 class="feature-title">Široké spektrum oborů</h3>
                    <p class="feature-text">Publikujeme články z ekonomiky, technologií, zdravotnictví i společenských věd.</p>
                    <a class="feature-link" href="#">Recenzní řízení</a>
                </div>
            </div>
            <div class="feature-card">
                <span class="feature-ico"></span>
                <div>
                    <h3 class="feature-title">Důsledná recenze</h3>
                    <p class="feature-text">Každý rukopis prochází pečlivým posouzením odborníků z praxe i akademie.</p>
                    <a class="feature-link" href="#">Zásady recenzí</a>
                </div>
            </div>
            <div class="feature-card">
                <span class="feature-ico"></span>
                <div>
                    <h3 class="feature-title">Tým zkušených editorů</h3>
                    <p class="feature-text">Za obsahem stojí redakce s dlouholetou zkušeností a podporou VŠPJ.</p>
                    <a class="feature-link" href="./board.php">Náš tým</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-title">Nejnovější články</div>
    <div class="section-body">
        <?php if (empty($posts)): ?>
            <p style="color: var(--muted); padding: 20px; text-align: center;">
                Zatím zde nejsou žádné publikované články. 
                <?php if (!empty($_SESSION['user']['username'])): ?>
                    <a href="./clanek.php" style="color: var(--brand); text-decoration: underline;">Vytvořte první článek</a>
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($posts as $post): ?>
                    <article class="card">
                        <div class="thumb"></div>
                        <div class="body">
                            <h3><?= e($post['title']) ?></h3>
                            <p><?= e($post['excerpt']) ?></p>
                            <div class="meta">
                                <span><?= e($post['author']) ?></span>
                                <span><?= e($post['date']) ?></span>
                            </div>
                            <div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                                <?php if (!empty($post['category'])): ?>
                                    <span style="background: var(--brand-2); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">
                                        <?= e($post['category']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($post['state_badge'])): ?>
                                    <span style="background: #ff9800; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">
                                        <?= e($post['state_badge']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="actions">
                            <a class="btn btn-small" href="./article.php?id=<?= $post['id'] ?>">Číst dále</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>