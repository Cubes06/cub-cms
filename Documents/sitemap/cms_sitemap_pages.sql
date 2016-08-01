-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 01, 2016 at 04:39 PM
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
-- Table structure for table `cms_sitemap_pages`
--

CREATE TABLE `cms_sitemap_pages` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `type` char(255) NOT NULL,
  `url_slug` char(255) NOT NULL,
  `short_title` varchar(255) NOT NULL,
  `title` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `body` longtext,
  `status` int(11) NOT NULL DEFAULT '1',
  `order_number` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cms_sitemap_pages`
--
ALTER TABLE `cms_sitemap_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cms_sitemap_page_parent_slug_unique` (`parent_id`,`url_slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cms_sitemap_pages`
--
ALTER TABLE `cms_sitemap_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
