-- Rozšířený seed pro lokální testování (obsah, recenze, komentáře, issues)
-- Spusť po importu hlavního dumpu a případně full_reset.sql
-- Využívá existující uživatele (administrator, sefredaktor, recenzent, redaktor, autor, ctenar, rec1/rec2, autor1/autor2, red1/red2, ctenar1/ctenar2 atd.)

START TRANSACTION;

-- Bezpečně vytvoř issues, pokud chybí
CREATE TABLE IF NOT EXISTS issues (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  year INT NOT NULL,
  number INT NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  published_at DATE DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Další autoři/uživatelé (username = heslo)
INSERT INTO users (username, password, role_id, email, phone, bio)
VALUES
  ('autor3', '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor3@example.com', '123456789', 'Popularizátor vědy, píše o AI v praxi.'),
  ('autor4', '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor4@example.com', '777444555', 'Datový analytik, zaměření na datové pipeline.'),
  ('red3',   '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'red3@example.com', '777333222', 'Redaktor pro cloud a DevOps.'),
  ('rec3',   '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'rec3@example.com', '999222111', 'Recenzent pro data/AI.')
ON DUPLICATE KEY UPDATE email=VALUES(email), bio=VALUES(bio);

-- Rychlé lookupy
SET @wf_novy      := (SELECT id FROM workflow WHERE state='Nový' LIMIT 1);
SET @wf_odeslany  := (SELECT id FROM workflow WHERE state='Odeslaný' LIMIT 1);
SET @wf_vrecenzi  := (SELECT id FROM workflow WHERE state='V recenzi' LIMIT 1);
SET @wf_vracen    := (SELECT id FROM workflow WHERE state='Vrácen k úpravám' LIMIT 1);
SET @wf_schvalen  := (SELECT id FROM workflow WHERE state='Schválen' LIMIT 1);
SET @wf_zamitnut  := (SELECT id FROM workflow WHERE state='Zamítnut' LIMIT 1);

SET @aut3 := (SELECT id FROM users WHERE username='autor3' LIMIT 1);
SET @aut4 := (SELECT id FROM users WHERE username='autor4' LIMIT 1);
SET @red3 := (SELECT id FROM users WHERE username='red3' LIMIT 1);
SET @rec1 := (SELECT id FROM users WHERE username='rec1' LIMIT 1);
SET @rec2 := (SELECT id FROM users WHERE username='rec2' LIMIT 1);
SET @rec3 := (SELECT id FROM users WHERE username='rec3' LIMIT 1);
SET @ct1  := (SELECT id FROM users WHERE username='ctenar1' LIMIT 1);
SET @ct2  := (SELECT id FROM users WHERE username='ctenar2' LIMIT 1);

-- Issues
INSERT INTO issues (year, number, title, published_at)
VALUES
  (2025, 1, 'Jaro 2025 – AI a data', '2025-03-15'),
  (2025, 2, 'Léto 2025 – Bezpečnost a DevOps', '2025-06-20'),
  (2025, 3, 'Podzim 2025 – Cloud a governance', '2025-09-25')
ON DUPLICATE KEY UPDATE title=VALUES(title), published_at=VALUES(published_at);

SET @iss1 := (SELECT id FROM issues WHERE year=2025 AND number=1 LIMIT 1);
SET @iss2 := (SELECT id FROM issues WHERE year=2025 AND number=2 LIMIT 1);
SET @iss3 := (SELECT id FROM issues WHERE year=2025 AND number=3 LIMIT 1);

-- Nové články (různé stavy + přiřazení k vydáním)
INSERT INTO posts (title, body, abstract, keywords, topic, authors, file_path, image_path, user_id, state, issue_id, created_at, updated_at, created_by, updated_by)
VALUES
  ('AI v malých firmách', 'Detailní návod, jak zavádět AI nástroje v malých firmách...', 'Praktický průvodce adopcí AI v SMB.', 'AI,SMB,automatizace', 'IT', 'Autor Tři', NULL, NULL, @aut3, @wf_schvalen, @iss1, NOW()-INTERVAL 20 DAY, NOW()-INTERVAL 18 DAY, @aut3, @aut3),
  ('Data pipeline pro e-commerce', 'Postup ETL/ELT pro e-commerce data...', 'Jak stavět škálovatelné datové pipeline.', 'data,pipeline,e-commerce', 'Data', 'Autor Čtyři', NULL, NULL, @aut4, @wf_vrecenzi, @iss2, NOW()-INTERVAL 15 DAY, NOW()-INTERVAL 12 DAY, @aut4, @aut4),
  ('DevOps bezpečnost v cloudu', 'Best practices pro zabezpečení CI/CD...', 'Bezpečnost DevOps v cloud prostředí.', 'devops,security,cloud', 'Security', 'Redaktor Tři', NULL, NULL, @red3, @wf_vrecenzi, @iss2, NOW()-INTERVAL 10 DAY, NOW()-INTERVAL 8 DAY, @red3, @red3),
  ('Governance AI modelů', 'Politiky, verzování a audit ML modelů...', 'Governance a audit ML pipeline.', 'ai,governance,mlops', 'AI', 'Autor Tři', NULL, NULL, @aut3, @wf_odeslany, @iss3, NOW()-INTERVAL 7 DAY, NOW()-INTERVAL 5 DAY, @aut3, @aut3),
  ('Observabilita datových toků', 'Metody observability a metriky pro data...', 'Jak sledovat kvalitu a SLA dat.', 'observability,data,SLA', 'Data', 'Autor Čtyři', NULL, NULL, @aut4, @wf_novy, NULL, NOW()-INTERVAL 4 DAY, NOW()-INTERVAL 2 DAY, @aut4, @aut4)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), state=VALUES(state), issue_id=VALUES(issue_id);

SET @p_smb   := (SELECT id FROM posts WHERE title='AI v malých firmách' LIMIT 1);
SET @p_pipe  := (SELECT id FROM posts WHERE title='Data pipeline pro e-commerce' LIMIT 1);
SET @p_dev   := (SELECT id FROM posts WHERE title='DevOps bezpečnost v cloudu' LIMIT 1);
SET @p_gov   := (SELECT id FROM posts WHERE title='Governance AI modelů' LIMIT 1);
SET @p_obsv  := (SELECT id FROM posts WHERE title='Observabilita datových toků' LIMIT 1);

-- Přiřazení recenzentů
INSERT INTO post_assignments (post_id, reviewer_id, assigned_by, assigned_at, due_date, status)
VALUES
  (@p_pipe, @rec1, @red3, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 2 DAY, 'Přiděleno'),
  (@p_pipe, @rec2, @red3, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 1 DAY, 'Přiděleno'),
  (@p_dev,  @rec2, @red3, NOW()-INTERVAL 9 DAY,  NOW()+INTERVAL 2 DAY, 'Přiděleno'),
  (@p_dev,  @rec3, @red3, NOW()-INTERVAL 9 DAY,  NOW()+INTERVAL 4 DAY, 'Přiděleno')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Oficiální recenze
INSERT INTO post_reviews (post_id, reviewer_id, score_actuality, score_originality, score_language, score_expertise, comment, created_at, updated_at)
VALUES
  (@p_pipe, @rec1, 4,4,4,4, 'Dobře strukturované, doplnit SLA příklady.', NOW()-INTERVAL 7 DAY, NOW()-INTERVAL 6 DAY),
  (@p_pipe, @rec2, 5,4,5,4, 'Skvělý jazyk, doporučuji přidat ukázkový ETL kód.', NOW()-INTERVAL 6 DAY, NOW()-INTERVAL 4 DAY),
  (@p_dev,  @rec2, 4,3,4,4, 'Přehledné, přidat sekci o tajných klíčích.', NOW()-INTERVAL 5 DAY, NULL),
  (@p_dev,  @rec3, 3,3,3,3, 'Potřeba víc praktických příkladů.', NOW()-INTERVAL 4 DAY, NULL)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

-- Uživatelské recenze
INSERT INTO user_reviews (post_id, user_id, rating, comment, created_at, updated_at)
VALUES
  (@p_smb,  @ct1, 5, 'Velmi praktické pro malé firmy, doporučuji.', NOW()-INTERVAL 3 DAY, NULL),
  (@p_smb,  @ct2, 4, 'Chybí krátký checklist, jinak super.', NOW()-INTERVAL 2 DAY, NULL),
  (@p_pipe, @ct1, 4, 'Hodně informací, možná stručnější závěr.', NOW()-INTERVAL 1 DAY, NULL),
  (@p_dev,  @ct2, 3, 'Fajn přehled, přidat část o nástrojích.', NOW()-INTERVAL 1 DAY, NULL)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

-- Komentáře
INSERT INTO comments (post_id, author_id, content, visibility, created_at)
VALUES
  (@p_smb,  @ct1, 'Díky za konkrétní tipy na nástroje.', 'public', NOW()-INTERVAL 2 DAY),
  (@p_smb,  @ct2, 'Souhlasím, checklist by se hodil.', 'public', NOW()-INTERVAL 1 DAY),
  (@p_pipe, @aut3, 'Přidám přílohu s ukázkovým ETL.', 'public', NOW()-INTERVAL 1 DAY),
  (@p_dev,  @rec2, 'Doplním sekci o správě secrets.', 'public', NOW()-INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE content=VALUES(content);

COMMIT;

