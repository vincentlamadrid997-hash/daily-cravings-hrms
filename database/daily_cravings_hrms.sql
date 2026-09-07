-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 10:50 AM
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
-- Database: `daily_cravings_hrms`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `allowance_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `allowance_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allowances`
--

INSERT INTO `allowances` (`allowance_id`, `employee_id`, `allowance_name`, `amount`, `created_at`) VALUES
(1, 3, 'Transportation Allowance', 1500.00, '2026-07-21 15:19:58'),
(2, 3, 'Meal Allowance', 1000.00, '2026-07-21 15:19:58'),
(3, 4, 'Meal Allowance', 1200.00, '2026-07-21 15:19:58'),
(4, 4, 'Rice Allowance', 1000.00, '2026-07-21 15:19:58'),
(5, 5, 'Transportation Allowance', 1500.00, '2026-07-21 15:19:58'),
(6, 5, 'Communication Allowance', 1000.00, '2026-07-21 15:19:58'),
(7, 6, 'Marketing Allowance', 2000.00, '2026-07-21 15:19:58'),
(8, 6, 'Transportation Allowance', 1200.00, '2026-07-21 15:19:58'),
(9, 7, 'Communication Allowance', 1200.00, '2026-07-21 15:19:58'),
(10, 7, 'Transportation Allowance', 1500.00, '2026-07-21 15:19:58'),
(11, 8, 'Meal Allowance', 1500.00, '2026-07-21 15:19:58'),
(12, 8, 'Rice Allowance', 1000.00, '2026-07-21 15:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `expected_salary` decimal(10,2) DEFAULT NULL,
  `available_date` date DEFAULT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `cover_letter` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Reviewed','Shortlisted','Initial Interview','Final Interview','Accepted','Rejected') DEFAULT 'Pending',
  `employee_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_hired` enum('No','Yes') DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `job_id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `birthdate`, `gender`, `civil_status`, `address`, `expected_salary`, `available_date`, `resume`, `cover_letter`, `status`, `employee_id`, `created_at`, `is_hired`) VALUES
