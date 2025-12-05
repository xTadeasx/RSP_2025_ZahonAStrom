# Přehled úkolů (pracovní board)

Legenda stavu: `pending` = neuděláno, `in_progress` = rozpracováno, `done` = hotovo, `blocked` = blokováno.

| ID | Název / karta | Role | Požadavky / podúkoly | Stav | Poznámky |
|----|---------------|------|----------------------|------|----------|
| 52186 | Zobrazení posudků recenzentů a reakce, možnost upravit článek | Autor | - Odpověď autora + nahrání nové verze<br>- Komentáře pod články<br>- Zobrazení recenzního formuláře | done | Stránka `Frontend/article_feedback.php`, reakce uložené v recenzích, nová verze souboru. Komentáře pod články v `article.php` (pouze publikované články) |
| 52152 | Prohlížet publikované články v PDF na stránce | Čtenář | - Stránka s archivem článků<br>- Stažení článku<br>- Zobrazení stránky článku | done | Archiv napojen na DB v `Frontend/archive.php`, stažení přes download.php |
| 52193 | Textově prohledávat vydání | Čtenář | - Vyhledávání dle nadpisu článku | done | Fulltext podle názvu (LIKE) v `Frontend/archive.php` |
| 52099 | Komentáře recenzí | Autor | - Přidat pole „Komentář" pod posudkem<br>- E-mailová notifikace při nové reakci<br>- Práva: jen redaktor vidí celou konverzaci | done | Reakce autora uložená k recenzi, notifikace recenzentovi i zadavateli. Redaktor vidí celou konverzaci v `edit_article.php` |
| 522102 | Přehled všech článků a jejich stavu | Šéfredaktor | - Dashboard se stavovým filtrem (odeslané, v recenzi, přijaté, zamítnuté)<br>- Tabulka s přehledem článků + detailní zobrazení | done | Přidán souhrn stavů + filtry a tabulka v `Frontend/articles_overview.php` |
| 522103 | Schvalování finálních rozhodnutí o článcích | Šéfredaktor | - Workflow pro „finální schválení“ po recenzi<br>- Uložit rozhodnutí a poznámku | done | Finální rozhodnutí + poznámka v `edit_article.php`, ukládá se v `posts` |
| 522104 | Správa redaktorů a recenzentů | Šéfredaktor | - Zobrazit seznam redaktorů/recenzentů s aktivními články<br>- Ruční přiřazení článků | done | Nová stránka `Frontend/staff_management.php`, akce v `postControl.php` |

