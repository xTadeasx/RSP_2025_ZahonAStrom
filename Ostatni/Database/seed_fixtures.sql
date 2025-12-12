-- Seed rozsáhlejších fiktivních dat pro lokální testování
-- Spouštěj po importu hlavního dumpu rsp.sql

START TRANSACTION;

-- Uživatelé (username = heslo)
INSERT INTO users (username, password, role_id, email)
VALUES
  ('admin2',        '$2y$12$fsE1SBnu3UIZItt//ij7AOWVD7bAIxsFKLtTB4PkcN/rQGJL4f8Su', 1, 'admin2@example.com'),
  ('sef',           '$2y$12$19J.Wcpdrt5z7Y2HlKy1M.uIR7tPQvnvxhIhlBcK9SPx7jRMsDhY2', 2, 'sef@example.com'),
  ('rec1',          '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'rec1@example.com'),
  ('rec2',          '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'rec2@example.com'),
  ('red1',          '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'red1@example.com'),
  ('red2',          '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'red2@example.com'),
  ('autor1',        '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor1@example.com'),
  ('autor2',        '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor2@example.com'),
  ('ctenar1',       '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'ctenar1@example.com'),
  ('ctenar2',       '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'ctenar2@example.com')
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Rychlé lookup ID
SET @admin := (SELECT id FROM users WHERE username='admin2' LIMIT 1);
SET @sef   := (SELECT id FROM users WHERE username='sef' LIMIT 1);
SET @rec1  := (SELECT id FROM users WHERE username='rec1' LIMIT 1);
SET @rec2  := (SELECT id FROM users WHERE username='rec2' LIMIT 1);
SET @red1  := (SELECT id FROM users WHERE username='red1' LIMIT 1);
SET @red2  := (SELECT id FROM users WHERE username='red2' LIMIT 1);
SET @aut1  := (SELECT id FROM users WHERE username='autor1' LIMIT 1);
SET @aut2  := (SELECT id FROM users WHERE username='autor2' LIMIT 1);
SET @cte1  := (SELECT id FROM users WHERE username='ctenar1' LIMIT 1);
SET @cte2  := (SELECT id FROM users WHERE username='ctenar2' LIMIT 1);

-- Workflows
SET @wf_novy      := (SELECT id FROM workflow WHERE state='Nový' LIMIT 1);
SET @wf_odeslany  := (SELECT id FROM workflow WHERE state='Odeslaný' LIMIT 1);
SET @wf_vrecenzi  := (SELECT id FROM workflow WHERE state='V recenzi' LIMIT 1);
SET @wf_vracen    := (SELECT id FROM workflow WHERE state='Vrácen k úpravám' LIMIT 1);
SET @wf_schvalen  := (SELECT id FROM workflow WHERE state='Schválen' LIMIT 1);
SET @wf_zamitnut  := (SELECT id FROM workflow WHERE state='Zamítnut' LIMIT 1);

-- Články autorů/redaktorů
INSERT INTO posts (title, body, abstract, keywords, topic, authors, user_id, state, created_at, updated_at, created_by, updated_by)
VALUES
  ('AI v redakci', 'Obsah AI v redakci...', 'Abstrakt AI...', 'AI,redakce,workflow', 'IT', 'Autor Jeden', @aut1, @wf_odeslany, NOW()-INTERVAL 20 DAY, NOW()-INTERVAL 19 DAY, @aut1, @aut1),
  ('Kyberbezpečnost 2025', 'Trendy v bezpečnosti...', 'Abstrakt k bezpečnosti', 'security,zero trust,ai', 'Security', 'Redaktor Jedna', @red1, @wf_vrecenzi, NOW()-INTERVAL 18 DAY, NOW()-INTERVAL 15 DAY, @red1, @red1),
  ('Cloud a náklady', 'Optimalizace cloudu...', 'Abstrakt cloudu', 'cloud,costs,finops', 'Cloud', 'Autor Dva', @aut2, @wf_vracen, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 10 DAY, @aut2, @aut2),
  ('Datová analytika', 'Data pipeline...', 'Abstrakt data', 'data,analytics,etl', 'Data', 'Redaktor Dva', @red2, @wf_vrecenzi, NOW()-INTERVAL 12 DAY, NOW()-INTERVAL 9 DAY, @red2, @red2),
  ('AI governance', 'Policy a governance...', 'Abstrakt governance', 'ai,governance,policy', 'IT', 'Autor Jeden', @aut1, @wf_schvalen, NOW()-INTERVAL 30 DAY, NOW()-INTERVAL 5 DAY, @aut1, @aut1)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

SET @p_ai      := (SELECT id FROM posts WHERE title='AI v redakci' LIMIT 1);
SET @p_sec     := (SELECT id FROM posts WHERE title='Kyberbezpečnost 2025' LIMIT 1);
SET @p_cloud   := (SELECT id FROM posts WHERE title='Cloud a náklady' LIMIT 1);
SET @p_data    := (SELECT id FROM posts WHERE title='Datová analytika' LIMIT 1);
SET @p_gov     := (SELECT id FROM posts WHERE title='AI governance' LIMIT 1);

-- Ujisti se, že existuje tabulka user_reviews
CREATE TABLE IF NOT EXISTS user_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    INDEX user_reviews_post_idx (post_id),
    INDEX user_reviews_user_idx (user_id),
    CONSTRAINT user_reviews_post_fk FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT user_reviews_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Přiřazení recenzentů
INSERT INTO post_assignments (post_id, reviewer_id, assigned_by, assigned_at, due_date, status)
VALUES
  (@p_sec,  @rec1, @admin, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 2 DAY, 'Přiděleno'),
  (@p_sec,  @rec2, @admin, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 1 DAY, 'Přiděleno'),
  (@p_data, @rec1, @sef,   NOW()-INTERVAL 10 DAY, NOW()+INTERVAL 3 DAY, 'Přiděleno')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Oficiální recenze (recenzenti)
INSERT INTO post_reviews (post_id, reviewer_id, score_actuality, score_originality, score_language, score_expertise, comment, created_at, updated_at)
VALUES
  (@p_sec, @rec1, 4,4,4,4, 'Dobře napsané, doporučuji doladit příklady.', NOW()-INTERVAL 7 DAY, NOW()-INTERVAL 6 DAY),
  (@p_sec, @rec2, 5,4,4,5, 'Výborná originalita, text je srozumitelný.', NOW()-INTERVAL 6 DAY, NOW()-INTERVAL 4 DAY),
  (@p_data,@rec1, 3,3,4,3, 'Potřeba víc detailů k ETL.', NOW()-INTERVAL 5 DAY, NULL)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

-- Uživatelské recenze (kdokoli přihlášený)
INSERT INTO user_reviews (post_id, user_id, rating, comment, created_at, updated_at)
VALUES
  (@p_sec,  @cte1, 5, 'Skvělý článek, srozumitelný i pro neodborníky.', NOW()-INTERVAL 3 DAY, NULL),
  (@p_sec,  @cte2, 4, 'Chybí mi více příkladů z praxe.', NOW()-INTERVAL 2 DAY, NULL),
  (@p_gov,  @cte1, 4, 'Užitečný přehled governance.', NOW()-INTERVAL 1 DAY, NULL),
  (@p_ai,   @cte2, 3, 'Zajímavé, ale chtělo by to víc ukázek.', NOW()-INTERVAL 1 DAY, NULL)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

-- Komentáře
INSERT INTO comments (post_id, author_id, content, visibility, created_at)
VALUES
  (@p_sec,  @cte1, 'Paráda, díky za sdílení.', 'public', NOW()-INTERVAL 2 DAY),
  (@p_sec,  @cte2, 'Souhlasím s recenzentem, více příkladů.', 'public', NOW()-INTERVAL 1 DAY),
  (@p_ai,   @aut2, 'Taky řešíme AI v týmu, díky za inspiraci.', 'public', NOW()-INTERVAL 1 DAY),
  (@p_gov,  @aut1, 'Governance je klíčová, dobrý přehled.', 'public', NOW()-INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE content=VALUES(content);

-- Nastavit published_at pro schválené
UPDATE posts SET published_at = COALESCE(published_at, NOW()-INTERVAL 4 DAY) WHERE id = @p_gov AND state = @wf_schvalen;

COMMIT;

