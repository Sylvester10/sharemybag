-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 24, 2023 at 02:08 PM
-- Server version: 10.5.22-MariaDB-cll-lve
-- PHP Version: 8.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sharemyb_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 0,
  `roles` varchar(1000) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `pass_reset_code` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `photo_thumb` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `level`, `roles`, `password`, `pass_reset_code`, `photo`, `photo_thumb`, `last_login`) VALUES
(4, 'Admin', 'admin@sharemybag.uk', '08184242507', 1, 'All Roles', 'SMB2122', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `adverts`
--

CREATE TABLE `adverts` (
  `id` int(11) NOT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `published` varchar(10) DEFAULT 'false',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `traveller_id` int(11) DEFAULT NULL,
  `traveller_name` varchar(100) DEFAULT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `sender_phone` int(11) DEFAULT NULL,
  `sender_email` varchar(100) DEFAULT NULL,
  `sender_address` varchar(300) DEFAULT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `receiver_phone` int(11) DEFAULT NULL,
  `receiver_email` varchar(100) DEFAULT NULL,
  `receiver_address` varchar(300) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_acc` varchar(20) DEFAULT NULL,
  `sort_code` varchar(20) DEFAULT NULL,
  `selected_space` varchar(20) DEFAULT NULL,
  `selected_price` varchar(20) DEFAULT NULL,
  `total_amount` varchar(20) DEFAULT NULL,
  `sub_total` varchar(20) DEFAULT NULL,
  `vat` varchar(20) DEFAULT NULL,
  `service_charge` varchar(20) DEFAULT NULL,
  `id_type` varchar(20) DEFAULT NULL,
  `id_photo` varchar(50) DEFAULT NULL,
  `selfie` varchar(100) DEFAULT NULL,
  `tracking_id` varchar(20) DEFAULT NULL,
  `items` varchar(500) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `insurance` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `delivery_status` varchar(20) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` int(11) NOT NULL,
  `rate` varchar(10) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange_rates`
--

INSERT INTO `exchange_rates` (`id`, `rate`, `date_added`) VALUES
(1, '1000', '2023-08-03 23:40:14');

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--

CREATE TABLE `shipping` (
  `id` int(11) NOT NULL,
  `tracking_id` varchar(18) DEFAULT NULL,
  `heading` varchar(300) DEFAULT NULL,
  `body` varchar(300) DEFAULT NULL,
  `delivery_status` varchar(20) DEFAULT NULL,
  `icon_text` varchar(70) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping`
--

INSERT INTO `shipping` (`id`, `tracking_id`, `heading`, `body`, `delivery_status`, `icon_text`, `date_added`) VALUES
(20, 'SMBJEACNB', 'Shipping', '<p>fgs/b;mgb;sblviudnvklev</p>', 'Shipment Created', '<i class=\"fa-solid fa-calendar-check text-danger\"></i>', '2023-06-02 20:17:02'),
(21, 'SMBJEACNB', 'Shipping', '<p>ef;b l;ksb ln lbksnbl;wrnbr\'bg</p>', 'In Transit', '<i class=\"fa-solid fa-earth-americas text-primary\"></i>', '2023-06-02 20:17:45'),
(31, 'SMBBN1RKV', 'Shipping', 'Lkfngmdbo;ndsfbionfgbo[bhj;nrwoihnsktbwr[ioaby', 'Shipment Created', '<i class=\"fa-solid fa-calendar-check text-danger\"></i>', '2023-06-03 08:30:09'),
(36, 'SMBT3AXT7', 'Shipment Created', '<p>Shipment has been created</p>', 'Shipment Created', '<i class=\"fa-solid fa-calendar-check text-danger\"></i>', '2023-08-04 01:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `travellers`
--

CREATE TABLE `travellers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `alt_phone` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(300) DEFAULT NULL,
  `drop_date1` varchar(300) DEFAULT NULL,
  `drop_address1` varchar(300) DEFAULT NULL,
  `drop_date2` varchar(300) DEFAULT NULL,
  `drop_address2` varchar(300) DEFAULT NULL,
  `travel_date` varchar(100) DEFAULT NULL,
  `arrival_date` varchar(100) DEFAULT NULL,
  `current_state` varchar(100) DEFAULT NULL,
  `departure_state` varchar(300) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `airline` varchar(100) DEFAULT NULL,
  `unwanted_items` varchar(300) DEFAULT NULL,
  `original_bag_space` varchar(20) DEFAULT NULL,
  `used_space` varchar(20) DEFAULT NULL,
  `available_space` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `delivery_status` varchar(20) DEFAULT NULL,
  `tracking_id` varchar(20) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travellers`
--

INSERT INTO `travellers` (`id`, `name`, `phone`, `alt_phone`, `email`, `address`, `drop_date1`, `drop_address1`, `drop_date2`, `drop_address2`, `travel_date`, `arrival_date`, `current_state`, `departure_state`, `location`, `destination`, `airline`, `unwanted_items`, `original_bag_space`, `used_space`, `available_space`, `status`, `delivery_status`, `tracking_id`, `date_added`) VALUES
(56, 'Bclass', '+447868236301', 44825866, 'edokpayib@hotmail.com', '', NULL, NULL, NULL, NULL, '2023-08-11', NULL, NULL, NULL, 'Nigeria', 'United Kingdom (UK)', NULL, '', NULL, NULL, NULL, 'Pending', NULL, NULL, '2023-08-09 18:58:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adverts`
--
ALTER TABLE `adverts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `travellers`
--
ALTER TABLE `travellers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `adverts`
--
ALTER TABLE `adverts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `travellers`
--
ALTER TABLE `travellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