(11, 4, 'Carlos', 'Adrian', 'Mendoza', 'carlos.mendoza@gmail.com', '09184567891', '2001-03-15', 'Male', 'Single', 'Dasmarinas City, Cavite', 22000.00, '2026-08-01', 'Carlos_Mendoza_Resume.pdf', 'Carlos_Mendoza_CoverLetter.pdf', 'Rejected', NULL, '2026-07-21 16:01:27', 'No'),
(12, 5, 'Bianca', 'Louise', 'Ramos', 'bianca.ramos@gmail.com', '09184567892', '2002-07-20', 'Female', 'Single', 'Imus City, Cavite', 20000.00, '2026-08-05', 'Bianca_Ramos_Resume.pdf', 'Bianca_Ramos_CoverLetter.pdf', 'Reviewed', NULL, '2026-07-21 16:01:27', 'No'),
(13, 6, 'Daniel', 'Paulo', 'Villanueva', 'daniel.villanueva@gmail.com', '09184567893', '1999-11-10', 'Male', 'Single', 'Bacoor City, Cavite', 25000.00, '2026-08-10', 'Daniel_Villanueva_Resume.pdf', 'Daniel_Villanueva_CoverLetter.pdf', 'Initial Interview', NULL, '2026-07-21 16:01:27', 'No'),
(14, 7, 'Ella', 'Marie', 'Castillo', 'ella.castillo@gmail.com', '09184567894', '2000-05-18', 'Female', 'Single', 'General Trias, Cavite', 23000.00, '2026-08-03', 'Ella_Castillo_Resume.pdf', 'Ella_Castillo_CoverLetter.pdf', 'Final Interview', NULL, '2026-07-21 16:01:27', 'No'),
(15, 8, 'Miguel', 'Antonio', 'Torres', 'miguel.torres@gmail.com', '09184567895', '1998-09-25', 'Male', 'Married', 'Tagaytay City, Cavite', 21000.00, '2026-08-07', 'Miguel_Torres_Resume.pdf', 'Miguel_Torres_CoverLetter.pdf', 'Accepted', 12, '2026-07-21 16:01:27', 'Yes');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `work_type` enum('Regular','Holiday','Rest Day') DEFAULT 'Regular',
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `total_working_hours` time DEFAULT NULL,
  `overtime_hours` time DEFAULT NULL,
  `overtime_request_id` int(11) DEFAULT NULL,
  `undertime_hours` time DEFAULT NULL,
  `status` enum('Present','Late','Half Day','Absent','Leave','Holiday') DEFAULT NULL,
  `halfday_request_id` int(11) DEFAULT NULL,
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `is_absent` tinyint(1) NOT NULL DEFAULT 0,
  `is_rest_day` tinyint(1) NOT NULL DEFAULT 0,
  `is_holiday` tinyint(1) NOT NULL DEFAULT 0,
  `holiday_name` varchar(100) DEFAULT NULL,
  `break_minutes` int(11) NOT NULL DEFAULT 30,
  `worked_seconds` int(11) NOT NULL DEFAULT 0,
  `timeline_completed` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `break_out` time DEFAULT NULL,
  `break_in` time DEFAULT NULL,
  `lunch_deducted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `attendance_date`, `work_type`, `time_in`, `time_out`, `total_working_hours`, `overtime_hours`, `overtime_request_id`, `undertime_hours`, `status`, `halfday_request_id`, `late_minutes`, `is_absent`, `is_rest_day`, `is_holiday`, `holiday_name`, `break_minutes`, `worked_seconds`, `timeline_completed`, `remarks`, `updated_by`, `updated_at`, `created_at`, `break_out`, `break_in`, `lunch_deducted`) VALUES
(8, 3, '2026-07-21', 'Regular', '08:00:00', '17:00:00', '08:00:00', '00:00:00', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Complete shift', NULL, NULL, '2026-07-21 15:29:52', '12:00:00', '13:00:00', 0),
(9, 4, '2026-07-21', 'Regular', '08:15:00', '17:00:00', '07:45:00', '03:00:00', NULL, '00:15:00', 'Late', NULL, 15, 0, 0, 0, NULL, 30, 27900, 1, 'Late arrival', NULL, '2026-07-29 18:27:09', '2026-07-21 15:29:52', '12:00:00', '13:00:00', 0),
(10, 5, '2026-07-21', 'Regular', '08:00:00', '17:00:00', '08:00:00', '00:00:00', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'On time', NULL, NULL, '2026-07-21 15:29:52', '12:00:00', '13:00:00', 0),
(11, 6, '2026-07-21', 'Regular', '08:00:00', '17:00:00', '08:00:00', '00:00:00', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Complete shift', NULL, NULL, '2026-07-21 15:29:52', '12:00:00', '13:00:00', 0),
(12, 7, '2026-07-21', 'Regular', '08:30:00', '17:00:00', '07:30:00', '00:00:00', NULL, '00:30:00', 'Late', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Traffic delay', NULL, NULL, '2026-07-21 15:29:52', '12:00:00', '13:00:00', 0),
(13, 8, '2026-07-21', 'Regular', NULL, NULL, '00:00:00', '00:00:00', NULL, '00:00:00', 'Leave', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Approved leave', NULL, NULL, '2026-07-21 15:29:52', NULL, NULL, 0),
(14, 3, '2026-07-20', 'Regular', '08:00:00', '17:00:00', '08:00:00', '00:00:00', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Complete shift', NULL, NULL, '2026-07-21 15:30:07', '12:00:00', '13:00:00', 0),
(15, 4, '2026-07-20', 'Regular', '08:15:00', '17:00:00', '07:45:00', '00:00:00', NULL, '00:15:00', 'Late', NULL, 15, 0, 0, 0, NULL, 30, 27900, 1, 'Late arrival', NULL, '2026-07-29 18:27:09', '2026-07-21 15:30:07', '12:00:00', '13:00:00', 0),
(16, 5, '2026-07-20', 'Regular', '08:00:00', '17:00:00', '08:00:00', '00:00:00', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'On time', NULL, NULL, '2026-07-21 15:30:07', '12:00:00', '13:00:00', 0),
(17, 6, '2026-07-20', 'Regular', '08:00:00', '17:00:00', '08:00:00', '00:00:00', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Complete shift', NULL, NULL, '2026-07-21 15:30:07', '12:00:00', '13:00:00', 0),
(18, 7, '2026-07-20', 'Regular', '08:30:00', '17:00:00', '07:30:00', '00:00:00', NULL, '00:30:00', 'Late', NULL, 0, 0, 0, 0, NULL, 30, 0, 0, 'Traffic delay', NULL, NULL, '2026-07-21 15:30:07', '12:00:00', '13:00:00', 0),
(43, 4, '2026-07-29', 'Regular', '14:37:19', '14:38:44', '00:01:25', '00:00:49', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 85, 1, 'Completed with overtime', NULL, NULL, '2026-07-31 06:37:19', NULL, NULL, 0),
(44, 4, '2026-07-15', 'Regular', '14:49:40', '14:51:13', '00:01:16', '00:00:40', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 76, 1, 'Completed with overtime', NULL, NULL, '2026-07-31 06:49:40', '14:50:20', '14:50:37', 0),
(45, 4, '2026-07-16', 'Regular', '14:52:38', '14:53:35', '00:00:18', '00:00:00', NULL, '00:00:18', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 18, 1, 'Shift completed', NULL, NULL, '2026-07-31 06:52:38', '14:52:43', '14:53:22', 0),
(46, 4, '2026-07-01', 'Regular', '15:01:10', '15:01:17', '00:00:07', '00:00:00', NULL, '00:00:29', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 7, 1, 'Shift completed', NULL, NULL, '2026-07-31 07:01:10', NULL, NULL, 0),
(48, 4, '2026-08-12', 'Regular', '08:36:35', '08:38:18', '00:01:43', '00:01:07', NULL, '00:00:00', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 103, 1, 'Completed with overtime', NULL, NULL, '2026-08-12 00:36:35', NULL, NULL, 0),
(49, 4, '2026-09-01', 'Regular', '16:02:45', '16:03:07', '00:00:13', '00:00:00', NULL, '00:00:23', 'Present', NULL, 0, 0, 0, 0, NULL, 30, 13, 1, 'Shift completed', NULL, NULL, '2026-09-01 08:02:45', '16:02:52', '16:03:01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `log_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_id` int(11) DEFAULT NULL,
  `action` enum('Time In','Break Out','Break In','Time Out','Half Day Request','OT Request','OT Start','OT End') DEFAULT NULL,
  `log_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`log_id`, `employee_id`, `attendance_id`, `action`, `log_time`) VALUES
(11, 3, 14, 'Time In', '2026-07-20 08:00:00'),
(12, 3, 14, 'Time Out', '2026-07-20 17:00:00'),
(13, 4, 15, 'Time In', '2026-07-20 08:15:00'),
(14, 4, 15, 'Time Out', '2026-07-20 17:00:00'),
(15, 5, 16, 'Time In', '2026-07-20 08:00:00'),
(16, 5, 16, 'Time Out', '2026-07-20 17:00:00'),
(17, 6, 17, 'Time In', '2026-07-20 08:00:00'),
(18, 6, 17, 'Time Out', '2026-07-20 17:00:00'),
(19, 7, 18, 'Time In', '2026-07-20 08:30:00'),
(20, 7, 18, 'Time Out', '2026-07-20 17:00:00'),
(21, 3, 14, 'Time In', '2026-07-20 08:00:00'),
(22, 3, 14, 'Time Out', '2026-07-20 17:00:00'),
(23, 4, 15, 'Time In', '2026-07-20 08:15:00'),
(24, 4, 15, 'Time Out', '2026-07-20 17:00:00'),
(25, 5, 16, 'Time In', '2026-07-20 08:00:00'),
(26, 5, 16, 'Time Out', '2026-07-20 17:00:00'),
(27, 6, 17, 'Time In', '2026-07-20 08:00:00'),
(28, 6, 17, 'Time Out', '2026-07-20 17:00:00'),
(29, 7, 18, 'Time In', '2026-07-20 08:30:00'),
(30, 7, 18, 'Time Out', '2026-07-20 17:00:00'),
(41, 3, 14, 'Time In', '2026-07-20 08:00:00'),
(42, 3, 14, 'Time Out', '2026-07-20 17:00:00'),
(43, 4, 15, 'Time In', '2026-07-20 08:15:00'),
(44, 4, 15, 'Time Out', '2026-07-20 17:00:00'),
(45, 5, 16, 'Time In', '2026-07-20 08:00:00'),
(46, 5, 16, 'Time Out', '2026-07-20 17:00:00'),
(47, 6, 17, 'Time In', '2026-07-20 08:00:00'),
(48, 6, 17, 'Time Out', '2026-07-20 17:00:00'),
(49, 7, 18, 'Time In', '2026-07-20 08:30:00'),
(50, 7, 18, 'Time Out', '2026-07-20 17:00:00'),
(61, 3, 8, 'Time In', '2026-07-21 08:00:00'),
(62, 3, 8, 'Time Out', '2026-07-21 17:00:00'),
(63, 4, 9, 'Time In', '2026-07-21 08:10:00'),
(64, 4, 9, 'Time Out', '2026-07-21 17:00:00'),
(65, 5, 10, 'Time In', '2026-07-21 08:00:00'),
(66, 5, 10, 'Time Out', '2026-07-21 17:00:00'),
(67, 6, 11, 'Time In', '2026-07-21 08:05:00'),
(68, 6, 11, 'Time Out', '2026-07-21 17:00:00'),
(69, 7, 12, 'Time In', '2026-07-21 08:20:00'),
(70, 7, 12, 'Time Out', '2026-07-21 17:00:00'),
(71, 4, 43, 'Time In', '2026-07-31 14:37:19'),
(72, 4, 43, 'Time Out', '2026-07-31 14:38:44'),
(73, 4, 44, 'Time In', '2026-07-31 14:49:40'),
(74, 4, 44, 'Break Out', '2026-07-31 14:50:20'),
(75, 4, 44, 'Break In', '2026-07-31 14:50:38'),
(76, 4, 44, 'Time Out', '2026-07-31 14:51:13'),
(77, 4, 45, 'Time In', '2026-07-31 14:52:38'),
(78, 4, 45, 'Break Out', '2026-07-31 14:52:43'),
(79, 4, 45, 'Break In', '2026-07-31 14:53:22'),
(80, 4, 45, 'Time Out', '2026-07-31 14:53:35'),
(81, 4, 46, 'Time In', '2026-07-31 15:01:10'),
(82, 4, 46, 'Time Out', '2026-07-31 15:01:17'),
(85, 4, 48, 'Time In', '2026-08-12 08:36:35'),
(86, 4, 48, 'Time Out', '2026-08-12 08:38:18'),
(87, 4, 49, 'Time In', '2026-09-01 16:02:45'),
(88, 4, 49, 'Break Out', '2026-09-01 16:02:52'),
(89, 4, 49, 'Break In', '2026-09-01 16:03:01'),
(90, 4, 49, 'Time Out', '2026-09-01 16:03:07');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `setting_id` int(11) NOT NULL,
  `time_in_start` time DEFAULT NULL,
  `time_in_end` time DEFAULT NULL,
  `late_until` time DEFAULT NULL,
  `absent_time` time DEFAULT NULL,
  `break_out_time` time DEFAULT NULL,
  `break_in_time` time DEFAULT NULL,
  `time_out_time` time DEFAULT NULL,
  `required_hours` decimal(4,2) DEFAULT NULL,
  `break_minutes` int(11) DEFAULT NULL,
  `minimum_ot` int(11) DEFAULT NULL,
  `maximum_ot` int(11) DEFAULT NULL,
  `work_days` varchar(100) DEFAULT 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_settings`
--

INSERT INTO `attendance_settings` (`setting_id`, `time_in_start`, `time_in_end`, `late_until`, `absent_time`, `break_out_time`, `break_in_time`, `time_out_time`, `required_hours`, `break_minutes`, `minimum_ot`, `maximum_ot`, `work_days`) VALUES
(1, '00:00:00', '23:59:00', '23:59:00', '23:59:59', '12:00:00', '12:30:00', '00:00:00', 0.01, 30, 1, 4, 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_timeline`
--

