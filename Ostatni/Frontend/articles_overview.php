<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>
<?php require_once __DIR__ . '/../Database/dataControl.php'; ?>
<?php require_once __DIR__ . '/../Database/db.php'; ?>

<?php
// Přehled je veřejný: nepřihlášený uživatel uvidí jen publikované (Schválen)
$userId = $_SESSION['user']['id'] ?? null;
$userRoleId = $_SESSION['user']['role_id'] ?? null;

// Získání filtrů z GET parametrů
$filterState = isset($_GET['stav']) ? (int)$_GET['stav'] : null;
$filterTitle = isset($_GET['nazev']) ? trim($_GET['nazev']) : '';
$filterAuthorId = isset($_GET['author_id']) ? (int)$_GET['author_id'] : null;

// Načtení všech stavů workflow pro filtr a pro přehled
$workflowStates = [];
try {
    $statesQuery = "SELECT id, state FROM workflow ORDER BY id";
    $statesResult = $conn->query($statesQuery);
    if ($statesResult && $statesResult->num_rows > 0) {
        while ($stateRow = $statesResult->fetch_assoc()) {
            $workflowStates[] = $stateRow;
        }
    }
} catch (Exception $e) {
    error_log("Chyba při načítání stavů workflow: " . $e->getMessage());
}

// Přehled počtů podle stavů (pro hosty jen schválené)
// Nenačítáme statistiky, pokud je aktivní filtr podle autora
$stateSummary = [];
$totalAll = 0;
if ($filterAuthorId === null || $filterAuthorId <= 0) {
    try {
        if (in_array($userRoleId, [1, 2, 4])) {
            $summarySql = "SELECT w.state AS name, COUNT(*) AS total
                           FROM posts p
                           LEFT JOIN workflow w ON p.state = w.id
                           GROUP BY w.state
                           ORDER BY w.state";
        } elseif ($userRoleId == 3) {
            // Recenzent – jen přiřazené články
            $summarySql = "SELECT w.state AS name, COUNT(*) AS total
                           FROM posts p
                           INNER JOIN post_assignments pa ON p.id = pa.post_id
                           LEFT JOIN workflow w ON p.state = w.id
                           WHERE pa.reviewer_id = " . (int)$userId . "
                           GROUP BY w.state
                           ORDER BY w.state";
        } elseif ($userRoleId == 5) {
            // Autor – jen vlastní články
            $summarySql = "SELECT w.state AS name, COUNT(*) AS total
                           FROM posts p
                           LEFT JOIN workflow w ON p.state = w.id
                           WHERE p.user_id = " . (int)$userId . "
                           GROUP BY w.state
                           ORDER BY w.state";
        } else {
            // Host: jen schválené
            $summarySql = "SELECT w.state AS name, COUNT(*) AS total
                           FROM posts p
                           LEFT JOIN workflow w ON p.state = w.id
                           WHERE w.state = 'Schválen'
                           GROUP BY w.state
                           ORDER BY w.state";
        }
        $summaryResult = $conn->query($summarySql);
        if ($summaryResult && $summaryResult->num_rows > 0) {
            while ($row = $summaryResult->fetch_assoc()) {
                $stateName = $row['name'] ?: 'Nezadáno';
                $stateSummary[$stateName] = (int)$row['total'];
                $totalAll += (int)$row['total'];
            }
        }
    } catch (Exception $e) {
        error_log("Chyba při načítání přehledu stavů: " . $e->getMessage());
    }
}

// Načtení článků podle role
$articles = [];
$pageTitle = "Přehled článků";
$totalCount = 0;

// Pokud je filtrováno podle autora, načteme jméno autora pro nadpis
$authorName = null;
if ($filterAuthorId !== null && $filterAuthorId > 0) {
    try {
        $authorQuery = "SELECT username, email FROM users WHERE id = " . (int)$filterAuthorId;
        $authorResult = $conn->query($authorQuery);
        if ($authorResult && $authorResult->num_rows > 0) {
            $authorRow = $authorResult->fetch_assoc();
            // Určení zobrazovaného jména
            if (!empty($authorRow['email'])) {
                $emailParts = explode('@', $authorRow['email']);
                if (!empty($emailParts[0])) {
                    $nameParts = explode('.', $emailParts[0]);
                    $authorName = '';
                    foreach ($nameParts as $part) {
                        $authorName .= ucfirst($part) . ' ';
                    }
                    $authorName = trim($authorName);
                }
            }
            if (empty($authorName) && !empty($authorRow['username'])) {
                $username = str_replace('_', ' ', $authorRow['username']);
                $parts = explode(' ', $username);
                $authorName = '';
                foreach ($parts as $part) {
                    $authorName .= ucfirst($part) . ' ';
                }
                $authorName = trim($authorName);
            }
        }
    } catch (Exception $e) {
        error_log("Chyba při načítání jména autora: " . $e->getMessage());
    }
}

