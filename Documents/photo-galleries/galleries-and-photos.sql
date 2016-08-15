-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 12, 2016 at 04:51 PM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.5.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cubes_school_cms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cms_photos`
--

CREATE TABLE `cms_photos` (
  `id` int(11) NOT NULL,
  `photo_gallery_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `order_number` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_photos`
--

INSERT INTO `cms_photos` (`id`, `photo_gallery_id`, `title`, `description`, `status`, `order_number`) VALUES
(1, 1, 'Photo 1 Title', 'Photo 1 Description', 1, 1),
(2, 1, 'Photo 2 Title', 'Photo 2 Description', 1, 2),
(3, 1, 'Photo 3 Title', 'Photo 3 Description', 1, 3),
(4, 2, 'Photo 1 Title', 'Photo 1 Description', 1, 1),
(5, 2, 'Photo 2 Title', 'Photo 2 Description', 1, 2),
(6, 2, 'Photo 3 Title', 'Photo 3 Description', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `cms_photo_galleries`
--

CREATE TABLE `cms_photo_galleries` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `order_number` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_photo_galleries`
--

INSERT INTO `cms_photo_galleries` (`id`, `title`, `description`, `status`, `order_number`) VALUES
(1, 'Photo Gallery 1 Title', 'Photo Gallery 1 Description', 1, 1),
(2, 'Photo Gallery 2 Title', 'Photo Gallery 2 Description', 1, 2),
(4, 'sport', 'jhcjcds', 1, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cms_photos`
--
ALTER TABLE `cms_photos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_photo_galleries`
--
ALTER TABLE `cms_photo_galleries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cms_photos`
--
ALTER TABLE `cms_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `cms_photo_galleries`
--
ALTER TABLE `cms_photo_galleries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
