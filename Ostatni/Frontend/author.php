<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<?php
$authorName = isset($_GET['name']) ? trim($_GET['name']) : 'Autor';

// Demo data článků – stejné struktury jako na homepage, jen zde je filtrujeme podle autora
$allPosts = [
    [ 'title' => 'Dopady digitalizace na malé a střední podniky', 'excerpt' => 'Analýza trendů, bariér a příležitostí v kontextu průmyslu 4.0.', 'author' => 'Ing. Jana Nováková', 'date' => '28. 10. 2025' ],
    [ 'title' => 'Strojové učení v praxi: od prototypu k nasazení', 'excerpt' => 'Přehled nástrojů, architektur a provozních vzorců pro ML systémy.', 'author' => 'doc. Petr Malý, Ph.D.', 'date' => '25. 10. 2025' ],
    [ 'title' => 'Telemedicína po roce 2020', 'excerpt' => 'Případové studie z regionální praxe a doporučení pro implementaci.', 'author' => 'MUDr. Eva Králová', 'date' => '21. 10. 2025' ],
    [ 'title' => 'Sociální sítě a well‑being vysokoškoláků', 'excerpt' => 'Výsledky longitudinálního šetření a doporučení pro praxi.', 'author' => 'Mgr. Tomáš Veselý', 'date' => '18. 10. 2025' ],
];

$posts = array_values(array_filter($allPosts, function($p) use ($authorName) {
    return mb_strtolower($p['author']) === mb_strtolower($authorName);
}));

?>

<section class="section">
    <div class="section-title">Všechny články autora</div>
    <div class="section-body">
        <div class="author-head">
            <div class="avatar"><!-- zde může být <img src="..."> --></div>
            <div>
                <h1 style="margin:0 0 6px"><?= e($authorName) ?></h1>
                <div class="member-role">Články publikované v našem časopisu</div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-title">Publikace</div>
    <div class="section-body">
        <?php if (empty($posts)): ?>
            <p>Zatím zde nemáme žádné publikace tohoto autora.</p>
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
                        </div>
                        <div class="actions">
                            <a class="btn btn-small" href="./article.php">Číst dále</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>


