# Migrace databáze - Přidání sloupců abstract a keywords

## Instrukce

Pro aplikování změn v databázi spusťte následující SQL příkaz:

```sql
ALTER TABLE `posts`
  ADD COLUMN `abstract` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Abstrakt článku' AFTER `body`,
  ADD COLUMN `keywords` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Klíčová slova oddělená čárkami' AFTER `abstract`;
```

Nebo použijte soubor: `migration_add_abstract_keywords.sql`

## Co bylo změněno

1. **Tabulka `posts`**:
   - Přidán sloupec `abstract` (TEXT) - abstrakt článku
   - Přidán sloupec `keywords` (VARCHAR(500)) - klíčová slova oddělená čárkami

2. **Formulář pro přidání článku** (`Frontend/clanek.php`):
   - Přidána pole pro abstrakt (povinné)
   - Přidáno pole pro klíčová slova (volitelné)
   - Přidáno pole pro téma/kategorii (volitelné)
   - Přidáno pole pro více autorů (volitelné)
   - Přidáno pole pro nahrání souboru (volitelné)

3. **Backend handler** (`Backend/postControl.php`):
   - Zpracování nových polí (abstract, keywords, topic, authors)
   - Validace abstraktu (povinné pole)
   - Oprava workflow stavu z "Koncept" na "Nový"

4. **Databázová funkce** (`Database/dataControl.php`):
   - Vylepšená funkce `insert()` pro správné zpracování NULL hodnot

## Poznámky

- Pokud již máte data v tabulce `posts`, nové sloupce budou mít hodnotu NULL
- Abstrakt je nyní povinný při vytváření nového článku
- Klíčová slova, téma a autoři jsou volitelné
- Nahrávání souborů je připraveno, ale ještě není implementováno v backendu (TODO)

