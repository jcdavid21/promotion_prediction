-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 08, 2025 at 04:27 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emp_performance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `Discipline_Grave`
--

CREATE TABLE `Discipline_Grave` (
  `level_id` int(11) NOT NULL,
  `min_minor` int(11) NOT NULL,
  `max_minor` int(11) DEFAULT NULL,
  `min_grave` int(11) NOT NULL,
  `max_grave` int(11) DEFAULT NULL,
  `min_suspension` int(11) NOT NULL,
  `max_suspension` int(11) DEFAULT NULL,
  `rate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Discipline_Grave`
--

INSERT INTO `Discipline_Grave` (`level_id`, `min_minor`, `max_minor`, `min_grave`, `max_grave`, `min_suspension`, `max_suspension`, `rate`) VALUES
(1, 0, 0, 0, 0, 0, 0, 10),
(2, 1, 3, 1, 1, 1, 3, 9),
(3, 4, 5, 2, 2, 4, 5, 8),
(4, 6, 9, 3, 3, 6, 9, 7),
(5, 10, 12, 4, 4, 10, 12, 6),
(6, 12, 15, 5, 5, 12, 15, 5),
(7, 16, 18, 6, 6, 16, 18, 4),
(8, 19, 20, 7, 7, 19, 20, 3),
(9, 21, 23, 8, 8, 21, 23, 2),
(10, 24, NULL, 9, NULL, 24, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_Criteria`
--

CREATE TABLE `Evaluation_Criteria` (
  `criteria_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `scale` int(11) NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `rating` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Evaluation_Criteria`
--

INSERT INTO `Evaluation_Criteria` (`criteria_id`, `category`, `weight`, `scale`, `score`, `rating`) VALUES
(1, 'ATTENDANCE', 20.00, 10, NULL, NULL),
(2, 'DISCIPLINE', 20.00, 10, NULL, NULL),
(3, 'PERFORMANCE EVAL', 20.00, 10, NULL, NULL),
(4, 'MNGR INPUT', 20.00, 5, NULL, NULL),
(5, 'PSA INPUT', 20.00, 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Performance_Rating_Scale`
--

CREATE TABLE `Performance_Rating_Scale` (
  `scale_id` int(11) NOT NULL,
  `min_score` int(11) DEFAULT NULL,
  `max_score` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Performance_Rating_Scale`
--

INSERT INTO `Performance_Rating_Scale` (`scale_id`, `min_score`, `max_score`, `rating`) VALUES
(11, 79, NULL, 10),
(12, 62, 69, 9),
(13, 55, 61, 8),
(14, 48, 54, 7),
(15, 41, 47, 6),
(16, 34, 40, 5),
(17, 27, 33, 4),
(18, 20, 26, 3),
(19, 13, 19, 2),
(20, NULL, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `Tardiness_Rating`
--

CREATE TABLE `Tardiness_Rating` (
  `rate_id` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  `min_instances` int(11) NOT NULL,
  `max_instances` int(11) DEFAULT NULL,
  `min_minutes` int(11) NOT NULL,
  `max_minutes` int(11) DEFAULT NULL,
  `min_absenteeism` int(11) NOT NULL,
  `max_absenteeism` int(11) DEFAULT NULL,
  `min_uab_uhd` int(11) NOT NULL,
  `max_uab_uhd` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Tardiness_Rating`
--

INSERT INTO `Tardiness_Rating` (`rate_id`, `rate`, `min_instances`, `max_instances`, `min_minutes`, `max_minutes`, `min_absenteeism`, `max_absenteeism`, `min_uab_uhd`, `max_uab_uhd`) VALUES
(1, 10, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 9, 1, 7, 1, 240, 1, 3, 1, 1),
(3, 8, 8, 14, 241, 480, 4, 6, 2, 2),
(4, 7, 15, 21, 481, 720, 7, 9, 3, 3),
(5, 6, 22, 28, 721, 960, 10, 12, 4, 4),
(6, 5, 29, 35, 961, 1200, 13, 15, 5, 5),
(7, 4, 36, 42, 1201, 1440, 16, 18, 6, 6),
(8, 3, 43, 49, 1441, 1680, 19, 21, 7, 7),
(9, 2, 50, 56, 1681, 1920, 22, 24, 8, 8),
(10, 1, 57, NULL, 1921, NULL, 25, NULL, 9, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_account`
--

CREATE TABLE `tbl_account` (
  `acc_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_account`
--

INSERT INTO `tbl_account` (`acc_id`, `username`, `password`, `position_id`) VALUES
(1, 'admin', '$2y$10$hylp5tJfzmNt0s2KzpM/geq4KfqK6GKAYVjPckyDZ8erHFULe5Hru', 1),
(2, 'admin2', '$2y$10$X2HRV59x8KkB0spANBeD8O0Fr.SyPU.2sUk4jON07KdkfMHtHr/oe', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_account_details`
--

CREATE TABLE `tbl_account_details` (
  `id` int(11) NOT NULL,
  `acc_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_account_details`
--

INSERT INTO `tbl_account_details` (`id`, `acc_id`, `first_name`, `middle_name`, `last_name`, `contact`, `address`) VALUES
(1, 1, 'Christoper', '', 'Licuanan', '09565535401', 'Cielito Homes'),
(2, 2, 'Jc', '', 'David', '09565535401', 'Cielito Homes');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_department`
--

CREATE TABLE `tbl_department` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_department`
--

INSERT INTO `tbl_department` (`dept_id`, `dept_name`) VALUES
(1, 'Accounting'),
(2, 'Human Resources'),
(3, 'Comp & Ben'),
(4, 'Operations'),
(5, 'Maintenance'),
(6, 'Finance');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_employee_details`
--

CREATE TABLE `tbl_employee_details` (
  `emp_id` int(11) NOT NULL,
  `emp_name` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `regularization` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `exit_reason` varchar(255) DEFAULT NULL,
  `emp_status` varchar(50) DEFAULT NULL,
  `tenure` varchar(100) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `department` int(11) DEFAULT NULL,
  `active_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_employee_details`
--

INSERT INTO `tbl_employee_details` (`emp_id`, `emp_name`, `age`, `gender`, `status`, `start_date`, `regularization`, `end_date`, `exit_reason`, `emp_status`, `tenure`, `position`, `department`, `active_status`) VALUES
(1, 'ZAPANTA JR, ROBERTO', 31, 'M', 'SINGLE', '2019-10-15', '2020-04-12', '2025-04-28', NULL, 'INACTIVE', '5 Years, 5 Months, 13 Days', 9, 5, 2),
(2, 'VILLAVERDE, JEN JEN', 30, 'F', 'MARRIED', '2015-11-02', '2016-04-30', NULL, NULL, 'REGULAR', '9 Years, 6 Months, 5 Days', 10, 1, 1),
(3, 'TUAZON, GLAIZY BENZ', 27, 'F', 'SINGLE', '2024-11-20', '2025-05-19', NULL, NULL, 'PROBI', '0 Years, 3 Months and 12 Days', 2, 2, 1),
(4, 'TABOADA, MA. ROSARIO', 47, 'F', 'MARRIED', '2016-02-22', '2016-08-20', NULL, NULL, 'REGULAR', '9 Years, 0 Months and 10 Days', 15, 4, 1),
(5, 'TABANGAY, EDDIE JR.', 35, 'M', 'SINGLE', '2021-07-23', '2022-01-19', NULL, NULL, 'REGULAR', '3 Years, 7 Months and 9 Days', 2, 2, 1),
(6, 'SUICO, IVAN PHILLIP', 32, 'M', 'SINGLE', '2020-02-29', '2020-08-27', NULL, NULL, 'REGULAR', '5 Years, 0 Months and 3 Days', 2, 2, 1),
(7, 'SLEEBUSH, JASON HOWARD RHYS', 33, 'M', 'SINGLE', '2018-05-30', '2018-11-26', NULL, NULL, 'REGULAR', '6 Years, 9 Months and 2 Days', 14, 6, 1),
(8, 'SISON, AARON DOMINIC', 24, 'M', 'SINGLE', '2024-08-12', '2025-02-08', NULL, NULL, 'PROBI', '0 Years, 6 Months and 20 Days', 3, 2, 1),
(9, 'SERRANO, LIEZEL', 25, 'F', 'SINGLE', '2024-11-08', '2025-05-07', NULL, NULL, 'PROBI', '0 Years, 3 Months and 24 Days', 15, 4, 1),
(10, 'SAYO, VAN PAOLO', 32, 'M', 'SINGLE', '2018-01-17', '2018-07-16', NULL, NULL, 'REGULAR', '7 Years, 1 Months and 15 Days', 15, 4, 1),
(11, 'SARMIENTO, QUEEN MAE', 32, 'F', 'MARRIED', '2022-03-01', '2022-08-28', NULL, NULL, 'REGULAR', '3 Years, 0 Months and 3 Days', 18, 1, 1),
(12, 'SANTIAGO, ROBERT', 45, 'M', 'MARRIED', '2024-08-12', '2025-02-08', NULL, NULL, 'PROBI', '0 Years, 6 Months and 20 Days', 15, 4, 1),
(13, 'SANTIAGO, JULCEE', 35, 'F', 'SINGLE', '2022-05-17', '2022-11-13', NULL, NULL, 'REGULAR', '2 Years, 9 Months and 15 Days', 18, 1, 1),
(14, 'SANTIAGO, JENNIFER', 54, 'F', 'SINGLE', '2023-08-16', '2024-02-12', NULL, NULL, 'REGULAR', '1 Years, 6 Months and 16 Days', 17, 4, 1),
(15, 'SAMBALIDO, ILLANA KYLE', 23, 'F', 'SINGLE', '2024-03-19', '2024-09-15', NULL, NULL, 'PROBI', '0 Years, 11 Months and 13 Days', 3, 2, 1),
(16, 'ROMANO, HARLAIN IAN', 31, 'M', 'SINGLE', '2022-01-03', '2022-07-02', NULL, NULL, 'REGULAR', '3 Years, 2 Months and 1 Days', 18, 1, 1),
(17, 'RIVERA, HEIDILYN', 39, 'F', 'MARRIED', '2022-01-06', '2022-07-05', NULL, NULL, 'REGULAR', '3 Years, 1 Months and 26 Days', 15, 4, 1),
(18, 'REYES, ELENJOY', 26, 'F', '', '2024-04-18', '2024-10-15', NULL, NULL, 'PROBI', '0 Years, 10 Months and 14 Days', 2, 2, 1),
(19, 'RAMOS, CYRA LOU', 26, 'F', 'SINGLE', '2019-05-02', '2019-10-29', NULL, NULL, 'REGULAR', '5 Years, 10 Months and 2 Days', 18, 1, 1),
(20, 'PULVERA, JOSIE FE', 43, 'F', 'SINGLE', '2016-05-23', '2016-11-19', NULL, NULL, 'REGULAR', '8 Years, 9 Months and 9 Days', 15, 4, 1),
(21, 'PORTE, JENELYN', 44, 'F', 'MARRIED', '2016-06-26', '2016-12-23', NULL, NULL, 'REGULAR', '8 Years, 8 Months and 6 Days', 3, 2, 1),
(22, 'PENTOLO, RICA MAE', 26, 'F', 'SINGLE', '2024-07-29', '2025-01-25', NULL, NULL, 'PROBI', '0 Years, 7 Months and 3 Days', 15, 4, 1),
(23, 'PATLINGRAO, ARIEN', 39, 'F', 'SINGLE', '2018-06-22', '2018-12-19', NULL, NULL, 'REGULAR', '6 Years, 8 Months and 10 Days', 15, 4, 1),
(24, 'PATAGAN, ARLENE', 29, 'F', 'SINGLE', '2017-08-30', '2018-02-26', NULL, NULL, 'REGULAR', '7 Years, 6 Months and 2 Days', 15, 4, 1),
(25, 'PASCUA, FELANIE YVONNE', 34, 'F', 'SINGLE', '2020-02-03', '2020-08-01', NULL, NULL, 'REGULAR', '5 Years, 1 Months and 1 Days', 15, 4, 1),
(26, 'PANCITO, MILDRED', 32, 'F', 'SINGLE', '2023-02-23', '2023-08-22', NULL, NULL, 'REGULAR', '2 Years, 0 Months and 9 Days', 11, 3, 1),
(27, 'PAGAR, MA. JASMIN', 32, 'F', 'SINGLE', '2023-06-27', '2023-12-24', NULL, NULL, 'REGULAR', '1 Years, 8 Months and 5 Days', 15, 4, 1),
(28, 'NACORDA, DIVINE JASMIN', 24, 'F', 'SINGLE', '2024-04-01', '2024-09-28', NULL, NULL, 'PROBI', '0 Years, 11 Months and 3 Days', 15, 4, 1),
(29, 'MERCADO, ADRIEL', 29, 'M', 'SINGLE', '2024-09-02', '2025-03-01', NULL, NULL, 'PROBI', '0 Years, 6 Months and 2 Days', 3, 2, 1),
(30, 'LLEVA, MARY ANN', 32, 'F', 'SINGLE', '2018-05-04', '2018-10-31', NULL, NULL, 'REGULAR', '6 Years, 10 Months and 0 Days', 12, 6, 1),
(31, 'LICUANAN, DIANA', 39, 'F', 'SINGLE', '2018-02-25', '2018-08-24', NULL, NULL, 'REGULAR', '7 Years, 0 Months and 7 Days', 5, 2, 1),
(32, 'LADEMORA, RHEA', 36, 'F', 'SINGLE', '2023-06-20', '2023-12-17', NULL, NULL, 'PROBI', '1 Years, 8 Months and 12 Days', 15, 4, 1),
(33, 'JUALO, ANNAMIE', 26, 'F', 'SINGLE', '2024-08-13', '2025-02-09', NULL, NULL, 'PROBI', '0 Years, 6 Months and 19 Days', 3, 2, 1),
(34, 'JAVIER, ANGELINE', 23, 'F', 'SINGLE', '2024-03-05', '2024-09-01', NULL, NULL, 'SEASONAL', '0 Years, 11 Months and 27 Days', 3, 2, 1),
(35, 'JACINTO, VICTOR', 33, 'M', 'SINGLE', '2017-06-16', '2017-12-13', NULL, NULL, 'REGULAR', '7 Years, 8 Months and 16 Days', 18, 1, 1),
(36, 'GUTIERREZ, MARIECHRIS', 28, 'M', 'SINGLE', '2022-05-17', '2022-11-13', NULL, NULL, 'REGULAR', '2 Years, 9 Months and 15 Days', 18, 1, 1),
(37, 'GUIZA, MARISSA', 30, 'F', 'SINGLE', '2023-10-04', '2024-04-01', NULL, NULL, 'PROBI', '1 Years, 5 Months and 0 Days', 15, 4, 1),
(38, 'GEMIDA, CATHERINE', 35, 'F', 'SINGLE', '2022-09-03', '2023-03-02', NULL, NULL, 'REGULAR', '2 Years, 6 Months and 1 Days', 15, 4, 1),
(39, 'GAVINA, MYLEN', 30, 'F', 'SINGLE', '2016-04-27', '2016-10-24', NULL, NULL, 'REGULAR', '8 Years, 10 Months and 5 Days', 12, 6, 1),
(40, 'GAMBOA, MARIA CRISALYN', 29, 'F', 'SINGLE', '2017-04-27', '2017-10-24', NULL, NULL, 'REGULAR', '7 Years, 10 Months and 5 Days', 11, 3, 1),
(41, 'GALAPON, DESIREE', 50, 'F', 'MARRIED', '2021-01-01', '2021-06-30', NULL, NULL, 'REGULAR', '4 Years, 2 Months and 3 Days', 15, 4, 1),
(42, 'DIVINO, MARK ANDREW', 34, 'M', 'SINGLE', '2024-03-25', '2024-09-21', NULL, NULL, 'PROBI', '0 Years, 11 Months and 7 Days', 15, 4, 1),
(43, 'DIAZ, MICHELLE', 35, 'F', 'MARRIED', '2023-03-06', '2023-09-02', NULL, NULL, 'REGULAR', '1 Years, 11 Months and 26 Days', 15, 4, 1),
(44, 'DEL ROSARIO, JOSEPH', 32, 'M', 'SINGLE', '2016-09-20', '2017-03-19', NULL, NULL, 'REGULAR', '8 Years, 5 Months and 12 Days', 7, 1, 1),
(45, 'DEL ROSARIO, DARREL', 25, 'M', 'SINGLE', '2023-07-20', '2024-01-16', NULL, NULL, 'REGULAR', '1 Years, 7 Months and 12 Days', 18, 1, 1),
(46, 'DECENA, RODETTE', 25, 'F', 'SINGLE', '2023-05-29', '2023-11-25', NULL, NULL, 'REGULAR', '1 Years, 9 Months and 3 Days', 3, 2, 1),
(47, 'DE LAZO, JOANNA MARIZ', 33, 'F', 'SINGLE', '2023-07-12', '2024-01-08', NULL, NULL, 'REGULAR', '1 Years, 7 Months and 20 Days', 15, 4, 1),
(48, 'DALISAY, JANE', 26, 'F', 'SINGLE', '2019-12-03', '2020-05-31', NULL, NULL, 'REGULAR', '5 Years, 3 Months and 1 Days', 11, 3, 1),
(49, 'DAGANIO, JOHN PATRICK', 22, 'M', 'SINGLE', '2024-06-24', '2024-12-21', NULL, NULL, 'PROBI', '0 Years, 8 Months and 8 Days', 2, 2, 1),
(50, 'CUTA, ANGELINE CLAIRE', 22, 'F', 'SINGLE', '2024-08-02', '2025-01-29', NULL, NULL, 'PROBI', '0 Years, 7 Months and 2 Days', 2, 2, 1),
(51, 'CUSTODIO, CHARLES CHRISTIAN', 24, 'M', 'SINGLE', '2023-10-02', '2024-03-30', NULL, NULL, 'PROBI', '1 Years, 5 Months and 2 Days', 14, 6, 1),
(52, 'CULA, REANNE GAY', 30, 'F', 'MARRIED', '2017-08-03', '2018-01-30', NULL, NULL, 'REGULAR', '7 Years, 7 Months and 1 Days', 20, 2, 1),
(53, 'CHUA, RAQUEL', 41, 'F', 'SINGLE', '2016-03-16', '2016-09-12', NULL, NULL, 'REGULAR', '8 Years, 11 Months and 16 Days', 12, 6, 1),
(54, 'CENON, CLARISSE JOY', 24, 'F', 'SINGLE', '2024-01-15', '2024-07-13', NULL, NULL, 'PROBI', '1 Years, 1 Months and 17 Days', 12, 6, 1),
(55, 'CATIEMPO, ELDIE', 38, 'M', 'MARRIED', '2017-08-24', '2018-02-20', NULL, NULL, 'REGULAR', '7 Years, 6 Months and 8 Days', 18, 1, 1),
(56, 'CASTILLON, RENAN', 34, 'M', 'SINGLE', '2024-10-03', '2025-04-01', NULL, NULL, 'PROBI', '0 Years, 5 Months and 1 Days', 15, 4, 1),
(57, 'CASABAL, JASCHA MEINE', 24, 'F', 'SINGLE', '2023-06-13', '2023-12-10', NULL, NULL, 'REGULAR', '1 Years, 8 Months and 19 Days', 23, 1, 1),
(58, 'CALANUGA, DIANNE', 30, 'F', 'SINGLE', '2023-08-31', '2024-02-27', NULL, NULL, 'REGULAR', '1 Years, 6 Months and 1 Days', 2, 2, 1),
(59, 'CABIGAYAN, JESSA', 23, 'F', 'SINGLE', '2024-10-04', '2025-04-02', NULL, NULL, 'PROBI', '0 Years, 5 Months and 0 Days', 3, 2, 1),
(60, 'BOBIS, JUDY ANN', 26, 'F', 'SINGLE', '2023-09-04', '2024-03-02', NULL, NULL, 'PROBI', '1 Years, 6 Months and 0 Days', 18, 1, 1),
(61, 'BINIBINI, JENNY', 25, 'F', 'SINGLE', '2024-12-01', '2025-05-30', NULL, NULL, 'PROJ BASED', '0 Years, 3 Months and 3 Days', 21, 2, 1),
(62, 'BERTIS, HAROLD', 47, 'M', 'SINGLE', '2024-10-04', '2025-04-02', NULL, NULL, 'PROBI', '0 Years, 5 Months and 0 Days', 16, 4, 1),
(63, 'BAÑAGA, JEMMILYN', 26, 'F', 'SINGLE', '2023-10-06', '2024-04-03', NULL, NULL, 'PROBI', '1 Years, 4 Months and 26 Days', 18, 1, 1),
(64, 'BALDUEZA, KIM HERACLENE', 36, 'F', 'SINGLE', '2015-01-01', '2015-06-30', NULL, NULL, 'REGULAR', '10 Years, 2 Months and 3 Days', 19, 1, 1),
(65, 'MALAZA, JOLIANA', 22, 'F', 'SINGLE', '2025-01-25', '2025-07-25', NULL, NULL, '1 MO TRAINING', '0 Years, 1 Months and 7 Days', 3, 2, 1),
(66, 'CRUZ, JUAN MIGUEL', 28, 'M', 'SINGLE', '2022-03-15', '2022-09-11', NULL, NULL, 'REGULAR', '3 Years, 0 Months and 17 Days', 3, 2, 1),
(67, 'DELA PEÑA, MARIA LOURDES', 31, 'F', 'MARRIED', '2021-05-10', '2021-11-06', NULL, NULL, 'REGULAR', '3 Years, 10 Months and 22 Days', 2, 2, 1),
(68, 'GONZALES, CARLO ANTONIO', 35, 'M', 'SINGLE', '2020-08-22', '2021-02-18', NULL, NULL, 'REGULAR', '4 Years, 7 Months and 10 Days', 15, 4, 1),
(69, 'LIM, ANGELICA ROSE', 26, 'F', 'SINGLE', '2023-01-05', '2023-07-04', NULL, NULL, 'REGULAR', '2 Years, 2 Months and 27 Days', 18, 1, 1),
(70, 'MANALO, RAFAEL EDUARDO', 29, 'M', 'SINGLE', '2022-11-30', '2023-05-29', NULL, NULL, 'REGULAR', '2 Years, 4 Months and 2 Days', 11, 3, 1),
(71, 'ORTEGA, BEATRIZ MARIE', 33, 'F', 'MARRIED', '2021-09-14', '2022-03-13', NULL, NULL, 'REGULAR', '3 Years, 6 Months and 18 Days', 12, 6, 1),
(72, 'QUINTOS, LORENZO MIGUEL', 27, 'M', 'SINGLE', '2023-04-17', '2023-10-14', NULL, NULL, 'REGULAR', '1 Years, 11 Months and 15 Days', 15, 4, 1),
(73, 'REYES, ISABELLA SOPHIA', 30, 'F', 'SINGLE', '2020-12-01', '2021-05-30', NULL, NULL, 'REGULAR', '4 Years, 4 Months and 2 Days', 18, 1, 1),
(74, 'SANTOS, DIEGO ALEJANDRO', 32, 'M', 'SINGLE', '2019-07-19', '2020-01-15', NULL, NULL, 'REGULAR', '5 Years, 8 Months and 13 Days', 7, 1, 1),
(75, 'VALDEZ, CAMILLE ANNE', 25, 'F', 'SINGLE', '2024-02-10', '2024-08-08', NULL, NULL, 'PROBI', '1 Years, 1 Months and 22 Days', 3, 2, 1),
(76, 'jc', 24, 'M', NULL, '2025-05-07', NULL, '2025-05-08', NULL, 'INACTIVE', NULL, 1, 2, 2),
(77, 'Josh', 24, 'M', NULL, '2025-05-07', NULL, '2025-05-07', NULL, 'INACTIVE', NULL, 1, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_emp_status`
--

CREATE TABLE `tbl_emp_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_emp_status`
--

INSERT INTO `tbl_emp_status` (`status_id`, `status_name`) VALUES
(1, 'Trainee'),
(2, 'Probationary'),
(3, 'Regular');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_evaluation`
--

CREATE TABLE `tbl_evaluation` (
  `ev_id` int(11) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `administration` int(11) DEFAULT NULL,
  `knowledge_of_work` int(11) DEFAULT NULL,
  `quality_of_work` int(11) DEFAULT NULL,
  `communication` int(11) DEFAULT NULL,
  `team` int(11) DEFAULT NULL,
  `decision` int(11) DEFAULT NULL,
  `dependability` int(11) DEFAULT NULL,
  `adaptability` int(11) DEFAULT NULL,
  `leadership` int(11) DEFAULT NULL,
  `customer` int(11) DEFAULT NULL,
  `human_relations` int(11) DEFAULT NULL,
  `personal_appearance` int(11) DEFAULT NULL,
  `safety` int(11) DEFAULT NULL,
  `discipline` int(11) DEFAULT NULL,
  `potential_growth` int(11) DEFAULT NULL,
  `highlight` varchar(255) DEFAULT NULL,
  `lowlight` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_evaluation`
--

INSERT INTO `tbl_evaluation` (`ev_id`, `emp_id`, `administration`, `knowledge_of_work`, `quality_of_work`, `communication`, `team`, `decision`, `dependability`, `adaptability`, `leadership`, `customer`, `human_relations`, `personal_appearance`, `safety`, `discipline`, `potential_growth`, `highlight`, `lowlight`) VALUES
(1, 4, 5, 5, 5, 5, 5, 4, 4, 5, 5, 5, 4, 4, 3, 5, 5, NULL, NULL),
(2, 48, 3, 3, 5, 3, 4, 4, 5, 3, 4, 4, 4, 5, 3, 5, 4, 'Taking responsibility for one\'s action & work outcomes. Being dependable and able to consitently complete task on time.', 'Impatience'),
(3, 52, 4, 5, 3, 4, 4, 3, 4, 4, 4, 4, 4, 4, 3, 4, 5, NULL, NULL),
(4, 13, 4, 4, 3, 4, 4, 3, 3, 5, 3, 3, 4, 3, 3, 5, 4, NULL, NULL),
(5, 43, 3, 4, 3, 3, 4, 3, 5, 4, 3, 3, 4, 4, 3, 5, 3, NULL, NULL),
(6, 16, 4, 4, 3, 3, 4, 3, 5, 3, 3, 3, 4, 3, 3, 5, 4, NULL, NULL),
(7, 5, 4, 4, 4, 4, 3, 3, 4, 4, 4, 4, 4, 3, 3, 3, 3, 'managed his task without minimal supervision \nTime management (HR Admin & Recruitment)\nDeployed 480 days aging (Prifood) ', 'Prifood Recruitment focus on aging '),
(8, 30, 4, 4, 2, 4, 4, 3, 4, 3, 3, 4, 4, 4, 3, 4, 3, NULL, NULL),
(9, 29, 3, 4, 2, 4, 4, 3, 5, 3, 3, 3, 3, 3, 3, 5, 4, NULL, NULL),
(10, 8, 3, 4, 2, 3, 4, 3, 5, 4, 3, 3, 3, 3, 3, 5, 4, NULL, NULL),
(11, 11, 4, 4, 3, 3, 4, 3, 4, 3, 3, 3, 3, 3, 3, 5, 4, NULL, NULL),
(12, 34, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 5, 4, NULL, NULL),
(13, 53, 4, 4, 3, 3, 4, 3, 4, 3, 3, 3, 4, 4, 3, 4, 2, NULL, NULL),
(14, 51, 4, 3, 3, 3, 4, 3, 5, 3, 3, 3, 3, 3, 3, 5, 3, NULL, NULL),
(15, 7, 4, 3, 4, 3, 4, 4, 3, 3, 3, 3, 3, 3, 3, 4, 3, NULL, NULL),
(16, 44, 4, 4, 4, 3, 3, 3, 4, 3, 3, 3, 3, 2, 3, 4, 4, NULL, NULL),
(17, 36, 4, 4, 3, 3, 3, 2, 5, 3, 3, 3, 3, 3, 3, 4, 3, NULL, NULL),
(18, 54, 3, 4, 3, 3, 3, 3, 5, 3, 3, 3, 3, 3, 3, 4, 3, NULL, NULL),
(19, 60, 4, 4, 3, 3, 3, 2, 5, 3, 3, 3, 2, 3, 3, 4, 3, NULL, NULL),
(20, 19, 4, 4, 3, 3, 3, 2, 4, 3, 2, 3, 3, 3, 3, 5, 3, NULL, NULL),
(21, 15, 3, 3, 3, 3, 3, 3, 4, 4, 3, 3, 3, 2, 3, 4, 4, NULL, NULL),
(22, 59, 3, 2, 2, 3, 3, 2, 5, 3, 3, 3, 3, 3, 3, 5, 4, NULL, NULL),
(23, 55, 4, 4, 3, 3, 3, 2, 5, 3, 2, 2, 3, 3, 3, 4, 3, NULL, NULL),
(24, 58, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 5, 3, 'Lots of improvement in communication skills Communicates effectively and coordinate client concerns to management. Overall, shows strong motivationto learn and managed her task well.', 'needs to improve her attendance\n '),
(25, 18, 3, 3, 3, 3, 4, 2, 4, 4, 3, 3, 3, 3, 3, 3, 3, 'willingness to learn and quickly adapted new assigned task \ntaking initiative to do things\nminimal lates and absences \ntake feedback seriously and open minded\n', 'need to improve email reponse \nand confidentiality '),
(26, 33, 3, 3, 4, 2, 3, 3, 5, 3, 2, 2, 2, 3, 3, 5, 3, NULL, NULL),
(27, 45, 3, 2, 3, 3, 3, 2, 5, 4, 3, 3, 3, 3, 3, 3, 3, 'willingness to learn and quickly adapted new assigned task \nPositive Attitude \nGood attendance record ', 'be resourceful and give deadline coordinator compliance in Invalid Mandatory'),
(28, 14, 3, 4, 3, 3, 3, 2, 5, 3, 1, 2, 3, 3, 3, 5, 3, NULL, NULL),
(29, 35, 4, 4, 3, 3, 3, 2, 3, 3, 2, 3, 3, 3, 3, 3, 3, NULL, NULL),
(30, 49, 3, 3, 3, 3, 3, 2, 3, 3, 3, 3, 3, 3, 3, 3, 3, 'Willingness to learn and ask questions\npays attention to advice \nTeam player \nhas Positive attitude \ntaking initiative to do things', 'need to improve attendance always late '),
(31, 50, 3, 2, 3, 2, 3, 2, 4, 3, 2, 3, 3, 3, 3, 4, 3, 'No absences', 'need to improve attendance come early'),
(32, 1, 2, 3, 2, 2, 3, 2, 5, 2, 2, 3, 3, 3, 3, 5, 3, '', ''),
(33, 46, 3, 3, 1, 2, 4, 2, 3, 3, 2, 3, 3, 3, 3, 3, 4, NULL, NULL),
(34, 57, 3, 3, 2, 2, 3, 2, 4, 2, 2, 3, 3, 3, 3, 4, 3, NULL, NULL),
(35, 3, 2, 2, 2, 2, 3, 2, 5, 2, 2, 3, 3, 3, 3, 5, 3, 'Willingness to learn\nGood attendance record\n', 'Analyzing datas and information\nneeds to develop her critical thinking \n\nGet along with her teamates'),
(36, 32, 3, 3, 3, 2, 3, 2, 4, 3, 3, 2, 2, 3, 2, 4, 3, NULL, NULL),
(37, 26, 2, 2, 2, 1, 3, 2, 5, 2, 1, 2, 2, 5, 3, 5, 2, 'Taking responsibility for her quality of work', 'Lack of Initiative and lack of communicates when it comes in her job'),
(38, 21, 2, 3, 3, 3, 3, 3, 2, 3, 2, 2, 2, 3, 3, 2, 2, NULL, NULL),
(39, 6, 2, 3, 2, 2, 2, 2, 2, 2, 2, 3, 4, 5, 3, 3, 2, 'Brings energy to the team\nManaged RRJ ER', 'need to improve attendance\nTime Management  (use calendar or to do list)\nBe calm in dealing pressure or stress\n'),
(40, 63, 2, 2, 2, 2, 3, 2, 4, 2, 2, 2, 2, 3, 2, 2, 3, NULL, NULL),
(41, 40, 3, 1, 2, 3, 2, 2, 3, 2, 2, 3, 3, 3, 3, 3, 2, NULL, NULL),
(42, 39, 2, 2, 3, 2, 3, 3, 3, 2, 2, 2, 2, 3, 2, 2, 3, NULL, NULL),
(43, 31, 3, 3, 2, 3, 2, 2, 3, 3, 2, 3, 3, 2, 3, 3, 2, NULL, NULL),
(44, 65, 2, 3, 3, 2, 3, 3, 2, 3, 3, 2, 2, 3, 2, 2, 3, NULL, NULL),
(45, 62, 3, 2, 2, 3, 2, 2, 2, 2, 2, NULL, 3, 2, 3, 3, 2, NULL, NULL),
(46, 56, 3, 2, 3, 2, 4, 2, 3, 3, 2, 2, 2, 3, 2, 2, 3, NULL, NULL),
(47, 47, 3, 3, 3, 3, 3, 3, 2, 2, 3, 3, 2, 2, 3, 3, 2, NULL, NULL),
(48, 42, 3, 2, 3, 2, 2, 2, 3, 3, 2, NULL, 3, 3, 2, 2, 3, NULL, NULL),
(49, 41, 4, 3, 2, 3, 1, 3, 2, 3, 3, 3, 2, 2, 3, 3, 2, NULL, NULL),
(50, 38, 3, 2, 3, 2, 2, 2, 2, 2, 2, 2, 3, 2, 2, 2, 3, NULL, NULL),
(51, 37, 1, 3, 2, 3, 3, 3, 2, 2, 3, 3, 2, 2, 3, 3, 2, NULL, NULL),
(52, 27, 3, 1, 3, 2, 3, 3, 2, 2, 2, 2, 3, 2, 3, 3, 2, NULL, NULL),
(53, 25, 4, 3, 2, 3, 2, 3, 3, 3, 2, 2, 2, 3, 2, 2, 3, NULL, NULL),
(54, 24, 2, 2, 3, 2, 2, 3, 2, 2, 2, 2, 2, 2, 3, 3, 2, NULL, NULL),
(55, 23, 3, 1, 2, 3, 2, 2, 3, 3, 3, 3, 3, 3, 2, 2, 3, NULL, NULL),
(56, 22, 3, 2, 3, 2, 3, 3, 2, 2, 2, 3, 3, 3, 3, 3, 2, NULL, NULL),
(57, 20, 2, 3, 2, 3, 2, 3, 3, 3, 3, 2, 2, 2, 2, 2, 3, NULL, NULL),
(58, 17, 2, 3, 3, 3, 3, 2, 3, 2, 2, 3, 3, 3, 3, 3, 3, NULL, NULL),
(59, 12, 3, 2, 3, 3, 2, 2, 2, 3, 3, 2, 2, 3, 2, 2, 2, NULL, NULL),
(60, 10, 2, 3, 3, 2, 1, 2, 3, 2, 2, 2, 3, 2, 3, 2, 3, NULL, NULL),
(61, 9, 3, 2, 2, 3, 2, 3, 2, 3, 3, 3, 2, 3, 3, 3, 3, NULL, NULL),
(62, 27, 3, 1, 3, 2, 3, 3, 2, 2, 2, 2, 3, 2, 3, 3, 2, NULL, NULL),
(63, 66, 4, 4, 4, 4, 4, 3, 5, 4, 3, 4, 4, 4, 3, 5, 4, 'Consistently exceeds expectations in recruitment metrics', 'Could improve documentation of candidate interactions'),
(64, 67, 5, 5, 5, 4, 5, 4, 5, 5, 4, 5, 5, 5, 4, 5, 5, 'Excellent HR policies implementation', 'Occasionally needs reminders for report deadlines'),
(65, 68, 4, 4, 4, 3, 4, 3, 4, 4, 3, 4, 4, 4, 3, 4, 4, 'Strong financial analysis skills', 'Could communicate findings more clearly'),
(66, 69, 3, 4, 3, 3, 4, 3, 4, 3, 3, 3, 3, 3, 3, 4, 4, 'Great team player in operations', 'Needs to take more initiative'),
(67, 70, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 4, 4, 'Excellent benefits administration', 'Should delegate more tasks'),
(68, 71, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 4, 5, 5, 'Outstanding financial reporting', 'None'),
(69, 72, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 'Meets all job requirements', 'Could be more proactive'),
(70, 73, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 4, 4, 'Strong operations management', 'Occasionally late with reports'),
(71, 74, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 4, 5, 5, 'Exceptional accounting skills', 'None'),
(72, 75, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 'Good potential as recruiter', 'Still learning systems'),
(75, 64, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_eval_attendance`
--

CREATE TABLE `tbl_eval_attendance` (
  `eval_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `tardiness` int(11) NOT NULL,
  `tardy` int(11) NOT NULL,
  `comb_ab_hd` int(11) NOT NULL,
  `comb_uab_uhd` int(11) NOT NULL,
  `AB` int(11) NOT NULL,
  `UAB` int(11) NOT NULL,
  `HD` int(11) NOT NULL,
  `UHD` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_eval_attendance`
--

INSERT INTO `tbl_eval_attendance` (`eval_id`, `emp_id`, `tardiness`, `tardy`, `comb_ab_hd`, `comb_uab_uhd`, `AB`, `UAB`, `HD`, `UHD`) VALUES
(1, 13, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 16, 1, 3, 1, 0, 0, 0, 1, 0),
(3, 11, 20, 776, 2, 0, 1, 0, 1, 0),
(4, 44, 1, 23, 0, 0, 0, 0, 0, 0),
(5, 36, 0, 0, 1, 0, 1, 0, 0, 0),
(6, 60, 0, 0, 0, 0, 0, 0, 0, 0),
(7, 19, 26, 421, 0, 0, 0, 0, 0, 0),
(8, 55, 2, 11, 2, 0, 0, 0, 2, 0),
(9, 35, 21, 816, 1, 7, 0, 4, 1, 3),
(10, 57, 24, 223, 19, 0, 10, 0, 9, 0),
(11, 63, 29, 372, 8, 0, 4, 0, 4, 0),
(12, 1, 9, 238, 1, 0, 1, 0, 0, 0),
(13, 48, 2, 55, 0, 1, 0, 1, 0, 0),
(14, 26, 7, 157, 3, 0, 2, 0, 1, 0),
(15, 40, 57, 815, 9, 1, 5, 0, 4, 1),
(16, 30, 17, 798, 4, 1, 3, 1, 1, 0),
(17, 53, 69, 415, 2, 0, 1, 0, 1, 0),
(18, 51, 0, 0, 6, 0, 5, 0, 1, 0),
(19, 7, 51, 847, 1, 1, 1, 0, 0, 1),
(20, 54, 1, 4, 6, 0, 5, 0, 1, 0),
(21, 39, 65, 2300, 7, 0, 1, 0, 6, 0),
(22, 52, 48, 472, 1, 0, 1, 0, 0, 0),
(23, 5, 14, 74, 8, 1, 7, 1, 1, 0),
(24, 58, 66, 660, 13, 0, 8, 0, 5, 0),
(25, 18, 4, 53, 11, 2, 9, 2, 2, 0),
(26, 45, 1, 65, 1, 0, 0, 0, 1, 0),
(27, 49, 35, 341, 2, 0, 1, 0, 1, 0),
(28, 50, 25, 310, 4, 0, 2, 0, 2, 0),
(29, 3, 0, 0, 0, 0, 0, 0, 0, 0),
(30, 6, 62, 1001, 14, 7, 1, 5, 13, 2),
(31, 15, 36, 536, 0, 0, 0, 0, 0, 0),
(32, 59, 1, 8, 1, 0, 0, 0, 1, 0),
(33, 46, 56, 695, 4, 1, 3, 1, 1, 0),
(34, 34, 47, 562, 13, 0, 12, 0, 1, 0),
(35, 33, 0, 0, 1, 0, 1, 0, 0, 0),
(36, 21, 64, 858, 21, 2, 13, 1, 8, 1),
(37, 8, 7, 52, 4, 0, 2, 0, 2, 0),
(38, 29, 1, 14, 1, 0, 1, 0, 0, 0),
(39, 31, 69, 2885, 13, 1, 5, 0, 8, 1),
(40, 43, 0, 0, 2, 0, 2, 0, 0, 0),
(41, 32, 32, 199, 5, 1, 2, 1, 3, 0),
(42, 62, 4, 65, 6, 0, 6, 0, 0, 0),
(43, 56, 3, 8, 0, 0, 0, 0, 0, 0),
(44, 42, 2, 23, 0, 0, 0, 0, 0, 0),
(45, 41, 2, 76, 4, 1, 3, 1, 1, 0),
(46, 38, 0, 0, 0, 1, 0, 1, 0, 0),
(47, 37, 11, 95, 29, 0, 22, 0, 7, 0),
(48, 27, 15, 324, 22, 0, 15, 0, 7, 0),
(49, 25, 4, 103, 12, 1, 10, 1, 2, 0),
(50, 24, 75, 1111, 7, 2, 5, 1, 2, 1),
(51, 23, 0, 0, 0, 0, 0, 0, 0, 0),
(52, 22, 10, 208, 9, 2, 5, 2, 4, 0),
(53, 17, 5, 52, 0, 0, 0, 0, 0, 0),
(54, 14, 10, 102, 0, 0, 0, 0, 0, 0),
(55, 12, 0, 0, 0, 0, 0, 0, 0, 0),
(56, 10, 58, 1546, 20, 3, 15, 3, 5, 0),
(57, 9, 0, 0, 0, 0, 0, 0, 0, 0),
(58, 4, 35, 184, 3, 0, 1, 0, 2, 0),
(59, 47, 4, 18, 0, 1, 0, 1, 0, 0),
(60, 20, 2, 9, 2, 0, 1, 0, 1, 0),
(61, 66, 5, 45, 2, 0, 1, 0, 1, 0),
(62, 67, 2, 15, 0, 0, 0, 0, 0, 0),
(63, 68, 8, 120, 1, 0, 0, 0, 1, 0),
(64, 69, 12, 210, 3, 0, 2, 0, 1, 0),
(65, 70, 0, 0, 0, 0, 0, 0, 0, 0),
(66, 71, 3, 30, 1, 0, 1, 0, 0, 0),
(67, 72, 15, 320, 4, 1, 3, 1, 1, 0),
(68, 73, 7, 95, 2, 0, 1, 0, 1, 0),
(69, 74, 1, 5, 0, 0, 0, 0, 0, 0),
(70, 75, 0, 0, 0, 0, 0, 0, 0, 0),
(73, 64, 2, 30, 4, 4, 2, 2, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_eval_discipline`
--

CREATE TABLE `tbl_eval_discipline` (
  `eval_id` int(11) NOT NULL,
  `minor` int(11) DEFAULT NULL,
  `grave` int(11) DEFAULT NULL,
  `suspension` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_eval_discipline`
--

INSERT INTO `tbl_eval_discipline` (`eval_id`, `minor`, `grave`, `suspension`) VALUES
(1, 3, 0, 0),
(2, 3, 0, 0),
(3, 3, 0, 0),
(4, 0, 3, 1),
(5, 3, 0, 0),
(6, 3, 3, 1),
(7, 0, 0, 0),
(8, 3, 4, 10),
(9, 17, 0, 21),
(10, 15, 0, 1),
(11, 11, 6, 12),
(12, 3, 0, 0),
(13, 3, 0, 0),
(14, 0, 0, 0),
(15, 9, 0, 7),
(16, 6, 0, 3),
(17, 7, 0, 3),
(18, 0, 0, 0),
(19, 11, 0, 3),
(20, 12, 4, 0),
(21, 14, 0, 7),
(22, 13, 0, 0),
(23, 6, 3, 33),
(24, 5, 0, 0),
(25, 21, 0, 0),
(26, 7, 3, 5),
(27, 11, 0, 0),
(28, 8, 0, 0),
(29, 0, 0, 0),
(30, 47, 4, 30),
(31, 11, 0, 0),
(32, 3, 0, 0),
(33, 10, 9, 3),
(34, 8, 0, 0),
(35, 0, 0, 0),
(36, 24, 6, 14),
(37, 0, 3, 0),
(38, 0, 0, 0),
(39, 8, 0, 6),
(40, 0, 0, 0),
(41, 3, 4, 4),
(42, 0, 0, 0),
(43, 0, 0, 0),
(44, 0, 0, 0),
(45, 6, 6, 5),
(46, 3, 0, 0),
(47, 9, 0, 1),
(48, 12, 6, 0),
(49, 12, 0, 3),
(50, 23, 0, 15),
(51, 0, 0, 0),
(52, 6, 0, 0),
(53, 0, 0, 0),
(54, 0, 0, 0),
(55, 0, 0, 0),
(56, 17, 0, 1),
(57, 0, 0, 0),
(58, 6, 0, 0),
(59, 3, 0, 0),
(60, 0, 0, 0),
(61, 0, 0, 0),
(62, 0, 0, 0),
(63, 1, 0, 0),
(64, 2, 0, 0),
(65, 0, 0, 0),
(66, 0, 0, 0),
(67, 3, 0, 0),
(68, 1, 0, 0),
(69, 0, 0, 0),
(70, 0, 0, 0),
(73, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_eval_others`
--

CREATE TABLE `tbl_eval_others` (
  `eval_id` int(11) NOT NULL,
  `performance` int(11) DEFAULT NULL,
  `manager_input` float DEFAULT NULL,
  `psa_input` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_eval_others`
--

INSERT INTO `tbl_eval_others` (`eval_id`, `performance`, `manager_input`, `psa_input`) VALUES
(1, 55, NULL, NULL),
(2, 54, NULL, NULL),
(3, 52, NULL, NULL),
(4, 50, NULL, NULL),
(5, 49, NULL, NULL),
(6, 48, NULL, NULL),
(7, 48, NULL, NULL),
(8, 47, NULL, NULL),
(9, 45, NULL, NULL),
(10, 42, NULL, NULL),
(11, 35, NULL, NULL),
(12, 43, 0, 0),
(13, 59, NULL, NULL),
(14, 39, NULL, NULL),
(15, 37, NULL, NULL),
(16, 53, NULL, NULL),
(17, 51, NULL, NULL),
(18, 51, NULL, NULL),
(19, 50, NULL, NULL),
(20, 49, NULL, NULL),
(21, 36, NULL, NULL),
(22, 59, 4.5, NULL),
(23, 54, 3.5, NULL),
(24, 47, 4, 0),
(25, 47, 3.5, NULL),
(26, 46, 2.5, NULL),
(27, 44, 3, NULL),
(28, 43, 3, NULL),
(29, 42, 3, NULL),
(30, 39, 2.5, NULL),
(31, 48, 3, NULL),
(32, 47, 3, NULL),
(33, 42, 2.5, NULL),
(34, 51, 3, NULL),
(35, 46, 3, NULL),
(36, 38, 2.5, NULL),
(37, 52, 3.5, NULL),
(38, 52, 3.5, NULL),
(39, 39, NULL, NULL),
(40, 54, NULL, NULL),
(41, 42, NULL, NULL),
(42, 33, NULL, NULL),
(43, 38, NULL, NULL),
(44, 35, NULL, NULL),
(45, 39, NULL, NULL),
(46, 34, NULL, NULL),
(47, 37, NULL, NULL),
(48, 36, NULL, NULL),
(49, 39, NULL, NULL),
(50, 34, NULL, NULL),
(51, 38, NULL, NULL),
(52, 39, NULL, NULL),
(53, 41, NULL, NULL),
(54, 46, NULL, NULL),
(55, 36, NULL, NULL),
(56, 35, NULL, NULL),
(57, 40, NULL, NULL),
(58, 69, NULL, NULL),
(59, 40, NULL, NULL),
(60, 37, NULL, NULL),
(61, 72, 4.5, 4),
(62, 85, 5, 4.5),
(63, 68, 4, 3.5),
(64, 65, 3.5, 3.5),
(65, 78, 4.5, 4),
(66, 92, 5, 5),
(67, 58, 3, 3),
(68, 70, 4, 4),
(69, 88, 5, 4.5),
(70, 62, 3.5, 3),
(73, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_eval_standing`
--

CREATE TABLE `tbl_eval_standing` (
  `standing_id` int(11) NOT NULL,
  `standing_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_eval_standing`
--

INSERT INTO `tbl_eval_standing` (`standing_id`, `standing_name`) VALUES
(1, 'UNSATISFACTORY'),
(2, 'BELOW EXPECTATION'),
(3, 'MEET EXPECTATION'),
(4, 'EXCEED EXPECTATIONS'),
(5, 'OUTSTANDING');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pin`
--

CREATE TABLE `tbl_pin` (
  `pin_id` int(11) NOT NULL,
  `acc_Id` int(11) NOT NULL,
  `pin_pass` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pin`
--

INSERT INTO `tbl_pin` (`pin_id`, `acc_Id`, `pin_pass`) VALUES
(1, 1, 1234);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_positions`
--

CREATE TABLE `tbl_positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_positions`
--

INSERT INTO `tbl_positions` (`position_id`, `position_name`) VALUES
(1, 'HR Admin'),
(2, 'HR Officer'),
(3, 'HR Recruiter'),
(4, 'HR Team Leader'),
(5, 'HR Manager'),
(6, 'Payroll Master'),
(7, 'Accounting Supervisor'),
(8, 'Clearance Officer'),
(9, 'Maintenance'),
(10, 'Excutive Assistant'),
(11, 'Compben Officer'),
(12, 'Finance Officer'),
(13, 'Collections Officer'),
(14, 'Liason Officer'),
(15, 'Finance Officer'),
(16, 'Coordinator'),
(17, 'Encoder'),
(18, 'OPS Manager'),
(19, 'Officer'),
(20, 'Officer I'),
(21, 'Officer II'),
(22, 'Officer III'),
(23, 'SA Officer'),
(24, 'SH Officer'),
(25, 'SM Officer'),
(26, 'Account Head');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_promotion_history`
--

CREATE TABLE `tbl_promotion_history` (
  `promotion_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `previous_position_id` int(11) NOT NULL,
  `new_position_id` int(11) NOT NULL,
  `previous_department_id` int(11) NOT NULL,
  `new_department_id` int(11) NOT NULL,
  `promotion_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `performance_rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_promotion_history`
--

INSERT INTO `tbl_promotion_history` (`promotion_id`, `emp_id`, `previous_position_id`, `new_position_id`, `previous_department_id`, `new_department_id`, `promotion_date`, `reason`, `approved_by`, `performance_rating`) VALUES
(31, 66, 3, 2, 2, 2, '2023-09-15', 'Recruiter to HR Officer for outstanding performance metrics', 'Laura Green', 7),
(32, 67, 2, 4, 2, 2, '2023-12-01', 'HR Officer to Team Lead for exceptional policy implementation', 'Eric Baker', 8),
(33, 68, 15, 18, 4, 1, '2023-06-20', 'Finance Officer to OPS Manager for strong leadership', 'Michelle Adams', 8),
(34, 69, 18, 26, 1, 1, '2024-01-15', 'Promoted to Account Head for operational excellence', 'Steven Nelson', 7),
(35, 70, 11, 12, 3, 6, '2023-08-10', 'Compben Officer to Finance Officer for cross-training', 'Patricia Brown', 7),
(36, 71, 12, 19, 6, 1, '2023-10-10', 'Finance to Officer role for leadership potential', 'Melissa Hill', 9),
(37, 72, 15, 18, 4, 1, '2024-02-05', 'Finance Officer to OPS Manager for project success', 'Daniel Hernandez', 7),
(38, 73, 18, 26, 1, 1, '2023-11-20', 'Promoted to Account Head for team leadership', 'Karen Moore', 8),
(39, 74, 7, 26, 1, 1, '2022-05-15', 'Accounting Supervisor to Account Head for tenure', 'Mark Clark', 8),
(40, 75, 3, 2, 2, 2, '2024-05-01', 'Recruiter to HR Officer for quick learning', 'Jessica Lewis', 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Discipline_Grave`
--
ALTER TABLE `Discipline_Grave`
  ADD PRIMARY KEY (`level_id`);

--
-- Indexes for table `Evaluation_Criteria`
--
ALTER TABLE `Evaluation_Criteria`
  ADD PRIMARY KEY (`criteria_id`);

--
-- Indexes for table `Performance_Rating_Scale`
--
ALTER TABLE `Performance_Rating_Scale`
  ADD PRIMARY KEY (`scale_id`);

--
-- Indexes for table `Tardiness_Rating`
--
ALTER TABLE `Tardiness_Rating`
  ADD PRIMARY KEY (`rate_id`);

--
-- Indexes for table `tbl_account`
--
ALTER TABLE `tbl_account`
  ADD PRIMARY KEY (`acc_id`);

--
-- Indexes for table `tbl_account_details`
--
ALTER TABLE `tbl_account_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_department`
--
ALTER TABLE `tbl_department`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `tbl_employee_details`
--
ALTER TABLE `tbl_employee_details`
  ADD PRIMARY KEY (`emp_id`);

--
-- Indexes for table `tbl_emp_status`
--
ALTER TABLE `tbl_emp_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `tbl_evaluation`
--
ALTER TABLE `tbl_evaluation`
  ADD PRIMARY KEY (`ev_id`);

--
-- Indexes for table `tbl_eval_attendance`
--
ALTER TABLE `tbl_eval_attendance`
  ADD PRIMARY KEY (`eval_id`);

--
-- Indexes for table `tbl_eval_discipline`
--
ALTER TABLE `tbl_eval_discipline`
  ADD PRIMARY KEY (`eval_id`);

--
-- Indexes for table `tbl_eval_others`
--
ALTER TABLE `tbl_eval_others`
  ADD PRIMARY KEY (`eval_id`);

--
-- Indexes for table `tbl_eval_standing`
--
ALTER TABLE `tbl_eval_standing`
  ADD PRIMARY KEY (`standing_id`);

--
-- Indexes for table `tbl_pin`
--
ALTER TABLE `tbl_pin`
  ADD PRIMARY KEY (`pin_id`);

--
-- Indexes for table `tbl_positions`
--
ALTER TABLE `tbl_positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `tbl_promotion_history`
--
ALTER TABLE `tbl_promotion_history`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `emp_id` (`emp_id`),
  ADD KEY `previous_position_id` (`previous_position_id`),
  ADD KEY `new_position_id` (`new_position_id`),
  ADD KEY `previous_department_id` (`previous_department_id`),
  ADD KEY `new_department_id` (`new_department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Discipline_Grave`
--
ALTER TABLE `Discipline_Grave`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Evaluation_Criteria`
--
ALTER TABLE `Evaluation_Criteria`
  MODIFY `criteria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Performance_Rating_Scale`
--
ALTER TABLE `Performance_Rating_Scale`
  MODIFY `scale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `Tardiness_Rating`
--
ALTER TABLE `Tardiness_Rating`
  MODIFY `rate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_account`
--
ALTER TABLE `tbl_account`
  MODIFY `acc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_account_details`
--
ALTER TABLE `tbl_account_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_department`
--
ALTER TABLE `tbl_department`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_employee_details`
--
ALTER TABLE `tbl_employee_details`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `tbl_emp_status`
--
ALTER TABLE `tbl_emp_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_evaluation`
--
ALTER TABLE `tbl_evaluation`
  MODIFY `ev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `tbl_eval_attendance`
--
ALTER TABLE `tbl_eval_attendance`
  MODIFY `eval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `tbl_eval_discipline`
--
ALTER TABLE `tbl_eval_discipline`
  MODIFY `eval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `tbl_eval_others`
--
ALTER TABLE `tbl_eval_others`
  MODIFY `eval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `tbl_eval_standing`
--
ALTER TABLE `tbl_eval_standing`
  MODIFY `standing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_pin`
--
ALTER TABLE `tbl_pin`
  MODIFY `pin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_positions`
--
ALTER TABLE `tbl_positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_promotion_history`
--
ALTER TABLE `tbl_promotion_history`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
