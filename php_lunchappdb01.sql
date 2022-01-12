-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 11, 2022 at 11:11 AM
-- Server version: 5.7.24
-- PHP Version: 7.2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_lunchappdb01`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `password` varchar(80) COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$iuKKK/.Ec/T.Xc0ObtanCet1wCH.Q9.xFqIBkJyjmUISzq/4ZSoZG');

-- --------------------------------------------------------

--
-- Table structure for table `credit_account`
--

CREATE TABLE `credit_account` (
  `id` int(11) NOT NULL,
  `balance` float NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credit_system`
--

CREATE TABLE `credit_system` (
  `id` int(11) NOT NULL,
  `spent` float DEFAULT '0',
  `day_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(34) COLLATE latin1_general_ci NOT NULL,
  `email` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `company` varchar(34) COLLATE latin1_general_ci DEFAULT NULL,
  `firstdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'create user date',
  `lastdate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'last login'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `company`, `firstdate`, `lastdate`) VALUES
(1, 'Christopher', 'c.badenhorst@iasset.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(2, 'Erik', 'e.akkerman@iasset.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(3, 'Giel', 'g.hakman@iasset.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(4, 'Andras', 'a.schuh@iasset.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(5, 'Robbert', 'r.bloksma@it-firm.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(6, 'Gillian', 'gillian@it-firm.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(7, 'Ana', 'a.jekova@it-firm.nl', 'iASSET', '2021-12-31 22:00:00', '2022-12-30 22:00:00'),
(8, 'Mahmoud', 'mahmoud@gmail.com', 'mycompany', '2022-01-11 08:14:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_meta`
--

CREATE TABLE `user_meta` (
  `id` int(11) NOT NULL,
  `topped_up` float DEFAULT '0',
  `attended` tinyint(1) DEFAULT '0',
  `fee` float DEFAULT '0',
  `day_date` date NOT NULL DEFAULT '1970-01-01',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `credit_account`
--
ALTER TABLE `credit_account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credit_system`
--
ALTER TABLE `credit_system`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`day_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_meta`
--
ALTER TABLE `user_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `credit_account`
--
ALTER TABLE `credit_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credit_system`
--
ALTER TABLE `credit_system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_meta`
--
ALTER TABLE `user_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_meta`
--
ALTER TABLE `user_meta`
  ADD CONSTRAINT `user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
