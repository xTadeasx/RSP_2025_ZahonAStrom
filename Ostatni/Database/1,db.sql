-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pon 27. říj 2025, 18:40
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Vytvoření databáze
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `rsp` CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci;
USE `rsp`;

-- --------------------------------------------------------
-- Databáze: `rsp`
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Struktura tabulky `posts`
-- --------------------------------------------------------

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL COMMENT 'Content of the post',
  `user_id` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------
-- Struktura tabulky `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------
-- Vypisuji data pro tabulku `users`
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `role_id`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(2, 'jahoda', '$2y$10$fEofEot/Ql.I484Sz6GTt.BN2MHP6OugteXcLBGL5aHVPURe6RlNK', '', '', NULL, NULL, NULL, NULL, NULL),
(4, 'tadeas', '$2y$10$UUPMB2jRJtoXhH6DLgyNDuBMeL9kqT8IhhN/ck.aGUO04JtAqpU4u', 'jahoda.tadeas@gmail.com', '123123', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Struktura tabulky `users_roles`
-- --------------------------------------------------------

CREATE TABLE `users_roles` (
  `id` int(11) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------
-- Vypisuji data pro tabulku `users_roles`
-- --------------------------------------------------------

INSERT INTO `users_roles` (`id`, `role`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Administrátor', NULL, NULL, NULL, NULL),
(2, 'Šéfredaktor', NULL, NULL, NULL, NULL),
(3, 'Recenzent', NULL, NULL, NULL, NULL),
(4, 'Redaktor', NULL, NULL, NULL, NULL),
(5, 'Autor', NULL, NULL, NULL, NULL),
(6, 'Čtenář', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Struktura tabulky `workflow`
-- --------------------------------------------------------

CREATE TABLE `workflow` (
  `id` int(11) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------
-- Indexy pro exportované tabulky
-- --------------------------------------------------------

ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state` (`state`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

ALTER TABLE `users_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `created_by` (`created_by`);

ALTER TABLE `workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

-- --------------------------------------------------------
-- AUTO_INCREMENT pro tabulky
-- --------------------------------------------------------

ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `users_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `workflow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Omezení pro tabulky
-- --------------------------------------------------------

ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`state`) REFERENCES `workflow` (`id`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posts_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `users_roles` (`id`);

ALTER TABLE `users_roles`
  ADD CONSTRAINT `users_roles_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_roles_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `workflow`
  ADD CONSTRAINT `workflow_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `workflow_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