try {
    if ($userRoleId == 3) {
        // Recenzent - pouze články přiřazené k recenzi
        $pageTitle = "Moje recenze";
        
        // Sestavení SQL dotazu s filtry - použijeme escape_string pro jednoduchost
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
                    u.email as author_email,
                    u.id as author_id,
                    w.state as workflow_state,
                    pa.assigned_at,
                    pa.due_date,
                    pa.status as assignment_status
                FROM posts p
                INNER JOIN post_assignments pa ON p.id = pa.post_id
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN workflow w ON p.state = w.id
                WHERE pa.reviewer_id = " . (int)$userId;
        
        // Přidání filtru podle stavu
        if ($filterState !== null && $filterState > 0) {
            $sql .= " AND p.state = " . (int)$filterState;
        }

        // Filtr podle autora (pokud dorazil parametr)
        if ($filterAuthorId !== null && $filterAuthorId > 0) {
            $sql .= " AND p.user_id = " . (int)$filterAuthorId;
        }
        
        // Přidání filtru podle názvu
        if (!empty($filterTitle)) {
            $escapedTitle = $conn->real_escape_string($filterTitle);
            $sql .= " AND p.title LIKE '%" . $escapedTitle . "%'";
        }
        
        $sql .= " ORDER BY COALESCE(p.published_at, p.created_at) DESC";
        
        $result = $conn->query($sql);
        if ($result) {
            $totalCount = $result->num_rows;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $articles[] = $row;
                }
            }
        }
    } elseif ($userRoleId == 5) {
        // Autor - pouze vlastní články
        $pageTitle = "Moje články";
        
        // Sestavení SQL dotazu s filtry - použijeme escape_string pro jednoduchost
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
                    u.email as author_email,
                    u.id as author_id,
                    w.state as workflow_state
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN workflow w ON p.state = w.id
                WHERE p.user_id = " . (int)$userId;
        
        // Přidání filtru podle stavu
        if ($filterState !== null && $filterState > 0) {
            $sql .= " AND p.state = " . (int)$filterState;
        }

        if ($filterAuthorId !== null && $filterAuthorId > 0) {
            $sql .= " AND p.user_id = " . (int)$filterAuthorId;
        }
        
        // Přidání filtru podle názvu
        if (!empty($filterTitle)) {
            $escapedTitle = $conn->real_escape_string($filterTitle);
            $sql .= " AND p.title LIKE '%" . $escapedTitle . "%'";
        }
        
        $sql .= " ORDER BY COALESCE(p.published_at, p.created_at) DESC";
        
        $result = $conn->query($sql);
        if ($result) {
            $totalCount = $result->num_rows;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $articles[] = $row;
                }
            }
        }
    } elseif (in_array($userRoleId, [1,2,4])) {
        // Admin, Šéfredaktor, Redaktor - všechny články
        // Sestavení SQL dotazu s filtry - použijeme escape_string pro jednoduchost
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
                    u.email as author_email,
                    u.id as author_id,
                    w.state as workflow_state
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN workflow w ON p.state = w.id
                WHERE 1=1";
        
        // Přidání filtru podle stavu
        if ($filterState !== null && $filterState > 0) {
            $sql .= " AND p.state = " . (int)$filterState;
        }

        if ($filterAuthorId !== null && $filterAuthorId > 0) {
            $sql .= " AND p.user_id = " . (int)$filterAuthorId;
        }
        
        // Přidání filtru podle názvu
        if (!empty($filterTitle)) {
            $escapedTitle = $conn->real_escape_string($filterTitle);
            $sql .= " AND p.title LIKE '%" . $escapedTitle . "%'";
        }
        
        $sql .= " ORDER BY COALESCE(p.published_at, p.created_at) DESC";
        
        $result = $conn->query($sql);
        if ($result) {
            $totalCount = $result->num_rows;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $articles[] = $row;
                }
            }
        }
    } else {
        // Host: jen schválené články
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
                    u.email as author_email,
                    u.id as author_id,
                    w.state as workflow_state
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN workflow w ON p.state = w.id
                WHERE w.state = 'Schválen'";

        if ($filterTitle !== '') {
            $escapedTitle = $conn->real_escape_string($filterTitle);
            $sql .= " AND p.title LIKE '%" . $escapedTitle . "%'";
        }

        if ($filterState !== null && $filterState > 0) {
            $sql .= " AND p.state = " . (int)$filterState;
        }

        if ($filterAuthorId !== null && $filterAuthorId > 0) {
            $sql .= " AND p.user_id = " . (int)$filterAuthorId;
        }

        $sql .= " ORDER BY COALESCE(p.published_at, p.created_at) DESC";

        $result = $conn->query($sql);
        if ($result) {
            $totalCount = $result->num_rows;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $articles[] = $row;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Chyba při načítání článků: " . $e->getMessage());
    $_SESSION['error'] = "Došlo k chybě při načítání článků.";
}

// Funkce pro formátování data
function formatDate($date) {
    if (!$date) {
        return 'Datum nezadáno';
    }
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format('d. m. Y');
    } catch (Exception $e) {
        return date('d. m. Y', strtotime($date));
    }
}

