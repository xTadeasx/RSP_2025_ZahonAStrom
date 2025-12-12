-- phpMyAdmin SQL Dump
-- Kompletní reset a seed databáze RSP
-- POZOR: Smaže existující databázi `RSP` a všechna její data.
-- Spouštěj jen v lokálním vývoji nebo při prvním nastavení.

DROP DATABASE IF EXISTS `RSP`;
CREATE DATABASE `RSP` CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
USE `RSP`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Struktura tabulek
-- --------------------------------------------------------

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

CREATE TABLE `issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL,
  `number` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `published_at` date DEFAULT NULL,
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
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `avatar_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
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
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci COMMENT 'Content of the post',
  `abstract` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Abstrakt článku',
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Klíčová slova oddělená čárkami',
  `user_id` int DEFAULT NULL,
  `state` int DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci DEFAULT NULL COMMENT 'Náhledový obrázek článku',
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
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci,
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
  KEY `notifications_user_fk` (`user_id`),
  KEY `notifications_post_fk` (`related_post_id`)
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

-- --------------------------------------------------------
-- Foreign Keys
-- --------------------------------------------------------

ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `users_roles` (`id`);
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`state`) REFERENCES `workflow` (`id`);
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_issue` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`);
ALTER TABLE `comments` ADD CONSTRAINT `comments_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `comments` ADD CONSTRAINT `comments_author_fk` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);
ALTER TABLE `post_assignments` ADD CONSTRAINT `post_assignments_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `post_assignments` ADD CONSTRAINT `post_assignments_reviewer_fk` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);
ALTER TABLE `post_reviews` ADD CONSTRAINT `post_reviews_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `post_reviews` ADD CONSTRAINT `post_reviews_reviewer_fk` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);
ALTER TABLE `user_reviews` ADD CONSTRAINT `user_reviews_post_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;
ALTER TABLE `user_reviews` ADD CONSTRAINT `user_reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `chats` ADD CONSTRAINT `chats_user_one_fk` FOREIGN KEY (`user_one_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `chats` ADD CONSTRAINT `chats_user_two_fk` FOREIGN KEY (`user_two_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `chat_messages` ADD CONSTRAINT `chat_messages_chat_fk` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;
ALTER TABLE `chat_messages` ADD CONSTRAINT `chat_messages_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `chat_messages` ADD CONSTRAINT `chat_messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_post_fk` FOREIGN KEY (`related_post_id`) REFERENCES `posts` (`id`);
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
ALTER TABLE `system_logs` ADD CONSTRAINT `system_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- --------------------------------------------------------
-- Základní data: Role a Workflow
-- --------------------------------------------------------

INSERT INTO `users_roles` (`id`, `role`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Administrátor', NULL, NULL, NULL, NULL),
(2, 'Šéfredaktor', NULL, NULL, NULL, NULL),
(3, 'Recenzent', NULL, NULL, NULL, NULL),
(4, 'Redaktor', NULL, NULL, NULL, NULL),
(5, 'Autor', NULL, NULL, NULL, NULL),
(6, 'Čtenář', NULL, NULL, NULL, NULL);

INSERT INTO `workflow` (`id`, `state`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Nový', NULL, NULL, NULL, NULL),
(2, 'Odeslaný', NULL, NULL, NULL, NULL),
(3, 'V recenzi', NULL, NULL, NULL, NULL),
(4, 'Schváleno recenzenty', NULL, NULL, NULL, NULL),
(5, 'Vrácen k úpravám', NULL, NULL, NULL, NULL),
(6, 'Schválen', NULL, NULL, NULL, NULL),
(7, 'Zamítnut', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Základní testovací uživatelé (username = heslo = název role)
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `password`, `role_id`, `email`, `phone`, `bio`, `avatar_path`) VALUES
('administrator', '$2y$12$fsE1SBnu3UIZItt//ij7AOWVD7bAIxsFKLtTB4PkcN/rQGJL4f8Su', 1, 'administrator@rsp.cz', '+420 100 000 000', 'Hlavní administrátor systému.', 'uploads/avatars/administrator.jpg'),
('sefredaktor', '$2y$12$19J.Wcpdrt5z7Y2HlKy1M.uIR7tPQvnvxhIhlBcK9SPx7jRMsDhY2', 2, 'sefredaktor@rsp.cz', '+420 200 000 000', 'Šéfredaktor časopisu.', 'uploads/avatars/sefredaktor.jpg'),
('recenzent', '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'recenzent@rsp.cz', '+420 300 000 000', 'Recenzent článků.', 'uploads/avatars/recenzent.jpg'),
('redaktor', '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'redaktor@rsp.cz', '+420 400 000 000', 'Redaktor časopisu.', 'uploads/avatars/redaktor.jpg'),
('autor', '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'autor@rsp.cz', '+420 500 000 000', 'Autor článků.', 'uploads/avatars/autor.jpg'),
('ctenar', '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'ctenar@rsp.cz', '+420 600 000 000', 'Čtenář časopisu.', 'uploads/avatars/ctenar.jpg');

-- --------------------------------------------------------
-- Základní uživatelé s fiktivními jmény (username = fiktivní jméno, heslo = role)
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `password`, `role_id`, `email`, `phone`, `bio`, `avatar_path`) VALUES
('petr_novak', '$2y$12$fsE1SBnu3UIZItt//ij7AOWVD7bAIxsFKLtTB4PkcN/rQGJL4f8Su', 1, 'petr.novak@rsp.cz', '+420 123 456 789', 'Hlavní administrátor systému s více než 10 lety praxe v IT.', 'uploads/avatars/petr_novak.jpg'),
('jana_svobodova',   '$2y$12$19J.Wcpdrt5z7Y2HlKy1M.uIR7tPQvnvxhIhlBcK9SPx7jRMsDhY2', 2, 'jana.svobodova@rsp.cz', '+420 987 654 321', 'Šéfredaktorka s dlouholetou zkušeností v akademickém publikování a vědecké komunikaci.', 'uploads/avatars/jana_svobodova.jpg'),
('martin_prochazka',     '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'martin.prochazka@rsp.cz', '+420 555 666 777', 'Recenzent specializující se na IT a kyberbezpečnost, doktorand na ČVUT.', 'uploads/avatars/martin_prochazka.jpg'),
('lucie_dvorakova',      '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'lucie.dvorakova@rsp.cz', '+420 111 222 333', 'Redaktorka s více než 5 lety zkušeností v technickém psaní a editaci.', 'uploads/avatars/lucie_dvorakova.jpg'),
('tomas_horak',         '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'tomas.horak@rsp.cz', '+420 444 555 666', 'Autor článků o moderních technologiích, absolvent VŠE.', 'uploads/avatars/tomas_horak.jpg'),
('veronika_krejcova',        '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'veronika.krejcova@rsp.cz', '+420 777 888 999', 'Nadšená čtenářka technických článků a studentka informatiky.', 'uploads/avatars/veronika_krejcova.jpg');

-- --------------------------------------------------------
-- Rozšíření uživatelé s fiktivními jmény, bio a profilovkami
-- --------------------------------------------------------

INSERT INTO `users` (`username`, `password`, `role_id`, `email`, `phone`, `bio`, `avatar_path`) VALUES
('david_cerny',  '$2y$12$fsE1SBnu3UIZItt//ij7AOWVD7bAIxsFKLtTB4PkcN/rQGJL4f8Su', 1, 'david.cerny@rsp.cz', '+420 111 111 111', 'Zkušený administrátor systému s více než 10 lety praxe v enterprise prostředí.', 'uploads/avatars/david_cerny.jpg'),
('katerina_novotna',     '$2y$12$19J.Wcpdrt5z7Y2HlKy1M.uIR7tPQvnvxhIhlBcK9SPx7jRMsDhY2', 2, 'katerina.novotna@rsp.cz', '+420 222 222 222', 'Šéfredaktorka s dlouholetou zkušeností v akademickém publikování, Ph.D. v oboru informatiky.', 'uploads/avatars/katerina_novotna.jpg'),
('jakub_vesely',    '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'jakub.vesely@rsp.cz', '+420 333 333 333', 'Recenzent specializující se na IT a kyberbezpečnost, bezpečnostní analytik ve velké firmě.', 'uploads/avatars/jakub_vesely.jpg'),
('pavla_pokorna',    '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'pavla.pokorna@rsp.cz', '+420 444 444 444', 'Recenzentka zaměřená na datovou analytiku a cloud technologie, datová vědkyně.', 'uploads/avatars/pavla_pokorna.jpg'),
('ondrej_maly',    '$2y$12$61STGjpX3zbwBHeQhmxkre4TEeNuD4cinHyKaH6SNLM3TJl/gFxXS', 3, 'ondrej.maly@rsp.cz', '+420 999 222 111', 'Recenzent pro data/AI, výzkumný pracovník v oblasti strojového učení.', 'uploads/avatars/ondrej_maly.jpg'),
('michal_kral',    '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'michal.kral@rsp.cz', '+420 555 555 555', 'Redaktor pro IT sekci, zaměření na AI a automatizaci, bývalý vývojář.', 'uploads/avatars/michal_kral.jpg'),
('tereza_benesova',    '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'tereza.benesova@rsp.cz', '+420 666 666 666', 'Redaktorka pro datovou sekci, expertka na ETL a data pipeline, certifikovaná datová inženýrka.', 'uploads/avatars/tereza_benesova.jpg'),
('radek_stepanek',    '$2y$12$8w72.VApO1M7rcgHv2.B0.ZQVc2cEBTWstIN/4NNsDhqT0IUMJVu.', 4, 'radek.stepanek@rsp.cz', '+420 777 333 222', 'Redaktor pro cloud a DevOps, cloud architekt s certifikacemi AWS a Azure.', 'uploads/avatars/radek_stepanek.jpg'),
('lenka_hajkova',  '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'lenka.hajkova@rsp.cz', '+420 777 777 777', 'Autorka článků o AI a strojovém učení, popularizátorka vědy, Ph.D. studentka.', 'uploads/avatars/lenka_hajkova.jpg'),
('filip_urban',  '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'filip.urban@rsp.cz', '+420 888 888 888', 'Autor zaměřený na cloud computing a DevOps praktiky, senior DevOps engineer.', 'uploads/avatars/filip_urban.jpg'),
('zuzana_moravcova',  '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'zuzana.moravcova@rsp.cz', '+420 123 456 789', 'Popularizátorka vědy, píše o AI v praxi, konzultantka pro malé firmy.', 'uploads/avatars/zuzana_moravcova.jpg'),
('jan_kubicek',  '$2y$12$EaFVSrWdjIk1xHnH1K3RR.lwMHqDe2QVtMTZh3V8oeHnl1fzXC07G', 5, 'jan.kubicek@rsp.cz', '+420 777 444 555', 'Datový analytik, zaměření na datové pipeline, senior data engineer.', 'uploads/avatars/jan_kubicek.jpg'),
('adam_klimes', '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'adam.klimes@rsp.cz', '+420 999 999 999', 'Nadšený čtenář technických článků, student informatiky na VŠPJ.', 'uploads/avatars/adam_klimes.jpg'),
('eva_soukupova', '$2y$12$A4WlD1gwvWpGa/QDY8amhOPvkLZkNJ/SwDz3Re3CTsKAYBaW4ZC7y', 6, 'eva.soukupova@rsp.cz', '+420 000 000 000', 'Čtenářka se zájmem o AI a moderní technologie, začínající vývojářka.', 'uploads/avatars/eva_soukupova.jpg');

-- --------------------------------------------------------
-- Vydání (Issues)
-- --------------------------------------------------------

INSERT INTO `issues` (`year`, `number`, `title`, `published_at`) VALUES
(2025, 1, 'Jaro 2025 – AI a data', '2025-03-15'),
(2025, 2, 'Léto 2025 – Bezpečnost a DevOps', '2025-06-20'),
(2025, 3, 'Podzim 2025 – Cloud a governance', '2025-09-25');

-- --------------------------------------------------------
-- Lookup proměnné pro další data
-- --------------------------------------------------------

SET @admin := (SELECT id FROM users WHERE username='petr_novak' LIMIT 1);
SET @admin2 := (SELECT id FROM users WHERE username='david_cerny' LIMIT 1);
SET @sef   := (SELECT id FROM users WHERE username='jana_svobodova' LIMIT 1);
SET @sef2  := (SELECT id FROM users WHERE username='katerina_novotna' LIMIT 1);
SET @rec1  := (SELECT id FROM users WHERE username='jakub_vesely' LIMIT 1);
SET @rec2  := (SELECT id FROM users WHERE username='pavla_pokorna' LIMIT 1);
SET @rec3  := (SELECT id FROM users WHERE username='ondrej_maly' LIMIT 1);
SET @red1  := (SELECT id FROM users WHERE username='michal_kral' LIMIT 1);
SET @red2  := (SELECT id FROM users WHERE username='tereza_benesova' LIMIT 1);
SET @red3  := (SELECT id FROM users WHERE username='radek_stepanek' LIMIT 1);
SET @aut1  := (SELECT id FROM users WHERE username='lenka_hajkova' LIMIT 1);
SET @aut2  := (SELECT id FROM users WHERE username='filip_urban' LIMIT 1);
SET @aut3  := (SELECT id FROM users WHERE username='zuzana_moravcova' LIMIT 1);
SET @aut4  := (SELECT id FROM users WHERE username='jan_kubicek' LIMIT 1);
SET @cte1  := (SELECT id FROM users WHERE username='adam_klimes' LIMIT 1);
SET @cte2  := (SELECT id FROM users WHERE username='eva_soukupova' LIMIT 1);

SET @wf_novy      := (SELECT id FROM workflow WHERE state='Nový' LIMIT 1);
SET @wf_odeslany  := (SELECT id FROM workflow WHERE state='Odeslaný' LIMIT 1);
SET @wf_vrecenzi  := (SELECT id FROM workflow WHERE state='V recenzi' LIMIT 1);
SET @wf_vracen    := (SELECT id FROM workflow WHERE state='Vrácen k úpravám' LIMIT 1);
SET @wf_schvalen  := (SELECT id FROM workflow WHERE state='Schválen' LIMIT 1);
SET @wf_zamitnut  := (SELECT id FROM workflow WHERE state='Zamítnut' LIMIT 1);

SET @iss1 := (SELECT id FROM issues WHERE year=2025 AND number=1 LIMIT 1);
SET @iss2 := (SELECT id FROM issues WHERE year=2025 AND number=2 LIMIT 1);
SET @iss3 := (SELECT id FROM issues WHERE year=2025 AND number=3 LIMIT 1);

-- --------------------------------------------------------
-- Články (různé stavy, přiřazení k vydáním)
-- --------------------------------------------------------

INSERT INTO `posts` (`title`, `body`, `abstract`, `keywords`, `topic`, `authors`, `file_path`, `image_path`, `user_id`, `state`, `issue_id`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
('AI v redakci', 
'Umělá inteligence se stává stále důležitějším nástrojem v redakční práci. V posledních letech jsme svědky rapidního rozvoje AI technologií, které transformují způsob, jakým redaktoři pracují s textem. Tento článek se zaměřuje na praktické využití AI nástrojů v redakčním workflow a jejich dopad na produktivitu a kvalitu publikovaných materiálů.

**Úvod do AI v redakci**

Moderní redakční systémy začínají integrovat AI technologie do svých workflow procesů. Tyto nástroje pomáhají s kontrolou gramatiky, stylistiky, faktografickou kontrolou a dokonce s návrhy struktury článků. Gramatické kontrolory založené na AI, jako je Grammarly nebo LanguageTool, dokáží detekovat nejen základní chyby, ale také stylistické nedostatky a nejasnosti v textu.

**Automatizace redakčních procesů**

Jednou z hlavních výhod AI v redakci je automatizace rutinních úkolů. AI nástroje mohou automaticky kontrolovat konzistenci terminologie, formátování citací podle různých stylů (APA, MLA, Chicago) a dokonce navrhovat vhodné nadpisy a podnadpisy pro lepší strukturu textu. To umožňuje redaktorům soustředit se na obsahovou stránku textu místo technických detailů.

**AI a kontrola faktů**

Pokročilejší AI systémy začínají být schopné kontrolovat faktografickou správnost tvrzení v článcích. Pomocí přístupu k rozsáhlým databázím a znalostním grafům mohou identifikovat potenciálně problematická tvrzení a navrhnout ověření zdrojů. Tato funkce je zvláště cenná v akademickém a vědeckém publikování, kde přesnost informací je kritická.

**Výzvy a limity**

Navzdory výhodám AI nástrojů existují určité limity. AI systémy mohou mít problémy s kontextem, ironií nebo kulturními nuancemi. Je důležité, aby redaktoři používali AI jako podpůrný nástroj, nikoliv jako náhradu za lidský úsudek. Kvalitní redakční práce stále vyžaduje lidskou expertizu a kritické myšlení.

**Budoucnost AI v redakci**

Budoucí vývoj AI v redakci pravděpodobně povede k ještě sofistikovanějším nástrojům, které budou schopné komplexnější analýzy textu, včetně hodnocení argumentační struktury nebo identifikace potenciálních biasů. Klíčové bude najít správnou rovnováhu mezi automatizací a zachováním lidského přístupu k redakční práci.',
'Tento článek se zabývá využitím umělé inteligence v redakčním workflow. Analyzuje konkrétní AI nástroje pro kontrolu gramatiky, stylistiky a faktografickou kontrolu, diskutuje výhody automatizace redakčních procesů a upozorňuje na limity současných AI systémů. Článek poskytuje praktický přehled současného stavu a budoucích trendů AI v redakční práci.',
'AI,redakce,workflow,automatizace,gramatika,stylistika',
'IT',
'Lenka Hájková',
NULL,
'uploads/images/ai_redakce.jpg',
@aut1,
@wf_odeslany,
NULL,
NOW()-INTERVAL 20 DAY,
NOW()-INTERVAL 19 DAY,
@aut1,
@aut1),

('Kyberbezpečnost 2025',
'Rok 2025 přináší do oblasti kybernetické bezpečnosti nové výzvy a příležitosti. S rostoucí digitalizací a závislostí na cloudových službách se bezpečnostní hrozby stávají stále sofistikovanějšími. Tento článek poskytuje komplexní přehled aktuálních trendů v kyberbezpečnosti a praktické doporučení pro organizace.

**Zero Trust architektura jako nový standard**

Zero Trust architektura se stává de facto standardem pro moderní bezpečnostní infrastrukturu. Na rozdíl od tradičního přístupu "důvěřuj, ale ověřuj" Zero Trust předpokládá, že žádný uživatel ani zařízení není automaticky důvěryhodný. Každý přístupový požadavek musí být ověřen a autorizován bez ohledu na umístění uživatele nebo zařízení v síti.

Implementace Zero Trust vyžaduje kombinaci více technologií: multi-factor autentizace (MFA), identity a access management (IAM), micro-segmentation sítě a kontinuální monitoring. Organizace, které implementují Zero Trust, zaznamenávají významné snížení rizika úspěšných útoků.

**AI-powered útoky a obrana**

Umělá inteligence se stává dvojsečnou zbraní v kyberbezpečnosti. Útočníci využívají AI pro automatizaci útoků, vytváření sofistikovanějších phishingových kampaní a obcházení bezpečnostních kontrol. Na druhé straně AI-powered bezpečnostní nástroje dokáží detekovat anomálie v síťovém provozu, identifikovat zero-day zranitelnosti a automaticky reagovat na hrozby v reálném čase.

Machine learning modely analyzují obrovské množství dat z bezpečnostních logů a identifikují vzorce, které by lidským analytikům unikly. Behaviorální analýza uživatelů (UEBA) dokáže detekovat neobvyklé aktivity, které mohou indikovat kompromitovaný účet nebo insider threat.

**Cloud security a sdílená odpovědnost**

S migrací do cloudu se mění model bezpečnosti. Cloud poskytovatelé (CSP) jsou zodpovědní za bezpečnost cloudu samotného (security of the cloud), zatímco zákazníci zodpovídají za bezpečnost v cloudu (security in the cloud). Tento model sdílené odpovědnosti vyžaduje od organizací důkladné pochopení jejich zodpovědnosti.

Cloud security posture management (CSPM) nástroje pomáhají organizacím identifikovat miskonfigurace cloudových prostředků, které mohou vést k bezpečnostním incidentům. Pravidelné audity IAM politik, šifrování dat v klidu i při přenosu a správné nastavení firewallů jsou kritické pro cloudovou bezpečnost.

**Ransomware a ochrana dat**

Ransomware útoky zůstávají jednou z největších hrozeb. Útočníci stále zdokonalují své techniky, včetně double extortion, kdy kromě šifrování dat také exfiltrují citlivé informace a hrozí jejich zveřejněním. Ochrana proti ransomware vyžaduje multi-layered přístup: pravidelné zálohy, network segmentation, endpoint detection and response (EDR) a uživatelské vzdělávání.

**Best practices pro rok 2025**

Organizace by měly implementovat následující best practices: pravidelné bezpečnostní audity a penetration testing, implementaci security information and event management (SIEM) systémů, kontinuální monitoring a incident response plánování. Důležitá je také spolupráce s bezpečnostní komunitou a sdílení informací o hrozbách (threat intelligence sharing).',
'Článek poskytuje komplexní přehled aktuálních trendů v kybernetické bezpečnosti pro rok 2025. Zaměřuje se na Zero Trust architekturu, AI-powered útoky a obranu, cloud security a ochranu proti ransomware. Obsahuje praktická doporučení a best practices pro organizace.',
'security,zero trust,ai,cloud,ransomware,cyberbezpečnost',
'Security',
'Michal Král',
NULL,
'uploads/images/kyberbezpecnost_2025.jpg',
@red1,
@wf_vrecenzi,
@iss2,
NOW()-INTERVAL 18 DAY,
NOW()-INTERVAL 15 DAY,
@red1,
@red1),

('Cloud a náklady',
'FinOps (Financial Operations) se stává klíčovou disciplínou pro organizace využívající cloudové služby. S rostoucí adopcí cloudu roste také potřeba efektivního řízení cloudových nákladů. Tento článek poskytuje praktický průvodce optimalizací cloudových výdajů a implementací FinOps principů.

**Výzvy cloudových nákladů**

Jednou z hlavních výzev cloud computing je nepředvídatelnost nákladů. Organizace často čelí "cloud bill shock" - neočekávaně vysokým účtům za cloudové služby. Příčiny mohou být různé: nedostatečné monitorování využití zdrojů, opuštěné instance, neoptimální velikosti instancí nebo nedostatečné využití rezervovaných instancí.

Cloud poskytovatelé nabízejí flexibilní cenové modely, ale bez správného řízení mohou náklady rychle narůst. Důležité je pochopit různé cenové modely (on-demand, reserved instances, spot instances) a vybrat ten správný pro konkrétní use case.

**FinOps metodologie**

FinOps je kulturní praxe, která kombinuje finance, technologie a obchodní jednotky pro optimalizaci cloudových nákladů. Základní principy FinOps zahrnují: informovanost (informování všech týmů o cloudových nákladech), přidělování nákladů (cost allocation) a optimalizaci (kontinuální optimalizace využití zdrojů).

Klíčové metriky pro FinOps zahrnují: celkové cloudové náklady, náklady na jednotku (cost per unit), využití zdrojů (resource utilization) a waste (nevyužité nebo neefektivně využívané zdroje). Pravidelné FinOps review meetingy pomáhají identifikovat příležitosti k optimalizaci.

**Nástroje pro monitoring a optimalizaci**

Trh nabízí řadu nástrojů pro monitoring a optimalizaci cloudových nákladů. Cloud poskytovatelé poskytují vlastní nástroje (AWS Cost Explorer, Azure Cost Management, Google Cloud Billing), ale existují také third-party řešení, která poskytují cross-cloud visibility a pokročilejší analytiku.

Cost allocation tags jsou kritické pro správné přidělování nákladů na jednotlivé projekty, týmy nebo aplikace. Správné tagování umožňuje identifikovat, které části organizace generují nejvyšší náklady a kde jsou příležitosti k optimalizaci.

**Strategie optimalizace nákladů**

Efektivní optimalizace cloudových nákladů vyžaduje kombinaci několika strategií. Right-sizing instancí znamená výběr správné velikosti instance pro konkrétní workload - často organizace používají příliš velké instance, což vede k plýtvání zdroji.

Reserved instances a savings plans poskytují významné slevy (až 72%) za závazek k dlouhodobému využití. Spot instances jsou ideální pro fault-tolerant workloads a mohou poskytnout úspory až 90% oproti on-demand cenám.

Automatizace je klíčová pro efektivní správu cloudových nákladů. Auto-scaling skupiny automaticky upravují počet instancí podle aktuální zátěže. Scheduled actions mohou automaticky zastavovat nebo spouštět instance mimo pracovní dobu. Lifecycle policies automaticky archivují nebo mažou stará data podle předdefinovaných pravidel.

**Best practices**

Doporučené postupy zahrnují: pravidelné review cloudových účtů, implementaci cost alerts pro upozornění na neobvyklé výdaje, pravidelné right-sizing review, využití cost optimization doporučení od cloud poskytovatelů a vzdělávání týmů o cloudových nákladech a FinOps principech.',
'Článek poskytuje komplexní průvodce optimalizací cloudových nákladů pomocí FinOps metodik. Diskutuje výzvy cloudových nákladů, FinOps principy, nástroje pro monitoring a strategie optimalizace včetně right-sizing, reserved instances a automatizace.',
'cloud,costs,finops,optimization,cloud computing,řízení nákladů',
'Cloud',
'Filip Urban',
NULL,
'uploads/images/cloud_naklady.jpg',
@aut2,
@wf_schvalen,
@iss1,
NOW()-INTERVAL 14 DAY,
NOW()-INTERVAL 10 DAY,
@aut2,
@aut2),

('Datová analytika',
'Moderní datová analytika vyžaduje robustní a škálovatelné pipeline pro zpracování velkých objemů dat. S exponenciálním růstem dat generovaných organizacemi se stává efektivní zpracování dat kritickým faktorem úspěchu. Tento článek poskytuje komplexní přehled datových pipeline, ETL/ELT procesů a nástrojů pro efektivní zpracování dat.

**Úvod do datových pipeline**

Datová pipeline je sada procesů, které přenášejí data z různých zdrojů do cílového úložiště, přičemž data transformují a validují. Moderní datové pipeline musí být schopny zpracovávat strukturovaná i nestrukturovaná data, pracovat s real-time i batch zpracováním a škálovat podle potřeby.

Klíčové charakteristiky kvalitní datové pipeline zahrnují: spolehlivost (fault tolerance a error handling), škálovatelnost (schopnost zpracovat rostoucí objemy dat), udržovatelnost (dobře zdokumentovaný a testovatelný kód) a observabilitu (monitoring a logging).

**ETL vs ELT architektury**

Tradiční ETL (Extract, Transform, Load) přístup extrahuje data ze zdrojů, transformuje je před načtením do datového skladu. Tento přístup je vhodný pro strukturovaná data a předem definované transformace. Moderní ELT (Extract, Load, Transform) přístup načítá surová data do datového skladu a transformace provádí až v cílovém systému, což umožňuje větší flexibilitu.

ELT přístup je výhodný pro cloudové datové sklady (Snowflake, BigQuery, Redshift), které mají výpočetní kapacitu pro transformace. Umožňuje také rychlejší vývoj, protože transformace mohou být upravovány bez nutnosti změny celé pipeline.

**Nástroje pro orchestrace datových pipeline**

Apache Airflow je open-source platforma pro programmatickou autorizaci, plánování a monitorování workflow. Umožňuje definovat komplexní datové pipeline jako Python kód, což poskytuje version control a testovatelnost. Airflow poskytuje bohatou sadu operátorů pro různé úlohy a integraci s mnoha datovými systémy.

Alternativní nástroje zahrnují Prefect (modernější alternativa k Airflow s lepším developer experience), Dagster (data-aware orchestration), nebo cloud-native řešení jako AWS Step Functions, Azure Data Factory nebo Google Cloud Composer.

**Best practices pro datové pipeline**

Idempotence je kritická vlastnost datových pipeline - spuštění pipeline vícekrát by mělo produkovat stejný výsledek. Toho lze dosáhnout pomocí upsert operací nebo deduplikace na základě timestamp nebo unique identifiers.

Incremental processing místo full refresh šetří výpočetní zdroje a čas. Change data capture (CDC) techniky umožňují identifikovat pouze změněná data a zpracovat pouze ta. Partitioning dat podle času nebo jiných kritérií umožňuje efektivní zpracování pouze relevantních částí dat.

Data quality checks by měly být integrovány do pipeline. Validace schématu, kontrola completeness, accuracy a consistency dat pomáhají identifikovat problémy brzy v procesu. Nástroje jako Great Expectations nebo Soda Data poskytují framework pro data quality testing.

**Monitoring a observabilita**

Efektivní monitoring datových pipeline vyžaduje sledování několika metrik: latency (jak dlouho trvá zpracování), throughput (kolik dat je zpracováno za jednotku času), error rate a data freshness. Alerting by měl být nastaven pro kritické chyby a anomálie v datech.

Logging by měl být strukturovaný a obsahovat dostatek kontextu pro debugging. Distributed tracing pomáhá identifikovat bottlenecky v komplexních pipeline. Data lineage tracking umožňuje sledovat původ dat a dopad změn.',
'Článek poskytuje komplexní přehled datových pipeline a ETL/ELT procesů. Diskutuje rozdíly mezi ETL a ELT architekturami, nástroje pro orchestrace (Apache Airflow, Prefect), best practices včetně idempotence, incremental processing a data quality checks, a monitoring datových pipeline.',
'data,analytics,etl,pipeline,airflow,data engineering',
'Data',
'Tereza Benešová',
NULL,
'uploads/images/datova_analytika.jpg',
@red2,
@wf_vrecenzi,
@iss2,
NOW()-INTERVAL 12 DAY,
NOW()-INTERVAL 9 DAY,
@red2,
@red2),

('AI governance',
'S rostoucím nasazením AI modelů v produkci roste potřeba jejich efektivního řízení a governance. AI governance zahrnuje procesy, politiky a nástroje pro zajištění, že AI systémy jsou spravovány zodpovědně, transparentně a v souladu s právními a etickými požadavky. Tento článek poskytuje průvodce governance a auditováním AI modelů v produkčním prostředí.

**Proč je AI governance důležitá**

AI modely mohou mít významný dopad na obchodní rozhodnutí, zákazníky a společnost jako celek. Bez správné governance mohou AI systémy produkovat biased výsledky, porušovat soukromí nebo vést k nedůvěře. Governance pomáhá zajistit, že AI systémy jsou spravedlivé, transparentní, zodpovědné a v souladu s regulacemi jako GDPR nebo AI Act.

**Verzování a model registry**

Model versioning je kritická součást AI governance. Každá verze modelu by měla být uložena v model registry spolu s metadaty: trénovací data, hyperparametry, metriky výkonu, a dependencies. To umožňuje reprodukovatelnost, rollback k předchozím verzím a audit trail.

MLflow, Weights & Biases nebo neptune.ai poskytují platformy pro model registry a experiment tracking. Model registry by měl také obsahovat informace o schválení modelu pro produkci, včetně kdo schválil, kdy a proč.

**Audit trail a compliance**

Kompletní audit trail je nezbytný pro compliance a debugging. Mělo by být možné sledovat: kdo trénoval model, jaká data byla použita, jaké transformace byly aplikovány, jaké metriky byly dosaženy, kdo schválil nasazení a jaké změny byly provedeny v produkci.

Pro regulované odvětví (finance, zdravotnictví) může být vyžadována dokumentace pro compliance. Model cards poskytují standardizovaný způsob dokumentace modelů, včetně jejich zamýšleného použití, omezení a známých biasů.

**Model monitoring a drift detection**

AI modely mohou degradovat v čase kvůli concept drift (změna v distribuci dat) nebo data drift (změna v distribuci vstupních dat). Kontinuální monitoring modelů v produkci je kritický pro identifikaci těchto problémů.

Monitoring by měl zahrnovat: prediction distribution, feature distribution, model performance metrics a business metrics. Alerting by měl být nastaven pro detekci významných změn. Nástroje jako Evidently AI, Fiddler nebo Arize AI poskytují platformy pro model monitoring.

**Ethics a bias detection**

AI systémy mohou reprodukovat nebo zesilovat bias přítomný v trénovacích datech. Bias detection a mitigation jsou důležité součásti AI governance. Fairness metriky (demographic parity, equalized odds) pomáhají identifikovat potenciální bias.

Explainability a interpretability jsou důležité pro pochopení rozhodnutí modelů. SHAP values, LIME nebo attention maps pomáhají vysvětlit predikce modelů. Pro kritické aplikace může být vyžadována plná interpretabilita modelů.

**Best practices**

Doporučené postupy zahrnují: definici AI governance frameworku a rolí v organizaci, implementaci model registry a versioning, kontinuální monitoring modelů v produkci, pravidelné auditování modelů a jejich dopadu, dokumentaci modelů a jejich rozhodnutí, a vzdělávání týmů o AI ethics a governance.',
'Článek poskytuje komplexní průvodce AI governance v produkčním prostředí. Diskutuje důležitost governance, verzování modelů, audit trail, compliance, model monitoring, drift detection, ethics a bias detection. Obsahuje praktické doporučení pro implementaci AI governance frameworku.',
'ai,governance,policy,mlops,model registry,compliance',
'IT',
'Lenka Hájková',
NULL,
'uploads/images/ai_governance.jpg',
@aut1,
@wf_schvalen,
@iss1,
NOW()-INTERVAL 30 DAY,
NOW()-INTERVAL 5 DAY,
@aut1,
@aut1),

('AI v malých firmách',
'Malé a střední podniky (SMB) často čelí výzvě, jak efektivně zavést AI technologie bez nutnosti velkých investic do infrastruktury a expertízy. Naštěstí existuje řada dostupných AI nástrojů a služeb, které jsou cenově přijatelné a nevyžadují rozsáhlé technické znalosti. Tento článek poskytuje praktický průvodce adopcí AI v malých firmách.

**Začínáme s AI - kde začít**

Prvním krokem je identifikace konkrétních problémů nebo příležitostí, kde může AI přinést hodnotu. Časté use cases pro SMB zahrnují: automatizaci zákaznického servisu (chatboty), analýzu zákaznických dat, automatizaci marketingových kampaní, optimalizaci inventáře nebo detekci podvodů.

Je důležité začít s malými, řízenými projekty místo komplexních transformací. Pilotní projekty umožňují otestovat hodnotu AI s minimálním rizikem a investicí. Měření ROI pilotního projektu pomáhá rozhodnout o dalším rozšíření.

**Dostupné AI nástroje pro SMB**

Cloud-based AI služby poskytují snadný způsob, jak začít s AI bez nutnosti vlastní infrastruktury. OpenAI GPT API umožňuje integraci pokročilých language modelů do aplikací. Google Cloud AI nebo AWS AI services poskytují pre-trained modely pro různé use cases (vision, language, translation).

No-code/low-code platformy jako Zapier, Make (Integromat) nebo Bubble umožňují vytvářet AI-powered automatizace bez programování. Chatbot builders jako Chatfuel, ManyChat nebo Dialogflow umožňují vytvářet chatboty pro zákaznický servis.

**Případové studie**

E-commerce firma může použít AI pro personalizaci produktových doporučení pomocí služeb jako Amazon Personalize nebo Recombee. To může zvýšit konverzní poměr a průměrnou hodnotu objednávky.

Malá marketingová agentura může použít AI nástroje pro generování obsahu (Jasper, Copy.ai), optimalizaci reklamních kampaní nebo analýzu sentimentu na sociálních sítích. To umožňuje efektivnější práci s menším týmem.

**Výzvy a jak je překonat**

Hlavní výzvy pro SMB zahrnují: nedostatek AI expertízy, omezený rozpočet a obavy z komplexnosti. Řešení zahrnují: využití cloud služeb místo vlastní infrastruktury, začátek s pre-trained modely místo vlastního trénování, a využití no-code nástrojů.

Důležité je také vzdělávání týmu. Online kurzy, webináře a dokumentace poskytovatelů služeb mohou pomoci týmu získat potřebné znalosti. Partnerství s AI konzultanty nebo freelancery může být efektivní pro komplexnější projekty.

**Best practices**

Doporučené postupy zahrnují: začátek s konkrétním problémem místo technologie, měření ROI každého AI projektu, začátek s cloud službami před vlastní infrastrukturou, zaměření na uživatelský experience a jednoduchost, a postupnou expanzi úspěšných pilotů.',
'Praktický průvodce adopcí AI v malých a středních firmách. Diskutuje dostupné AI nástroje a služby, případy použití, případové studie a strategie pro překonání výzev. Poskytuje konkrétní doporučení pro začátek s AI v SMB prostředí.',
'AI,SMB,automatizace,business,cloud AI,no-code',
'IT',
'Zuzana Moravcová',
NULL,
'uploads/images/ai_male_firmy.jpg',
@aut3,
@wf_schvalen,
@iss1,
NOW()-INTERVAL 20 DAY,
NOW()-INTERVAL 18 DAY,
@aut3,
@aut3),

('Data pipeline pro e-commerce',
'E-commerce platformy generují obrovské množství dat z různých zdrojů: transakce, zákaznické chování, inventář, marketingové kampaně a další. Efektivní zpracování těchto dat je kritické pro business intelligence, personalizaci a optimalizaci operací. Tento článek poskytuje průvodce stavbou škálovatelných datových pipeline pro e-commerce.

**Výzvy e-commerce dat**

E-commerce data mají několik charakteristik, které představují výzvy: vysoký objem (miliony transakcí denně), vysoká rychlost (real-time zpracování požadavků), různorodost zdrojů (web, mobilní aplikace, API, externí systémy) a sezónní variace (špičky během svátků).

Data přicházejí z různých zdrojů: web analytics (Google Analytics, Adobe Analytics), e-commerce platformy (Shopify, WooCommerce, Magento), CRM systémy, marketingové platformy, a externí dodavatelé. Konsolidace těchto dat vyžaduje robustní integrační strategii.

**Architektura datové pipeline**

Moderní e-commerce datové pipeline typicky kombinují batch a stream processing. Real-time pipeline zpracovávají kritická data jako inventory updates, order processing nebo personalizace v reálném čase. Batch pipeline zpracovávají historická data pro reporting a analýzy.

Lambda nebo Kappa architektura poskytuje framework pro kombinaci batch a stream processingu. Event-driven architektura umožňuje reagovat na události v reálném čase (např. když zákazník přidá produkt do košíku).

**Real-time zpracování**

Pro real-time use cases jako personalizace nebo inventory management jsou vhodné stream processing nástroje jako Apache Kafka s Kafka Streams, Apache Flink nebo cloud služby jako AWS Kinesis nebo Google Cloud Pub/Sub. Tyto nástroje umožňují zpracování datových toků s nízkou latencí.

Real-time pipeline mohou aktualizovat customer profiles, inventory levels nebo recommendation engines v reálném čase. To umožňuje personalizované zkušenosti a přesné inventory management.

**Batch processing pro analýzy**

Batch pipeline jsou vhodné pro historické analýzy, reporting a machine learning. ETL procesy mohou běžet denně nebo týdně a načítat data do data warehouse pro analýzy. Cloud data warehouses jako Snowflake, BigQuery nebo Redshift poskytují škálovatelné úložiště pro analytická data.

Data marts mohou být vytvořeny pro specifické business jednotky (marketing, sales, operations), což umožňuje rychlejší přístup k relevantním datům.

**Best practices**

Doporučené postupy zahrnují: implementaci data quality checks na začátku pipeline, použití idempotentních operací pro spolehlivost, monitoring pipeline performance a data freshness, implementaci error handling a retry logiky, a dokumentaci data lineage pro compliance a debugging.',
'Průvodce stavbou datových pipeline pro e-commerce platformy. Diskutuje výzvy e-commerce dat, architekturu pipeline kombinující batch a stream processing, real-time zpracování pro personalizaci a batch processing pro analýzy. Obsahuje praktické doporučení pro implementaci.',
'data,pipeline,e-commerce,etl,real-time processing,data warehouse',
'Data',
'Jan Kubíček',
NULL,
'uploads/images/data_pipeline_ecommerce.jpg',
@aut4,
@wf_vrecenzi,
@iss2,
NOW()-INTERVAL 15 DAY,
NOW()-INTERVAL 12 DAY,
@aut4,
@aut4),

('DevOps bezpečnost v cloudu',
'DevSecOps integruje bezpečnostní praktiky do DevOps procesů od samého začátku vývojového cyklu. V cloudovém prostředí, kde infrastruktura je definována jako kód a aplikace jsou nasazovány kontinuálně, je bezpečnost kritická pro každou fázi procesu. Tento článek poskytuje best practices pro zabezpečení CI/CD pipeline a DevSecOps v cloudu.

**Zabezpečení CI/CD pipeline**

CI/CD pipeline jsou kritickým bodem pro bezpečnost - kompromitovaná pipeline může vést k nasazení škodlivého kódu do produkce. Pipeline by měly běžet v izolovaném prostředí s minimálními oprávněními. Secrets management je kritický - tajné klíče by nikdy neměly být hardcodované v kódu nebo committované do version control.

Infrastructure as Code (IaC) nástroje jako Terraform nebo CloudFormation umožňují definovat infrastrukturu jako kód, což umožňuje security reviews a version control. IaC by měl být validován pomocí security scanning nástrojů před nasazením.

**Správa tajných klíčů**

Tajné klíče (API keys, passwords, certificates) by měly být spravovány pomocí dedicated secrets management služeb jako AWS Secrets Manager, Azure Key Vault nebo HashiCorp Vault. Tyto služby poskytují šifrování, rotaci klíčů a audit logging.

CI/CD systémy by měly používat secrets injection místo hardcoding. GitHub Secrets, GitLab CI/CD variables nebo CI/CD specific secrets management umožňují bezpečné předávání tajných klíčů do pipeline bez jejich vystavení v logách.

**Container security**

Containery jsou základní stavební blok moderních cloudových aplikací. Container images by měly být skenovány pro známé zranitelnosti pomocí nástrojů jako Trivy, Snyk nebo Clair. Base images by měly být pravidelně aktualizovány a měly by pocházet z důvěryhodných zdrojů.

Multi-stage builds v Docker umožňují vytvářet menší images s pouze nezbytnými závislostmi. Non-root users v containers zvyšují bezpečnost. Container runtime security nástroje jako Falco monitorují runtime chování a detekují anomálie.

**Infrastructure security**

Cloud infrastructure by měla být konfigurována podle security best practices: network segmentation pomocí VPCs a security groups, šifrování dat v klidu i při přenosu, a pravidelné security audits. Cloud security posture management (CSPM) nástroje identifikují miskonfigurace.

Identity and Access Management (IAM) by mělo následovat princip least privilege - uživatelé a služby by měly mít pouze minimální potřebná oprávnění. Role-based access control (RBAC) a pravidelné access reviews jsou důležité.

**Security scanning v pipeline**

Security scanning by měl být integrován do CI/CD pipeline. Static Application Security Testing (SAST) skenuje zdrojový kód pro zranitelnosti. Software Composition Analysis (SCA) identifikuje zranitelnosti v závislostech. Dynamic Application Security Testing (DAST) testuje běžící aplikace.

Security scanning by měl být automatizován a měl by blokovat deployment při detekci kritických zranitelností. Security gates v pipeline zajišťují, že pouze bezpečný kód může být nasazen do produkce.

**Compliance a audit**

Pro regulované odvětví může být vyžadována compliance s různými standardy (SOC 2, ISO 27001, PCI DSS). Compliance by měla být automatizována pomocí policy as code nástrojů jako Open Policy Agent (OPA) nebo AWS Config Rules.

Audit logging by měl zachytávat všechny změny v infrastruktuře a aplikacích. CloudTrail, CloudWatch Logs nebo podobné služby poskytují audit trail pro compliance a forensics.',
'Best practices pro zabezpečení DevOps procesů v cloudovém prostředí. Diskutuje zabezpečení CI/CD pipeline, správu tajných klíčů, container security, infrastructure security, security scanning v pipeline a compliance. Poskytuje praktické doporučení pro implementaci DevSecOps.',
'devops,security,cloud,cicd,devsecops,container security',
'Security',
'Radek Štěpánek',
NULL,
'uploads/images/devops_bezpecnost.jpg',
@red3,
@wf_vrecenzi,
@iss2,
NOW()-INTERVAL 10 DAY,
NOW()-INTERVAL 8 DAY,
@red3,
@red3),

('Governance AI modelů',
'Governance ML modelů je kritická pro jejich úspěšné nasazení v produkci a zajištění, že modely jsou spravovány zodpovědně a v souladu s obchodními a regulačními požadavky. Tento článek poskytuje průvodce governance ML modelů včetně verzování, auditu a compliance.

**Model lifecycle management**

ML model lifecycle zahrnuje fáze od vývoje přes trénování, validaci, nasazení, monitoring až po decommissioning. Každá fáze vyžaduje specifické governance procesy. Model registry centralizuje informace o všech modelech v organizaci a jejich verzích.

MLOps platformy jako MLflow, Kubeflow nebo neptune.ai poskytují nástroje pro správu celého lifecycle. Tyto platformy umožňují tracking experimentů, versioning modelů a artefaktů, a automatizaci deployment procesů.

**Verzování a reprodukovatelnost**

Reprodukovatelnost je kritická pro ML modely. Každá verze modelu by měla být spojena s: přesnou verzí trénovacích dat, hyperparametry, kódem pro trénování, a prostředím (dependencies, hardware). To umožňuje reprodukovat výsledky a debugovat problémy.

Data versioning pomocí nástrojů jako DVC (Data Version Control) nebo Pachyderm umožňuje verzovat trénovací data spolu s modely. Experiment tracking nástroje zaznamenávají všechny parametry a metriky pro každý experiment.

**Model validation a testing**

Před nasazením do produkce by modely měly projít rigorous validation procesem. To zahrnuje: holdout test set evaluation, cross-validation, A/B testing, a business metrics validation. Model by měl být testován na reprezentativních datech podobných produkčním.

Bias a fairness testing by měly být součástí validation procesu. Modely by měly být testovány na různých demografických skupinách, aby se zajistilo, že neprodukují diskriminační výsledky.

**Production monitoring**

Monitoring modelů v produkci je kritický pro identifikaci degradace výkonu. Concept drift (změna v distribuci dat) nebo data drift (změna v distribuci vstupů) mohou vést k degradaci modelu. Kontinuální monitoring predikcí, vstupních dat a výkonnostních metrik pomáhá identifikovat tyto problémy.

Model performance monitoring by měl zahrnovat: prediction distribution, accuracy metrics, latency, a business KPIs. Alerting by měl být nastaven pro detekci významných změn. Retraining triggers mohou automaticky spustit retraining při detekci degradace.

**Compliance a audit**

Pro regulované odvětví může být vyžadována dokumentace modelů pro compliance. Model cards poskytují standardizovanou dokumentaci modelů včetně jejich zamýšleného použití, omezení, známých biasů a výkonnostních metrik.

Audit trail by měl zachytávat: kdo trénoval model, jaká data byla použita, jaké transformace byly aplikovány, kdo schválil nasazení, a jaké změny byly provedeny v produkci. Tato dokumentace je důležitá pro compliance a debugging.',
'Průvodce governance ML modelů v produkčním prostředí. Diskutuje model lifecycle management, verzování a reprodukovatelnost, model validation a testing, production monitoring, a compliance. Poskytuje praktické doporučení pro implementaci governance frameworku.',
'ai,governance,mlops,models,model registry,compliance',
'AI',
'Zuzana Moravcová',
NULL,
'uploads/images/governance_ai_modelu.jpg',
@aut3,
@wf_odeslany,
@iss3,
NOW()-INTERVAL 7 DAY,
NOW()-INTERVAL 5 DAY,
@aut3,
@aut3),

('Observabilita datových toků',
'Observabilita je klíčová pro spolehlivost a výkon datových systémů. Zatímco monitoring poskytuje metriky o stavu systému, observabilita umožňuje pochopit, proč systém se chová určitým způsobem. Tento článek poskytuje průvodce observabilitou datových toků včetně metrik kvality dat a SLA.

**Tři pilíře observability**

Observabilita je založena na třech pilířích: metriky (metrics), logy (logs) a stopy (traces). Metriky poskytují numerická data o výkonu systému (latency, throughput, error rate). Logy zachytávají události a chyby v systému. Distributed tracing umožňuje sledovat requesty napříč různými službami a identifikovat bottlenecky.

Pro datové pipeline jsou důležité metriky jako: data freshness (jak aktuální jsou data), data completeness (kolik dat chybí), pipeline latency (jak dlouho trvá zpracování), a error rate. Tyto metriky pomáhají identifikovat problémy brzy.

**Data quality monitoring**

Kvalita dat je kritická pro spolehlivost datových systémů. Data quality monitoring by měl zahrnovat: schema validation (kontrola struktury dat), completeness checks (kontrola chybějících hodnot), accuracy checks (validace hodnot proti business rules), a consistency checks (kontrola konzistence napříč systémy).

Nástroje jako Great Expectations, Soda Data nebo Deequ poskytují framework pro data quality testing. Data quality checks by měly být integrovány do datových pipeline a měly by blokovat processing při detekci kritických problémů.

**SLA a SLO pro data**

Service Level Objectives (SLO) pro datové pipeline definují cíle pro dostupnost, latenci a kvalitu dat. Service Level Agreements (SLA) jsou smluvní závazky založené na SLO. Typické SLO pro datové pipeline zahrnují: data freshness (data by měla být dostupná do X minut po generování), pipeline uptime (pipeline by měla být dostupná Y% času), a data quality (Z% dat by mělo projít quality checks).

Error budgets umožňují trade-off mezi dostupností a rychlostí vývoje. Pokud je error budget vyčerpán, musí být priorita na stabilitu místo nových funkcí.

**Distributed tracing pro datové pipeline**

Distributed tracing umožňuje sledovat datové toky napříč různými systémy a službami. Trace poskytuje kompletní pohled na cestu dat od zdroje přes transformace až do cíle. To pomáhá identifikovat bottlenecky, chyby a performance problémy.

OpenTelemetry poskytuje standardizovaný framework pro instrumentaci aplikací a generování traces. Tracing by měl být implementován na všech kritických místech v pipeline: data ingestion, transformace, a data loading.

**Alerting a incident response**

Efektivní alerting je kritický pro rychlou reakci na problémy. Alerty by měly být nastaveny pro: kritické chyby v pipeline, degradaci data quality, porušení SLA, a anomálie v datech. Alert fatigue by měl být minimalizován pomocí inteligentního thresholding a alert grouping.

Incident response plán by měl definovat: kdo je zodpovědný za různé typy incidentů, jaké jsou escalation procedury, a jaké jsou recovery procedury. Runbooks dokumentují standardní procedury pro řešení běžných problémů.

**Best practices**

Doporučené postupy zahrnují: implementaci comprehensive logging na všech úrovních pipeline, použití structured logging pro snadnější analýzu, implementaci distributed tracing pro komplexní pipeline, pravidelné review metrik a alertů, a dokumentaci SLO a SLA pro všechny kritické datové toky.',
'Průvodce observabilitou datových toků včetně metrik kvality dat a SLA. Diskutuje tři pilíře observability (metriky, logy, stopy), data quality monitoring, SLA a SLO pro data, distributed tracing a alerting. Poskytuje praktické doporučení pro implementaci.',
'observability,data,SLA,monitoring,data quality,distributed tracing',
'Data',
'Jan Kubíček',
NULL,
'uploads/images/observabilita_dat.jpg',
@aut4,
@wf_novy,
NULL,
NOW()-INTERVAL 4 DAY,
NOW()-INTERVAL 2 DAY,
@aut4,
@aut4),

('Microservices architektura',
'Microservices architektura se stává standardem pro moderní aplikace. Tento článek poskytuje komplexní přehled microservices architektury, jejích výhod a výzev, a praktické doporučení pro implementaci.',
'Průvodce microservices architekturou včetně výhod, výzev a best practices pro implementaci.',
'microservices,architecture,cloud,devops',
'IT',
'Filip Urban',
NULL,
'uploads/images/microservices.jpg',
@aut2,
@wf_schvalen,
@iss1,
NOW()-INTERVAL 25 DAY,
NOW()-INTERVAL 20 DAY,
@aut2,
@aut2),

('Kubernetes pro začátečníky',
'Kubernetes je de facto standard pro orchestrace containerů. Tento článek poskytuje úvod do Kubernetes pro začátečníky, základní koncepty a praktické příklady nasazení aplikací.',
'Úvod do Kubernetes pro začátečníky. Základní koncepty, architektura a praktické příklady nasazení aplikací.',
'kubernetes,containers,devops,orchestration',
'Cloud',
'Radek Štěpánek',
NULL,
'uploads/images/kubernetes.jpg',
@red3,
@wf_vrecenzi,
@iss2,
NOW()-INTERVAL 11 DAY,
NOW()-INTERVAL 9 DAY,
@red3,
@red3),

('Machine Learning v produkci',
'Nasazení ML modelů do produkce představuje jedinečné výzvy. Tento článek diskutuje MLOps praktiky, monitoring modelů, A/B testing a best practices pro produkční ML systémy.',
'MLOps praktiky pro nasazení ML modelů do produkce. Monitoring, A/B testing a best practices.',
'machine learning,mlops,production,monitoring',
'AI',
'Lenka Hájková',
NULL,
'uploads/images/ml_production.jpg',
@aut1,
@wf_schvalen,
@iss1,
NOW()-INTERVAL 22 DAY,
NOW()-INTERVAL 15 DAY,
@aut1,
@aut1),

('Blockchain a decentralizované aplikace',
'Blockchain technologie transformují způsob, jakým přemýšlíme o decentralizaci a důvěře v digitálním světě. Tento článek poskytuje přehled blockchain technologií a jejich praktických aplikací.',
'Přehled blockchain technologií a jejich praktických aplikací v různých odvětvích.',
'blockchain,decentralization,cryptocurrency,dapps',
'IT',
'Zuzana Moravcová',
NULL,
'uploads/images/blockchain.jpg',
@aut3,
@wf_odeslany,
NULL,
NOW()-INTERVAL 6 DAY,
NOW()-INTERVAL 4 DAY,
@aut3,
@aut3),

('Edge computing a IoT',
'Edge computing přináší výpočetní sílu blíže k datovým zdrojům, což je kritické pro IoT aplikace. Tento článek diskutuje edge computing architektury, výhody a use cases.',
'Edge computing architektury pro IoT aplikace. Výhody, výzvy a praktické use cases.',
'edge computing,iot,cloud,real-time',
'Cloud',
'Jan Kubíček',
NULL,
'uploads/images/edge_computing.jpg',
@aut4,
@wf_novy,
NULL,
NOW()-INTERVAL 3 DAY,
NOW()-INTERVAL 1 DAY,
@aut4,
@aut4),

('API design best practices',
'Dobře navržené API jsou klíčová pro úspěch moderních aplikací. Tento článek poskytuje best practices pro design RESTful API, včetně versioning, dokumentace a error handling.',
'Best practices pro design RESTful API. Versioning, dokumentace, error handling a security.',
'API,rest,design,development',
'IT',
'Michal Král',
NULL,
'uploads/images/api_design.jpg',
@red1,
@wf_vrecenzi,
@iss2,
NOW()-INTERVAL 13 DAY,
NOW()-INTERVAL 10 DAY,
@red1,
@red1),

('Databázové optimalizace',
'Optimalizace databázových dotazů je kritická pro výkon aplikací. Tento článek diskutuje indexování, query optimization, a best practices pro různé typy databází.',
'Optimalizace databázových dotazů a výkonu. Indexování, query optimization a best practices.',
'database,optimization,performance,sql',
'Data',
'Tereza Benešová',
NULL,
'uploads/images/database_optimization.jpg',
@red2,
@wf_schvalen,
@iss1,
NOW()-INTERVAL 16 DAY,
NOW()-INTERVAL 12 DAY,
@red2,
@red2);

-- --------------------------------------------------------
-- Lookup proměnné pro články
-- --------------------------------------------------------

SET @p_ai      := (SELECT id FROM posts WHERE title='AI v redakci' LIMIT 1);
SET @p_sec     := (SELECT id FROM posts WHERE title='Kyberbezpečnost 2025' LIMIT 1);
SET @p_cloud   := (SELECT id FROM posts WHERE title='Cloud a náklady' LIMIT 1);
SET @p_data    := (SELECT id FROM posts WHERE title='Datová analytika' LIMIT 1);
SET @p_gov     := (SELECT id FROM posts WHERE title='AI governance' LIMIT 1);
SET @p_smb     := (SELECT id FROM posts WHERE title='AI v malých firmách' LIMIT 1);
SET @p_pipe    := (SELECT id FROM posts WHERE title='Data pipeline pro e-commerce' LIMIT 1);
SET @p_dev     := (SELECT id FROM posts WHERE title='DevOps bezpečnost v cloudu' LIMIT 1);
SET @p_gov2    := (SELECT id FROM posts WHERE title='Governance AI modelů' LIMIT 1);
SET @p_obsv    := (SELECT id FROM posts WHERE title='Observabilita datových toků' LIMIT 1);
SET @p_micro   := (SELECT id FROM posts WHERE title='Microservices architektura' LIMIT 1);
SET @p_k8s     := (SELECT id FROM posts WHERE title='Kubernetes pro začátečníky' LIMIT 1);
SET @p_mlprod  := (SELECT id FROM posts WHERE title='Machine Learning v produkci' LIMIT 1);
SET @p_block   := (SELECT id FROM posts WHERE title='Blockchain a decentralizované aplikace' LIMIT 1);
SET @p_edge    := (SELECT id FROM posts WHERE title='Edge computing a IoT' LIMIT 1);
SET @p_api     := (SELECT id FROM posts WHERE title='API design best practices' LIMIT 1);
SET @p_dbopt   := (SELECT id FROM posts WHERE title='Databázové optimalizace' LIMIT 1);

-- --------------------------------------------------------
-- Přiřazení recenzentů
-- --------------------------------------------------------

INSERT INTO `post_assignments` (`post_id`, `reviewer_id`, `assigned_by`, `assigned_at`, `due_date`, `status`) VALUES
(@p_sec,  @rec1, @admin, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 2 DAY, 'Přiděleno'),
(@p_sec,  @rec2, @admin, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 1 DAY, 'Přiděleno'),
(@p_data, @rec1, @sef,   NOW()-INTERVAL 10 DAY, NOW()+INTERVAL 3 DAY, 'Přiděleno'),
(@p_cloud,@rec2, @admin2, NOW()-INTERVAL 9 DAY,  NOW()+INTERVAL 5 DAY, 'Přiděleno'),
(@p_pipe, @rec1, @red3, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 2 DAY, 'Přiděleno'),
(@p_pipe, @rec2, @red3, NOW()-INTERVAL 14 DAY, NOW()-INTERVAL 1 DAY, 'Přiděleno'),
(@p_dev,  @rec2, @red3, NOW()-INTERVAL 9 DAY,  NOW()+INTERVAL 2 DAY, 'Přiděleno'),
(@p_dev,  @rec3, @red3, NOW()-INTERVAL 9 DAY,  NOW()+INTERVAL 4 DAY, 'Přiděleno'),
(@p_k8s,  @rec2, @red3, NOW()-INTERVAL 10 DAY, NOW()+INTERVAL 1 DAY, 'Přiděleno'),
(@p_k8s,  @rec3, @red3, NOW()-INTERVAL 10 DAY, NOW()+INTERVAL 3 DAY, 'Přiděleno'),
(@p_api,  @rec1, @red1, NOW()-INTERVAL 12 DAY, NOW()+INTERVAL 2 DAY, 'Přiděleno'),
(@p_api,  @rec2, @red1, NOW()-INTERVAL 12 DAY, NOW()+INTERVAL 1 DAY, 'Přiděleno');

-- --------------------------------------------------------
-- Oficiální recenze
-- --------------------------------------------------------

INSERT INTO `post_reviews` (`post_id`, `reviewer_id`, `score_actuality`, `score_originality`, `score_language`, `score_expertise`, `comment`, `created_at`, `updated_at`) VALUES
(@p_sec, @rec1, 4,4,4,4, 'Dobře napsané, doporučuji doladit příklady. Text je srozumitelný a strukturovaný, ale chybí více konkrétních případových studií z praxe.', NOW()-INTERVAL 7 DAY, NOW()-INTERVAL 6 DAY),
(@p_sec, @rec2, 5,4,4,5, 'Výborná originalita, text je srozumitelný. Autor dobře vysvětlil komplexní téma způsobem přístupným i pro méně zkušené čtenáře. Doporučuji přidat sekci o konkrétních nástrojích.', NOW()-INTERVAL 6 DAY, NOW()-INTERVAL 4 DAY),
(@p_data,@rec1, 3,3,4,3, 'Potřeba víc detailů k ETL. Článek je obecný, ale chybí konkrétní příklady ETL kódu a best practices pro konkrétní nástroje.', NOW()-INTERVAL 5 DAY, NULL),
(@p_cloud,@rec2, 4,3,4,4, 'Srozumitelné, přidat cost model příklady. Článek dobře vysvětluje FinOps koncepty, ale praktické příklady cost modelů by byly užitečné.', NOW()-INTERVAL 4 DAY, NULL),
(@p_pipe, @rec1, 4,4,4,4, 'Dobře strukturované, doplnit SLA příklady. Článek pokrývá důležité aspekty datových pipeline, ale příklady SLA metrik by byly přínosné.', NOW()-INTERVAL 7 DAY, NOW()-INTERVAL 6 DAY),
(@p_pipe, @rec2, 5,4,5,4, 'Skvělý jazyk, doporučuji přidat ukázkový ETL kód. Autor píše velmi srozumitelně, ale praktické příklady kódu by článek ještě vylepšily.', NOW()-INTERVAL 6 DAY, NOW()-INTERVAL 4 DAY),
(@p_dev,  @rec2, 4,3,4,4, 'Přehledné, přidat sekci o tajných klíčích. Článek dobře pokrývá DevSecOps, ale sekce o správě secrets by byla užitečná.', NOW()-INTERVAL 5 DAY, NULL),
(@p_dev,  @rec3, 3,3,3,3, 'Potřeba víc praktických příkladů. Článek je obecný, konkrétní příklady konfigurací a nástrojů by byly přínosné.', NOW()-INTERVAL 4 DAY, NULL),
(@p_k8s,  @rec2, 4,4,5,4, 'Výborný úvod do Kubernetes. Článek je velmi srozumitelný a dobře strukturovaný. Doporučuji přidat více praktických příkladů YAML konfigurací.', NOW()-INTERVAL 8 DAY, NULL),
(@p_k8s,  @rec3, 5,4,4,5, 'Skvělý článek pro začátečníky. Autor dokázal vysvětlit komplexní téma přístupným způsobem. Líbí se mi praktické příklady.', NOW()-INTERVAL 7 DAY, NULL),
(@p_api,  @rec1, 4,3,4,4, 'Dobrý přehled API design best practices. Chybí více příkladů konkrétních API a jejich dokumentace.', NOW()-INTERVAL 9 DAY, NULL),
(@p_api,  @rec2, 4,4,4,4, 'Přehledné a praktické. Článek pokrývá důležité aspekty API designu. Doporučuji přidat sekci o GraphQL jako alternativu k REST.', NOW()-INTERVAL 8 DAY, NULL);

-- --------------------------------------------------------
-- Uživatelské recenze
-- --------------------------------------------------------

INSERT INTO `user_reviews` (`post_id`, `user_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(@p_sec,  @cte1, 5, 'Skvělý článek, srozumitelný i pro neodborníky. Velmi užitečné informace o aktuálních trendech v bezpečnosti.', NOW()-INTERVAL 3 DAY, NULL),
(@p_sec,  @cte2, 4, 'Chybí mi více příkladů z praxe. Jinak velmi dobrý přehled tématu.', NOW()-INTERVAL 2 DAY, NULL),
(@p_gov,  @cte1, 4, 'Užitečný přehled governance. Pomohl mi lépe pochopit, jak řídit AI projekty.', NOW()-INTERVAL 1 DAY, NULL),
(@p_ai,   @cte2, 3, 'Zajímavé, ale chtělo by to víc ukázek. Obecné informace jsou dobré, ale praktické příklady chybí.', NOW()-INTERVAL 1 DAY, NULL),
(@p_cloud,@cte1, 4, 'Praktické, líbily se mi tipy na optimalizaci. Pomohlo mi to snížit cloudové náklady.', NOW()-INTERVAL 1 DAY, NULL),
(@p_smb,  @cte1, 5, 'Velmi praktické pro malé firmy, doporučuji. Přesně to, co jsem hledal pro naši firmu.', NOW()-INTERVAL 3 DAY, NULL),
(@p_smb,  @cte2, 4, 'Chybí krátký checklist, jinak super. Užitečné informace, ale praktický checklist by byl skvělý.', NOW()-INTERVAL 2 DAY, NULL),
(@p_pipe, @cte1, 4, 'Hodně informací, možná stručnější závěr. Článek je velmi obsáhlý, ale závěr by mohl být stručnější.', NOW()-INTERVAL 1 DAY, NULL),
(@p_dev,  @cte2, 3, 'Fajn přehled, přidat část o nástrojích. Obecné informace jsou dobré, ale konkrétní nástroje chybí.', NOW()-INTERVAL 1 DAY, NULL),
(@p_micro,@cte1, 5, 'Výborný článek o microservices. Pomohl mi lépe pochopit architekturu a výhody tohoto přístupu.', NOW()-INTERVAL 2 DAY, NULL),
(@p_k8s,  @cte1, 4, 'Dobrý úvod do Kubernetes. Jako začátečník jsem našel spoustu užitečných informací.', NOW()-INTERVAL 1 DAY, NULL),
(@p_mlprod,@cte2, 5, 'Skvělý článek o MLOps. Praktické tipy pro nasazení ML modelů do produkce.', NOW()-INTERVAL 1 DAY, NULL),
(@p_dbopt,@cte1, 4, 'Užitečné tipy na optimalizaci databází. Pomohlo mi to zlepšit výkon naší aplikace.', NOW()-INTERVAL 1 DAY, NULL);

-- --------------------------------------------------------
-- Komentáře pod články
-- --------------------------------------------------------

INSERT INTO `comments` (`post_id`, `author_id`, `content`, `visibility`, `created_at`) VALUES
(@p_sec,  @cte1, 'Paráda, díky za sdílení. Velmi užitečné informace!', 'public', NOW()-INTERVAL 2 DAY),
(@p_sec,  @cte2, 'Souhlasím s recenzentem, více příkladů by bylo skvělé.', 'public', NOW()-INTERVAL 1 DAY),
(@p_ai,   @aut2, 'Taky řešíme AI v týmu, díky za inspiraci. Zkusíme některé z těchto nástrojů.', 'public', NOW()-INTERVAL 1 DAY),
(@p_gov,  @aut1, 'Governance je klíčová, dobrý přehled. Děkuji za článek.', 'public', NOW()-INTERVAL 1 DAY),
(@p_smb,  @cte1, 'Díky za konkrétní tipy na nástroje. Některé už testujeme.', 'public', NOW()-INTERVAL 2 DAY),
(@p_smb,  @cte2, 'Souhlasím, checklist by se hodil. Možná by autor mohl přidat.', 'public', NOW()-INTERVAL 1 DAY),
(@p_pipe, @aut3, 'Přidám přílohu s ukázkovým ETL. Děkuji za zpětnou vazbu.', 'public', NOW()-INTERVAL 1 DAY),
(@p_dev,  @rec2, 'Doplním sekci o správě secrets. Děkuji za návrh.', 'public', NOW()-INTERVAL 1 DAY),
(@p_micro,@cte1, 'Skvělý článek! Pomohl mi lépe pochopit výhody microservices architektury.', 'public', NOW()-INTERVAL 1 DAY),
(@p_k8s,  @aut2, 'Díky za praktické příklady. Kubernetes je opravdu komplexní, ale tento článek to dobře vysvětluje.', 'public', NOW()-INTERVAL 1 DAY),
(@p_mlprod,@cte2, 'MLOps je budoucnost. Děkuji za přehled nástrojů a best practices.', 'public', NOW()-INTERVAL 1 DAY),
(@p_dbopt,@aut4, 'Optimalizace databází je často přehlížená. Skvělé tipy!', 'public', NOW()-INTERVAL 1 DAY);

-- --------------------------------------------------------
-- Publikace schválených článků
-- --------------------------------------------------------

UPDATE `posts` SET `published_at` = COALESCE(`published_at`, NOW()-INTERVAL 4 DAY) WHERE `id` = @p_gov AND `state` = @wf_schvalen;
UPDATE `posts` SET `published_at` = COALESCE(`published_at`, NOW()-INTERVAL 6 DAY) WHERE `id` = @p_cloud AND `state` = @wf_schvalen;
UPDATE `posts` SET `published_at` = COALESCE(`published_at`, NOW()-INTERVAL 8 DAY) WHERE `id` = @p_smb AND `state` = @wf_schvalen;
UPDATE `posts` SET `published_at` = COALESCE(`published_at`, NOW()-INTERVAL 10 DAY) WHERE `id` = @p_micro AND `state` = @wf_schvalen;
UPDATE `posts` SET `published_at` = COALESCE(`published_at`, NOW()-INTERVAL 12 DAY) WHERE `id` = @p_mlprod AND `state` = @wf_schvalen;
UPDATE `posts` SET `published_at` = COALESCE(`published_at`, NOW()-INTERVAL 14 DAY) WHERE `id` = @p_dbopt AND `state` = @wf_schvalen;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