CREATE TABLE `attendance_timeline` (
  `timeline_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `activity` enum('Time In','Break Out','Break In','Time Out','OT Start','OT End') DEFAULT NULL,
  `activity_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_timeline`
--

INSERT INTO `attendance_timeline` (`timeline_id`, `attendance_id`, `employee_id`, `activity`, `activity_time`, `created_at`) VALUES
(1, 35, 4, 'Time In', '2026-07-29 22:41:20', '2026-07-29 14:41:20'),
(2, 35, 4, 'Break Out', '2026-07-29 22:41:35', '2026-07-29 14:41:35'),
(3, 35, 4, 'Break In', '2026-07-29 22:41:54', '2026-07-29 14:41:54'),
(4, 35, 4, 'Time Out', '2026-07-29 22:41:59', '2026-07-29 14:41:59'),
(5, 36, 4, 'Time In', '2026-07-29 22:42:44', '2026-07-29 14:42:44'),
(6, 36, 4, 'Time Out', '2026-07-29 22:45:57', '2026-07-29 14:45:57'),
(7, 37, 4, 'Time In', '2026-07-29 22:46:34', '2026-07-29 14:46:34'),
(8, 37, 4, 'Break Out', '2026-07-29 23:07:59', '2026-07-29 15:07:59'),
(9, 37, 4, 'Break In', '2026-07-29 23:08:20', '2026-07-29 15:08:20'),
(10, 37, 4, 'Time Out', '2026-07-29 23:08:29', '2026-07-29 15:08:29'),
(11, 38, 4, 'Time In', '2026-07-29 23:10:26', '2026-07-29 15:10:26'),
(12, 39, 4, 'Time In', '2026-07-29 23:10:52', '2026-07-29 15:10:52'),
(13, 40, 4, 'Time In', '2026-07-30 00:06:10', '2026-07-29 16:06:10'),
(14, 41, 4, 'Time In', '2026-07-30 01:37:07', '2026-07-29 17:37:07'),
(15, 42, 4, 'Time In', '2026-07-31 13:38:51', '2026-07-31 05:38:51'),
(16, 43, 4, 'Time In', '2026-07-31 14:37:19', '2026-07-31 06:37:19'),
(17, 43, 4, 'Time Out', '2026-07-31 14:38:44', '2026-07-31 06:38:44'),
(18, 44, 4, 'Time In', '2026-07-31 14:49:40', '2026-07-31 06:49:40'),
(19, 44, 4, 'Break Out', '2026-07-31 14:50:20', '2026-07-31 06:50:20'),
(20, 44, 4, 'Break In', '2026-07-31 14:50:38', '2026-07-31 06:50:38'),
(21, 44, 4, 'Time Out', '2026-07-31 14:51:13', '2026-07-31 06:51:13'),
(22, 45, 4, 'Time In', '2026-07-31 14:52:38', '2026-07-31 06:52:38'),
(23, 45, 4, 'Break Out', '2026-07-31 14:52:43', '2026-07-31 06:52:43'),
(24, 45, 4, 'Break In', '2026-07-31 14:53:22', '2026-07-31 06:53:22'),
(25, 45, 4, 'Time Out', '2026-07-31 14:53:35', '2026-07-31 06:53:35'),
(26, 46, 4, 'Time In', '2026-07-31 15:01:10', '2026-07-31 07:01:10'),
(27, 46, 4, 'Time Out', '2026-07-31 15:01:17', '2026-07-31 07:01:17'),
(28, 47, 4, 'Time In', '2026-08-12 08:32:39', '2026-08-12 00:32:39'),
(29, 47, 4, 'Time Out', '2026-08-12 08:33:12', '2026-08-12 00:33:12'),
(30, 48, 4, 'Time In', '2026-08-12 08:36:35', '2026-08-12 00:36:35'),
(31, 48, 4, 'Time Out', '2026-08-12 08:38:18', '2026-08-12 00:38:18'),
(32, 49, 4, 'Time In', '2026-09-01 16:02:45', '2026-09-01 08:02:45'),
(33, 49, 4, 'Break Out', '2026-09-01 16:02:52', '2026-09-01 08:02:52'),
(34, 49, 4, 'Break In', '2026-09-01 16:03:01', '2026-09-01 08:03:01'),
(35, 49, 4, 'Time Out', '2026-09-01 16:03:07', '2026-09-01 08:03:07');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `activity`, `module`, `action_type`, `date_created`) VALUES
(1, 2, 'Added allowance records for employees', NULL, NULL, '2026-07-21 15:22:54'),
(2, 2, 'Added employee deduction records', NULL, NULL, '2026-07-21 15:22:54'),
(3, 2, 'Updated attendance records', NULL, NULL, '2026-07-21 15:22:54'),
(4, 2, 'Created attendance logs', NULL, NULL, '2026-07-21 15:22:54'),
(5, 1, 'Reviewed employee management records', NULL, NULL, '2026-07-21 15:22:54'),
(6, 1, 'Checked HRMS employee data', NULL, NULL, '2026-07-21 15:22:54');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `contract_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Expired') DEFAULT 'Active',
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`contract_id`, `employee_id`, `contract_type`, `start_date`, `end_date`, `salary`, `description`, `status`, `file`, `created_at`) VALUES
(4, 4, 'Regular', '2026-07-21', '2027-07-21', 20000.00, 'Regular employment contract for production staff.', 'Expired', 'Nicole_Garcia_Contract.pdf', '2026-07-21 15:57:15'),
(5, 6, 'Regular', '2026-07-21', '2027-07-21', 22000.00, 'Regular employment contract for marketing assistant.', 'Active', 'Joshua_Reyes_Contract.pdf', '2026-07-21 15:57:15'),
(6, 7, 'Probationary', '2026-07-21', '2027-01-21', 23000.00, 'Probationary employment contract for payroll staff.', 'Active', 'Angela_Santos_Contract.pdf', '2026-07-21 15:57:15');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `deduction_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `deduction_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`deduction_id`, `employee_id`, `deduction_name`, `amount`, `created_at`) VALUES
(1, 3, 'SSS Contribution', 900.00, '2026-07-21 15:21:06'),
(2, 3, 'PhilHealth Contribution', 500.00, '2026-07-21 15:21:06'),
(3, 4, 'SSS Contribution', 800.00, '2026-07-21 15:21:06'),
(4, 4, 'Pag-IBIG Contribution', 300.00, '2026-07-21 15:21:06'),
(5, 5, 'SSS Contribution', 900.00, '2026-07-21 15:21:06'),
(6, 5, 'Tax Deduction', 700.00, '2026-07-21 15:21:06'),
(7, 6, 'SSS Contribution', 850.00, '2026-07-21 15:21:06'),
(8, 6, 'PhilHealth Contribution', 500.00, '2026-07-21 15:21:06'),
(9, 7, 'SSS Contribution', 900.00, '2026-07-21 15:21:06'),
(10, 7, 'Pag-IBIG Contribution', 300.00, '2026-07-21 15:21:06'),
(11, 8, 'SSS Contribution', 850.00, '2026-07-21 15:21:06'),
(12, 8, 'Tax Deduction', 600.00, '2026-07-21 15:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `created_at`, `status`) VALUES
(1, 'Human Resources', 'Handles recruitment, employee relations and HR operations.', '2026-07-18 14:42:54', 'Active'),
(2, 'Production', 'Responsible for food preparation and production.', '2026-07-18 14:42:54', 'Active'),
(3, 'Finance', 'Handles payroll, accounting and company finances.', '2026-07-18 14:42:54', 'Active'),
(4, 'Marketing', 'Handles company promotions, branding, and customer engagement activities.', '2026-07-21 14:47:58', 'Active'),
(5, 'Inventory', 'Manages stock monitoring, supplies, and inventory records.', '2026-07-21 14:47:58', 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `basic_salary` decimal(10,2) DEFAULT 0.00,
  `employment_status` enum('Active','Inactive','On Leave','Suspended','Resigned','Terminated') DEFAULT 'Active',
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `birthdate`, `gender`, `civil_status`, `address`, `department_id`, `position_id`, `basic_salary`, `employment_status`, `hire_date`, `created_at`) VALUES
(3, 'DCI-EMP-0003', 'Kevin', 'Louie', 'Dela Cruz', 'employee1@dailycravings.com', '09191234569', '1998-11-03', 'Male', 'Single', 'Bacoor City, Cavite', 1, 1, 24000.00, 'Active', '2025-03-10', '2026-07-18 15:11:52'),
(4, 'DCI-EMP-0004', 'Nicole', 'Mae', 'Garcia', 'dailycravings.hrms@gmail.com', '09201234572', '1997-08-13', 'Female', 'Married', 'General Trias, Cavite', 2, 2, 20000.00, 'Active', '2025-04-05', '2026-07-18 15:11:52'),
(5, 'DCI-EMP-0005', 'Sophia', 'Mae', 'Navarro', 'dailycravings.hrms@gmail.com', '09192345678', '2001-09-28', 'Female', 'Single', 'Imus City, Cavite', 3, 3, 23000.00, 'Active', '2026-07-20', '2026-07-20 13:48:40'),
(6, 'DCI-EMP-0006', 'Joshua', 'Miguel', 'Reyes', 'employee3@dailycravings.com', '09193456781', '1999-02-15', 'Male', 'Single', 'Dasmarinas, Cavite', 4, 4, 22000.00, 'Active', '2026-07-21', '2026-07-21 14:33:37'),
(7, 'DCI-EMP-0007', 'Angela', 'Marie', 'Santos', 'employee4@dailycravings.com', '09193456782', '2000-06-21', 'Female', 'Single', 'Imus City, Cavite', 3, 3, 23000.00, 'Active', '2026-07-21', '2026-07-21 14:33:37'),
(8, 'DCI-EMP-0008', 'Mark', 'Daniel', 'Flores', 'employee5@dailycravings.com', '09193456783', '1998-12-10', 'Male', 'Married', 'Bacoor City, Cavite', 2, 2, 21000.00, 'Resigned', '2026-07-21', '2026-07-21 14:33:37'),
(12, 'DCI-EMP-0009', 'Miguel', 'Antonio', 'Torres', 'miguel.torres@gmail.com', '09184567895', '1998-09-25', 'Male', 'Married', 'Tagaytay City, Cavite', 5, 5, 0.00, 'Active', '2026-07-21', '2026-07-21 16:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `halfday_requests`
--

CREATE TABLE `halfday_requests` (
  `request_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `type` enum('Morning','Afternoon') DEFAULT 'Afternoon',
  `halfday_type` enum('Morning','Afternoon') DEFAULT NULL,
  `reason` text NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `holiday_id` int(11) NOT NULL,
  `holiday_name` varchar(100) DEFAULT NULL,
  `holiday_date` date DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `interview_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `interview_date` date DEFAULT NULL,
  `interview_time` time DEFAULT NULL,
  `stage` enum('Initial','Final') DEFAULT NULL,
  `interviewer` varchar(100) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `interview_result` enum('Pending','Passed','Failed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reschedule_reason` text DEFAULT NULL,
  `previous_date` date DEFAULT NULL,
  `previous_time` time DEFAULT NULL,
  `email_sent` enum('Yes','No') DEFAULT 'No',
  `email_sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`interview_id`, `application_id`, `interview_date`, `interview_time`, `stage`, `interviewer`, `status`, `interview_result`, `created_at`, `reschedule_reason`, `previous_date`, `previous_time`, `email_sent`, `email_sent_at`) VALUES
(14, 13, '2026-07-25', '09:00:00', 'Initial', 'Althea Lois Sinahon', 'Scheduled', 'Pending', '2026-07-21 16:04:11', NULL, NULL, NULL, 'Yes', '2026-07-22 01:00:00'),
(15, 14, '2026-07-26', '10:30:00', 'Final', 'Althea Lois Sinahon', 'Scheduled', 'Pending', '2026-07-21 16:04:11', NULL, NULL, NULL, 'Yes', '2026-07-22 01:30:00'),
(16, 15, '2026-07-27', '01:00:00', 'Final', 'Althea Lois Sinahon', 'Completed', 'Passed', '2026-07-21 16:04:11', NULL, NULL, NULL, 'Yes', '2026-07-22 02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `job_id` int(11) NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `employment_type` enum('Full-Time','Part-Time','Contractual','Probationary','Internship') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `vacancies` int(11) DEFAULT 1,
  `requirements` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `status` enum('Open','Closed') DEFAULT 'Open',
  `posted_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_postings`
--

INSERT INTO `job_postings` (`job_id`, `position_id`, `department_id`, `job_title`, `salary`, `employment_type`, `description`, `location`, `vacancies`, `requirements`, `responsibilities`, `status`, `posted_date`, `closing_date`, `created_at`) VALUES
(4, 3, 1, 'HR Assistant', 22000.00, 'Full-Time', 'Supports HR operations including recruitment, employee records, and documentation.', 'Dasmarinas City, Cavite', 2, 'Bachelor degree in Human Resource Management or related course. Good communication skills.', 'Assist recruitment process, maintain employee files, and support HR activities.', 'Open', '2026-07-21', '2026-08-15', '2026-07-21 15:11:08'),
(5, 2, 2, 'Food Production Staff', 20000.00, 'Full-Time', 'Responsible for food preparation, production, and maintaining quality standards.', 'Dasmarinas City, Cavite', 5, 'Senior High School graduate. Experience in food handling is an advantage.', 'Prepare food products, maintain cleanliness, and follow production procedures.', 'Open', '2026-07-21', '2026-08-20', '2026-07-21 15:11:08'),
(6, 3, 3, 'Payroll Specialist', 25000.00, 'Full-Time', 'Handles employee payroll processing and payroll documentation.', 'Dasmarinas City, Cavite', 1, 'Bachelor degree in Accounting, Finance, or related field.', 'Process salaries, prepare payroll reports, and maintain payroll records.', 'Open', '2026-07-21', '2026-08-30', '2026-07-21 15:11:08'),
(7, 4, 4, 'Marketing Assistant', 23000.00, 'Full-Time', 'Assists in company marketing campaigns and promotional activities.', 'Imus City, Cavite', 2, 'Degree in Marketing or related course. Creative and organized.', 'Create marketing materials, assist campaigns, and manage promotions.', 'Open', '2026-07-21', '2026-08-25', '2026-07-21 15:11:08'),
(8, 5, 5, 'Inventory Staff', 21000.00, 'Probationary', 'Manages inventory records, stock monitoring, and supplies.', 'Bacoor City, Cavite', 2, 'Senior High School graduate. Knowledge in inventory management preferred.', 'Monitor stocks, update inventory records, and coordinate supplies.', 'Open', '2026-07-21', '2026-08-25', '2026-07-21 15:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type_id` int(11) DEFAULT NULL,
  `custom_leave_type` varchar(150) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_days` decimal(5,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancelled_at` datetime DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `employee_id`, `leave_type_id`, `custom_leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `attachment`, `status`, `approved_by`, `approved_at`, `remarks`, `created_at`, `cancelled_at`, `cancelled_reason`) VALUES
(4, 3, 2, NULL, '2026-07-25', '2026-07-27', NULL, 'Family vacation and personal rest.', NULL, 'Rejected', 2, '2026-07-22 09:00:00', 'Approved by HR', '2026-07-21 15:48:22', NULL, NULL),
(5, 6, 3, NULL, '2026-07-23', '2026-07-23', NULL, 'Urgent family emergency that requires absence.', NULL, 'Pending', NULL, NULL, 'Waiting for HR approval', '2026-07-21 15:48:22', NULL, NULL),
(6, 8, 1, NULL, '2026-07-22', '2026-07-23', NULL, 'Not feeling well and needs medical rest.', NULL, 'Approved', 2, '2026-07-22 10:30:00', 'Approved medical leave', '2026-07-21 15:48:22', NULL, NULL),
(12, 4, 3, NULL, '2026-07-30', '2026-08-05', 5.00, 'test ko lng pooo', NULL, 'Pending', NULL, NULL, NULL, '2026-07-30 15:22:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `leave_type_id` int(11) NOT NULL,
  `leave_type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `allow_custom_reason` enum('Yes','No') DEFAULT 'No',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`leave_type_id`, `leave_type_name`, `description`, `status`, `allow_custom_reason`, `created_at`) VALUES
(1, 'Sick Leave', 'Leave provided when an employee is sick or unable to work due to health reasons.', 'Active', 'No', '2026-07-21 08:04:01'),
(2, 'Vacation Leave', 'Leave for personal rest, travel, or vacation purposes.', 'Active', 'No', '2026-07-21 08:04:01'),
(3, 'Emergency Leave', 'Leave for urgent and unexpected personal emergencies.', 'Active', 'No', '2026-07-21 08:04:01'),
(4, 'Maternity Leave', 'Leave granted to female employees for childbirth and recovery.', 'Active', 'No', '2026-07-21 08:04:01'),
(5, 'Paternity Leave', 'Leave granted to male employees for childbirth support.', 'Active', 'No', '2026-07-21 08:04:01'),
(6, 'Bereavement Leave', 'Leave provided due to death of an immediate family member.', 'Active', 'No', '2026-07-21 08:04:01'),
(7, 'Medical Leave', 'Leave required for medical treatment, consultation, or recovery.', 'Active', 'No', '2026-07-21 08:04:01'),
(8, 'Personal Leave', 'Leave for personal matters that require employee absence.', 'Active', 'No', '2026-07-21 08:04:01'),
(9, 'Unpaid Leave', 'Leave without salary compensation.', 'Active', 'No', '2026-07-21 08:04:01'),
(10, 'Other Leave', 'Other approved leave categories not listed above.', 'Active', 'Yes', '2026-07-21 08:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `attempt_id` int(11) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `attempt_count` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `status` enum('Unread','Read') DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overtime_requests`
--

CREATE TABLE `overtime_requests` (
  `request_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `overtime_date` date NOT NULL,
  `requested_hours` enum('02:00:00','04:00:00') DEFAULT NULL,
  `approved_hours` time DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `pay_period` varchar(50) DEFAULT NULL,
  `basic_salary` decimal(10,2) DEFAULT NULL,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `gross_pay` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) DEFAULT NULL,
  `status` enum('Draft','Generated','Paid') DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `employee_id`, `pay_period`, `basic_salary`, `allowances`, `deductions`, `gross_pay`, `net_pay`, `status`, `created_at`) VALUES
(6, 5, '2026-07', 24000.00, 2500.00, 1600.00, 26500.00, 24900.00, 'Draft', '2026-07-21 15:54:02'),
(7, 6, '2026-07', 22000.00, 3200.00, 1350.00, 25200.00, 23850.00, 'Generated', '2026-07-21 15:54:02'),
(8, 7, '2026-07', 23000.00, 2700.00, 1200.00, 25700.00, 24500.00, 'Paid', '2026-07-21 15:54:02'),
(10, 4, 'July 2026', 20000.00, 2000.00, 500.00, 22000.00, 21500.00, 'Paid', '2026-07-29 19:30:09');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `payslip_id` int(11) NOT NULL,
  `payroll_id` int(11) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `generated_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`payslip_id`, `payroll_id`, `file`, `generated_date`, `created_at`) VALUES
(1, 6, 'Sophia_Navarro_Payslip.pdf', '2026-07-21', '2026-07-21 15:55:40'),
(2, 7, 'Joshua_Reyes_Payslip.pdf', '2026-07-21', '2026-07-21 15:55:40'),
(3, 8, 'Angela_Santos_Payslip.pdf', '2026-07-21', '2026-07-21 15:55:40'),
(8, 10, 'Nicole_Garcia_Payslip.pdf', '2026-07-30', '2026-07-29 19:36:25');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `department_id`, `position_name`, `description`, `status`, `created_at`) VALUES
(1, 1, 'HR Assistant', 'Supports recruitment and employee documentation.', 'Inactive', '2026-07-18 15:11:52'),
(2, 2, 'Food Production Staff', 'Prepares food products while maintaining quality standards.', 'Active', '2026-07-18 15:11:52'),
(3, 3, 'Payroll Staff', 'Processes employee salaries and payroll reports.', 'Active', '2026-07-18 15:11:52'),
(4, 4, 'Marketing Assistant', 'Assists with company promotions, social media campaigns, and marketing activities.', 'Inactive', '2026-07-21 14:52:00'),
(5, 5, 'Inventory Staff', 'Handles stock monitoring, inventory records, and supply management.', 'Inactive', '2026-07-21 14:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `report_logs`
--

CREATE TABLE `report_logs` (
  `report_id` int(11) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_logs`
--

INSERT INTO `report_logs` (`report_id`, `generated_by`, `report_type`, `generated_at`) VALUES
(1, 2, 'Employee Report', '2026-07-21 23:21:56'),
(2, 2, 'Employee Report', '2026-07-21 23:23:52'),
(3, 2, 'Employee Report', '2026-07-21 23:45:47'),
(4, 2, 'Employee Report', '2026-07-21 23:46:43'),
(5, 2, 'Employee Report', '2026-07-21 23:48:22'),
(6, 2, 'Employee Report', '2026-07-21 23:48:51'),
(7, 2, 'Employee Report', '2026-07-21 23:50:53'),
(8, 2, 'Employee Report', '2026-07-21 23:53:28'),
(9, 2, 'Attendance Report', '2026-07-22 00:07:01');

-- --------------------------------------------------------

--
-- Table structure for table `resignations`
--

CREATE TABLE `resignations` (
  `resignation_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `resignation_date` date DEFAULT NULL,
  `last_working_day` date DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resignations`
--

INSERT INTO `resignations` (`resignation_id`, `employee_id`, `reason`, `resignation_date`, `last_working_day`, `status`, `remarks`, `approved_by`, `approved_date`, `created_at`, `updated_at`) VALUES
(1, 6, 'Personal reasons and career growth opportunity outside the company.', '2026-08-15', '2026-08-31', 'Pending', 'Resignation approved after HR review.', 2, NULL, '2026-07-21 16:07:12', '2026-07-21 21:10:49'),
(2, 8, 'Relocating to another city and unable to continue employment.', '2026-07-31', '2026-08-15', 'Approved', 'Approved by HR. Employee will complete turnover process.', 2, '2026-07-22 00:15:25', '2026-07-21 16:07:12', '2026-07-21 22:15:48'),
(6, 4, 'Test ko lng poo', '2026-08-03', '2026-08-07', 'Pending', NULL, NULL, NULL, '2026-07-30 23:21:33', '2026-07-30 23:21:33');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_name` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','hr','employee') NOT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','locked') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `employee_id`, `full_name`, `email`, `password`, `role`, `gender`, `profile_picture`, `status`, `last_login`, `created_at`) VALUES
(1, NULL, 'Lawrence Hernandez', 'admin@dailycravings.com', '$2y$10$8PzXW.rmVMF1h/dw2HawJeKLDtjD9pKlmVp1h7Leau/72Zf43X5rK', 'admin', 'Male', NULL, 'active', '2026-09-01 16:35:35', '2026-07-18 15:11:52'),
(2, NULL, 'Althea Lois Sinahon', 'hr@dailycravings.com', '$2y$10$yJV2BiqDssceera/Ygn8ZOTQQK8TcfSxPTBMQU/dFkfvxwXphDwS2', 'hr', 'Female', NULL, 'active', '2026-09-01 16:34:59', '2026-07-18 15:11:52'),
(3, 3, 'Kevin Louie Dela Cruz', 'employee1@dailycravings.com', '$2y$10$0pZ0VKjmCzQmMczcXRr.SetaYk8YwGCst7JudXbsMwLhx1sxxneam', 'employee', NULL, NULL, 'active', NULL, '2026-07-18 15:11:52'),
(4, 4, 'Nicole Mae Garcia', 'dailycravings.hrms@gmail.com', '$2y$10$orLTlHa5Tski5N9vDLy2NOwgKifKKRByNxmUx9CKzFm28u0jMYlNe', 'employee', 'Female', '', 'active', '2026-09-01 16:35:52', '2026-07-18 15:11:52'),
(16, 6, 'Joshua Miguel Reyes', 'employee3@dailycravings.com', '$2y$10$AORASbXQAkiXMOM7BIEKOusZyxatYZhcjyaUkey2poxzG9aRaKN6S', 'employee', NULL, NULL, 'active', NULL, '2026-07-21 14:36:37'),
(17, 7, 'Angela Marie Santos', 'employee4@dailycravings.com', '$2y$10$1aJVngcbQH7BrGtlNpqn1OznKIABErON680DXhWxHzPZp9101eYHi', 'employee', NULL, NULL, 'active', NULL, '2026-07-21 14:36:37'),
(18, 8, 'Mark Daniel Flores', 'employee5@dailycravings.com', '$2y$10$xQmBUHsaaAVtxzUWhV.HO.SkPT8ydJn7dG3npmTdXneN07sVPPZkq', 'employee', NULL, NULL, 'active', NULL, '2026-07-21 14:36:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`allowance_id`),
  ADD KEY `fk_allowance_employee` (`employee_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `fk_application_job` (`job_id`),
  ADD KEY `idx_application_status` (`status`),
  ADD KEY `fk_application_employee` (`employee_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_employee_daily_attendance` (`employee_id`,`attendance_date`),
  ADD KEY `idx_attendance_date` (`attendance_date`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `attendance_id` (`attendance_id`);

--
-- Indexes for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `attendance_timeline`
--
ALTER TABLE `attendance_timeline`
  ADD PRIMARY KEY (`timeline_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_audit_user` (`user_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `fk_contract_employee` (`employee_id`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`deduction_id`),
  ADD KEY `fk_deduction_employee` (`employee_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `idx_employee_department` (`department_id`),
  ADD KEY `idx_employee_position` (`position_id`),
  ADD KEY `idx_employee_status` (`employment_status`);

--
-- Indexes for table `halfday_requests`
--
ALTER TABLE `halfday_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`holiday_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `fk_interview_application` (`application_id`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `fk_job_position` (`position_id`),
  ADD KEY `fk_job_department` (`department_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `fk_leave_employee` (`employee_id`),
  ADD KEY `fk_leave_requests_type` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`leave_type_id`),
  ADD UNIQUE KEY `leave_type_name` (`leave_type_name`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`attempt_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notification_user` (`user_id`);

--
-- Indexes for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD UNIQUE KEY `unique_employee_period` (`employee_id`,`pay_period`),
  ADD KEY `idx_payroll_period` (`pay_period`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`payslip_id`),
  ADD KEY `fk_payslip_payroll` (`payroll_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`),
  ADD KEY `fk_position_department` (`department_id`);

--
-- Indexes for table `report_logs`
--
ALTER TABLE `report_logs`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_report_logs_user` (`generated_by`);

--
-- Indexes for table `resignations`
--
ALTER TABLE `resignations`
  ADD PRIMARY KEY (`resignation_id`),
  ADD KEY `fk_resignation_employee` (`employee_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_employee` (`employee_id`),
  ADD KEY `idx_user_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `allowance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_timeline`
--
ALTER TABLE `attendance_timeline`
  MODIFY `timeline_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `deduction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `halfday_requests`
--
ALTER TABLE `halfday_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `holiday_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `interview_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `leave_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `payslip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `report_logs`
--
ALTER TABLE `report_logs`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `resignations`
--
ALTER TABLE `resignations`
  MODIFY `resignation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allowances`
--
ALTER TABLE `allowances`
  ADD CONSTRAINT `fk_allowance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_application_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `fk_application_job` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `fk_contract_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `deductions`
--
ALTER TABLE `deductions`
  ADD CONSTRAINT `fk_deduction_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employee_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `fk_interview_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `fk_job_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_job_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_requests_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`leave_type_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`leave_type_id`) ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `fk_payslip_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`payroll_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `fk_position_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report_logs`
--
ALTER TABLE `report_logs`
  ADD CONSTRAINT `fk_report_logs_user` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `resignations`
--
ALTER TABLE `resignations`
  ADD CONSTRAINT `fk_resignation_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
