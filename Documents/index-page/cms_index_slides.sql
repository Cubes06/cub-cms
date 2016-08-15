-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 15, 2016 at 04:32 PM
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
-- Table structure for table `cms_index_slides`
--

CREATE TABLE `cms_index_slides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `link_type` char(50) NOT NULL DEFAULT 'NoLink',
  `link_label` varchar(255) DEFAULT NULL,
  `sitemap_page_id` int(11) DEFAULT NULL,
  `internal_link_url` varchar(255) DEFAULT NULL,
  `external_link_url` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `order_number` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cms_index_slides`
--

INSERT INTO `cms_index_slides` (`id`, `title`, `description`, `link_type`, `link_label`, `sitemap_page_id`, `internal_link_url`, `external_link_url`, `status`, `order_number`) VALUES
(1, 'Slide 1 Title', 'Slide 1 Description', 'NoLink', 'slide 1 link label', NULL, NULL, NULL, 1, 1),
(2, 'Slide 2 Title', 'Slide 2 Description', 'NoLink', 'slide 2 link label', NULL, NULL, NULL, 1, 2),
(3, 'Slide 3 Title', 'Slide 3 Description', 'NoLink', 'slide 3 link label', NULL, NULL, NULL, 1, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cms_index_slides`
--
ALTER TABLE `cms_index_slides`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cms_index_slides`
--
ALTER TABLE `cms_index_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
