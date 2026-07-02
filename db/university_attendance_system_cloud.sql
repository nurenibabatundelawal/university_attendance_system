-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2026 at 11:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `university_attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_sessions`
--

CREATE TABLE `academic_sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_sessions`
--

INSERT INTO `academic_sessions` (`id`, `session_name`, `created_at`) VALUES
(1, '2026/2027', '2026-06-23 14:17:51'),
(2, '2027/2028', '2026-06-23 14:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `fullname`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'System Administrator', 'admin@example.com', '$2y$10$W.Zb0ahVxvQxhKBdpnA4te94WK/yFP8SNAYg6FHCGHPMJlWGco5hS', '08000000000', '2026-06-23 14:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_corrections`
--

CREATE TABLE `attendance_corrections` (
  `id` int(11) NOT NULL,
  `attendance_record_id` int(11) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `corrected_by` int(11) NOT NULL,
  `corrected_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `attendance_session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `verification_method` enum('Fingerprint','RFID','Manual') DEFAULT NULL,
  `attendance_status` enum('Present','Late','Absent') DEFAULT 'Present',
  `marked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `marked_by` varchar(50) DEFAULT 'device',
  `manual_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `attendance_session_id`, `student_id`, `course_id`, `verification_method`, `attendance_status`, `marked_at`, `marked_by`, `manual_reason`) VALUES
(8, 26, 6, 1, 'Fingerprint', 'Present', '2026-06-27 19:00:52', 'device', NULL),
(10, 28, 9, 1, 'Fingerprint', 'Present', '2026-06-28 20:03:02', 'device', NULL),
(11, 28, 5, 1, 'Fingerprint', 'Present', '2026-06-28 20:23:56', 'device', NULL),
(12, 29, 5, 1, 'Fingerprint', 'Present', '2026-06-28 20:39:32', 'device', NULL),
(13, 29, 9, 1, 'Fingerprint', 'Present', '2026-06-28 20:40:25', 'device', NULL),
(14, 30, 5, 1, 'RFID', 'Present', '2026-06-29 12:19:04', 'device', NULL),
(15, 30, 6, 1, 'Manual', 'Present', '2026-06-29 12:20:03', 'lecturer', NULL),
(16, 30, 9, 1, 'Manual', 'Present', '2026-06-29 12:20:03', 'lecturer', NULL),
(17, 31, 5, 1, 'Fingerprint', 'Present', '2026-06-30 08:13:50', 'device', NULL),
(18, 31, 9, 1, 'Fingerprint', 'Present', '2026-06-30 08:14:07', 'device', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `academic_session_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('Active','Ended') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration_minutes` int(11) DEFAULT NULL,
  `auto_end_at` datetime DEFAULT NULL,
  `attendance_method` enum('fingerprint','rfid','both') DEFAULT 'both'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_sessions`
--

INSERT INTO `attendance_sessions` (`id`, `course_id`, `lecturer_id`, `academic_session_id`, `semester_id`, `session_date`, `start_time`, `end_time`, `status`, `created_at`, `duration_minutes`, `auto_end_at`, `attendance_method`) VALUES
(14, 1, 1, 1, 1, '2026-06-24', '21:39:51', '22:10:25', 'Ended', '2026-06-24 19:39:51', NULL, NULL, 'both'),
(15, 1, 1, 1, 1, '2026-06-24', '22:10:30', '22:14:40', 'Ended', '2026-06-24 20:10:30', NULL, NULL, 'both'),
(16, 1, 1, 1, 1, '2026-06-24', '22:14:35', '22:16:20', 'Ended', '2026-06-24 20:14:35', NULL, NULL, 'both'),
(17, 1, 1, 1, 1, '2026-06-24', '22:16:30', '22:36:10', 'Ended', '2026-06-24 20:16:30', NULL, NULL, 'both'),
(18, 1, 1, 1, 1, '2026-06-24', '22:16:39', '22:36:13', 'Ended', '2026-06-24 20:16:39', NULL, NULL, 'both'),
(19, 1, 1, 1, 1, '2026-06-24', '22:17:18', '22:36:16', 'Ended', '2026-06-24 20:17:18', NULL, NULL, 'both'),
(20, 1, 1, 1, 1, '2026-06-24', '22:37:09', '22:18:34', 'Ended', '2026-06-24 20:37:09', NULL, NULL, 'both'),
(21, 1, 1, 1, 1, '2026-06-25', '22:16:34', '22:18:37', 'Ended', '2026-06-25 20:16:34', 180, '2026-06-26 01:16:34', 'both'),
(22, 1, 1, 1, 1, '2026-06-25', '22:19:19', '22:27:33', 'Ended', '2026-06-25 20:19:19', 120, '2026-06-26 00:19:19', 'both'),
(23, 1, 1, 1, 1, '2026-06-25', '22:27:51', '23:09:38', 'Ended', '2026-06-25 20:27:51', 120, '2026-06-26 00:27:51', 'fingerprint'),
(25, 1, 1, 1, 1, '2026-06-25', '23:12:57', '09:20:59', 'Ended', '2026-06-25 21:12:57', 60, '2026-06-26 00:12:57', 'fingerprint'),
(26, 1, 1, 1, 1, '2026-06-27', '11:49:40', NULL, 'Ended', '2026-06-27 18:49:40', NULL, NULL, 'both'),
(27, 1, 1, 1, 1, '2026-06-28', '20:53:02', '20:54:48', 'Ended', '2026-06-28 18:53:02', 60, '2026-06-28 21:53:02', 'both'),
(28, 1, 1, 1, 1, '2026-06-28', '21:38:07', '22:31:03', 'Ended', '2026-06-28 19:38:07', 60, '2026-06-28 22:38:07', 'fingerprint'),
(29, 1, 1, 1, 1, '2026-06-28', '22:39:08', '14:16:44', 'Ended', '2026-06-28 20:39:08', 180, '2026-06-29 01:39:08', 'fingerprint'),
(30, 1, 1, 1, 1, '2026-06-29', '14:17:13', '01:13:34', 'Ended', '2026-06-29 12:17:13', 60, '2026-06-29 15:17:13', 'rfid'),
(31, 1, 1, 1, 1, '2026-06-30', '10:13:07', NULL, 'Active', '2026-06-30 08:13:07', 90, '2026-06-30 11:43:07', 'fingerprint');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_title` varchar(150) NOT NULL,
  `course_unit` int(11) NOT NULL,
  `level` varchar(20) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `department_id`, `course_code`, `course_title`, `course_unit`, `level`, `semester_id`, `created_at`) VALUES
(1, 1, 'CSC301', 'Embedded Systems', 3, '300', 1, '2026-06-23 19:21:08');

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `academic_session_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_registrations`
--

INSERT INTO `course_registrations` (`id`, `student_id`, `course_id`, `academic_session_id`, `semester_id`, `registered_at`) VALUES
(5, 6, 1, 1, 1, '2026-06-27 17:26:16'),
(6, 9, 1, 1, 1, '2026-06-28 20:02:00'),
(7, 5, 1, 1, 1, '2026-06-28 20:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `department_name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `faculty_id`, `department_name`, `created_at`) VALUES
(1, 1, 'Computer Engineering', '2026-06-23 19:19:57');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `device_code` varchar(100) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `last_seen` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_registration_requests`
--

CREATE TABLE `device_registration_requests` (
  `id` int(11) NOT NULL,
  `request_type` enum('fingerprint','rfid') NOT NULL,
  `fingerprint_id` int(11) DEFAULT NULL,
  `captured_value` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_registration_requests`
