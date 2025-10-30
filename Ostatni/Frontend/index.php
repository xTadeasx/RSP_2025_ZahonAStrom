<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<?php
// ukázková data (frontend-only)
$posts = [
    [
        'title' => 'Dopady digitalizace na malé a střední podniky',
        'excerpt' => 'Analýza trendů, bariér a příležitostí v kontextu průmyslu 4.0.',
        'author' => 'Ing. Jana Nováková',
        'date' => '28. 10. 2025',
        'category' => 'Ekonomika'
    ],
    [
        'title' => 'Strojové učení v praxi: od prototypu k nasazení',
        'excerpt' => 'Přehled nástrojů, architektur a provozních vzorců pro ML systémy.',
        'author' => 'doc. Petr Malý, Ph.D.',
        'date' => '25. 10. 2025',
        'category' => 'Technologie'
    ],
    [
        'title' => 'Telemedicína po roce 2020',
        'excerpt' => 'Případové studie z regionální praxe a doporučení pro implementaci.',
        'author' => 'MUDr. Eva Králová',
        'date' => '21. 10. 2025',
        'category' => 'Zdravotnictví'
    ],
    [
        'title' => 'Sociální sítě a well‑being vysokoškoláků',
        'excerpt' => 'Výsledky longitudinálního šetření a doporučení pro praxi.',
        'author' => 'Mgr. Tomáš Veselý',
        'date' => '18. 10. 2025',
        'category' => 'Společnost'
    ],
];
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
    </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>