<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
$posts = [];

try {
    // Zobrazujeme jen publikované/schválené články (workflow "Schválen")
    // Ve schématu je "Schválen" s id 6 (viz seed).
    $allowedStates = [6];
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
                p.image_path,
                u.username as author_username,
                u.email as author_email,
                u.id as author_id,
                w.state as workflow_state
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN workflow w ON p.state = w.id
            WHERE p.state IN ($placeholders)
            ORDER BY COALESCE(p.published_at, p.created_at) DESC
            LIMIT 3";
    
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
                
                // Určení autora - použijeme skutečné jméno
                // Pokud existuje pole authors, použij ho
                if (!empty($row['authors'])) {
                    $author = $row['authors'];
                } elseif (!empty($row['author_email'])) {
                    // Pokud ne, zkus extrahovat jméno z emailu
                    $email = $row['author_email'];
                    // Formát: jmeno.prijmeni@rsp.cz -> Jméno Příjmení
                    $emailParts = explode('@', $email);
                    if (!empty($emailParts[0])) {
                        $nameParts = explode('.', $emailParts[0]);
                        $author = '';
                        foreach ($nameParts as $part) {
                            $author .= ucfirst($part) . ' ';
                        }
                        $author = trim($author);
                    } else {
                        $author = 'Neznámý autor';
                    }
                } elseif (!empty($row['author_username'])) {
                    // Fallback na username (ale převedeme podtržítka na mezery a kapitalizujeme)
                    $username = str_replace('_', ' ', $row['author_username']);
                    $parts = explode(' ', $username);
                    $author = '';
                    foreach ($parts as $part) {
                        $author .= ucfirst($part) . ' ';
                    }
                    $author = trim($author);
                } else {
                    $author = 'Neznámý autor';
                }
                
                // Zkrácení abstraktu pro excerpt
                $excerpt = $row['abstract'] ?? '';
                if (strlen($excerpt) > 150) {
                    $excerpt = mb_substr($excerpt, 0, 147, 'UTF-8') . '...';
                }
                
                $posts[] = [
                    'id' => $row['id'],
                    'title' => $row['title'] ?? 'Bez názvu',
                    'excerpt' => $excerpt ?: 'Článek bez abstraktu.',
                    'author' => $author,
                    'authors' => $row['authors'] ?? null,
                    'author_email' => $row['author_email'] ?? null,
                    'date' => $formattedDate,
                    'category' => $row['topic'] ?? 'Obecné',
                    'author_id' => $row['author_id'],
                    'image_path' => $row['image_path'] ?? null
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
                p.image_path,
                u.username as author_username,
                u.email as author_email,
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
                    
                    // Určení autora - použijeme skutečné jméno
                    // Pokud existuje pole authors, použij ho
                    if (!empty($row['authors'])) {
                        $author = $row['authors'];
                    } elseif (!empty($row['author_email'])) {
                        // Pokud ne, zkus extrahovat jméno z emailu
                        $email = $row['author_email'];
                        // Formát: jmeno.prijmeni@rsp.cz -> Jméno Příjmení
                        $emailParts = explode('@', $email);
                        if (!empty($emailParts[0])) {
                            $nameParts = explode('.', $emailParts[0]);
                            $author = '';
                            foreach ($nameParts as $part) {
                                $author .= ucfirst($part) . ' ';
                            }
                            $author = trim($author);
                        } else {
                            $author = 'Neznámý autor';
                        }
                    } elseif (!empty($row['author_username'])) {
                        // Fallback na username (ale převedeme podtržítka na mezery a kapitalizujeme)
                        $username = str_replace('_', ' ', $row['author_username']);
                        $parts = explode(' ', $username);
                        $author = '';
                        foreach ($parts as $part) {
                            $author .= ucfirst($part) . ' ';
                        }
                        $author = trim($author);
                    } else {
                        $author = 'Neznámý autor';
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
                        'authors' => $row['authors'] ?? null,
                        'author_email' => $row['author_email'] ?? null,
                        'date' => $formattedDate,
                        'category' => $row['topic'] ?? 'Obecné',
                        'author_id' => $row['author_id'],
                        'image_path' => $row['image_path'] ?? null
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
                    <a class="feature-link" href="./review_process.php">Recenzní řízení</a>
                </div>
            </div>
            <div class="feature-card">
                <span class="feature-ico"></span>
                <div>
                    <h3 class="feature-title">Důsledná recenze</h3>
                    <p class="feature-text">Každý rukopis prochází pečlivým posouzením odborníků z praxe i akademie.</p>
                    <a class="feature-link" href="./review_guidelines.php">Zásady recenzí</a>
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
                        <div class="thumb" style="background:#4e1835; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                            <?php if (!empty($post['image_path'])): ?>
                                <img src="../<?= e($post['image_path']) ?>" alt="Náhled" style="width:100%; height:100%; object-fit:cover;">
                            <?php endif; ?>
                        </div>
                        <div class="body">
                            <h3><?= e($post['title']) ?></h3>
                            <p><?= e($post['excerpt']) ?></p>
                            <div class="meta">
                                <span>
                                    <?php if (!empty($post['author_id'])): ?>
                                        <a class="feature-link" href="./articles_overview.php?author_id=<?= (int)$post['author_id'] ?>" style="text-decoration: none; color: inherit;"><?= e($post['author']) ?></a>
                                    <?php else: ?>
                                        <?= e($post['author']) ?>
                                    <?php endif; ?>
                                </span>
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