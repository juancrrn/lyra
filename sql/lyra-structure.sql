-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-05-2021 a las 20:15:36
-- Versión del servidor: 5.7.31
-- Versión de PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lyra`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_donations`
--

DROP TABLE IF EXISTS `book_donations`;
CREATE TABLE IF NOT EXISTS `book_donations` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `student_id` int(16) NOT NULL,
  `creation_date` datetime NOT NULL,
  `creator_id` int(16) NOT NULL,
  `education_level` enum('edu_level_eso_1','edu_level_eso_2','edu_level_eso_3','edu_level_eso_4','edu_level_bach_1','edu_level_bach_2','edu_level_other') NOT NULL,
  `school_year` int(8) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_donation_contents`
--

DROP TABLE IF EXISTS `book_donation_contents`;
CREATE TABLE IF NOT EXISTS `book_donation_contents` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `donation_id` int(16) NOT NULL,
  `subject_id` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `donation_id` (`donation_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_lots`
--

DROP TABLE IF EXISTS `book_lots`;
CREATE TABLE IF NOT EXISTS `book_lots` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `request_id` int(16) NOT NULL,
  `student_id` int(16) NOT NULL,
  `status` enum('book_lot_status_initial','book_lot_status_ready','book_lot_status_picked_up','book_lot_status_returned','book_lot_status_rejected') NOT NULL,
  `creation_date` datetime NOT NULL,
  `creator_id` int(16) NOT NULL,
  `education_level` enum('edu_level_eso_1','edu_level_eso_2','edu_level_eso_3','edu_level_eso_4','edu_level_bach_1','edu_level_bach_2','edu_level_other') NOT NULL,
  `school_year` int(8) NOT NULL,
  `pickup_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `request_id` (`request_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_lot_contents`
--

DROP TABLE IF EXISTS `book_lot_contents`;
CREATE TABLE IF NOT EXISTS `book_lot_contents` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `lot_id` int(16) NOT NULL,
  `subject_id` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lot_id` (`lot_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_requests`
--

DROP TABLE IF EXISTS `book_requests`;
CREATE TABLE IF NOT EXISTS `book_requests` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `student_id` int(16) NOT NULL,
  `status` enum('book_request_status_pending','book_request_status_processed','book_request_status_rejected_stock','book_request_status_rejected_other') NOT NULL,
  `creation_date` datetime NOT NULL,
  `creator_id` int(16) NOT NULL,
  `education_level` enum('edu_level_eso_1','edu_level_eso_2','edu_level_eso_3','edu_level_eso_4','edu_level_bach_1','edu_level_bach_2','edu_level_other') NOT NULL,
  `school_year` int(8) NOT NULL,
  `specification` text NOT NULL,
  `locked` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_subjects`
--

DROP TABLE IF EXISTS `book_subjects`;
CREATE TABLE IF NOT EXISTS `book_subjects` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `education_level` enum('edu_level_eso_1','edu_level_eso_2','edu_level_eso_3','edu_level_eso_4','edu_level_bach_1','edu_level_bach_2','edu_level_other') NOT NULL,
  `school_year` int(8) NOT NULL,
  `book_name` varchar(256) DEFAULT NULL,
  `book_isbn` varchar(128) DEFAULT NULL,
  `book_image_url` varchar(512) DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `creator_id` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permission_groups`
--

DROP TABLE IF EXISTS `permission_groups`;
CREATE TABLE IF NOT EXISTS `permission_groups` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `type` enum('permission_group_type_default') NOT NULL,
  `short_name` varchar(128) NOT NULL,
  `full_name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `parent` int(16) DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `creator_id` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `gov_id` varchar(9) DEFAULT NULL,
  `first_name` varchar(256) NOT NULL,
  `last_name` varchar(256) NOT NULL,
  `birth_date` date NOT NULL,
  `hashed_password` varchar(512) DEFAULT NULL,
  `email_address` varchar(256) NOT NULL,
  `phone_number` varchar(32) NOT NULL,
  `representative_id` int(16) DEFAULT NULL,
  `registration_date` datetime NOT NULL,
  `last_login_date` datetime DEFAULT NULL,
  `token` varchar(256) DEFAULT NULL,
  `status` enum('user_status_inactive','user_status_active','user_status_reset') NOT NULL DEFAULT 'user_status_inactive',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gov_id` (`gov_id`),
  KEY `users_ibfk_1` (`representative_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_permission_group_links`
--

DROP TABLE IF EXISTS `user_permission_group_links`;
CREATE TABLE IF NOT EXISTS `user_permission_group_links` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_id` int(16) NOT NULL,
  `permission_group_id` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission_group` (`user_id`,`permission_group_id`),
  KEY `permission_group_id` (`permission_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `book_donations`
--
ALTER TABLE `book_donations`
  ADD CONSTRAINT `book_donations_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `book_donations_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `book_donation_contents`
--
ALTER TABLE `book_donation_contents`
  ADD CONSTRAINT `book_donation_contents_ibfk_1` FOREIGN KEY (`donation_id`) REFERENCES `book_donations` (`id`),
  ADD CONSTRAINT `book_donation_contents_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `book_subjects` (`id`);

--
-- Filtros para la tabla `book_lots`
--
ALTER TABLE `book_lots`
  ADD CONSTRAINT `book_lots_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `book_lots_ibfk_2` FOREIGN KEY (`request_id`) REFERENCES `book_requests` (`id`),
  ADD CONSTRAINT `book_lots_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `book_lot_contents`
--
ALTER TABLE `book_lot_contents`
  ADD CONSTRAINT `book_lot_contents_ibfk_1` FOREIGN KEY (`lot_id`) REFERENCES `book_lots` (`id`),
  ADD CONSTRAINT `book_lot_contents_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `book_subjects` (`id`);

--
-- Filtros para la tabla `book_requests`
--
ALTER TABLE `book_requests`
  ADD CONSTRAINT `book_requests_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `book_requests_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `book_subjects`
--
ALTER TABLE `book_subjects`
  ADD CONSTRAINT `book_subjects_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `permission_groups`
--
ALTER TABLE `permission_groups`
  ADD CONSTRAINT `permission_groups_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `permission_groups_ibfk_2` FOREIGN KEY (`parent`) REFERENCES `permission_groups` (`id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`representative_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `user_permission_group_links`
--
ALTER TABLE `user_permission_group_links`
  ADD CONSTRAINT `user_permission_group_links_ibfk_1` FOREIGN KEY (`permission_group_id`) REFERENCES `permission_groups` (`id`),
  ADD CONSTRAINT `user_permission_group_links_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