// Funkce pro získání barvy stavu
function getStateColor($state) {
    $colors = [
        'Nový' => '#2196F3',
        'Odeslaný' => '#9C27B0',
        'V recenzi' => '#FF9800',
        'Vrácen k úpravám' => '#F44336',
        'Schválen' => '#4CAF50',
        'Zamítnut' => '#616161'
    ];
    return $colors[$state] ?? '#757575';
}
?>

<div class="section">
    <div class="section-title">
        <h1 style="margin: 0;">
            <?= e($pageTitle) ?>
            <?php if ($authorName !== null): ?>
                - <?= e($authorName) ?>
            <?php endif; ?>
        </h1>
    </div>
    <div class="section-body">
        <?php if (($filterAuthorId === null || $filterAuthorId <= 0) && !empty($stateSummary)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <div style="padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--bg);">
                    <div style="font-size: 0.85rem; color: var(--muted);">Celkem</div>
                    <div style="font-size: 1.4rem; font-weight: 700;"><?= (int)$totalAll ?></div>
                </div>
                <?php foreach ($stateSummary as $stateName => $count): ?>
                    <div style="padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: var(--surface);">
                        <div style="font-size: 0.85rem; color: var(--muted);"><?= e($stateName) ?></div>
                        <div style="font-size: 1.2rem; font-weight: 700;"><?= (int)$count ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($filterAuthorId === null || $filterAuthorId <= 0): ?>
            <!-- Formulář pro filtry -->
            <form method="GET" action="./articles_overview.php" style="background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div class="filter-form-grid" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end;">
                    <div>
                        <label for="nazev" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Filtr podle názvu:</label>
                        <input 
                            type="text" 
                            id="nazev" 
                            name="nazev" 
                            value="<?= e($filterTitle) ?>"
                            placeholder="Zadejte název článku..."
                            style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box;"
                        >
                    </div>
                    <div>
                        <label for="stav" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Filtr podle stavu:</label>
                        <select 
                            id="stav" 
                            name="stav" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: white; box-sizing: border-box;"
                        >
                            <option value="">Všechny stavy</option>
                            <?php foreach ($workflowStates as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= $filterState == $state['id'] ? 'selected' : '' ?>>
                                    <?= e($state['state']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-buttons" style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="submit" class="btn" style="background: var(--brand); color: white; padding: 8px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                            🔍 Filtrovat
                        </button>
                        <a href="./articles_overview.php" class="btn" style="background: var(--muted); color: white; padding: 8px 20px; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: 600; white-space: nowrap;">
                            🔄 Reset filtrů
                        </a>
                    </div>
                </div>
                <?php if ($filterState !== null && $filterState > 0 || !empty($filterTitle)): ?>
                    <div style="margin-top: 12px; padding: 8px 12px; background: var(--bg); border-radius: 6px; font-size: 0.875rem; color: var(--muted);">
                        <strong>Aktivní filtry:</strong>
                        <?php if (!empty($filterTitle)): ?>
                            <span style="background: var(--brand); color: white; padding: 2px 8px; border-radius: 4px; margin-left: 6px;">
                                Název: "<?= e($filterTitle) ?>"
                            </span>
                        <?php endif; ?>
                        <?php if ($filterState !== null && $filterState > 0): ?>
                            <?php 
                            $selectedState = null;
                            foreach ($workflowStates as $state) {
                                if ($state['id'] == $filterState) {
                                    $selectedState = $state['state'];
                                    break;
                                }
                            }
                            ?>
                            <?php if ($selectedState): ?>
                                <span style="background: var(--brand); color: white; padding: 2px 8px; border-radius: 4px; margin-left: 6px;">
                                    Stav: <?= e($selectedState) ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
        
        <?php if (empty($articles)): ?>
            <p style="color: var(--muted); padding: 20px; text-align: center;">
                <?php if ($filterAuthorId !== null && $filterAuthorId > 0): ?>
                    Tento autor zatím nemá žádné články.
                <?php elseif ($filterState !== null && $filterState > 0 || !empty($filterTitle)): ?>
                    Žádné články neodpovídají zadaným filtrům.
                    <a href="./articles_overview.php" style="color: var(--brand); text-decoration: underline; margin-left: 8px;">
                        Zobrazit všechny články
                    </a>
                <?php elseif ($userRoleId == 3): ?>
                    Nemáte přiřazené žádné články k recenzi.
                <?php elseif ($userRoleId == 5): ?>
                    Zatím jste nevytvořil žádné články.
                    <a href="./clanek.php" style="color: var(--brand); text-decoration: underline; margin-left: 8px;">
                        Vytvořit nový článek
                    </a>
                <?php else: ?>
                    Zatím nejsou k dispozici žádné články.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg); border-bottom: 2px solid var(--border);">
                            <th style="padding: 12px; text-align: left; font-weight: 600; width: 80px;">ID</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Název článku</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Autor</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Téma</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Stav</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600;">Datum vytvoření</th>
                            <?php if ($userRoleId == 3): ?>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Termín</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600;">Stav recenze</th>
                            <?php endif; ?>
                            <th style="padding: 12px; text-align: center; font-weight: 600;">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr style="border-bottom: 1px solid var(--border);" class="article-row">
                                <td style="padding: 12px; color: var(--muted); font-weight: 700;">
                                    <?= (int)$article['id'] ?>
                                </td>
                                <td style="padding: 12px;">
                                    <strong><?= e($article['title'] ?? 'Bez názvu') ?></strong>
                                    <?php if (!empty($article['abstract'])): ?>
                                        <div style="font-size: 0.875rem; color: var(--muted); margin-top: 4px;">
                                            <?= e(mb_substr($article['abstract'], 0, 100, 'UTF-8')) ?><?= strlen($article['abstract']) > 100 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php
                                    // Určení zobrazovaného jména autora
                                    $authorDisplayName = 'Neznámý autor';
                                    if (!empty($article['authors'])) {
                                        $authorDisplayName = $article['authors'];
                                    } elseif (!empty($article['author_email'])) {
                                        // Extrahuj jméno z emailu: jmeno.prijmeni@rsp.cz -> Jméno Příjmení
                                        $emailParts = explode('@', $article['author_email']);
                                        if (!empty($emailParts[0])) {
                                            $nameParts = explode('.', $emailParts[0]);
                                            $displayName = '';
                                            foreach ($nameParts as $part) {
                                                $displayName .= ucfirst($part) . ' ';
                                            }
                                            $authorDisplayName = trim($displayName);
                                        }
                                    } elseif (!empty($article['author_username'])) {
                                        // Fallback: převeď username na hezké jméno
                                        $username = str_replace('_', ' ', $article['author_username']);
                                        $parts = explode(' ', $username);
                                        $displayName = '';
                                        foreach ($parts as $part) {
                                            $displayName .= ucfirst($part) . ' ';
                                        }
                                        $authorDisplayName = trim($displayName);
                                    }
                                    
                                    if (!empty($article['author_id'])) {
                                        echo '<a href="./articles_overview.php?author_id=' . (int)$article['author_id'] . '" style="color: var(--brand); text-decoration: underline;">' . e($authorDisplayName) . '</a>';
                                    } else {
                                        echo e($authorDisplayName);
                                    }
                                    ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?= e($article['topic'] ?? 'Obecné') ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php if (!empty($article['workflow_state'])): ?>
                                        <span style="background: <?= getStateColor($article['workflow_state']) ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                            <?= e($article['workflow_state']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--muted);">Nezadáno</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?= formatDate($article['created_at']) ?>
                                </td>
                                <?php if ($userRoleId == 3): ?>
                                    <td style="padding: 12px;">
                                        <?php if (!empty($article['due_date'])): ?>
                                            <?= formatDate($article['due_date']) ?>
                                            <?php
                                            // Kontrola, zda je termín překročen
                                            if (strtotime($article['due_date']) < time()) {
                                                echo ' <span style="color: #F44336; font-weight: 600;">(po termínu)</span>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <span style="color: var(--muted);">Nezadáno</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php if (!empty($article['assignment_status'])): ?>
                                            <span style="background: #9E9E9E; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                                                <?= e($article['assignment_status']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td style="padding: 12px; text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                                        <a href="./article.php?id=<?= $article['id'] ?>" class="btn btn-small" style="text-decoration: none;">
                                            Zobrazit
                                        </a>
                                        <?php if (in_array($userRoleId, [1, 2, 4])): // Pouze Admin, Šéfredaktor, Redaktor ?>
                                            <a href="./edit_article.php?id=<?= $article['id'] ?>" class="btn btn-small" style="text-decoration: none; background: var(--brand-2); color: white;">
                                                Editovat
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($userRoleId == 5): // Autor ?>
                                            <a href="./article_feedback.php?id=<?= $article['id'] ?>" class="btn btn-small" style="text-decoration: none; background: var(--brand); color: white;">
                                                Reagovat na recenze
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($userRoleId == 3): // Recenzent ?>
                                            <?php
                                            // Zkontrolovat, zda recenzent už napsal recenzi
                                            $hasReview = false;
                                            try {
                                                $reviewCheckSql = "SELECT id FROM post_reviews WHERE post_id = ? AND reviewer_id = ?";
                                                $reviewCheckStmt = $conn->prepare($reviewCheckSql);
                                                if ($reviewCheckStmt) {
                                                    $reviewCheckStmt->bind_param("ii", $article['id'], $userId);
                                                    $reviewCheckStmt->execute();
                                                    if (method_exists($reviewCheckStmt, 'get_result')) {
                                                        $reviewCheckResult = $reviewCheckStmt->get_result();
                                                        $hasReview = $reviewCheckResult && $reviewCheckResult->num_rows > 0;
                                                    } else {
                                                        $reviewCheckStmt->bind_result($reviewId);
                                                        $hasReview = $reviewCheckStmt->fetch();
                                                    }
                                                    $reviewCheckStmt->close();
                                                }
                                            } catch (Exception $e) {
                                                error_log("Chyba při kontrole recenze: " . $e->getMessage());
                                            }
                                            ?>
                                            <a href="./review_article.php?id=<?= $article['id'] ?>" class="btn btn-small" style="text-decoration: none; background: #4CAF50; color: white;">
                                                <?= $hasReview ? '✏️ Upravit recenzi' : '⭐ Napsat recenzi' ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 16px; padding: 12px; background: var(--bg); border-radius: 8px; font-size: 0.875rem; color: var(--muted);">
                <strong>Zobrazeno článků:</strong> <?= count($articles) ?>
                <?php if ($filterState !== null && $filterState > 0 || !empty($filterTitle)): ?>
                    <span style="color: var(--brand); margin-left: 8px;">
                        (filtrováno)
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .article-row {
        transition: background-color 0.2s ease;
    }
    .article-row:hover {
        background-color: var(--bg);
    }
    
    /* Responzivní design pro filtry */
    @media (max-width: 768px) {
        .filter-form-grid {
            grid-template-columns: 1fr !important;
        }
        .filter-buttons {
            flex-direction: column;
        }
        .filter-buttons a,
        .filter-buttons button {
            width: 100%;
        }
    }
</style>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

