-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 12:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `g03sec47fbsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `BookingDate` date NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `BookingStatus` enum('Pending','Approved','Rejected','Completed','Cancelled') DEFAULT 'Pending',
  `Purpose` varchar(255) NOT NULL,
  `CreatedDateTime` datetime DEFAULT current_timestamp(),
  `MemberID` int(11) NOT NULL,
  `FacilityID` int(11) NOT NULL,
  `StaffID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `BookingDate`, `StartTime`, `EndTime`, `BookingStatus`, `Purpose`, `CreatedDateTime`, `MemberID`, `FacilityID`, `StaffID`) VALUES
(2, '2026-06-10', '14:00:00', '16:00:00', 'Pending', 'Casual swimming with friends', '2026-06-09 18:20:00', 2, 3, 2),
(3, '2026-06-11', '18:00:00', '20:00:00', 'Pending', 'Tennis practice for tournament', '2026-06-09 18:20:00', 3, 5, 2),
(4, '2026-06-08', '08:00:00', '10:00:00', 'Completed', 'Morning gym workout', '2026-06-09 18:20:00', 1, 4, 3),
(5, '2026-06-07', '16:00:00', '18:00:00', 'Completed', 'Company futsal match', '2026-06-09 18:20:00', 4, 9, 2),
(6, '2026-06-12', '09:00:00', '11:00:00', 'Pending', 'Team meeting presentation', '2026-06-09 18:20:00', 5, 11, 2),
(7, '2026-06-06', '14:00:00', '16:00:00', 'Approved', 'Basketball friendly match', '2026-06-09 18:20:00', 6, 8, 4);

-- --------------------------------------------------------

--
-- Table structure for table `facility`
--

