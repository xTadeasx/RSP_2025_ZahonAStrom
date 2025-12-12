-- Kompletní reset a seed databáze RSP (DROP + CREATE + DATA)
-- POZOR: Smaže existující databázi `RSP` a všechna její data.
-- Spouštěj jen v lokálním vývoji.

DROP DATABASE IF EXISTS `RSP`;
CREATE DATABASE `RSP` CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
USE `RSP`;

-- Schéma a základní data z původního dumpu (rsp.sql) + seeds

-- (1) Struktura tabulek
-- Převzato z rsp.sql (zkráceno na klíčové tabulky)
CREATE TABLE `users_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `workflow` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `password_temp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `role_id` int DEFAULT NULL,
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `abstract` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `state` int DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `topic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `authors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `issue_id` int DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `final_decision` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `final_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `final_decided_at` datetime DEFAULT NULL,
  `final_decided_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `user_id` (`user_id`),
  KEY `issue_id` (`issue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `author_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `visibility` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `comments_post_fk` (`post_id`),
  KEY `comments_author_fk` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `post_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `assigned_by` int DEFAULT NULL,
  `assigned_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `due_date` date DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_assignments_post_fk` (`post_id`),
  KEY `post_assignments_reviewer_fk` (`reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `post_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `score_actuality` tinyint NOT NULL,
  `score_originality` tinyint NOT NULL,
  `score_language` tinyint NOT NULL,
  `score_expertise` tinyint NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `author_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `author_comment_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_reviews_post_fk` (`post_id`),
  KEY `post_reviews_reviewer_fk` (`reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `user_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_reviews_post_idx` (`post_id`),
  KEY `user_reviews_user_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `chats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_one_id` int NOT NULL,
  `user_two_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chats_unique_pair` (`user_one_id`,`user_two_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_messages_chat_fk` (`chat_id`),
  KEY `chat_messages_sender_fk` (`sender_id`),
  KEY `chat_messages_receiver_fk` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL,
  `related_post_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_idx` (`user_id`),
  KEY `notifications_post_idx` (`related_post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE `system_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `event_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `system_logs_user_fk` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- (2) Vazby
ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `users_roles` (`id`);
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`state`) REFERENCES `workflow` (`id`);
ALTER TABLE `comments` ADD CONSTRAINT `comments_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `comments` ADD CONSTRAINT `comments_author_fk` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);
ALTER TABLE `post_assignments` ADD CONSTRAINT `post_assignments_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `post_assignments` ADD CONSTRAINT `post_assignments_reviewer_fk` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);
ALTER TABLE `post_reviews` ADD CONSTRAINT `post_reviews_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `post_reviews` ADD CONSTRAINT `post_reviews_reviewer_fk` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);
ALTER TABLE `chat_messages` ADD CONSTRAINT `chat_messages_chat_fk` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_post_fk` FOREIGN KEY (`related_post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `user_reviews` ADD CONSTRAINT `user_reviews_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_reviews` ADD CONSTRAINT `user_reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- (3) Základní data: role, workflow, admin
INSERT INTO users_roles (id, role) VALUES
 (1,'Administrátor'),(2,'Šéfredaktor'),(3,'Recenzent'),(4,'Redaktor'),(5,'Autor'),(6,'Čtenář')
ON DUPLICATE KEY UPDATE role=VALUES(role);

INSERT INTO workflow (id, state) VALUES
 (1,'Nový'),(2,'Odeslaný'),(3,'V recenzi'),(4,'Schváleno recenzenty'),(5,'Vrácen k úpravám'),(6,'Schválen'),(7,'Zamítnut')
ON DUPLICATE KEY UPDATE state=VALUES(state);

-- Seed hlavních účtů (username = heslo)
INSERT INTO users (username, password, role_id, email)
VALUES
 ('administrator', '$2y$12$fsE1SBnu3UIZItt//ij7AOWVD7bAIxsFKLtTB4PkcN/rQGJL4f8Su', 1, 'administrator@example.com'),
 ('sefredaktor',   '$2y$12$19J.Wcpdrt5z7Y2HlKy1M.uIR7tPQvnvxhIhlBcK9SPx7jRMsDhY2', 2, 'sefredaktor@example.com'),
 ('recenzent',     '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'recenzent@example.com'),
 ('redaktor',      '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'redaktor@example.com'),
 ('autor',         '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor@example.com'),
 ('ctenar',        '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'ctenar@example.com')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- (4) Rozšířená fiktivní data (uživatelé, články, recenze, komentáře)
-- Importuj SEM obsah seed_fixtures.sql (už sloučený):

-- Další uživatelé (username = heslo)
INSERT INTO users (username, password, role_id, email)
VALUES
  ('admin2',  '$2y$12$fsE1SBnu3UIZItt//ij7AOWVD7bAIxsFKLtTB4PkcN/rQGJL4f8Su', 1, 'admin2@example.com'),
  ('sef',     '$2y$12$19J.Wcpdrt5z7Y2HlKy1M.uIR7tPQvnvxhIhlBcK9SPx7jRMsDhY2', 2, 'sef@example.com'),
  ('rec1',    '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'rec1@example.com'),
  ('rec2',    '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'rec2@example.com'),
  ('red1',    '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'red1@example.com'),
  ('red2',    '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'red2@example.com'),
  ('autor1',  '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor1@example.com'),
  ('autor2',  '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor2@example.com'),
  ('ctenar1', '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'ctenar1@example.com'),
  ('ctenar2', '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'ctenar2@example.com')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- Lookup proměnné
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

SET @wf_novy      := (SELECT id FROM workflow WHERE state='Nový' LIMIT 1);
SET @wf_odeslany  := (SELECT id FROM workflow WHERE state='Odeslaný' LIMIT 1);
SET @wf_vrecenzi  := (SELECT id FROM workflow WHERE state='V recenzi' LIMIT 1);
SET @wf_vracen    := (SELECT id FROM workflow WHERE state='Vrácen k úpravám' LIMIT 1);
SET @wf_schvalen  := (SELECT id FROM workflow WHERE state='Schválen' LIMIT 1);
SET @wf_zamitnut  := (SELECT id FROM workflow WHERE state='Zamítnut' LIMIT 1);

-- Články
INSERT INTO posts (title, body, abstract, keywords, topic, authors, user_id, state, created_at, updated_at, created_by, updated_by)
VALUES
  ('AI v redakci', 'Obsah AI v redakci...', 'Abstrakt AI...', 'AI,redakce,workflow', 'IT', 'Autor Jeden', @aut1, @wf_odeslany, NOW()-INTERVAL 20 DAY, NOW()-INTERVAL 19 DAY, @aut1, @aut1),
  ('Kyberbezpečnost 2025', 'Trendy v bezpečnosti...', 'Abstrakt k bezpečnosti', 'security,zero trust,ai', 'Security', 'Redaktor Jedna', @red1, @wf_vrecenzi, NOW()-INTERVAL 18 DAY, NOW()-INTERVAL 15 DAY, @red1, @red1),
  ('Cloud a náklady', 'Optimalizace cloudu...', 'Abstrakt cloudu', 'cloud,costs,finops', 'Cloud', 'Autor Dva', @aut2, @wf_schvalen, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 10 DAY, @aut2, @aut2),
  ('Datová analytika', 'Data pipeline...', 'Abstrakt data', 'data,analytics,etl', 'Data', 'Redaktor Dva', @red2, @wf_vrecenzi, NOW()-INTERVAL 12 DAY, NOW()-INTERVAL 9 DAY, @red2, @red2),
  ('AI governance', 'Policy a governance...', 'Abstrakt governance', 'ai,governance,policy', 'IT', 'Autor Jeden', @aut1, @wf_schvalen, NOW()-INTERVAL 30 DAY, NOW()-INTERVAL 5 DAY, @aut1, @aut1)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

SET @p_ai      := (SELECT id FROM posts WHERE title='AI v redakci' LIMIT 1);
SET @p_sec     := (SELECT id FROM posts WHERE title='Kyberbezpečnost 2025' LIMIT 1);
SET @p_cloud   := (SELECT id FROM posts WHERE title='Cloud a náklady' LIMIT 1);
SET @p_data    := (SELECT id FROM posts WHERE title='Datová analytika' LIMIT 1);
SET @p_gov     := (SELECT id FROM posts WHERE title='AI governance' LIMIT 1);

-- Přiřazení recenzentů
INSERT INTO post_assignments (post_id, reviewer_id, assigned_by, assigned_at, due_date, status)
VALUES
  (@p_sec,  @rec1, @admin, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 2 DAY, 'Přiděleno'),
  (@p_sec,  @rec2, @admin, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 1 DAY, 'Přiděleno'),
  (@p_data, @rec1, @sef,   NOW()-INTERVAL 10 DAY, NOW()+INTERVAL 3 DAY, 'Přiděleno'),
  (@p_cloud,@rec2, @admin, NOW()-INTERVAL 9 DAY,  NOW()+INTERVAL 5 DAY, 'Přiděleno')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- Oficiální recenze
INSERT INTO post_reviews (post_id, reviewer_id, score_actuality, score_originality, score_language, score_expertise, comment, created_at, updated_at)
VALUES
  (@p_sec, @rec1, 4,4,4,4, 'Dobře napsané, doporučuji doladit příklady.', NOW()-INTERVAL 7 DAY, NOW()-INTERVAL 6 DAY),
  (@p_sec, @rec2, 5,4,4,5, 'Výborná originalita, text je srozumitelný.', NOW()-INTERVAL 6 DAY, NOW()-INTERVAL 4 DAY),
  (@p_data,@rec1, 3,3,4,3, 'Potřeba víc detailů k ETL.', NOW()-INTERVAL 5 DAY, NULL),
  (@p_cloud,@rec2, 4,3,4,4, 'Srozumitelné, přidat cost model příklady.', NOW()-INTERVAL 4 DAY, NULL)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

-- Uživatelské recenze (tabulka už existuje)
INSERT INTO user_reviews (post_id, user_id, rating, comment, created_at, updated_at)
VALUES
  (@p_sec,  @cte1, 5, 'Skvělý článek, srozumitelný i pro neodborníky.', NOW()-INTERVAL 3 DAY, NULL),
  (@p_sec,  @cte2, 4, 'Chybí mi více příkladů z praxe.', NOW()-INTERVAL 2 DAY, NULL),
  (@p_gov,  @cte1, 4, 'Užitečný přehled governance.', NOW()-INTERVAL 1 DAY, NULL),
  (@p_ai,   @cte2, 3, 'Zajímavé, ale chtělo by to víc ukázek.', NOW()-INTERVAL 1 DAY, NULL),
  (@p_cloud,@cte1, 4, 'Praktické, líbily se mi tipy na optimalizaci.', NOW()-INTERVAL 1 DAY, NULL)
ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at);

-- Komentáře
INSERT INTO comments (post_id, author_id, content, visibility, created_at)
VALUES
  (@p_sec,  @cte1, 'Paráda, díky za sdílení.', 'public', NOW()-INTERVAL 2 DAY),
  (@p_sec,  @cte2, 'Souhlasím s recenzentem, více příkladů.', 'public', NOW()-INTERVAL 1 DAY),
  (@p_ai,   @aut2, 'Taky řešíme AI v týmu, díky za inspiraci.', 'public', NOW()-INTERVAL 1 DAY),
  (@p_gov,  @aut1, 'Governance je klíčová, dobrý přehled.', 'public', NOW()-INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE content=VALUES(content);

-- Publikace schváleného
UPDATE posts SET published_at = COALESCE(published_at, NOW()-INTERVAL 4 DAY) WHERE id = @p_gov AND state = @wf_schvalen;
UPDATE posts SET published_at = COALESCE(published_at, NOW()-INTERVAL 6 DAY) WHERE id = @p_cloud AND state = @wf_schvalen;

COMMIT;

-- KONEC: spuštěním tohoto souboru dostaneš čistou DB s kompletním seedem

