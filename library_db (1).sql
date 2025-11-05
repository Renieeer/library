-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 25, 2025 at 09:48 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_tbl`
--

CREATE TABLE `account_tbl` (
  `id` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `Identification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `account_tbl`
--

INSERT INTO `account_tbl` (`id`, `username`, `password`, `Identification`) VALUES
(76, 'andal@gmail.com', '$2y$10$wM0jfVCyRDB1dXIsRFm1TOJejdBZMkCSmwzK3kDIdncHPoHU9BtUG', 'Faculty'),
(79, 'prettyluna@gmail.com', '0966', 'Faculty'),
(80, 'raindel@gmail.com', '$2y$10$5lpGpxa4792xJaLJqTpIi.MbGamibHNLYbgRUprdncltvhnMRHMI.', 'Student'),
(82, 'reneir@gmail.com', '$2y$10$XY3BWLsEBiJnznEbfvnnjud5YVLSP7NX7ocMbfcBIJvl4GaTe1ys6', 'Student'),
(84, 'admin@gmail.com', '$2y$10$CZMc8x1HjD4M7h.GPJxhfectg8reS8UoBTAiSNfUdgUdJjHtQkETq', 'Admin'),
(85, 'mizonkyle@gmail.com', '$2y$10$UwawZIec68cQm/JB38yRRuU93yol8kIvP9cVIBEJZjA4IptQXnjPm', 'Faculty'),
(86, 'ford@gmail.com', '$2y$10$.cz9YW2Zh7ZRRNoPgGY.YO29unb6JOgIhpMlMKGIedhouelmiqVnK', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `book_tbl`
--

CREATE TABLE `book_tbl` (
  `id` int NOT NULL,
  `no` int DEFAULT NULL,
  `bookname` varchar(50) DEFAULT NULL,
  `shelf_no` int DEFAULT NULL,
  `availability` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `book_tbl`
--

INSERT INTO `book_tbl` (`id`, `no`, `bookname`, `shelf_no`, `availability`) VALUES
(8, 89890, 'The Great Big Bang', 2, 1),
(9, 12342, 'Mathematics', 3, 1),
(10, 8990999, 'trigonometry', 1, 1),
(11, 11766, 'computer', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `borrow_books`
--

CREATE TABLE `borrow_books` (
  `id` int NOT NULL,
  `book_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `borrow_date` date DEFAULT NULL,
  `date_return` date DEFAULT NULL,
  `is_returned` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fines_tbl`
--

CREATE TABLE `fines_tbl` (
  `id` int NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint DEFAULT NULL,
  `dy_start` date DEFAULT NULL,
  `dy_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overdues_tbl`
--

CREATE TABLE `overdues_tbl` (
  `id` int NOT NULL,
  `borrowed_id` int DEFAULT NULL,
  `fine_id` int DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shelves_tbl`
--

CREATE TABLE `shelves_tbl` (
  `id` int NOT NULL,
  `no` int DEFAULT NULL,
  `shelf_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shelves_tbl`
--

INSERT INTO `shelves_tbl` (`id`, `no`, `shelf_name`) VALUES
(1, 6063, 'MATH'),
(2, 6064, 'Science'),
(3, 6065, 'English'),
(4, 6066, 'Entrepreneur'),
(5, 6067, 'Literature');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_tbl`
--
ALTER TABLE `account_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_tbl`
--
ALTER TABLE `book_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shelf_no` (`shelf_no`);

--
-- Indexes for table `borrow_books`
--
ALTER TABLE `borrow_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `fines_tbl`
--
ALTER TABLE `fines_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overdues_tbl`
--
ALTER TABLE `overdues_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrowed_id` (`borrowed_id`),
  ADD KEY `fine_id` (`fine_id`);

--
-- Indexes for table `shelves_tbl`
--
ALTER TABLE `shelves_tbl`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_tbl`
--
ALTER TABLE `account_tbl`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `book_tbl`
--
ALTER TABLE `book_tbl`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `borrow_books`
--
ALTER TABLE `borrow_books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `fines_tbl`
--
ALTER TABLE `fines_tbl`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overdues_tbl`
--
ALTER TABLE `overdues_tbl`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shelves_tbl`
--
ALTER TABLE `shelves_tbl`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_tbl`
--
ALTER TABLE `book_tbl`
  ADD CONSTRAINT `book_tbl_ibfk_1` FOREIGN KEY (`shelf_no`) REFERENCES `shelves_tbl` (`id`);

--
-- Constraints for table `borrow_books`
--
ALTER TABLE `borrow_books`
  ADD CONSTRAINT `borrow_books_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account_tbl` (`id`),
  ADD CONSTRAINT `borrow_books_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `book_tbl` (`id`);

--
-- Constraints for table `overdues_tbl`
--
ALTER TABLE `overdues_tbl`
  ADD CONSTRAINT `overdues_tbl_ibfk_1` FOREIGN KEY (`borrowed_id`) REFERENCES `borrow_books` (`id`),
  ADD CONSTRAINT `overdues_tbl_ibfk_2` FOREIGN KEY (`fine_id`) REFERENCES `fines_tbl` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
