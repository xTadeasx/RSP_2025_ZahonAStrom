-- Migrace: Přidání sloupců abstract a keywords do tabulky posts
-- Datum: 2025-01-17

ALTER TABLE `posts`
  ADD COLUMN `abstract` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Abstrakt článku' AFTER `body`,
  ADD COLUMN `keywords` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Klíčová slova oddělená čárkami' AFTER `abstract`;

