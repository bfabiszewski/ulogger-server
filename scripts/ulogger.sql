--
-- Database: `ulogger`
--

CREATE DATABASE IF NOT EXISTS `ulogger` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ulogger`;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `login` varchar(15) CHARACTER SET latin1 NOT NULL UNIQUE,
  `password` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

DROP TABLE IF EXISTS `tracks`;
CREATE TABLE `tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `comment` varchar(1024) DEFAULT NULL,
  INDEX `idx_user_id` (`user_id`),
  FOREIGN KEY(`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `altitude` double DEFAULT NULL,
  `speed` double DEFAULT NULL,
  `bearing` double DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  INDEX `idx_ptrack_id` (`track_id`),
  INDEX `index_puser_id` (`user_id`),
  FOREIGN KEY(`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY(`track_id`) REFERENCES `tracks`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- This will add default user admin with password admin
-- The password should be changed immediatelly after installation
-- Uncomment if needed
--
-- INSERT INTO `users` (`id`, `login`, `password`) VALUES
-- (1, 'admin', '$2y$10$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq');