-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 04, 2024 at 02:06 AM
-- Server version: 10.6.17-MariaDB
-- PHP Version: 8.1.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `memamali_member`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `open` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`, `open`) VALUES
('admin', '3b8846e733a3c932b298a8e0dda0406c', 1);

-- --------------------------------------------------------

--
-- Table structure for table `father`
--

CREATE TABLE `father` (
  `parent` varchar(9) NOT NULL,
  `child` varchar(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `hobbies`
--

CREATE TABLE `hobbies` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `meeting`
--

CREATE TABLE `meeting` (
  `cpr` int(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `name` varchar(30) NOT NULL,
  `CPR` varchar(9) NOT NULL,
  `dob` date NOT NULL DEFAULT '2016-01-01',
  `phone` varchar(30) NOT NULL,
  `edLevel` varchar(10) NOT NULL,
  `major` varchar(30) DEFAULT NULL,
  `TC` text DEFAULT NULL,
  `emState` varchar(10) NOT NULL,
  `employer` varchar(30) DEFAULT NULL,
  `jobName` varchar(30) DEFAULT NULL,
  `maState` varchar(10) NOT NULL,
  `kidNum` int(2) NOT NULL DEFAULT 0,
  `involved` varchar(3) NOT NULL,
  `involvedName` text DEFAULT NULL,
  `hobby` text NOT NULL,
  `otherHobby` text DEFAULT NULL,
  `boys` text DEFAULT NULL,
  `lastEdit` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `name` varchar(50) NOT NULL,
  `CPR` varchar(9) NOT NULL,
  `dob` date NOT NULL DEFAULT '2016-01-01',
  `phone` varchar(30) NOT NULL,
  `edLevel` varchar(10) NOT NULL,
  `major` varchar(30) DEFAULT NULL,
  `TC` text DEFAULT NULL,
  `emState` varchar(10) NOT NULL,
  `employer` varchar(30) DEFAULT NULL,
  `jobName` varchar(30) DEFAULT NULL,
  `maState` varchar(10) NOT NULL,
  `kidNum` int(2) NOT NULL DEFAULT 0,
  `involved` varchar(3) NOT NULL,
  `involvedName` text DEFAULT NULL,
  `hobby` text NOT NULL,
  `otherHobby` text DEFAULT NULL,
  `boys` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `active_comment` varchar(100) DEFAULT NULL,
  `lastEdit` date NOT NULL,
  `approvedBy` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `voting`
--

CREATE TABLE `voting` (
  `cpr` varchar(9) NOT NULL,
  `register` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `father`
--
ALTER TABLE `father`
  ADD PRIMARY KEY (`parent`,`child`);

--
-- Indexes for table `hobbies`
--
ALTER TABLE `hobbies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meeting`
--
ALTER TABLE `meeting`
  ADD PRIMARY KEY (`cpr`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`CPR`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`CPR`);

--
-- Indexes for table `voting`
--
ALTER TABLE `voting`
  ADD PRIMARY KEY (`cpr`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hobbies`
--
ALTER TABLE `hobbies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
