<?php require_once __DIR__ . '/Include/bootstrap.php'; ?>
<?php require_once __DIR__ . '/Include/header.php'; ?>

<section class="section">
    <div class="section-title">Recenzní řízení</div>
    <div class="section-body" style="display:grid; gap:16px;">
        <p>Jak u nás probíhá posuzování rukopisů. Cílem je transparentní, spravedlivý a rychlý proces, který chrání autory i čtenáře.</p>

        <div class="cards" style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            <article class="card">
                <div class="body">
                    <h3>1) Příjem rukopisu</h3>
                    <p>Autor nahraje článek, vyplní abstrakt, klíčová slova a přidá soubor. Každý rukopis kontrolujeme na formální náležitosti.</p>
                </div>
            </article>
            <article class="card">
                <div class="body">
                    <h3>2) Předvýběr redakce</h3>
                    <p>Šéfredaktor / redaktor posoudí vhodnost tématu a úplnost podkladů. Nepřijatelné články se zamítnou, ostatní jdou k recenzi.</p>
                </div>
            </article>
            <article class="card">
                <div class="body">
                    <h3>3) Přiřazení recenzentů</h3>
                    <p>K článku jsou přiděleni alespoň 1–2 recenzenti dle specializace. Recenzent vidí zadání, termín a stav „V recenzi“.</p>
                </div>
            </article>
            <article class="card">
                <div class="body">
                    <h3>4) Recenze</h3>
                    <p>Recenzent hodnotí aktualitu, originalitu, jazyk a odbornost. Přidává komentář a doporučení. Autor vidí reakce v detailu článku.</p>
                </div>
            </article>
            <article class="card">
                <div class="body">
                    <h3>5) Reakce autora</h3>
                    <p>Autor může nahrát novou verzi, reagovat na připomínky nebo doplnit poznámku. Stav se vrací k úpravám nebo k opětovnému posouzení.</p>
                </div>
            </article>
            <article class="card">
                <div class="body">
                    <h3>6) Finální rozhodnutí</h3>
                    <p>Šéfredaktor rozhodne: schválit, vrátit k úpravám, nebo zamítnout. Schválené články jsou publikovány v archivu.</p>
                </div>
            </article>
        </div>

        <div style="padding:16px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Role a práva</h3>
            <ul style="margin: 8px 0 0 18px; line-height:1.5;">
                <li><strong>Autor</strong>: vytváří článek, nahrává verze, reaguje na recenze.</li>
                <li><strong>Recenzent</strong>: hodnotí přiřazené články, píše oficiální recenze.</li>
                <li><strong>Redaktor / Šéfredaktor</strong>: přiřazuje recenzenty, edituje články, dává finální rozhodnutí.</li>
                <li><strong>Čtenář</strong>: čte publikované články, může psát uživatelské recenze a komentáře.</li>
            </ul>
        </div>

        <div style="padding:16px; border:1px solid var(--border); border-radius:8px; background:var(--surface);">
            <h3 style="margin-top:0;">Stavy workflow</h3>
            <p>Článek během recenzního řízení mění stavy:</p>
            <ul style="margin: 8px 0 0 18px; line-height:1.5;">
                <li><strong>Nový</strong>: právě založený článek.</li>
                <li><strong>Odeslaný</strong>: autor odeslal k posouzení.</li>
                <li><strong>V recenzi</strong>: přidělen recenzentům.</li>
                <li><strong>Vrácen k úpravám</strong>: čeká se na novou verzi od autora.</li>
                <li><strong>Schválen</strong>: finálně schváleno k publikaci.</li>
                <li><strong>Zamítnut</strong>: článek nebude publikován.</li>
            </ul>
        </div>

        <div style="padding:16px; border:1px solid var(--border); border-radius:8px; background:var(--bg);">
            <h3 style="margin-top:0;">Kontakt</h3>
            <p>Máte dotaz k recenznímu řízení? Napište na <a href="mailto:redakce@example.com" style="color:var(--brand);">redakce@example.com</a>.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/Include/footer.php'; ?>

