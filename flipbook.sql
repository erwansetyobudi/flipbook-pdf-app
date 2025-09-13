-- Adminer 5.4.0 MySQL 8.0.30 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `doc_types`;
CREATE TABLE `doc_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `doc_types` (`id`, `name`, `created_at`) VALUES
(1,	'Laporan',	'2025-09-13 18:10:38'),
(2,	'Buku Panduan',	'2025-09-13 18:10:54');

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `description` text,
  `pages` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `doc_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `documents` (`id`, `title`, `slug`, `filename`, `cover`, `description`, `pages`, `created_at`, `type_id`) VALUES
(1,	'Penelitian RAB',	'penelitian-rab',	'penelitian-rab-1757774685.pdf',	'penelitian-rab-1757786281.png',	'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',	0,	'2025-09-13 14:44:45',	NULL),
(6,	'aaaaaaa',	'aaaaaaa',	'aaaaaaa-1757786240.pdf',	'aaaaaaa-1757786240.png',	'bbbbbbbbbbb',	0,	'2025-09-13 17:57:20',	NULL),
(7,	'Lorem Ipsum',	'lorem-ipsum',	'lorem-ipsum-1757787304.pdf',	'lorem-ipsum-1757787304.png',	'Dolor is met',	0,	'2025-09-13 18:15:04',	1),
(8,	'sss',	'sss',	'sss-1757787332.pdf',	'sss-1757787332.png',	'ssss',	0,	'2025-09-13 18:15:32',	1);

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1,	'library_name',	'FlipBook'),
(2,	'tagline',	'Amazing Flip'),
(3,	'address',	'Semarang'),
(4,	'email',	'flipbook@gmail.com'),
(5,	'phone',	'083638353535');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1,	'admin',	'$2y$10$x7ads9ujmgbwxkA326tcUus2i0Mr9WLG8NmeaNajkQLx.Natnszze',	'2025-09-13 14:29:51');

-- 2025-09-13 19:50:52 UTC
