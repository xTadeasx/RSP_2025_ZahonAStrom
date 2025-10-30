<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<?php
// Demo data článku (frontend-only). Později lze napojit na DB.
$article = [
  'title' => 'Dopady digitalizace na malé a střední podniky',
  'perex' => 'Analýza trendů, bariér a příležitostí v kontextu průmyslu 4.0. Studie shrnuje postupy, které firmám pomáhají uspět během digitální transformace.',
  'author' => 'Ing. Jana Nováková',
  'date' => '28. 10. 2025',
  'category' => 'Ekonomika'
];
?>

<article class="article">
  <header class="article-hero">
    <div class="article-kicker"><?= e($article['category']) ?></div>
    <h1 class="article-title"><?= e($article['title']) ?></h1>
    <p class="article-perex"><?= e($article['perex']) ?></p>
    <div class="article-meta">
      <span><a class="feature-link" href="./author.php?name=<?= urlencode($article['author']) ?>"><?= e($article['author']) ?></a></span>
      <span>·</span>
      <span><?= e($article['date']) ?></span>
    </div>
  </header>

  <div class="article-body prose">
    <h2>Úvod</h2>
    <p>Digitalizace zásadně mění podobu podnikání. Malé a střední podniky čelí tlaku na automatizaci, datovou analytiku a kybernetickou bezpečnost. Přestože se liší zdroje i kompetence, existují vzorce a doporučení, která fungují napříč obory.</p>

    <h2>Klíčové trendy</h2>
    <ul>
      <li>Nástup <strong>průmyslu 4.0</strong> a propojené výroby.</li>
      <li>Využití <strong>umělé inteligence</strong> a strojového učení v praxi.</li>
      <li>Rostoucí význam <strong>datové kvality</strong> a governance.</li>
    </ul>

    <h3>Překážky adopce</h3>
    <p>Mezi časté bariéry patří nedostatek kvalifikovaných lidí, roztříštěná IT infrastruktura a podcenění bezpečnosti. Řešením je postupná roadmapa s jasnými milníky a metrikami úspěchu.</p>

    <blockquote>
      Postupujme inkrementálně: nejdříve sběr dat, pak jejich konsolidace, nakonec automatizace a AI.
    </blockquote>

    <h2>Doporučený postup</h2>
    <ol>
      <li>Audit procesů a dat – pojmenovat rizika a příležitosti.</li>
      <li>Pilotní projekt – malý, ale měřitelný dopad do 90 dní.</li>
      <li>Škálování – rozšíření úspěšných pilotů do dalších týmů.</li>
    </ol>

    <h2>Závěr</h2>
    <p>Digitální transformace je dlouhodobý proces. Úspěch nespočívá v jednorázové investici, ale ve schopnosti učit se a iterovat. Pro MSP je klíčové zvolit realistickou trajektorii a opřít se o data.</p>
  </div>

  <footer class="article-footer">
    <a class="btn btn-small" href="./index.php">Zpět na domovskou stránku</a>
  </footer>
</article>

<?php require_once __DIR__ . '/Include/footer.php'; ?>