CREATE TABLE `facility` (
  `FacilityID` int(11) NOT NULL,
  `FacilityName` varchar(100) NOT NULL,
  `FacilityCategory` varchar(50) NOT NULL,
  `FacilityCapacity` int(11) NOT NULL,
  `FacilityDetail` varchar(100) DEFAULT NULL,
  `RatePerHour` decimal(10,2) NOT NULL,
  `FacilityStatus` enum('Available','Unavailable','Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility`
--

INSERT INTO `facility` (`FacilityID`, `FacilityName`, `FacilityCategory`, `FacilityCapacity`, `FacilityDetail`, `RatePerHour`, `FacilityStatus`) VALUES
(3, 'Olympic Swimming Pool', 'Swimming', 50, '50-meter Olympic-standard swimming pool with 8 lanes.', 15.00, 'Available'),
(4, 'Fitness Centre', 'Gymnasium', 30, 'Fully equipped gym with cardio and free weights.', 20.00, 'Available'),
(5, 'Tennis Court 1', 'Tennis', 4, 'Outdoor hardcourt with floodlights.', 30.00, 'Available'),
(6, 'Tennis Court 2', 'Tennis', 4, 'Outdoor hardcourt with floodlights.', 30.00, 'Available'),
(7, 'Multi-Purpose Hall', 'Hall', 200, 'Large hall for events, seminars, and indoor sports.', 80.00, 'Available'),
(8, 'Basketball Court', 'Basketball', 10, 'Full-size outdoor court with covered seating.', 35.00, 'Available'),
(9, 'Futsal Court A', 'Futsal', 10, 'Indoor futsal court with artificial turf.', 40.00, 'Available'),
(10, 'Futsal Court B', 'Futsal', 10, 'Outdoor futsal court.', 35.00, 'Maintenance'),
(11, 'Conference Room Alpha', 'Meeting Room', 20, 'Conference room with projector and video conferencing.', 45.00, 'Available'),
(12, 'Conference Room Beta', 'Meeting Room', 12, 'Meeting room with smart TV and whiteboard.', 35.00, 'Available'),
(13, 'Football', 'football', 11, '', 12.00, 'Available'),
(14, 'Swimming pool', 'Swinmming pool', 30, 'Swimming pool area', 10.00, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `facility_schedule`
--

CREATE TABLE `facility_schedule` (
  `ScheduleID` int(11) NOT NULL,
  `AvailableTime_Start` time NOT NULL,
  `AvailableTime_End` time NOT NULL,
  `ScheduleStatus` enum('Available','Booked','Closed') DEFAULT 'Available',
  `FacilityID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_schedule`
--

INSERT INTO `facility_schedule` (`ScheduleID`, `AvailableTime_Start`, `AvailableTime_End`, `ScheduleStatus`, `FacilityID`) VALUES
(11, '06:00:00', '08:00:00', 'Available', 3),
(12, '08:00:00', '10:00:00', 'Available', 3),
(13, '10:00:00', '12:00:00', 'Available', 3),
(14, '14:00:00', '16:00:00', 'Available', 3),
(15, '16:00:00', '18:00:00', 'Available', 3),
(16, '18:00:00', '20:00:00', 'Available', 3),
(17, '20:00:00', '22:00:00', 'Available', 3),
(18, '06:00:00', '08:00:00', 'Available', 4),
(19, '08:00:00', '10:00:00', 'Available', 4),
(20, '10:00:00', '12:00:00', 'Available', 4),
(21, '14:00:00', '16:00:00', 'Available', 4),
(22, '16:00:00', '18:00:00', 'Available', 4),
(23, '18:00:00', '20:00:00', 'Available', 4),
(24, '20:00:00', '22:00:00', 'Available', 4),
(25, '08:00:00', '10:00:00', 'Available', 5),
(26, '10:00:00', '12:00:00', 'Available', 5),
(27, '16:00:00', '18:00:00', 'Available', 5),
(28, '18:00:00', '20:00:00', 'Available', 5),
(29, '08:00:00', '10:00:00', 'Available', 6),
(30, '10:00:00', '12:00:00', 'Available', 6),
(31, '16:00:00', '18:00:00', 'Available', 6),
(32, '18:00:00', '20:00:00', 'Available', 6),
(33, '08:00:00', '12:00:00', 'Available', 7),
(34, '14:00:00', '18:00:00', 'Available', 7),
(35, '19:00:00', '22:00:00', 'Available', 7),
(36, '08:00:00', '10:00:00', 'Available', 8),
(37, '10:00:00', '12:00:00', 'Available', 8),
(38, '16:00:00', '18:00:00', 'Available', 8),
(39, '18:00:00', '20:00:00', 'Available', 8),
(40, '08:00:00', '10:00:00', 'Available', 9),
(41, '10:00:00', '12:00:00', 'Available', 9),
(42, '14:00:00', '16:00:00', 'Available', 9),
(43, '16:00:00', '18:00:00', 'Available', 9),
(44, '18:00:00', '20:00:00', 'Available', 9),
(45, '20:00:00', '22:00:00', 'Available', 9),
(46, '09:00:00', '11:00:00', 'Available', 11),
(47, '11:00:00', '13:00:00', 'Available', 11),
(48, '14:00:00', '16:00:00', 'Available', 11),
(49, '16:00:00', '18:00:00', 'Available', 11),
(50, '09:00:00', '11:00:00', 'Available', 12),
(51, '11:00:00', '13:00:00', 'Available', 12),
(52, '14:00:00', '16:00:00', 'Available', 12),
(53, '16:00:00', '18:00:00', 'Available', 12),
(54, '22:18:00', '23:18:00', 'Available', 13),
(55, '09:00:00', '18:00:00', 'Available', 14);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `MemberID` int(11) NOT NULL,
  `MemberName` varchar(100) NOT NULL,
  `MemberContactNo` varchar(15) NOT NULL,
  `MemberEmail` varchar(100) NOT NULL,
  `MemberUsername` varchar(50) NOT NULL,
  `MemberPassword` varchar(255) NOT NULL,
  `AccountStatus` enum('Active','Inactive','Suspended') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`MemberID`, `MemberName`, `MemberContactNo`, `MemberEmail`, `MemberUsername`, `MemberPassword`, `AccountStatus`) VALUES
(1, 'Tan Wei Jie', '+6012-3456789', 'weijie@gmail.com', 'weijie', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active'),
(2, 'Siti Nurhaliza', '+6019-8765432', 'siti@gmail.com', 'siti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active'),
(4, 'Priya Sharma', '+6018-1122334', 'priya@gmail.com', 'priya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active'),
(6, 'Nur Aisyah', '+6015-4433221', 'aisyah@gmail.com', 'aisyah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active'),
(13, 'marcus', '93846278', 'marcus@gmail.com', 'lucas', '$2y$10$/n9BN1/BQ.kBWBrhKfBIL.tBp20ShGFQb1RSQIX76Ryu07.3uYApS', 'Inactive'),
(16, 'Jiale', '0183243873', 'jiale1608@gmail.com', 'Jiale', '$2y$10$gmTFUEU/y5hd9KSkJhyi/uWgzsO55ja9VyBsWu4Q147gpEWTUVdeO', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `PaymentAmount` decimal(10,2) NOT NULL,
  `PaymentMethod` enum('Cash','Online Banking','Credit Card','Debit Card','E-Wallet') NOT NULL,
  `PaymentStatus` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `PaymentDateTime` datetime DEFAULT current_timestamp(),
  `ReceiptNumber` varchar(50) DEFAULT NULL,
  `BookingID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `PaymentAmount`, `PaymentMethod`, `PaymentStatus`, `PaymentDateTime`, `ReceiptNumber`, `BookingID`) VALUES
(2, 30.00, 'Cash', 'Pending', NULL, NULL, 2),
(3, 60.00, 'E-Wallet', 'Pending', NULL, NULL, 3),
(4, 40.00, 'Credit Card', 'Paid', '2026-06-08 18:20:00', 'RCP-20260608-4', 4),
(5, 80.00, 'Online Banking', 'Paid', '2026-06-07 18:20:00', 'RCP-20260607-5', 5),
(6, 90.00, 'Cash', 'Pending', NULL, NULL, 6),
(7, 70.00, 'Debit Card', 'Paid', '2026-06-06 18:20:00', 'RCP-20260606-7', 7);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `StaffName` varchar(100) NOT NULL,
  `StaffContactNo` varchar(20) NOT NULL,
  `StaffEmail` varchar(100) NOT NULL,
  `StaffUsername` varchar(50) NOT NULL,
  `StaffPassword` varchar(255) NOT NULL,
  `StaffRole` enum('Admin','Manager','Staff') DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`StaffID`, `StaffName`, `StaffContactNo`, `StaffEmail`, `StaffUsername`, `StaffPassword`, `StaffRole`) VALUES
(1, 'Aminah binti Hassan', '+6012-9876543', 'aminah@fbs.com', 'aminah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
(2, 'Rajesh Kumar', '+6011-2345678', 'rajesh@fbs.com', 'rajesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager'),
(3, 'Lim Mei Ling', '+6013-8765432', 'meiling@fbs.com', 'meiling', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff'),
(4, 'Mohd Faizal', '+6014-5678901', 'faizal@fbs.com', 'faizal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff'),
(6, 'Ch\'ng Jia Le', '0183243873', 'jiale1608@gmail.com', 'Jiale', '$2y$10$XR2PPeasRIbnEDB28hHLk.jbE9nfzwK0cv37mbhCU54QO3XSaI87e', 'Staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `FacilityID` (`FacilityID`),
  ADD KEY `StaffID` (`StaffID`);

--
-- Indexes for table `facility`
--
ALTER TABLE `facility`
  ADD PRIMARY KEY (`FacilityID`);

--
-- Indexes for table `facility_schedule`
--
ALTER TABLE `facility_schedule`
  ADD PRIMARY KEY (`ScheduleID`),
  ADD KEY `FacilityID` (`FacilityID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MemberID`),
  ADD UNIQUE KEY `MemberEmail` (`MemberEmail`),
  ADD UNIQUE KEY `MemberUsername` (`MemberUsername`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD UNIQUE KEY `ReceiptNumber` (`ReceiptNumber`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`StaffID`),
  ADD UNIQUE KEY `StaffEmail` (`StaffEmail`),
  ADD UNIQUE KEY `StaffUsername` (`StaffUsername`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `facility`
--
ALTER TABLE `facility`
  MODIFY `FacilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `facility_schedule`
--
ALTER TABLE `facility_schedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `StaffID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`FacilityID`) REFERENCES `facility` (`FacilityID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`StaffID`) REFERENCES `staff` (`StaffID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `facility_schedule`
--
ALTER TABLE `facility_schedule`
  ADD CONSTRAINT `facility_schedule_ibfk_1` FOREIGN KEY (`FacilityID`) REFERENCES `facility` (`FacilityID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