--

INSERT INTO `device_registration_requests` (`id`, `request_type`, `fingerprint_id`, `captured_value`, `status`, `created_at`, `completed_at`) VALUES
(1, 'rfid', 0, 'C7FB4E07', 'Completed', '2026-06-26 17:56:53', '2026-06-26 10:58:36'),
(2, 'fingerprint', 1, NULL, 'Cancelled', '2026-06-26 18:01:57', NULL),
(3, 'fingerprint', 1, NULL, 'Cancelled', '2026-06-26 18:03:21', NULL),
(4, 'fingerprint', 1, NULL, 'Cancelled', '2026-06-26 18:03:48', NULL),
(5, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-26 18:08:27', NULL),
(6, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-26 18:08:56', NULL),
(7, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-26 18:09:09', NULL),
(8, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-26 18:58:54', NULL),
(9, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-26 19:03:37', NULL),
(10, 'fingerprint', 3, NULL, 'Cancelled', '2026-06-26 19:06:08', NULL),
(11, 'fingerprint', 3, NULL, 'Cancelled', '2026-06-26 19:35:20', NULL),
(12, 'fingerprint', 3, '3', 'Completed', '2026-06-26 19:35:26', '2026-06-26 12:37:46'),
(13, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-28 19:11:34', NULL),
(14, 'fingerprint', 2, '2', 'Completed', '2026-06-28 19:11:39', '2026-06-28 12:55:54'),
(15, 'fingerprint', 25, NULL, 'Pending', '2026-06-29 19:21:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `device_settings`
--

CREATE TABLE `device_settings` (
  `id` int(11) NOT NULL,
  `device_code` varchar(100) NOT NULL,
  `wifi_ssid_1` varchar(150) DEFAULT NULL,
  `wifi_pass_1` varchar(150) DEFAULT NULL,
  `wifi_ssid_2` varchar(150) DEFAULT NULL,
  `wifi_pass_2` varchar(150) DEFAULT NULL,
  `api_base_url` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_settings`
--

INSERT INTO `device_settings` (`id`, `device_code`, `wifi_ssid_1`, `wifi_pass_1`, `wifi_ssid_2`, `wifi_pass_2`, `api_base_url`, `api_key`, `is_active`, `updated_at`) VALUES
(1, 'ESP32-C3-DEVICE-001', '', '', '5g', 'Sweet01@', 'http://10.35.23.201/university_attendance_system/api', 'CHANGE_THIS_SECRET_KEY_12345', 1, '2026-06-28 19:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_requests`
--

CREATE TABLE `enrollment_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_type` enum('fingerprint','rfid') NOT NULL,
  `fingerprint_id` int(11) DEFAULT NULL,
  `rfid_uid` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_requests`
--

INSERT INTO `enrollment_requests` (`id`, `student_id`, `enrollment_type`, `fingerprint_id`, `rfid_uid`, `status`, `created_at`, `completed_at`) VALUES
(2, 5, 'fingerprint', 0, NULL, 'Completed', '2026-06-28 18:51:02', NULL),
(3, 8, 'fingerprint', 0, NULL, 'Cancelled', '2026-06-28 18:57:22', NULL),
(4, 8, 'fingerprint', 0, NULL, 'Cancelled', '2026-06-28 19:03:20', NULL),
(5, 8, 'fingerprint', 1, NULL, 'Cancelled', '2026-06-28 19:03:42', NULL),
(6, 8, 'fingerprint', 4, NULL, 'Cancelled', '2026-06-28 19:05:38', NULL),
(7, 9, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-28 19:09:48', NULL),
(8, 9, 'fingerprint', 2, NULL, 'Cancelled', '2026-06-28 19:36:23', NULL),
(9, 6, 'fingerprint', 3, NULL, 'Cancelled', '2026-06-28 20:25:29', NULL),
(10, 6, 'fingerprint', 3, NULL, 'Cancelled', '2026-06-28 20:26:54', NULL),
(11, 8, 'fingerprint', 5, NULL, 'Cancelled', '2026-06-28 20:32:22', NULL),
(12, 6, 'fingerprint', 5, NULL, 'Cancelled', '2026-06-28 20:42:05', NULL),
(13, 6, 'fingerprint', 6, NULL, 'Cancelled', '2026-06-28 20:43:03', NULL),
(14, 10, 'fingerprint', 31, NULL, 'Cancelled', '2026-06-28 20:44:47', NULL),
(15, 11, 'fingerprint', 25, NULL, 'Pending', '2026-06-29 12:12:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` int(11) NOT NULL,
  `faculty_name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `faculty_name`, `created_at`) VALUES
(1, 'Faculty of Engineering', '2026-06-23 19:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `fingerprint_templates`
--

CREATE TABLE `fingerprint_templates` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fingerprint_id` int(11) NOT NULL,
  `template_data` text DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`id`, `staff_id`, `fullname`, `email`, `password`, `phone`, `department_id`, `status`, `created_at`) VALUES
(1, 'LEC001', 'Dr John Doe', 'john@example.com', '123456', '09187654321', 1, 'Active', '2026-06-23 19:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_courses`
--

CREATE TABLE `lecturer_courses` (
  `id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `academic_session_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_courses`
--

INSERT INTO `lecturer_courses` (`id`, `lecturer_id`, `course_id`, `academic_session_id`, `semester_id`, `created_at`) VALUES
(1, 1, 1, 1, 1, '2026-06-23 19:24:48');

-- --------------------------------------------------------

--
-- Table structure for table `offline_sync_logs`
--

CREATE TABLE `offline_sync_logs` (
  `id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `attendance_session_id` int(11) DEFAULT NULL,
  `student_identifier` varchar(100) NOT NULL,
  `verification_method` enum('Fingerprint','RFID') NOT NULL,
  `sync_status` enum('Pending','Synced','Failed') DEFAULT 'Pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `synced_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rfid_uid` varchar(100) NOT NULL,
  `status` enum('Active','Inactive','Lost') DEFAULT 'Active',
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `semester_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `semester_name`, `created_at`) VALUES
(1, 'First Semester', '2026-06-23 14:17:51'),
(2, 'Second Semester', '2026-06-23 14:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `matric_no` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `level` varchar(20) NOT NULL,
  `academic_session_id` int(11) NOT NULL,
  `fingerprint_id` int(11) DEFAULT NULL,
  `rfid_uid` varchar(100) DEFAULT NULL,
  `passport` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `matric_no`, `fullname`, `email`, `password`, `phone`, `gender`, `department_id`, `level`, `academic_session_id`, `fingerprint_id`, `rfid_uid`, `passport`, `status`, `created_at`) VALUES
(5, '22/2026', 'NURENI BABATUNDE LAWAL', 'nurenibabatundelawal@gmail.com', '0987654321', NULL, NULL, 1, '200', 1, 3, 'C7FB4E07', '123456789', 'Active', '2026-06-25 20:14:19'),
(6, 'CSC/2026/003', 'Aisha Bello', 'aisha.bello@example.com', '123456', '08087654321', 'Female', 1, '300', 1, 6, NULL, '', 'Active', '2026-06-27 16:49:48'),
(7, '22/2027', 'NURENI BABATUNDE LAWAL', 'baba@gmail.com', '123456', NULL, NULL, 1, '100', 2, 1, 'C7FB4E08', NULL, 'Active', '2026-06-28 18:47:54'),
(8, '22/2028', 'NURENI BABATUNDE LAWAL', 'babatunde@gamil.com', '123456', NULL, NULL, 1, '100', 2, 4, NULL, NULL, 'Active', '2026-06-28 18:57:00'),
(9, '22/2029', 'NURENI BABATUNDE LAWAL', 'nurenibabatundelawal1@gmail.com', '123456', NULL, NULL, 1, '100', 2, 2, NULL, NULL, 'Active', '2026-06-28 19:09:31'),
(10, '22/2031', 'anu', 'anu@gmail.com', '123456', NULL, NULL, 1, '100', 2, 31, NULL, NULL, 'Active', '2026-06-28 20:44:27'),
(11, '22/2025', 'Bashir', 'bashir@gmail.com', '123456', NULL, NULL, 1, '100', 2, 25, NULL, NULL, 'Active', '2026-06-29 12:12:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_name` (`session_name`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_record_id` (`attendance_record_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_attendance` (`attendance_session_id`,`student_id`),
  ADD KEY `idx_attendance_records_student` (`student_id`),
  ADD KEY `idx_attendance_records_course` (`course_id`);

--
-- Indexes for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `academic_session_id` (`academic_session_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `idx_attendance_session_course` (`course_id`),
  ADD KEY `idx_attendance_sessions_status` (`status`),
  ADD KEY `idx_attendance_sessions_method` (`attendance_method`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `idx_courses_department` (`department_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_course_session` (`student_id`,`course_id`,`academic_session_id`,`semester_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `academic_session_id` (`academic_session_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_code` (`device_code`);

--
-- Indexes for table `device_registration_requests`
--
ALTER TABLE `device_registration_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_settings`
--
ALTER TABLE `device_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_code` (`device_code`);

--
-- Indexes for table `enrollment_requests`
--
ALTER TABLE `enrollment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_name` (`faculty_name`);

--
-- Indexes for table `fingerprint_templates`
--
ALTER TABLE `fingerprint_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprint_id` (`fingerprint_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `lecturer_courses`
--
ALTER TABLE `lecturer_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lecturer_course_session` (`lecturer_id`,`course_id`,`academic_session_id`,`semester_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `academic_session_id` (`academic_session_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `offline_sync_logs`
--
ALTER TABLE `offline_sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `attendance_session_id` (`attendance_session_id`);

--
-- Indexes for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `semester_name` (`semester_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_no` (`matric_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `fingerprint_id` (`fingerprint_id`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`),
  ADD KEY `academic_session_id` (`academic_session_id`),
  ADD KEY `idx_students_department` (`department_id`),
  ADD KEY `idx_students_fingerprint_id` (`fingerprint_id`),
  ADD KEY `idx_students_rfid_uid` (`rfid_uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_registration_requests`
--
ALTER TABLE `device_registration_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `device_settings`
--
ALTER TABLE `device_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enrollment_requests`
--
ALTER TABLE `enrollment_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fingerprint_templates`
--
ALTER TABLE `fingerprint_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lecturer_courses`
--
ALTER TABLE `lecturer_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offline_sync_logs`
--
ALTER TABLE `offline_sync_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  ADD CONSTRAINT `attendance_corrections_ibfk_1` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_records_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `attendance_sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_ibfk_3` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_sessions_ibfk_4` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD CONSTRAINT `course_registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_3` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_4` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollment_requests`
--
ALTER TABLE `enrollment_requests`
  ADD CONSTRAINT `enrollment_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fingerprint_templates`
--
ALTER TABLE `fingerprint_templates`
  ADD CONSTRAINT `fingerprint_templates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_courses`
--
ALTER TABLE `lecturer_courses`
  ADD CONSTRAINT `lecturer_courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturer_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturer_courses_ibfk_3` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturer_courses_ibfk_4` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offline_sync_logs`
--
ALTER TABLE `offline_sync_logs`
  ADD CONSTRAINT `offline_sync_logs_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `offline_sync_logs_ibfk_2` FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `rfid_cards_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions` (`id`) ON DELETE CASCADE;
COMMIT;

--
-- Add approval_status to lecturers table for lecturer registration flow
--

ALTER TABLE `lecturers` ADD `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Approved' AFTER `status`;
ALTER TABLE `lecturers` ADD `qualification` varchar(150) DEFAULT NULL AFTER `phone`;

-- --------------------------------------------------------

--
-- Table structure for table `department_admins`
--

CREATE TABLE `department_admins` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `staff_id` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_registration_tokens`
--

CREATE TABLE `lecturer_registration_tokens` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_by` int(11) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','department_admin','lecturer','student') NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','department_admin','lecturer','student') NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `department_admins`
--
ALTER TABLE `department_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `lecturer_registration_tokens`
--
ALTER TABLE `lecturer_registration_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`user_role`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`user_role`),
  ADD KEY `idx_unread` (`is_read`);

--
-- AUTO_INCREMENT for table `department_admins`
--
ALTER TABLE `department_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lecturer_registration_tokens`
--
ALTER TABLE `lecturer_registration_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `department_admins`
--
ALTER TABLE `department_admins`
  ADD CONSTRAINT `department_admins_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_registration_tokens`
--
ALTER TABLE `lecturer_registration_tokens`
  ADD CONSTRAINT `lecturer_registration_tokens_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `email_settings`
--

CREATE TABLE `email_settings` (
  `id` int(11) NOT NULL,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `smtp_email` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
  `smtp_encryption` enum('tls','ssl') DEFAULT 'tls',
  `from_name` varchar(150) DEFAULT 'University Attendance System',
  `api_key` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `email_settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `email_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` varchar(50) NOT NULL,
  `token` varchar(64) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email_role` (`email`,`role`);

ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =============================================
-- CLOUD UPGRADE - Added tables and column upgrades
-- =============================================

-- =============================================
-- Cloud Upgrade Migration for University Attendance System
-- Fully MySQL-compatible (works on Clever Cloud)
-- =============================================

-- ===== DEVICE TOKENS TABLE =====
CREATE TABLE IF NOT EXISTS `device_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `device_id` INT(11) NOT NULL,
  `device_token` VARCHAR(64) NOT NULL UNIQUE,
  `device_secret` VARCHAR(128) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== AUDIT LOGS TABLE =====
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `actor_type` ENUM('admin','dept_admin','lecturer','student','system','device') NOT NULL,
  `actor_id` INT(11) DEFAULT NULL,
  `device_id` INT(11) DEFAULT NULL,
  `department_id` INT(11) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `action` (`action`),
  KEY `actor_type` (`actor_type`),
  KEY `device_id` (`device_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== OFFLINE ATTENDANCE QUEUE TABLE =====
CREATE TABLE IF NOT EXISTS `offline_attendance_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `device_id` INT(11) NOT NULL,
  `session_id` INT(11) NOT NULL,
  `identifier_type` ENUM('fingerprint','rfid') NOT NULL,
  `identifier_value` VARCHAR(100) NOT NULL,
  `attendance_time` DATETIME NOT NULL,
  `sync_status` ENUM('pending','synced','failed') DEFAULT 'pending',
  `sync_attempts` INT(11) DEFAULT 0,
  `last_sync_attempt` DATETIME DEFAULT NULL,
  `unique_id` VARCHAR(64) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sync_status` (`sync_status`),
  KEY `device_id` (`device_id`),
  KEY `unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== DEVICES TABLE - SAFE COLUMN ADDITION =====
DROP PROCEDURE IF EXISTS `migrate_add_columns`;
DELIMITER $$
CREATE PROCEDURE `migrate_add_columns`()
BEGIN
  DECLARE _exists INT;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='device_type');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `device_type` VARCHAR(50) DEFAULT 'ESP32-C3' AFTER `device_code`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='lecturer_id');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `lecturer_id` INT(11) DEFAULT NULL AFTER `department_id`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='building');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `building` VARCHAR(100) DEFAULT NULL AFTER `location`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='room');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `room` VARCHAR(50) DEFAULT NULL AFTER `building`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='firmware_version');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `firmware_version` VARCHAR(20) DEFAULT NULL AFTER `status`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='battery_level');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `battery_level` INT(11) DEFAULT NULL AFTER `firmware_version`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='last_sync_time');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `last_sync_time` DATETIME DEFAULT NULL AFTER `last_seen`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='connection_status');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `connection_status` ENUM('online','offline') DEFAULT 'offline' AFTER `ip_address`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='updated_at');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`; END IF;

  -- Notifications table columns
  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='notifications' AND COLUMN_NAME='device_id');
  IF _exists = 0 THEN ALTER TABLE `notifications` ADD COLUMN `device_id` INT(11) DEFAULT NULL AFTER `actor_id`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='notifications' AND COLUMN_NAME='link');
  IF _exists = 0 THEN ALTER TABLE `notifications` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL AFTER `message`; END IF;

  -- Activity logs table
  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='activity_logs' AND COLUMN_NAME='device_id');
  IF _exists = 0 AND _exists IS NOT NULL THEN ALTER TABLE `activity_logs` ADD COLUMN `device_id` INT(11) DEFAULT NULL AFTER `actor_id`; END IF;
END$$
DELIMITER ;
CALL `migrate_add_columns`();
DROP PROCEDURE IF EXISTS `migrate_add_columns`;

-- ===== ADD FOREIGN KEYS (IF NOT EXISTS) =====
DROP PROCEDURE IF EXISTS `migrate_add_fks`;
DELIMITER $$
CREATE PROCEDURE `migrate_add_fks`()
BEGIN
  DECLARE _exists INT;
  SET _exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='device_tokens' AND CONSTRAINT_NAME='fk_device_tokens_device');
  IF _exists = 0 THEN
    ALTER TABLE `device_tokens` ADD CONSTRAINT `fk_device_tokens_device` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
  END IF;
END$$
DELIMITER ;
CALL `migrate_add_fks`();
DROP PROCEDURE IF EXISTS `migrate_add_fks`;

-- ===== INDEXES =====
ALTER TABLE `attendance_records` ADD INDEX `idx_session_student` (`attendance_session_id`, `student_id`);
ALTER TABLE `attendance_sessions` ADD INDEX `idx_status_created` (`status`, `created_at`);
ALTER TABLE `device_registration_requests` ADD INDEX `idx_status_type` (`status`, `request_type`);
ALTER TABLE `enrollment_requests` ADD INDEX `idx_status_student` (`status`, `student_id`);
ALTER TABLE `notifications` ADD INDEX `idx_user_read` (`user_id`, `is_read`);
