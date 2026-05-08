-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 07:26 AM
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
-- Database: `health_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(131, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 01:10:09'),
(133, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 01:50:32'),
(134, 1, 'UPDATE_FACILITY', 'USER', 1, NULL, 'facility name: hospitality -> hospitalityy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 01:51:22'),
(135, 1, 'UPDATE_FACILITY', 'USER', 1, NULL, 'facility name: hospitalityy -> hospitality\nfacility color: #fff701 -> #fff700', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 01:51:38'),
(136, 1, 'CREATE_FACILITY', 'USER', 1, NULL, 'facility name:  -> aaa\ntype:  -> Provincial Hospital\nprovince:  -> Leyte\nmunicipality:  -> Tacloban City/Palo\naddress:  -> Pawing, Palo, Leyte\ncontact person:  -> 1\ncontact email:  -> info@leyte-provincial.locall\nfacility color:  -> #5900ff', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:06:30'),
(137, 1, 'UPDATE_FACILITY', 'USER', 1, NULL, 'facility color: #5900ff -> #8c00ff', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:06:42'),
(138, 1, 'CREATE_EVALUATION', 'USER', 1, NULL, 'facility: hospitality\nevaluation date: 2026-04-20\nevaluation period: 2026-04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:36:25'),
(139, 1, 'UPDATE_EVALUATION', 'USER', 1, 'indicator: empty', 'indicator: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:49:10'),
(140, 1, 'UPDATE_EVALUATION', 'USER', 1, 'indicator: empty', 'indicator: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:49:12'),
(141, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'Assessed signature name: (empty) -> abc', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:49:20'),
(142, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'Verified date: (empty) -> 2026-04-20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:49:23'),
(143, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'Assessed e-signature: added', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:49:40'),
(144, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'facility: hospitality\nbuilding block: Leadership and Governance\nobjective: The presence of institutionalized and functional Units.\nindicator: Local Government Units with institutionalized Disaster Risk Reduction and Management in Health (DRRM-H) System (empty -> 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:53:51'),
(145, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'facility: hospitality\nAssessed e-signature: removed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:53:54'),
(146, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'facility: hospitality\nAssessed signature name: abc -> (empty)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:53:56'),
(147, 1, 'UPDATE_EVALUATION', 'USER', 1, NULL, 'facility: hospitality\nVerified date: 2026-04-20 -> (empty)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 02:53:58'),
(148, 1, 'UPDATE_USER', 'USER', 10, NULL, 'full_name: niezan golez -> niezan golezz', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:39:40'),
(149, 1, 'UPDATE_USER', 'USER', 10, NULL, 'username: niezagolez -> niezagolezz; full_name: niezan golezz -> niezan golez', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:39:58'),
(150, 1, 'UPDATE_USER', 'USER', 10, NULL, 'username: niezagolezz -> niezagolez\nfull_name: niezan golez -> niezan golezz', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:46:14'),
(151, 1, 'CREATE_USER', 'USER', 11, NULL, 'username: abc\nemail: a@gmail.com\nfull_name: abc\nrole: viewer\nfacility_name: hospitality', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:46:52'),
(152, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:51:01'),
(153, 2, 'LOGIN', 'USER', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:51:11'),
(154, 2, 'LOGOUT', 'USER', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:51:13'),
(155, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 03:51:19'),
(156, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 07:06:58'),
(157, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 07:08:55'),
(158, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 07:18:43'),
(159, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-20 07:43:22'),
(160, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-27 05:51:37'),
(161, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:20:41'),
(162, 1, 'CREATE_FACILITY', 'USER', 1, NULL, 'facility name:  -> QMER Training Center\ntype:  -> tRAINING cENTER\nprovince:  -> Leyte\nmunicipality:  -> Tunga\naddress:  -> Pawing, Palo, Leyte\ncontact person:  -> 1\ncontact email:  -> info@ormoc-health.local\nfacility color:  -> #ffa200', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:28:14'),
(163, 1, 'CREATE_USER', 'USER', 12, NULL, 'username: adminalas\nemail: aaaa@gmail.com\nfull_name: QMERJEMS INPUT SAMPLE\nrole: input\nfacility_name: QMER Training Center', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:29:26'),
(164, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:29:37'),
(165, 12, 'LOGIN', 'USER', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:29:41'),
(166, 12, 'UPDATE_FACILITY', 'USER', 12, NULL, 'municipality: Tunga -> Palo', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:30:21'),
(167, 12, 'CREATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nevaluation date: 2026-05-08\nevaluation period: 2026-05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:30:37'),
(168, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (empty -> 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:30:55'),
(169, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (empty -> 0)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:30:56'),
(170, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (0.00 -> 0)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:31:08'),
(171, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nAssessed signature name: (empty) -> aaa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:31:47'),
(172, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nAssessed e-signature: added', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:32:05'),
(173, 12, 'CREATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nevaluation date: 2026-05-08\nevaluation period: 2026-05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:35:48'),
(174, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: The presence of institutionalized and functional Units.\nindicator: Health promotion policies and programs implemented (empty -> 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:35:57'),
(175, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: \nindicator: Special Health Fund (SHF) (empty -> 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:35:58'),
(176, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: \nindicator: Special Health Fund (SHF) (1.00 -> 0)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:35:58'),
(177, 12, 'LOGOUT', 'USER', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:38:09'),
(178, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:38:19'),
(179, 1, 'CREATE_USER', 'USER', 13, NULL, 'username: salamidaqmer\nemail: admina@gmail.com\nfull_name: QMERJEMS VIEWER SAMPLE\nrole: viewer\nfacility_name: QMER Training Center', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:39:02'),
(180, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:39:11'),
(181, 13, 'LOGIN', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:39:14'),
(182, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:41:37'),
(183, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:41:40'),
(184, 13, 'LOGIN', 'USER', 13, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:52:00'),
(185, 12, 'LOGIN', 'USER', 12, NULL, NULL, '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:00'),
(186, 12, 'CREATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nevaluation date: 2026-05-08\nevaluation period: 2026-05', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:21'),
(187, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (empty -> 1)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:31'),
(188, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (1.00 -> 0)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:32'),
(189, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (0.00 -> 1)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:35'),
(190, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (1.00 -> 1)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:39'),
(191, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (empty -> 0)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:45'),
(192, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (0.00 -> 1)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:46'),
(193, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (1.00 -> 1)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:48'),
(194, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (1.00 -> 1)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:49'),
(195, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:53:51'),
(196, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (empty -> 0)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:51'),
(197, 12, 'UPDATE_EVALUATION', 'USER', 12, NULL, 'facility: QMER Training Center\nbuilding block: Leadership and Governance\nobjective: Organizational Structure of the Facility\nindicator: The presence and Functionality of the Facility Organizational Structure (0.00 -> 0)', '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:53:53'),
(198, 1, 'LOGIN', 'USER', 1, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 02:53:57'),
(199, 12, 'LOGOUT', 'USER', 12, NULL, NULL, '192.168.2.248', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:54:27'),
(200, 13, 'LOGIN', 'USER', 13, NULL, NULL, '192.168.2.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 02:56:32'),
(201, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '192.168.2.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 03:00:15'),
(202, 1, 'LOGIN', 'USER', 1, NULL, NULL, '192.168.2.65', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-05-08 03:00:41'),
(203, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:04:00'),
(204, 3, 'LOGIN', 'USER', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:04:05'),
(205, 3, 'LOGOUT', 'USER', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:04:17'),
(206, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:04:21'),
(207, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:04:37'),
(208, 13, 'LOGIN', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:04:40'),
(209, 13, 'EVALUATE_APPROVAL', 'EVALUATION', 26, '0', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:07:53'),
(210, 13, 'EVALUATE_APPROVAL', 'EVALUATION', 24, '0', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:10:09'),
(211, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:12:37'),
(212, 13, 'LOGIN', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:12:45'),
(213, 13, 'EVALUATE_APPROVAL', 'EVALUATION', 26, '1', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:12:59'),
(214, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:13:15'),
(215, 13, 'EVALUATE_APPROVAL', 'EVALUATION', 26, '1', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:13:41'),
(216, 3, 'LOGIN', 'USER', 3, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:13:42'),
(217, 3, 'LOGOUT', 'USER', 3, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:14:28'),
(218, 1, 'LOGIN', 'USER', 1, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:16:18'),
(219, 13, 'EVALUATE_APPROVAL', 'EVALUATION', 26, '1', '1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:17:54'),
(220, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:18:09'),
(221, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:18:09'),
(222, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:18:15'),
(223, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:18:24'),
(224, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:18:47'),
(225, 13, 'LOGIN', 'USER', 13, NULL, NULL, '192.168.2.76', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:19:03'),
(226, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:21:43'),
(227, 13, 'LOGIN', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:21:50'),
(228, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:22:04'),
(229, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:22:15'),
(230, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:22:52'),
(231, 12, 'LOGIN', 'USER', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:22:55'),
(232, 12, 'LOGOUT', 'USER', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:23:09'),
(233, 13, 'LOGIN', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:23:14'),
(234, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:25:29'),
(235, 12, 'LOGIN', 'USER', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:25:41'),
(236, 12, 'LOGOUT', 'USER', 12, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:29:46'),
(237, 13, 'LOGIN', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:29:52'),
(238, 13, 'EVALUATE_APPROVAL', 'EVALUATION', 25, '0', '2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 03:29:59'),
(239, 13, 'LOGOUT', 'USER', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 04:46:05'),
(240, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 04:46:08'),
(241, 1, 'LOGOUT', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 05:19:08'),
(242, 1, 'LOGIN', 'USER', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-08 05:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `building_blocks`
--

CREATE TABLE `building_blocks` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `weight_percentage` decimal(5,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `building_blocks`
--

INSERT INTO `building_blocks` (`id`, `code`, `name`, `weight_percentage`, `description`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'BB_I', 'Leadership and Governance', 15.00, 'Administrative structure and organizational leadership', 1, 1, '2026-02-18 01:10:48'),
(2, 'BB_II', 'Regulation', 15.00, 'Quality of health services provided', 2, 1, '2026-02-18 01:10:48'),
(3, 'BB_III', 'Health Financing', 15.00, 'Financial management and resource allocation', 3, 1, '2026-02-18 01:10:48'),
(4, 'BB_IV', 'Human Resource for Health', 15.00, 'Personnel and workforce management', 4, 1, '2026-02-18 01:10:48'),
(5, 'BB_V', 'Health Service Delivery', 15.00, 'a\r\n', 5, 1, '2026-02-24 06:37:25'),
(6, 'BB_VI', 'Information Communication and Technology', 25.00, 'Information systems and technology infrastructure', 6, 1, '2026-02-18 01:10:48'),
(7, 'BB_VII', 'Special Indicator', 0.00, 'Additional performance indicators', 7, 1, '2026-02-18 01:10:48');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `evaluation_date` date NOT NULL,
  `evaluation_period` varchar(50) DEFAULT NULL,
  `status` enum('draft','in_progress','completed','approved') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `assessed_by` varchar(255) DEFAULT NULL,
  `assessed_signature` text DEFAULT NULL,
  `assessed_date` date DEFAULT NULL,
  `verified_by` varchar(255) DEFAULT NULL,
  `verified_signature` text DEFAULT NULL,
  `verified_date` date DEFAULT NULL,
  `approved_by` varchar(255) DEFAULT NULL,
  `approved_signature` text DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `noted_by` varchar(255) DEFAULT NULL,
  `noted_signature` text DEFAULT NULL,
  `noted_date` date DEFAULT NULL,
  `conformed_by` varchar(255) DEFAULT NULL,
  `conformed_signature` text DEFAULT NULL,
  `conformed_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assessed_esignature` blob DEFAULT NULL,
  `verified_esignature` blob DEFAULT NULL,
  `approved_esignature` blob DEFAULT NULL,
  `noted_esignature` blob DEFAULT NULL,
  `conformed_esignature` blob DEFAULT NULL,
  `approved` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `facility_id`, `evaluation_date`, `evaluation_period`, `status`, `created_by`, `updated_by`, `assessed_by`, `assessed_signature`, `assessed_date`, `verified_by`, `verified_signature`, `verified_date`, `approved_by`, `approved_signature`, `approved_date`, `noted_by`, `noted_signature`, `noted_date`, `conformed_by`, `conformed_signature`, `conformed_date`, `remarks`, `created_at`, `updated_at`, `assessed_esignature`, `verified_esignature`, `approved_esignature`, `noted_esignature`, `conformed_esignature`, `approved`) VALUES
(18, 2, '2026-03-23', '2026-03', 'draft', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-23 03:28:14', '2026-03-23 03:37:33', NULL, NULL, NULL, NULL, NULL, 0),
(19, 1, '2026-03-23', '2026-03', 'draft', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29', NULL, NULL, NULL, NULL, NULL, 0),
(20, 3, '2026-03-23', '2026-03', 'draft', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42', NULL, NULL, NULL, NULL, NULL, 0),
(21, 2, '2026-03-23', '2026-03', 'draft', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-23 03:38:21', '2026-03-23 08:46:23', NULL, NULL, NULL, NULL, NULL, 0),
(22, 2, '2026-03-23', '2026-03', 'draft', 1, 1, 'abc', '', NULL, NULL, '', NULL, NULL, '', NULL, NULL, '', NULL, NULL, '', NULL, NULL, '2026-03-23 04:00:42', '2026-03-24 01:14:02', 0x89504e470d0a1a0a0000000d4948445200000190000000640806000000a8cb66770000075e494441547801ecdb896edc36140550a7ffffcfad5f10ba84a2754489db29c24a23ee87866ec741fff9f20f0102040810f84040807c80a60b010204087c7d09103f05046a09989740e70202a4f303b47c020408d4121020b5e4cd4b800081ce053a0e90cee52d9f0001029d0b0890ce0fd0f2091020504b4080d492372f818e052c9d40080890505008102040e0b28000b94ca603010204088480000985b78bf90810203080800019e0106d81000102350404480d75731220504bc0bc05050448414c431120406026010132d369db2b0102040a0a08908298330c658f0408104802022449b812204080c025010172894b63020408d412686f5e01d2de995811010204ba1010205d1c9345122040a03d0101d2de9958d13302462540a0b08000290c6a38020408cc222040663969fb244080406181d30152785ec31120408040e70202a4f303b47c020408d4121020b5e4cd4be0b4808604da1410206d9e8b55112040a0790101d2fc115920010204da14982140da94b72a02f505fefd5ec25af97eec0f81630101726ca40581ab02f152bedae7cdf6b1be285b73eed56df5f17c42010132e1a1dbf2a302e9e59bae8f4e7671f0585394a36ebf8e1a9caed77068010132f4f1da1c811f81657044486c959f4e6e08ec0908903d1d7504c610580b8f317666175505044855fea3c9d513b82d203c6e131a604b40806cc9784ea0ac40bcc853293bf2f668315f5e1bbfb2ca3fbb27704b4080dce2d3f9438178b12dcb874375d12df69a2f74f939af2b711fe347c9c7121eb9c6897b4d8e0504c8b19116ef082c5f78efccfafc2c6fed2be64925df55044794fc997b02450404481146837c28e0c5f6215cd66d2d34a23a6ca3c4bd42e0110101f208ab41bf103c2d20389e1636fea180003924d280c0c702f1924f9d4b7e1bc8c78df163ec54e2b342e0150101f20ab34908fc25b00c81bf1a6c3cc8fb098d0da4c91fbfb67d01f21ab58908dc165886c7ed010d40e08e8000b9a3a72f81f70484c77bd6663a2920404e4269368f40833b151e0d1e8a257d7d09103f053505f217e3d13aa2edd97234d6567d1a7fabbec6f358539af757ba7125d082800069e114aca1b440bc74a37c3a6ef48d72d4ff4c9b1823bdf8d3359e1d95183b4a6a77a56feae34ae05181f201f2e8720d3ea840bc1ca39cd95eb4db2acbfef90b785977e6f35eff5417d754d6c68cb5e6cf979ff3bab85f1beba84ff45308bc2e20405e279f7ec278415e45f8a4cf9939f271f3fb337dd7dadc1923fa46c9c78de088923f734fa0190101d2cc5134b99078a1952ecb8d3efd82dc5b7fac25d5c77d94e57a527dba469b2b25f5cbafa9ffdab354f7c9551f02af0a089057b94db622102fd195c73f8ff2fae5cbfda7d19f9ba3fa3fcd5c0810282120404a288e3b46e917728c1765299687c4b2eeeae718ff4c591b37faad3dcf9fc55aa3e4cff6fa45dd5e598e93b7cdebdc13684e40806447e2765520bdd0562b2f3c8c71f69ac74b3995bd76a5ea623dcb92c6de7a9eeaaf5cd39eb6ae69ac9833ddbb12e842408074714c4d2c325e70774a139b787011b9cdd569a2efd53eda13a82e2040aa1f8105ac08e4ffb5be52ddfca308842ba5f90d3dbf4033f42820407a3c356b6e41200222d691ae71af10984a40804c75dccd6c365ebacb922f6e59973ee76d6adec77a62fe748d7b85c0740202648c23b70b020408bc2e20405e273761e702be75747e80965f4e408094b334120102330a4cbc670132f1e1db3a010204ee0808903b7afa1220406062010132f1e1b7b1f5dfab88ffefe3f7cdf7bffc1dc337823f047a1010203d9c9235122040a0410101d2e0a14cb624df3e263b70db6d47e0ee4a04c85d41fd09102030a9800099f4e01bd9b66f1f8d1c846510f84440807ca2a64f09819ec223fe623f95fff7ee8ec0e4020264f21f800adb8fe08892a68e1773ba772540a0230101d2d1610db0d43c38623bc2231414029d0a540c904ec52cbb9480f02825691c029504044825f8c9a7151e93ff00d8fe180202648c73ec6117cb5f5ff5b0e661d76863044a080890128ac6204080c084020264c243afb0e5fcdb875f5f5538005312784240807ca2aacf1501e171454b5b021d0908908e0eabc3a50a8f0e0fcd92099c15102067a5b4bb2a203cae8a697f46409b86040448438731e852fc9dc7a0076b5b0404889f812704f26f1f4f8c6f4c02041a1010200d1cc29b4b78612ee1f102b22908b42020405a3885fed61021b155f2ddf8f555aee19ec060020264b0037d613b111c47d3447044396aa79ec04402e36d55808c77a64fefe828188eea9f5e9ff1091078494080bc043dd83411125b65b0adda0e01025b0202644bc6f3d604ac870081c604044863076239040810e8454080f47252d6498000815a021bf30a900d188f09102040605f4080ecfba825408000810d0101b201e331817202462230a6800019f35ced8a0001028f0b0890c7894d4080008131057a089031e5ed8a0001029d0b0890ce0fd0f2091020504b4080d492372f811e04ac91c08e8000d9c1514580000102db020264db460d01020408ec0808901d9cfb5546204080c0b8020264dcb3b5330204083c2a20401ee535380102b504ccfbbcc07f000000ffffae3ebbd2000000064944415403007dfaa6c98d8184520000000049454e44ae426082, NULL, NULL, NULL, NULL, 0),
(24, 17, '2026-05-08', '2026-05', 'completed', 12, 12, 'aaa', '', NULL, NULL, '', NULL, NULL, '', NULL, NULL, '', NULL, NULL, '', NULL, NULL, '2026-05-08 02:30:36', '2026-05-08 03:10:09', 0x89504e470d0a1a0a0000000d4948445200000190000000640806000000a8cb667700000720494441547801ecda8b8edc281005d0d9fdff7f4e54518890d57e363694392bb17637068a43e4ab99e4ff1fff1120408000810b0202e4029a2104081020f0f32340fc2920d04bc0ba04920b0890e407a87c020408f4121020bde4ad4b800081e402890324b9bcf2091020905c4080243f40e5132040a0978000e9256f5d028905944e20040448286804081020705a40809c2633800001020442408084c2d3cd7a0408107881800079c121da020102047a0808901eead62440a09780751b0a08908698a6224080c04c020264a6d3b657020408341410200d316798ca1e0910205004044891702540800081530202e4149787091020d04b60bc7505c87867a222020408a4101020298e4991040810184f40808c77262aba47c0ac0408341610208d414d47800081590404c82c276d9f040810682c7038401aaf6b3a02040810482e2040921fa0f2091020d04b4080f492b72e81c3021e2430a6800019f35c5445800081e10504c8f047a4400204088c293043808c29af2a02040824171020c90f50f9040810e82520407ac95b97c00c02f6f86a0101f2eae3b539020408dc272040eeb335330102045e2d2040863e5ec5112040605c010132eed9a82cafc0af014b8f9af6da80652b6964010132f2e9a82da340bca4a3ee728dfbdeed482dfff52e72b4f5d5b32f2040f68d3c4120b3401d1e11126b2df31ed5de4940807482b72c81070496e1f1c0929698494080cc74da4feed55abd058447ef1398607d0132c121db6277817899d7edce82ca3a658df89555b97725d054408034e534d94581f2d2abaf17a71a6e58ece989a2629d68f55ac2a3d698e7feb19d0a90c7a82d44a0b940044669f5e4111cd1eaefdc13682e20409a939af00b8137bff45aee6d2b345aaef3c5511a3a83800099e19473ee315e925d2a6fb8e81d7b58ce198111ad61d9a622704c40801c73f214819602cb10383a773d2e4223dad1b19e23d05c40803427352181430275181c19503f2f388e8879e67681f601727bc91620309d80f098eec8736c5880e4382755ce2b203ce63dfbe1772e40863fa2a90aac5f965b1b8fe78eb6ad79d6fa62eeb5be2bdf5ff99553d410adac77648ef2ac2b81470404c823cc16b920d0ea8559bf848f94519e8f6bb4bd317bcfd4fba8efd7e68df9a2d5fd47c6d5cfbb27f08880007984d92227058ebe30e3b9b5562fb97c21d77d7bf75b634b5f5ca3edcdb5d51fe3a3d5cf94bdd5dfb927308c8000a98ec26d17814f2fcd270b89f5a3c59ae51af757dad5f1cb7182e38abe318f0b0890c7c9d32f182fbb96ed0c4859f7cc98f26c19bbbcd6fde53e5ee0e53eaef598f87ca6d563e3be1e1b9fa3d5dfc5fda7efe27b8dc050020264a8e34851ccf2e5dabae8a32fcfbd3af6faafd61df59576758ebd7177d5beb76ec77e4b67141020194fad7fcdad5e70314fb4e58ee205bdfceecae7987bafd5f39667e3bbb88feb91b6f56cf47d6af5bccbfebacf3d81610504c8b047337c61cb97de95cf5b9b8c1089b6f54c8bbebaeee57c5b7d9f9e5d7e179f630f9f5af46904520b0890d4c7f7aff837dfd42fdfd1f7792670ca5e624cb97725904a4080a43a2ec50e24b0f7e28ffe236da02d2985c039010172cecbd3f7087c7ad16ead14cf6ff53fd51775447b6a3deb8c2830714d0264e2c34fb2f578412f5befd2a39ede35589f40770101d2fd0814408000819c020224e7b9bda86a5b214020ab8000c97a72ea26408040670101d2f9002cff5120fee9eec70e5f1220d04ee0db9904c8b782c6df29e02fabefd43537812f0504c89780863717f0d34773521312b8474080dce36ad61904ec91c0e4020264f23f00836dbffee9c3afaf063b1ce510580a0890a588cfbd04eaf0e855c3d6ba25d0ca75eb597d04a610e8182053f8dae431813a3ce2051dedd8c8679f1ab5ae6715ac46e0af8000f90be1d2452082235a59dc0bba48b81248202040121cd224250a8f070fda52045a080890168ae6f85640787c2b683c810e0202a403ba25ff08d4bfbafaf385ff1120904b40805c392f6308102040e04780f843d043a0fee9c3afaf7a9c803509341010200d104d714a40789ce2f2f042c0c7810404c84087314129c2638243b6c5790404c83c67dd7ba7c2a3f709589f40630101d21874f4e9d447800081560202a495a479b604fcf4b1a5a38f4052010192f4e092941dc1112d49b9ca2470a7c0fbe61620ef3bd31e3b8a90f8d496b5f827bb4b119f0924161020890f2f51e9111cd11295ac540204f60404c89e90fe2302110e5bedc81c7bcfe827406030010132d881288700010259040448969352270102047a09acac2b4056607c4d80000102db020264db472f01020408ac08089015185f136827602602ef141020ef3c57bb224080c0ed0202e476620b102040e09d021902e49df27645800081e4020224f9012a9f000102bd0404482f79eb12c820a046021b02026403471701020408ac0b0890751b3d04081020b02120403670beef3203010204de2b2040de7bb676468000815b0504c8adbc262740a0978075ef17f80d0000ffffec22dc1d0000000649444154030011e69bc983a5de430000000049454e44ae426082, NULL, NULL, NULL, NULL, 2),
(25, 17, '2026-05-08', '2026-05', 'completed', 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 02:35:48', '2026-05-08 03:29:59', NULL, NULL, NULL, NULL, NULL, 2),
(26, 17, '2026-05-08', '2026-05', 'completed', 12, 12, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-08 02:53:21', '2026-05-08 03:07:53', NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `facility_type` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `creator` int(11) DEFAULT NULL,
  `editor` int(11) DEFAULT NULL,
  `color_legend` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `facility_type`, `province`, `municipality`, `address`, `contact_person`, `contact_email`, `contact_phone`, `is_active`, `created_at`, `updated_at`, `creator`, `editor`, `color_legend`) VALUES
(1, 'Leyte Provincial Hospital', 'Provincial Hospital', 'Leyte', 'Tacloban', 'Magnolia St, Tacloban City', 'Dr. Pedro Lopez', 'info@leyte-provincial.local', '(053) 321-1234', 1, '2026-02-18 02:22:15', '2026-03-23 02:14:07', 1, 1, '#004cff'),
(2, 'Ormoc City Health Center', 'City Health Center', 'Leyte', 'Ormoc', 'Health St, Ormoc City', 'Dr. Rosa Martinez', 'info@ormoc-health.local', '(053) 231-5678', 1, '2026-02-18 02:22:15', '2026-03-23 02:14:23', 1, 1, '#ff0000'),
(3, 'Baybay Communal Health Center', 'Communal Health Center', 'Leyte', 'Baybay', 'Main St, Baybay', 'Nurse Maria Reyes', 'health@baybay-chc.local', '(053) 451-9876', 1, '2026-02-18 02:22:15', '2026-03-26 02:50:23', 1, 1, '#71fe78'),
(17, 'QMER Training Center', 'tRAINING cENTER', 'Leyte', 'Palo', 'Pawing, Palo, Leyte', '1', 'info@ormoc-health.local', NULL, 1, '2026-05-08 02:28:14', '2026-05-08 02:30:21', 1, 12, '#ffa200');

-- --------------------------------------------------------

--
-- Table structure for table `key_performance_indicators`
--

CREATE TABLE `key_performance_indicators` (
  `id` int(11) NOT NULL,
  `strategic_objective_id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `indicator_name` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `data_type` enum('numeric','percentage','text') DEFAULT 'numeric',
  `target_value` varchar(100) DEFAULT NULL,
  `means_of_verification` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `key_performance_indicators`
--

INSERT INTO `key_performance_indicators` (`id`, `strategic_objective_id`, `code`, `indicator_name`, `description`, `data_type`, `target_value`, `means_of_verification`, `sort_order`, `is_active`, `created_at`) VALUES
(11, 17, 'KPI_I_1_1', 'The presence and Functionality of the Facility Organizational Structure', 'The presence and Functionality of the Facility Organizational Structure', 'text', '1. Appropriate display of a standard Organizational chart.', 'Appropriate display of a standard Organizational chart of the office; Display of per section/deparment organizational charts.', 1, 1, '2026-02-27 04:59:14'),
(12, 17, 'KPI_I_1_2', 'The presence and Functionality of the Facility Organizational Structure', 'The presence and Functionality of the Facility Organizational Structure', 'text', '2. Appropriate display of the vision, mission and core values of the office', 'Mission and Vision with core values displayed appropriately.', 2, 1, '2026-02-27 04:59:14'),
(13, 17, 'KPI_I_1_3', 'The presence and Functionality of the Facility Organizational Structure', 'The presence and Functionality of the Facility Organizational Structure', 'text', '3.Outline and/or policy on how roles and activities are directed within the organization in achievin', 'Policy Guidelines, Office memos, Office orders, that outlines policies on how activities are directed in achieving the goals of the organizations', 3, 1, '2026-02-27 04:59:14'),
(14, 18, 'KPI_I_2_1', 'An active and operational Management Committee', 'An active and operational Management Committee', 'text', 'Regular meetings are conducted at least once a week or as necessary.', 'Office Order Memo on the creation and composition of the Facility MANCOM by the City/Municipal Health Officer.', 1, 1, '2026-02-27 04:59:14'),
(15, 18, 'KPI_I_2_2', 'An active and operational Management Committee', 'An active and operational Management Committee', 'text', 'Regular meetings are conducted at least once a week or as necessary.', 'Updated Record Logbooks of the Minutes and attendance of the meeting.', 2, 1, '2026-02-27 04:59:14'),
(16, 18, 'KPI_I_2_3', 'An active and operational Management Committee', 'An active and operational Management Committee', 'text', 'Regular meetings are conducted at least once a week or as necessary.', 'Complied Copies of resolutions or reports if any, duly approved by the committee with the timely submission of the same when required.', 3, 1, '2026-02-27 04:59:14'),
(17, 18, 'KPI_I_2_4', 'An effective and operational Office Management Sub-committee – Grievance Committee', 'An effective and operational Office Management Sub-committee – Grievance Committee', 'text', 'Regular meetings are conducted according to request.', 'a. Office orders issued by the C/MHO on the creation of various sub-committee and the composition of its members and its functions.', 4, 1, '2026-02-27 04:59:14'),
(18, 18, 'KPI_I_2_5', 'An effective and operational Office Management Sub-committee – Grievance Committee', 'An effective and operational Office Management Sub-committee – Grievance Committee', 'text', 'Regular meetings are conducted according to request.', 'b. Updated Record Logbooks of the minutes and attendance of the meetings.', 5, 1, '2026-02-27 04:59:14'),
(19, 18, 'KPI_I_2_6', 'An effective and operational Office Management Sub-committee – Grievance Committee', 'An effective and operational Office Management Sub-committee – Grievance Committee', 'text', 'Regular meetings are conducted according to request.', 'c. Complied copies of resolutions or reports and the timely submissions of the same when required.', 6, 1, '2026-02-27 04:59:14'),
(20, 19, 'KPI_I_3_1', 'Online submission of LIPH/AOP 2026-2028 as specified', 'Online submission of LIPH/AOP 2026-2028 as specified', 'text', 'LIPH/AOP 2026-2028 is crafted, completed, and submitted as required.', 'LIPH/AOP 2026-2028 is crafted per stage of planning and development as specified by PHO, CHD and the timely submission of the same as required.', 1, 1, '2026-02-27 04:59:14'),
(21, 36, 'KPI_I_6_1', 'Special Health Fund (SHF)', 'Special Health Fund (SHF)', 'text', '', 'Subsidiary ledger for SHF in their Trust Fund as Certified by the LGU Accountant.', 2, 1, '2026-02-27 04:59:14'),
(22, 34, 'KPI_I_4_1', 'Health Facility Development Plan', 'Health Facility Development Plan', 'text', '', 'LHB Resolution And Endorsing to the SB for Adoption of the Health Facility Developments', 1, 1, '2026-02-27 04:59:14'),
(23, 35, 'KPI_I_5_1', 'Local Health Board', 'Local Health Board', 'text', 'All LGUs have 3/3 LHB Components', 'With presence of 3/3 LHB Components. (1) EO on LHB Organization. (2) Received copy of LHB resolution to the Sanggunian or the Local Development Council proposing the annual health budget. (3) Received copy of at least 4 LHB resolutions per year to the Sanggunian on matters pertaining to health. (4) Minutes of the meeting.', 1, 1, '2026-02-27 04:59:14'),
(24, 35, 'KPI_I_5_2', 'Functional Local AIDS Council', 'Functional Local AIDS Council', 'text', 'All LGUs have complied with the 3/3 components', 'Complied with 3/3 components: (1) For creation of Council – Local Policy (Executive Order/Ordinance) issued by LGU on creation of LACs with multi-sectoral composition and corresponding functions, as harmonized with RA 11186. (2) For Budget – Local Investment Plan for Health (LIPH)/Annual Investment Plan (AOP). (3) For HIV-related programs/activities and projects implementation – LGU Annual Activity Report through DILG submitted to PNAC Secretariat (pursuant to Section 5.1 of IRR of RA 11186).', 2, 1, '2026-02-27 04:59:14'),
(25, 35, 'KPI_I_5_3', 'Local Government Units with institutionalized Disaster Risk Reduction and Management in Health (DRRM-H) System', 'Local Government Units with institutionalized Disaster Risk Reduction and Management in Health (DRRM-H) System', 'text', 'All LGUs have 4/4 DRRM-H Components', 'With presence of 4 DRRM-H Components: (1) Approved, updated, disseminated and tested Disaster Risk Reduction and Management in Health (DRRM-H) Plan. (2) Organized and trained Health Emergency Response Team on minimum required trainings: Basic Life Support (BLS), Standard First Aid (SFA). (3) Available and accessible within 24 hours post impact of emergency or disaster essential health emergency commodities e.g. medicines such as anti-infectives, analgesics, antipyretics, fluids/electrolytes, res', 3, 1, '2026-02-27 04:59:14'),
(26, 35, 'KPI_I_5_4', 'Organized Epidemiology and Surveillance Unit (ESU)', 'Organized Epidemiology and Surveillance Unit (ESU)', 'text', 'All LGUs have 11/11 components', '(1) Narrative Report with Baseline or Situational Analysis. (2) EO/Ordinance for the Creation of ESU. (3) EO or Office Order for DSO Designation. (4) Organizational Chart. (5) List of ESU Staff and Training Certificates. (6) Signed Health Board Resolution. (7) Photos of the ESU designated tables and chairs. (8) List of ESU Equipment and Supplies. (9) Ordinance/WFP/LIPH/AOP with Budget for Surveillance. (10) FHSIS Reports. (11) Weekly Notifiable Disease Report forms, CIFS, ESR, IMRAD.', 4, 1, '2026-02-27 04:59:14'),
(27, 35, 'KPI_I_5_5', 'Health promotion policies and programs implemented', 'Health promotion policies and programs implemented', 'text', 'Identified health promotion policies and programs implemented 9/9', '(1) Maternal and Child Nutrition. (2) Community nutrition (Barangay/Local Nutrition Prog). (3) Mandatory Infant and Children Health Immunization. (4) Tobacco and vape use prevention. (5) Restricted access of minors to alcoholic beverages. (6) Hygiene and sanitation. (7) Mental health. (8) Violence and Injury prevention (prevention interpersonal violence, prevention of fireworks-related injuries, and road-safety promotions). (9) Empowerment mechanisms for Barangay Health Workers.', 5, 1, '2026-02-27 04:59:14'),
(28, 35, 'KPI_I_5_6', 'Localized Mental Health Program', 'Localized Mental Health Program', 'text', 'Complied with 9/9 components', '(1) Presence of Local Ordinance/EO or similar regulation. (2) A coordinated body (e.g., through Local Health Boards or Regional Council or a Mental Health Working Group as applicable). (3) Complementary personnel – composed of one (1) MHO and (1) nurse or allied health professional preferably a plantilla position. (4) Complementary personnel serving as MH service providers trained on MHGap, MHPSS, SBIRT, CBDR among other DOH-prescribed MH training. (5) Promotion and awareness campaign plans usin', 1, 1, '2026-02-27 04:59:14'),
(29, 20, 'KPI_II_1_1', 'Primary care Facility License to Operate', NULL, 'text', 'Certified PCF Accredited', 'Certificate License to Operate by DOH', 1, 1, '2026-02-27 05:52:16'),
(30, 20, 'KPI_II_1_2', 'Birthing Clinic', NULL, 'text', NULL, 'License to Operate Issued by DOH', 2, 1, '2026-02-27 05:52:16'),
(31, 20, 'KPI_II_1_3', 'Ambulance DoH Licensed', NULL, 'text', 'DOH Certified', 'Certificate of Registration', 3, 1, '2026-02-27 05:52:16'),
(32, 20, 'KPI_II_1_4', 'PhilHealth Yakap/Konsulta Accreditation', NULL, 'text', 'PhilHealth Accredited', 'PhilHealth Certificate of accreditation', 4, 1, '2026-02-27 05:52:16'),
(33, 21, 'KPI_II_2_1', 'percent of health facilities with paperless record and regularly summit data', NULL, 'text', 'DOH Certified', 'DOH Accredited EMR', 1, 1, '2026-02-27 05:52:16'),
(34, 22, 'KPI_II_3_1', 'eLMIS Functionality', NULL, 'text', '', 'Generated eLMIS Report (timely and complete)', 1, 1, '2026-02-27 05:52:16'),
(35, 22, 'KPI_II_3_2', 'TB-DOTS', NULL, 'text', '', 'TB DOTS Certificate', 2, 1, '2026-02-27 05:52:16'),
(36, 22, 'KPI_II_3_3', 'Animal Bite Treatment Clinic', NULL, 'text', 'ABTC Certificated', 'Registered or Certificate of Animal Bite Treatment Clinic', 3, 1, '2026-02-27 05:52:16'),
(37, 22, 'KPI_II_3_4', 'CPG Guidelines', NULL, 'text', '(N/A)', 'LHB Resolution For Application', 4, 1, '2026-02-27 05:52:16'),
(38, 23, 'KPI_II_4_1', 'Telemedicine', NULL, 'text', 'Baseline', 'Logbook, Screenshots of prescription using digital app or mobile phone', 1, 1, '2026-02-27 05:52:16'),
(95, 24, 'KPI_III_1_1', 'PhilHealth Yakap/Konsulta Accreditation', NULL, 'text', '100% PhilHealth Reimbursement', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1 (3) list of registered to YAKAP and Konsulta', 1, 1, '2026-02-27 06:18:23'),
(96, 25, 'KPI_III_2_1', 'MAIFIP', NULL, 'text', '100% Utilized', 'Fund Utilization', 1, 1, '2026-02-27 06:18:57'),
(97, 26, 'KPI_III_3_1', 'Percent of Filipinos registered to a Primary Care Provider', NULL, 'text', '90% of the total population', 'PhilHealth Yakap/Konsulta Provider enrolled', 1, 1, '2026-02-27 06:19:29'),
(109, 26, 'KPI_III_3_3', 'Maternal and Child Care Package', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 3, 1, '2026-02-27 06:20:49'),
(110, 26, 'KPI_III_3_4', 'Animal Bite Treatment', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 4, 1, '2026-02-27 06:20:49'),
(111, 26, 'KPI_III_3_5', 'Gamot', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 5, 1, '2026-02-27 06:20:49'),
(112, 26, 'KPI_III_3_6', 'TB-DOTS', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 6, 1, '2026-02-27 06:21:04'),
(113, 26, 'KPI_III_3_7', 'Family Planning Claim', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 7, 1, '2026-02-27 06:21:04'),
(114, 26, 'KPI_III_3_8', 'Mental Health', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 8, 1, '2026-02-27 06:21:04'),
(115, 26, 'KPI_III_3_9', 'Nutrition', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 9, 1, '2026-02-27 06:21:24'),
(116, 26, 'KPI_III_3_10', 'Oral Health Care Package', NULL, 'text', '100%', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 10, 1, '2026-02-27 06:21:24'),
(117, 26, 'KPI_III_3_11', 'Eye care Package', NULL, 'text', 'Baseline', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 11, 1, '2026-02-27 06:21:24'),
(120, 26, 'KPI_III_3_12', 'HIV Package', NULL, 'text', 'Baseline', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 12, 1, '2026-02-27 06:22:27'),
(121, 26, 'KPI_III_3_13', 'Emergency Care Package', NULL, 'text', 'Baseline', 'PhilHealth Reimbursement (1) vouchers of successful claims as to how many enrolled, per quarterly report (2) issue of SAP 1', 13, 1, '2026-02-27 06:22:27'),
(122, 29, 'KPI_IV_1_1', 'Provision of FULL hazard pay, subsistence, and laundry allowances to permanent public health workers (Physician, Public Health Nurse & Midwife) in accordance with RA 7305 (Magna Carta of Public Health Workers).', NULL, 'text', 'An LGU must have provided all the three incentives (hazard pay, subsistence, and laundry allowance) ', 'The salary of the Physician, Public Health Nurse & Midwife complied with the Salary Standardization Law, and benefits are fully given to ALL the permanent LGU-hired health workers: 1. Hazard Allowance 2. Laundry Allowance 3. Subsistence Allowance.', 1, 1, '2026-02-27 06:44:37'),
(123, 29, 'KPI_IV_1_2', 'National Health Work Registry (NWHR) Report.', NULL, 'text', '100%', 'Generated from the NWHR and Signed NHWR', 2, 1, '2026-02-27 06:44:37'),
(124, 29, 'KPI_IV_1_3', 'List of DOH-PRC Certified primary care health workers in public primary care facilities.', NULL, 'text', '100%', 'List of DOH-PRC Certified primary care health workers in public primary care facilities.', 3, 1, '2026-02-27 06:44:37'),
(125, 30, 'KPI_IV_2_1', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'MD = 1:20,000', 'LGU Hired and DOH Hired.', 1, 1, '2026-02-27 06:44:37'),
(126, 37, 'KPI_V_1_1', 'Demand Satisfied with Modern Family Planning (mFP) Methods.', NULL, 'text', '62%', 'FHSIS Report ( TCL and Q1 Reports)', 1, 1, '2026-02-27 07:24:45'),
(127, 37, 'KPI_V_1_2', 'Women who delivered and provided with at least 8 or more prenatal check-ups', '', 'text', '95%', 'FHSIS Report ( TCL and Q1 Reports)', 2, 1, '2026-02-27 07:24:45'),
(128, 37, 'KPI_V_1_3', 'Postpartum women together with their newborn who completed at least 4 postnatal care', '', 'text', '95%', 'FHSIS Report ( TCL and Q1 Reports)', 3, 1, '2026-02-27 07:24:45'),
(129, 37, 'KPI_V_1_4', 'Fully Immunized Child (FIC)', '', 'text', '95%', 'FHSIS Report ( TCL, M1 and Q1 Reports)', 4, 1, '2026-02-27 07:24:45'),
(130, 37, 'KPI_V_1_5', 'Prevalence of stunting among children under five years of age', '', 'text', '13.5%', 'OPT Plus Report (MNAO)', 5, 1, '2026-02-27 07:24:45'),
(131, 37, 'KPI_V_1_7', 'Elderlies who are risk-assessed using the Philippine Package of Essential NCD Interventions (PHILPEN) Protocol', '', 'text', '40%', 'FHSIS Report ( TCL, M1 and Q1 Reports)', 7, 1, '2026-02-27 07:24:45'),
(132, 37, 'KPI_V_1_9', 'Mass Drug Administration for the Schistosomiasis Control and Prevention Program', '', 'text', '85%', 'FHSIS Report ( TCL and Q1 Reports)', 9, 1, '2026-02-27 07:24:45'),
(133, 37, 'KPI_V_1_10', 'TB case Notification Rate all forms', '', 'text', '>10% from the previous year accomplished or has achieved the national target', 'Integrated Tuberculosis Information System (ITIS)', 10, 1, '2026-02-27 07:24:45'),
(134, 37, 'KPI_V_1_11', 'TB Treatment Success Rate - all forms', '', 'text', 'equal or greater than 90%', 'Integrated Tuberculosis Information System (ITIS)', 11, 1, '2026-02-27 07:24:45'),
(135, 37, 'KPI_V_1_13', 'Households using Safely Managed Drinking-Water Services', '', 'text', '82.41%', 'FHSIS Report - Rural Sanitation Inspector Verified and Signed', 13, 1, '2026-02-27 07:24:45'),
(136, 37, 'KPI_V_1_14', 'Households using Safely Managed Sanitation Services', '', 'text', '83.05%', 'FHSIS Report - Rural Sanitation Inspector Verified and Signed', 14, 1, '2026-02-27 07:24:45'),
(137, 37, 'KPI_V_1_15', 'National Voluntary Blood Services Program (NVBSP)', '', 'text', '1%', 'NVBSP Accomplishment Report Singed and Submitted to PHO', 15, 1, '2026-02-27 07:24:45'),
(138, 37, 'KPI_V_1_16', 'Rabies Control and Prevention Program', '', 'text', '100%', 'FHSIS Report (Registry or Morbidity and Q1 Reports)', 16, 1, '2026-02-27 07:24:45'),
(139, 37, 'KPI_V_1_17', 'Newborn Screening Services Program', '', 'text', '100%', 'NBS Quarterly Report Signed and Submitted to PHO', 17, 1, '2026-02-27 07:24:45'),
(140, 38, 'KPI_V_2_1', 'Referral System', NULL, 'text', '100%', 'Health Facility referral logbook Monthly Reports', 1, 1, '2026-02-27 07:25:59'),
(141, 38, 'KPI_V_2_2', 'Field Health Services Information System', NULL, 'text', '100%', 'Submitted Reports to PHO via Email or Hard Copy on the 5th Day of the following month (Accurate, Completeness and Timeliness)', 2, 1, '2026-02-27 07:25:59'),
(142, 39, 'KPI_VI_1_1', 'QMeR system installed and operational', NULL, 'text', 'System installed and accessible in RHU', 'System demonstration, workstation inspection', 1, 1, '2026-02-27 07:31:15'),
(143, 40, 'KPI_VI_2_1', 'Patient consultations encoded in QMeR', NULL, 'text', 'Minimum 50 consultations encoded per month (or PHO standard)', 'System dashboard report, consultation records', 1, 1, '2026-02-27 07:31:15'),
(144, 8, 'KPI_VII_1_1', 'Private and Public Birthing Clinic Supportive Supervision and Visit (SSV)', NULL, 'text', '100% ILHZ Coverage (for baseline)', 'SSV conducted by the BeMONC Team and with Quarterly Reports of Deliveries (See Monitoring Tool)', 1, 1, '2026-02-27 07:32:13'),
(145, 26, 'KPI_III_3_2', 'Percent of Filipinos registered to a Primary Care Provider first patient encounter', NULL, 'text', '10% of the total projected population', 'PhilHealth Yakap/Konsulta Provider enrolled ', 2, 1, '2026-03-13 05:08:24'),
(146, 30, 'KPI_IV_2_2', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'Nurse = 1:10,000 ', 'LGU Hired and DOH Hired.', 2, 1, '2026-02-27 06:44:37'),
(147, 30, 'KPI_IV_2_3', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'Med Tech = 1:50,000 ', 'LGU Hired and DOH Hired.', 3, 1, '2026-02-27 06:44:37'),
(148, 30, 'KPI_IV_2_4', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'Midwife = 1: 5,000 ', 'LGU Hired and DOH Hired.', 4, 1, '2026-02-27 06:44:37'),
(149, 30, 'KPI_IV_2_5', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'Dentist = 1:50,000', 'LGU Hired and DOH Hired.', 5, 1, '2026-02-27 06:44:37'),
(150, 30, 'KPI_IV_2_6', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'RSI = 1:20,000', 'LGU Hired and DOH Hired.', 6, 1, '2026-02-27 06:44:37'),
(151, 30, 'KPI_IV_2_7', 'as to the National Objective for Health 2026-2028.', NULL, 'text', 'BHW = 1:20 HH', 'LGU Hired and DOH Hired.', 7, 1, '2026-02-27 06:44:37'),
(152, 37, 'KPI_V_1_12', 'Tuberculosis Preventive Treatment', NULL, 'text', '>75% of TB contacts eligible for TPT', 'Integrated Tubeculosis Information System (ITIS)', 12, 1, '2026-03-13 05:37:12'),
(153, 37, 'KPI_V_1_6', 'Adults who are risk-assessed using the Philippine Package of Essential NCD Interventions (PHILPEN) Protocol', '', 'text', '40%', 'FHSIS Report ( TCL, M1 and Q1 Reports)', 6, 1, '2026-02-27 07:24:45'),
(154, 37, 'KPI_V_1_8', 'Individuals Screened for eye ailment/s', '', 'text', 'Baseline', 'Target Client List for Eye Ailment/Screening ; Q1 Report signed', 8, 1, '2026-02-27 07:24:45'),
(155, 41, 'KPI_VI_3_1', 'QRef system utilized for referrals', NULL, 'text', 'Minimum 5 referrals encoded within evaluation period', 'QRef system report, referral records', 1, 1, '2026-02-27 07:31:15'),
(156, 42, 'KPI_VI_4_1', 'RHU personnel trained in QMeR system', NULL, 'text', 'At least 1 to 3 trained staff', 'Certificate of Training, training attendance sheet', 1, 1, '2026-02-27 07:31:15'),
(157, 43, 'KPI_VI_5_1', 'RHU personnel trained in QRef system', NULL, 'text', 'At least 1 to 3 trained staff', 'Certificate of Training, training documentation', 1, 1, '2026-02-27 07:31:15'),
(158, 44, 'KPI_VI_6_1', 'QMeR used during patient consultation', NULL, 'text', 'Majority of consultations encoded', 'Observation during visit, system logs', 1, 1, '2026-02-27 07:31:15'),
(159, 45, 'KPI_VI_7_1', 'Patient records contain complete basic information', NULL, 'text', '100% completeness of patient records', 'Random patient record validation (Patient chart sample)', 1, 1, '2026-02-27 07:31:15'),
(160, 46, 'KPI_VI_8_1', 'RHU has functional computer for QMeR', NULL, 'text', 'At least 1 dedicated workstation', 'Physical inspection (reports and inventory)', 1, 1, '2026-02-27 07:31:15'),
(161, 47, 'KPI_VI_9_1', 'Internet connection available for Quick Referral system use ', NULL, 'text', 'Stable internet connection', 'Connection test / observation (proof of connection)', 1, 1, '2026-02-27 07:31:15'),
(162, 48, 'KPI_VI_10_1', 'RHU recognized as active user of system', NULL, 'text', 'Facility certified by PGO_SPS and PHO', 'Certificate of System Utilization', 1, 1, '2026-02-27 07:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `key_performance_indicator_id` int(11) NOT NULL,
  `score_value` decimal(10,2) DEFAULT NULL,
  `percentage_value` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `last_edited_by` int(11) DEFAULT NULL,
  `last_edited_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `evaluation_id`, `key_performance_indicator_id`, `score_value`, `percentage_value`, `remarks`, `last_edited_by`, `last_edited_at`, `created_at`) VALUES
(420, 18, 11, 1.00, 100.00, '', 1, '2026-03-23 03:35:48', '2026-03-23 03:28:14'),
(421, 18, 12, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(422, 18, 13, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(423, 18, 14, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(424, 18, 15, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(425, 18, 16, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(426, 18, 17, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(427, 18, 18, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(428, 18, 19, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(429, 18, 20, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(430, 18, 21, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(431, 18, 22, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(432, 18, 23, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(433, 18, 24, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(434, 18, 25, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(435, 18, 26, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(436, 18, 27, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(437, 18, 28, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(438, 18, 29, 1.00, 100.00, '', 1, '2026-03-23 03:36:49', '2026-03-23 03:28:14'),
(439, 18, 30, 1.00, 100.00, '', 1, '2026-03-23 03:36:52', '2026-03-23 03:28:14'),
(440, 18, 31, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(441, 18, 32, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(442, 18, 33, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(443, 18, 34, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(444, 18, 35, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(445, 18, 36, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(446, 18, 37, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(447, 18, 38, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(448, 18, 95, 1.00, 100.00, '', 1, '2026-03-23 03:36:53', '2026-03-23 03:28:14'),
(449, 18, 96, 1.00, 100.00, '', 1, '2026-03-23 03:36:54', '2026-03-23 03:28:14'),
(450, 18, 97, 1.00, 100.00, '', 1, '2026-03-23 03:36:54', '2026-03-23 03:28:14'),
(451, 18, 109, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(452, 18, 110, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(453, 18, 111, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(454, 18, 112, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(455, 18, 113, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(456, 18, 114, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(457, 18, 115, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(458, 18, 116, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(459, 18, 117, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(460, 18, 120, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(461, 18, 121, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(462, 18, 122, 1.00, 100.00, '', 1, '2026-03-23 03:36:56', '2026-03-23 03:28:14'),
(463, 18, 123, 1.00, 100.00, '', 1, '2026-03-23 03:36:56', '2026-03-23 03:28:14'),
(464, 18, 124, 1.00, 100.00, '', 1, '2026-03-23 03:36:56', '2026-03-23 03:28:14'),
(465, 18, 125, 1.00, 100.00, '', 1, '2026-03-23 03:37:16', '2026-03-23 03:28:14'),
(466, 18, 126, 1.00, 100.00, '', 1, '2026-03-23 03:37:12', '2026-03-23 03:28:14'),
(467, 18, 127, 1.00, 100.00, '', 1, '2026-03-23 03:37:13', '2026-03-23 03:28:14'),
(468, 18, 128, 1.00, 100.00, '', 1, '2026-03-23 03:37:13', '2026-03-23 03:28:14'),
(469, 18, 129, 1.00, 100.00, '', 1, '2026-03-23 03:37:14', '2026-03-23 03:28:14'),
(470, 18, 130, 1.00, 100.00, '', 1, '2026-03-23 03:37:29', '2026-03-23 03:28:14'),
(471, 18, 131, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(472, 18, 132, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(473, 18, 133, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(474, 18, 134, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(475, 18, 135, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(476, 18, 136, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(477, 18, 137, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(478, 18, 138, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(479, 18, 139, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(480, 18, 140, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(481, 18, 141, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(482, 18, 142, 1.00, 100.00, '', 1, '2026-03-23 03:37:00', '2026-03-23 03:28:14'),
(483, 18, 143, 1.00, 100.00, '', 1, '2026-03-23 03:37:00', '2026-03-23 03:28:14'),
(484, 18, 144, 1.00, 100.00, '', 1, '2026-03-23 03:37:32', '2026-03-23 03:28:14'),
(485, 18, 145, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(486, 18, 146, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(487, 18, 147, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(488, 18, 148, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(489, 18, 149, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(490, 18, 150, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(491, 18, 151, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(492, 18, 152, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(493, 18, 153, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(494, 18, 154, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(495, 18, 155, 1.00, 100.00, '', 1, '2026-03-23 03:37:01', '2026-03-23 03:28:14'),
(496, 18, 156, 1.00, 100.00, '', 1, '2026-03-23 03:37:01', '2026-03-23 03:28:14'),
(497, 18, 157, 1.00, 100.00, '', 1, '2026-03-23 03:37:01', '2026-03-23 03:28:14'),
(498, 18, 158, 1.00, 100.00, '', 1, '2026-03-23 03:37:09', '2026-03-23 03:28:14'),
(499, 18, 159, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(500, 18, 160, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(501, 18, 161, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(502, 18, 162, NULL, NULL, '', NULL, '2026-03-23 03:28:14', '2026-03-23 03:28:14'),
(503, 19, 11, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(504, 19, 12, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(505, 19, 13, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(506, 19, 14, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(507, 19, 15, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(508, 19, 16, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(509, 19, 17, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(510, 19, 18, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(511, 19, 19, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(512, 19, 20, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(513, 19, 21, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(514, 19, 22, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(515, 19, 23, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(516, 19, 24, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(517, 19, 25, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(518, 19, 26, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(519, 19, 27, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(520, 19, 28, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(521, 19, 29, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(522, 19, 30, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(523, 19, 31, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(524, 19, 32, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(525, 19, 33, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(526, 19, 34, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(527, 19, 35, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(528, 19, 36, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(529, 19, 37, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(530, 19, 38, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(531, 19, 95, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(532, 19, 96, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(533, 19, 97, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(534, 19, 109, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(535, 19, 110, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(536, 19, 111, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(537, 19, 112, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(538, 19, 113, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(539, 19, 114, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(540, 19, 115, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(541, 19, 116, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(542, 19, 117, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(543, 19, 120, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(544, 19, 121, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(545, 19, 122, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(546, 19, 123, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(547, 19, 124, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(548, 19, 125, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(549, 19, 126, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(550, 19, 127, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(551, 19, 128, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(552, 19, 129, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(553, 19, 130, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(554, 19, 131, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(555, 19, 132, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(556, 19, 133, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(557, 19, 134, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(558, 19, 135, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(559, 19, 136, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(560, 19, 137, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(561, 19, 138, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(562, 19, 139, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(563, 19, 140, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(564, 19, 141, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(565, 19, 142, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(566, 19, 143, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(567, 19, 144, 1.00, 100.00, '', 1, '2026-03-23 08:02:17', '2026-03-23 03:32:29'),
(568, 19, 145, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(569, 19, 146, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(570, 19, 147, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(571, 19, 148, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(572, 19, 149, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(573, 19, 150, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(574, 19, 151, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(575, 19, 152, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(576, 19, 153, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(577, 19, 154, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(578, 19, 155, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(579, 19, 156, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(580, 19, 157, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(581, 19, 158, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(582, 19, 159, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(583, 19, 160, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(584, 19, 161, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(585, 19, 162, NULL, NULL, '', NULL, '2026-03-23 03:32:29', '2026-03-23 03:32:29'),
(586, 20, 11, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(587, 20, 12, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(588, 20, 13, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(589, 20, 14, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(590, 20, 15, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(591, 20, 16, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(592, 20, 17, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(593, 20, 18, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(594, 20, 19, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(595, 20, 20, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(596, 20, 21, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(597, 20, 22, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(598, 20, 23, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(599, 20, 24, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(600, 20, 25, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(601, 20, 26, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(602, 20, 27, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(603, 20, 28, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(604, 20, 29, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(605, 20, 30, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(606, 20, 31, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(607, 20, 32, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(608, 20, 33, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(609, 20, 34, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(610, 20, 35, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(611, 20, 36, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(612, 20, 37, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(613, 20, 38, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(614, 20, 95, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(615, 20, 96, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(616, 20, 97, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(617, 20, 109, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(618, 20, 110, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(619, 20, 111, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(620, 20, 112, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(621, 20, 113, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(622, 20, 114, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(623, 20, 115, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(624, 20, 116, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(625, 20, 117, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(626, 20, 120, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(627, 20, 121, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(628, 20, 122, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(629, 20, 123, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(630, 20, 124, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(631, 20, 125, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(632, 20, 126, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(633, 20, 127, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(634, 20, 128, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(635, 20, 129, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(636, 20, 130, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(637, 20, 131, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(638, 20, 132, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(639, 20, 133, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(640, 20, 134, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(641, 20, 135, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(642, 20, 136, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(643, 20, 137, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(644, 20, 138, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(645, 20, 139, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(646, 20, 140, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(647, 20, 141, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(648, 20, 142, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(649, 20, 143, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(650, 20, 144, 1.00, 100.00, '', 1, '2026-03-23 08:02:04', '2026-03-23 03:32:42'),
(651, 20, 145, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(652, 20, 146, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(653, 20, 147, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(654, 20, 148, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(655, 20, 149, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(656, 20, 150, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(657, 20, 151, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(658, 20, 152, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(659, 20, 153, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(660, 20, 154, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(661, 20, 155, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(662, 20, 156, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(663, 20, 157, 1.00, 100.00, '', 1, '2026-03-23 08:24:06', '2026-03-23 03:32:42'),
(664, 20, 158, 1.00, 100.00, '', 1, '2026-03-23 08:24:05', '2026-03-23 03:32:42'),
(665, 20, 159, 1.00, 100.00, '', 1, '2026-03-23 08:24:04', '2026-03-23 03:32:42'),
(666, 20, 160, 1.00, 100.00, '', 1, '2026-03-23 08:24:04', '2026-03-23 03:32:42'),
(667, 20, 161, 1.00, 100.00, '', 1, '2026-03-23 08:24:03', '2026-03-23 03:32:42'),
(668, 20, 162, NULL, NULL, '', NULL, '2026-03-23 03:32:42', '2026-03-23 03:32:42'),
(669, 21, 11, 1.00, 100.00, '', 1, '2026-03-23 03:38:30', '2026-03-23 03:38:21'),
(670, 21, 12, 1.00, 100.00, '', 1, '2026-03-23 03:38:31', '2026-03-23 03:38:21'),
(671, 21, 13, 1.00, 100.00, '', 1, '2026-03-23 03:38:31', '2026-03-23 03:38:21'),
(672, 21, 14, 1.00, 100.00, '', 1, '2026-03-23 03:38:32', '2026-03-23 03:38:21'),
(673, 21, 15, 1.00, 100.00, '', 1, '2026-03-23 03:38:33', '2026-03-23 03:38:21'),
(674, 21, 16, 1.00, 100.00, '', 1, '2026-03-23 03:38:33', '2026-03-23 03:38:21'),
(675, 21, 17, 0.00, 0.00, '', 1, '2026-03-23 03:41:59', '2026-03-23 03:38:21'),
(676, 21, 18, 0.00, 0.00, '', 1, '2026-03-23 03:41:59', '2026-03-23 03:38:21'),
(677, 21, 19, 0.00, 0.00, '', 1, '2026-03-23 03:41:58', '2026-03-23 03:38:21'),
(678, 21, 20, 0.00, 0.00, '', 1, '2026-03-23 03:41:57', '2026-03-23 03:38:21'),
(679, 21, 21, 0.00, 0.00, '', 1, '2026-03-23 03:41:53', '2026-03-23 03:38:21'),
(680, 21, 22, 0.00, 0.00, '', 1, '2026-03-23 03:41:57', '2026-03-23 03:38:21'),
(681, 21, 23, 0.00, 0.00, '', 1, '2026-03-23 03:41:56', '2026-03-23 03:38:21'),
(682, 21, 24, 0.00, 0.00, '', 1, '2026-03-23 03:41:55', '2026-03-23 03:38:21'),
(683, 21, 25, 0.00, 0.00, '', 1, '2026-03-23 03:41:54', '2026-03-23 03:38:21'),
(684, 21, 26, 0.00, 0.00, '', 1, '2026-03-23 03:41:54', '2026-03-23 03:38:21'),
(685, 21, 27, 0.00, 0.00, '', 1, '2026-03-23 03:41:53', '2026-03-23 03:38:21'),
(686, 21, 28, 0.00, 0.00, '', 1, '2026-03-23 03:41:56', '2026-03-23 03:38:21'),
(687, 21, 29, 1.00, 100.00, '', 1, '2026-03-23 03:38:39', '2026-03-23 03:38:21'),
(688, 21, 30, 1.00, 100.00, '', 1, '2026-03-23 03:38:40', '2026-03-23 03:38:21'),
(689, 21, 31, 1.00, 100.00, '', 1, '2026-03-23 03:38:40', '2026-03-23 03:38:21'),
(690, 21, 32, 1.00, 100.00, '', 1, '2026-03-23 03:38:41', '2026-03-23 03:38:21'),
(691, 21, 33, 1.00, 100.00, '', 1, '2026-03-23 03:38:41', '2026-03-23 03:38:21'),
(692, 21, 34, 0.00, 0.00, '', 1, '2026-03-23 03:41:52', '2026-03-23 03:38:21'),
(693, 21, 35, 0.00, 0.00, '', 1, '2026-03-23 03:41:51', '2026-03-23 03:38:21'),
(694, 21, 36, 0.00, 0.00, '', 1, '2026-03-23 03:41:51', '2026-03-23 03:38:21'),
(695, 21, 37, 0.00, 0.00, '', 1, '2026-03-23 03:41:50', '2026-03-23 03:38:21'),
(696, 21, 38, 0.00, 0.00, '', 1, '2026-03-23 03:41:50', '2026-03-23 03:38:21'),
(697, 21, 95, 1.00, 100.00, '', 1, '2026-03-23 03:38:44', '2026-03-23 03:38:21'),
(698, 21, 96, 1.00, 100.00, '', 1, '2026-03-23 03:38:44', '2026-03-23 03:38:21'),
(699, 21, 97, 1.00, 100.00, '', 1, '2026-03-23 03:38:45', '2026-03-23 03:38:21'),
(700, 21, 109, 0.00, 0.00, '', 1, '2026-03-23 03:41:49', '2026-03-23 03:38:21'),
(701, 21, 110, 0.00, 0.00, '', 1, '2026-03-23 03:41:48', '2026-03-23 03:38:21'),
(702, 21, 111, 0.00, 0.00, '', 1, '2026-03-23 03:41:47', '2026-03-23 03:38:21'),
(703, 21, 112, 0.00, 0.00, '', 1, '2026-03-23 03:41:47', '2026-03-23 03:38:21'),
(704, 21, 113, 0.00, 0.00, '', 1, '2026-03-23 03:41:47', '2026-03-23 03:38:21'),
(705, 21, 114, 0.00, 0.00, '', 1, '2026-03-23 03:41:44', '2026-03-23 03:38:21'),
(706, 21, 115, 0.00, 0.00, '', 1, '2026-03-23 03:41:46', '2026-03-23 03:38:21'),
(707, 21, 116, 0.00, 0.00, '', 1, '2026-03-23 03:41:44', '2026-03-23 03:38:21'),
(708, 21, 117, 0.00, 0.00, '', 1, '2026-03-23 03:41:42', '2026-03-23 03:38:21'),
(709, 21, 120, 0.00, 0.00, '', 1, '2026-03-23 03:41:42', '2026-03-23 03:38:21'),
(710, 21, 121, 0.00, 0.00, '', 1, '2026-03-23 03:41:42', '2026-03-23 03:38:21'),
(711, 21, 122, 1.00, 100.00, '', 1, '2026-03-23 03:38:48', '2026-03-23 03:38:21'),
(712, 21, 123, 1.00, 100.00, '', 1, '2026-03-23 03:38:48', '2026-03-23 03:38:21'),
(713, 21, 124, 1.00, 100.00, '', 1, '2026-03-23 03:38:49', '2026-03-23 03:38:21'),
(714, 21, 125, 0.00, 0.00, '', 1, '2026-03-23 03:40:01', '2026-03-23 03:38:21'),
(715, 21, 126, 1.00, 100.00, '', 1, '2026-03-23 03:38:50', '2026-03-23 03:38:21'),
(716, 21, 127, 1.00, 100.00, '', 1, '2026-03-23 03:38:50', '2026-03-23 03:38:21'),
(717, 21, 128, 0.00, 0.00, '', 1, '2026-03-23 03:39:54', '2026-03-23 03:38:21'),
(718, 21, 129, 0.00, 0.00, '', 1, '2026-03-23 03:39:54', '2026-03-23 03:38:21'),
(719, 21, 130, 0.00, 0.00, '', 1, '2026-03-23 03:39:54', '2026-03-23 03:38:21'),
(720, 21, 131, 0.00, 0.00, '', 1, '2026-03-23 03:39:52', '2026-03-23 03:38:21'),
(721, 21, 132, 0.00, 0.00, '', 1, '2026-03-23 03:39:52', '2026-03-23 03:38:21'),
(722, 21, 133, 0.00, 0.00, '', 1, '2026-03-23 03:39:51', '2026-03-23 03:38:21'),
(723, 21, 134, 0.00, 0.00, '', 1, '2026-03-23 03:39:50', '2026-03-23 03:38:21'),
(724, 21, 135, 0.00, 0.00, '', 1, '2026-03-23 03:39:50', '2026-03-23 03:38:21'),
(725, 21, 136, 0.00, 0.00, '', 1, '2026-03-23 03:39:49', '2026-03-23 03:38:21'),
(726, 21, 137, 0.00, 0.00, '', 1, '2026-03-23 03:39:48', '2026-03-23 03:38:21'),
(727, 21, 138, 0.00, 0.00, '', 1, '2026-03-23 03:39:47', '2026-03-23 03:38:21'),
(728, 21, 139, 0.00, 0.00, '', 1, '2026-03-23 03:39:47', '2026-03-23 03:38:21'),
(729, 21, 140, 0.00, 0.00, '', 1, '2026-03-23 03:39:46', '2026-03-23 03:38:21'),
(730, 21, 141, 0.00, 0.00, '', 1, '2026-03-23 03:39:46', '2026-03-23 03:38:21'),
(731, 21, 142, 1.00, 100.00, '', 1, '2026-03-23 03:38:53', '2026-03-23 03:38:21'),
(732, 21, 143, 0.00, 0.00, '', 1, '2026-03-23 03:39:35', '2026-03-23 03:38:21'),
(733, 21, 144, 1.00, 100.00, '', 1, '2026-03-23 03:39:01', '2026-03-23 03:38:21'),
(734, 21, 145, 1.00, 100.00, '', 1, '2026-03-23 03:38:46', '2026-03-23 03:38:21'),
(735, 21, 146, 0.00, 0.00, '', 1, '2026-03-23 03:40:01', '2026-03-23 03:38:21'),
(736, 21, 147, 0.00, 0.00, '', 1, '2026-03-23 03:40:00', '2026-03-23 03:38:21'),
(737, 21, 148, 0.00, 0.00, '', 1, '2026-03-23 03:39:57', '2026-03-23 03:38:21'),
(738, 21, 149, 0.00, 0.00, '', 1, '2026-03-23 03:39:57', '2026-03-23 03:38:21'),
(739, 21, 150, 0.00, 0.00, '', 1, '2026-03-23 03:39:56', '2026-03-23 03:38:21'),
(740, 21, 151, 0.00, 0.00, '', 1, '2026-03-23 03:40:00', '2026-03-23 03:38:21'),
(741, 21, 152, 0.00, 0.00, '', 1, '2026-03-23 03:39:50', '2026-03-23 03:38:21'),
(742, 21, 153, 0.00, 0.00, '', 1, '2026-03-23 03:39:53', '2026-03-23 03:38:21'),
(743, 21, 154, 0.00, 0.00, '', 1, '2026-03-23 03:39:52', '2026-03-23 03:38:21'),
(744, 21, 155, 0.00, 0.00, '', 1, '2026-03-23 03:39:34', '2026-03-23 03:38:21'),
(745, 21, 156, 0.00, 0.00, '', 1, '2026-03-23 03:39:34', '2026-03-23 03:38:21'),
(746, 21, 157, 0.00, 0.00, '', 1, '2026-03-23 03:39:33', '2026-03-23 03:38:21'),
(747, 21, 158, 0.00, 0.00, '', 1, '2026-03-23 03:39:32', '2026-03-23 03:38:21'),
(748, 21, 159, 0.00, 0.00, '', 1, '2026-03-23 03:39:32', '2026-03-23 03:38:21'),
(749, 21, 160, 0.00, 0.00, '', 1, '2026-03-23 03:39:31', '2026-03-23 03:38:21'),
(750, 21, 161, 0.00, 0.00, '', 1, '2026-03-23 03:39:31', '2026-03-23 03:38:21'),
(751, 21, 162, 0.00, 0.00, '', 1, '2026-03-23 03:39:30', '2026-03-23 03:38:21'),
(752, 22, 11, 1.00, 100.00, '', 1, '2026-03-23 08:46:05', '2026-03-23 04:00:42'),
(753, 22, 12, 0.00, 0.00, '', 1, '2026-03-23 04:00:48', '2026-03-23 04:00:42'),
(754, 22, 13, 1.00, 100.00, '', 1, '2026-03-23 04:00:47', '2026-03-23 04:00:42'),
(755, 22, 14, 1.00, 100.00, '', 1, '2026-03-23 04:00:49', '2026-03-23 04:00:42'),
(756, 22, 15, 0.00, 0.00, '', 1, '2026-03-23 04:00:50', '2026-03-23 04:00:42'),
(757, 22, 16, 0.00, 0.00, '', 1, '2026-03-23 04:00:50', '2026-03-23 04:00:42'),
(758, 22, 17, 1.00, 100.00, '', 1, '2026-03-23 04:00:51', '2026-03-23 04:00:42'),
(759, 22, 18, 1.00, 100.00, '', 1, '2026-03-23 04:00:52', '2026-03-23 04:00:42'),
(760, 22, 19, 1.00, 100.00, '', 1, '2026-03-23 04:00:52', '2026-03-23 04:00:42'),
(761, 22, 20, 0.00, 0.00, '', 1, '2026-03-23 04:00:53', '2026-03-23 04:00:42'),
(762, 22, 21, 0.00, 0.00, '', 1, '2026-03-23 04:01:00', '2026-03-23 04:00:42'),
(763, 22, 22, 0.00, 0.00, '', 1, '2026-03-23 04:00:54', '2026-03-23 04:00:42'),
(764, 22, 23, 0.00, 0.00, '', 1, '2026-03-23 04:00:54', '2026-03-23 04:00:42'),
(765, 22, 24, 1.00, 100.00, '', 1, '2026-03-23 04:00:56', '2026-03-23 04:00:42'),
(766, 22, 25, 1.00, 100.00, '', 1, '2026-03-23 04:00:56', '2026-03-23 04:00:42'),
(767, 22, 26, 1.00, 100.00, '', 1, '2026-03-23 04:00:57', '2026-03-23 04:00:42'),
(768, 22, 27, 0.00, 0.00, '', 1, '2026-03-23 04:00:58', '2026-03-23 04:00:42'),
(769, 22, 28, 1.00, 100.00, '', 1, '2026-03-23 04:00:55', '2026-03-23 04:00:42'),
(770, 22, 29, 1.00, 100.00, '', 1, '2026-03-23 04:01:07', '2026-03-23 04:00:42'),
(771, 22, 30, 0.00, 0.00, '', 1, '2026-03-23 04:01:10', '2026-03-23 04:00:42'),
(772, 22, 31, 0.00, 0.00, '', 1, '2026-03-23 04:01:10', '2026-03-23 04:00:42'),
(773, 22, 32, 1.00, 100.00, '', 1, '2026-03-23 04:01:11', '2026-03-23 04:00:42'),
(774, 22, 33, 1.00, 100.00, '', 1, '2026-03-23 04:01:11', '2026-03-23 04:00:42'),
(775, 22, 34, 1.00, 100.00, '', 1, '2026-03-23 04:01:12', '2026-03-23 04:00:42'),
(776, 22, 35, 0.00, 0.00, '', 1, '2026-03-23 04:01:13', '2026-03-23 04:00:42'),
(777, 22, 36, 0.00, 0.00, '', 1, '2026-03-23 04:01:13', '2026-03-23 04:00:42'),
(778, 22, 37, 0.00, 0.00, '', 1, '2026-03-23 04:01:14', '2026-03-23 04:00:42'),
(779, 22, 38, 0.00, 0.00, '', 1, '2026-03-23 04:01:15', '2026-03-23 04:00:42'),
(780, 22, 95, 1.00, 100.00, '', 1, '2026-03-23 04:01:16', '2026-03-23 04:00:42'),
(781, 22, 96, 0.00, 0.00, '', 1, '2026-03-23 04:01:20', '2026-03-23 04:00:42'),
(782, 22, 97, 1.00, 100.00, '', 1, '2026-03-23 04:01:20', '2026-03-23 04:00:42'),
(783, 22, 109, 1.00, 100.00, '', 1, '2026-03-23 04:01:21', '2026-03-23 04:00:42'),
(784, 22, 110, 0.00, 0.00, '', 1, '2026-03-23 04:01:22', '2026-03-23 04:00:42'),
(785, 22, 111, 1.00, 100.00, '', 1, '2026-03-23 04:01:23', '2026-03-23 04:00:42'),
(786, 22, 112, 0.00, 0.00, '', 1, '2026-03-23 04:01:23', '2026-03-23 04:00:42'),
(787, 22, 113, 1.00, 100.00, '', 1, '2026-03-23 04:01:24', '2026-03-23 04:00:42'),
(788, 22, 114, 0.00, 0.00, '', 1, '2026-03-23 04:01:25', '2026-03-23 04:00:42'),
(789, 22, 115, 1.00, 100.00, '', 1, '2026-03-23 04:01:26', '2026-03-23 04:00:42'),
(790, 22, 116, 0.00, 0.00, '', 1, '2026-03-23 04:01:26', '2026-03-23 04:00:42'),
(791, 22, 117, 1.00, 100.00, '', 1, '2026-03-23 04:01:27', '2026-03-23 04:00:42'),
(792, 22, 120, 0.00, 0.00, '', 1, '2026-03-23 04:01:27', '2026-03-23 04:00:42'),
(793, 22, 121, 1.00, 100.00, '', 1, '2026-03-23 04:01:28', '2026-03-23 04:00:42'),
(794, 22, 122, 1.00, 100.00, '', 1, '2026-03-23 04:01:29', '2026-03-23 04:00:42'),
(795, 22, 123, 0.00, 0.00, '', 1, '2026-03-23 04:01:31', '2026-03-23 04:00:42'),
(796, 22, 124, 0.00, 0.00, '', 1, '2026-03-23 04:01:32', '2026-03-23 04:00:42'),
(797, 22, 125, 1.00, 100.00, '', 1, '2026-03-23 04:01:32', '2026-03-23 04:00:42'),
(798, 22, 126, 1.00, 100.00, '', 1, '2026-03-23 04:01:42', '2026-03-23 04:00:42'),
(799, 22, 127, 1.00, 100.00, '', 1, '2026-03-23 04:01:43', '2026-03-23 04:00:42'),
(800, 22, 128, 0.00, 0.00, '', 1, '2026-03-23 04:01:44', '2026-03-23 04:00:42'),
(801, 22, 129, 0.00, 0.00, '', 1, '2026-03-23 04:01:44', '2026-03-23 04:00:42'),
(802, 22, 130, 1.00, 100.00, '', 1, '2026-03-23 04:01:45', '2026-03-23 04:00:42'),
(803, 22, 131, 1.00, 100.00, '', 1, '2026-03-23 04:01:46', '2026-03-23 04:00:42'),
(804, 22, 132, 0.00, 0.00, '', 1, '2026-03-23 04:01:48', '2026-03-23 04:00:42'),
(805, 22, 133, 0.00, 0.00, '', 1, '2026-03-23 04:01:48', '2026-03-23 04:00:42'),
(806, 22, 134, 0.00, 0.00, '', 1, '2026-03-23 04:01:49', '2026-03-23 04:00:42'),
(807, 22, 135, 1.00, 100.00, '', 1, '2026-03-23 04:01:53', '2026-03-23 04:00:42'),
(808, 22, 136, 1.00, 100.00, '', 1, '2026-03-23 04:01:53', '2026-03-23 04:00:42'),
(809, 22, 137, 1.00, 100.00, '', 1, '2026-03-23 04:01:54', '2026-03-23 04:00:42'),
(810, 22, 138, 0.00, 0.00, '', 1, '2026-03-23 04:01:56', '2026-03-23 04:00:42'),
(811, 22, 139, 0.00, 0.00, '', 1, '2026-03-23 04:01:56', '2026-03-23 04:00:42'),
(812, 22, 140, 0.00, 0.00, '', 1, '2026-03-23 04:01:57', '2026-03-23 04:00:42'),
(813, 22, 141, 0.00, 0.00, '', 1, '2026-03-23 04:01:57', '2026-03-23 04:00:42'),
(814, 22, 142, 1.00, 100.00, '', 1, '2026-03-23 04:01:59', '2026-03-23 04:00:42'),
(815, 22, 143, 1.00, 100.00, '', 1, '2026-03-23 04:01:59', '2026-03-23 04:00:42'),
(816, 22, 144, 1.00, 100.00, '', 1, '2026-03-23 07:47:10', '2026-03-23 04:00:42'),
(817, 22, 145, 0.00, 0.00, '', 1, '2026-03-23 04:01:21', '2026-03-23 04:00:42'),
(818, 22, 146, 0.00, 0.00, '', 1, '2026-03-23 04:01:33', '2026-03-23 04:00:42'),
(819, 22, 147, 1.00, 100.00, '', 1, '2026-03-23 04:01:34', '2026-03-23 04:00:42'),
(820, 22, 148, 1.00, 100.00, '', 1, '2026-03-23 04:01:37', '2026-03-23 04:00:42'),
(821, 22, 149, 0.00, 0.00, '', 1, '2026-03-23 04:01:38', '2026-03-23 04:00:42'),
(822, 22, 150, 1.00, 100.00, '', 1, '2026-03-23 04:01:40', '2026-03-23 04:00:42'),
(823, 22, 151, 0.00, 0.00, '', 1, '2026-03-23 04:01:40', '2026-03-23 04:00:42'),
(824, 22, 152, 0.00, 0.00, '', 1, '2026-03-23 04:01:49', '2026-03-23 04:00:42'),
(825, 22, 153, 1.00, 100.00, '', 1, '2026-03-23 04:01:46', '2026-03-23 04:00:42'),
(826, 22, 154, 1.00, 100.00, '', 1, '2026-03-23 04:01:47', '2026-03-23 04:00:42'),
(827, 22, 155, 1.00, 100.00, '', 1, '2026-03-23 04:02:00', '2026-03-23 04:00:42'),
(828, 22, 156, 1.00, 100.00, '', 1, '2026-03-23 04:02:00', '2026-03-23 04:00:42'),
(829, 22, 157, 1.00, 100.00, '', 1, '2026-03-23 04:02:01', '2026-03-23 04:00:42'),
(830, 22, 158, 0.00, 0.00, '', 1, '2026-03-23 04:02:02', '2026-03-23 04:00:42'),
(831, 22, 159, 0.00, 0.00, '', 1, '2026-03-23 04:02:02', '2026-03-23 04:00:42'),
(832, 22, 160, 0.00, 0.00, '', 1, '2026-03-23 04:02:03', '2026-03-23 04:00:42'),
(833, 22, 161, 0.00, 0.00, '', 1, '2026-03-23 04:02:03', '2026-03-23 04:00:42'),
(834, 22, 162, 0.00, 0.00, '', 1, '2026-03-23 04:02:04', '2026-03-23 04:00:42'),
(918, 24, 11, 1.00, 100.00, '', 12, '2026-05-08 02:30:55', '2026-05-08 02:30:36'),
(919, 24, 12, 0.00, 0.00, 'aaa', 12, '2026-05-08 02:31:08', '2026-05-08 02:30:36'),
(920, 24, 13, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(921, 24, 14, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(922, 24, 15, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(923, 24, 16, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(924, 24, 17, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(925, 24, 18, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(926, 24, 19, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(927, 24, 20, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(928, 24, 21, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(929, 24, 22, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(930, 24, 23, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(931, 24, 24, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(932, 24, 25, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(933, 24, 26, NULL, NULL, '', NULL, '2026-05-08 02:30:36', '2026-05-08 02:30:36'),
(934, 24, 27, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(935, 24, 28, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(936, 24, 29, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(937, 24, 30, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(938, 24, 31, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(939, 24, 32, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(940, 24, 33, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(941, 24, 34, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(942, 24, 35, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(943, 24, 36, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(944, 24, 37, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(945, 24, 38, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(946, 24, 95, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(947, 24, 96, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(948, 24, 97, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(949, 24, 109, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(950, 24, 110, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(951, 24, 111, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(952, 24, 112, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(953, 24, 113, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(954, 24, 114, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(955, 24, 115, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(956, 24, 116, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(957, 24, 117, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(958, 24, 120, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(959, 24, 121, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(960, 24, 122, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(961, 24, 123, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(962, 24, 124, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(963, 24, 125, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(964, 24, 126, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(965, 24, 127, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(966, 24, 128, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(967, 24, 129, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(968, 24, 130, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(969, 24, 131, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(970, 24, 132, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(971, 24, 133, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(972, 24, 134, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(973, 24, 135, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(974, 24, 136, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(975, 24, 137, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(976, 24, 138, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(977, 24, 139, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(978, 24, 140, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(979, 24, 141, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(980, 24, 142, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(981, 24, 143, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(982, 24, 144, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(983, 24, 145, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(984, 24, 146, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(985, 24, 147, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(986, 24, 148, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(987, 24, 149, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(988, 24, 150, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(989, 24, 151, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(990, 24, 152, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(991, 24, 153, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(992, 24, 154, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(993, 24, 155, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(994, 24, 156, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(995, 24, 157, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(996, 24, 158, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(997, 24, 159, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(998, 24, 160, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(999, 24, 161, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(1000, 24, 162, NULL, NULL, '', NULL, '2026-05-08 02:30:37', '2026-05-08 02:30:37'),
(1001, 25, 11, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1002, 25, 12, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1003, 25, 13, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1004, 25, 14, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1005, 25, 15, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1006, 25, 16, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1007, 25, 17, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1008, 25, 18, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1009, 25, 19, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1010, 25, 20, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1011, 25, 21, 0.00, 0.00, '', 12, '2026-05-08 02:35:58', '2026-05-08 02:35:48'),
(1012, 25, 22, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1013, 25, 23, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1014, 25, 24, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1015, 25, 25, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1016, 25, 26, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1017, 25, 27, 1.00, 100.00, '', 12, '2026-05-08 02:35:57', '2026-05-08 02:35:48'),
(1018, 25, 28, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1019, 25, 29, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1020, 25, 30, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1021, 25, 31, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1022, 25, 32, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1023, 25, 33, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1024, 25, 34, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1025, 25, 35, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1026, 25, 36, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1027, 25, 37, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1028, 25, 38, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1029, 25, 95, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1030, 25, 96, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1031, 25, 97, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1032, 25, 109, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1033, 25, 110, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1034, 25, 111, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1035, 25, 112, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1036, 25, 113, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1037, 25, 114, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1038, 25, 115, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1039, 25, 116, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1040, 25, 117, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1041, 25, 120, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1042, 25, 121, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1043, 25, 122, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1044, 25, 123, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1045, 25, 124, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1046, 25, 125, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1047, 25, 126, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1048, 25, 127, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1049, 25, 128, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1050, 25, 129, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1051, 25, 130, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1052, 25, 131, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1053, 25, 132, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1054, 25, 133, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1055, 25, 134, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1056, 25, 135, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1057, 25, 136, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1058, 25, 137, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1059, 25, 138, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1060, 25, 139, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1061, 25, 140, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1062, 25, 141, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1063, 25, 142, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1064, 25, 143, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1065, 25, 144, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1066, 25, 145, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1067, 25, 146, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1068, 25, 147, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1069, 25, 148, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1070, 25, 149, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1071, 25, 150, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1072, 25, 151, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1073, 25, 152, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1074, 25, 153, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1075, 25, 154, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1076, 25, 155, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1077, 25, 156, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1078, 25, 157, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1079, 25, 158, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1080, 25, 159, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1081, 25, 160, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1082, 25, 161, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1083, 25, 162, NULL, NULL, '', NULL, '2026-05-08 02:35:48', '2026-05-08 02:35:48'),
(1084, 26, 11, 1.00, 100.00, 'remark', 12, '2026-05-08 02:53:39', '2026-05-08 02:53:21'),
(1085, 26, 12, 1.00, 100.00, 'good', 12, '2026-05-08 02:53:49', '2026-05-08 02:53:21'),
(1086, 26, 13, 0.00, 0.00, 'good', 12, '2026-05-08 02:53:53', '2026-05-08 02:53:21'),
(1087, 26, 14, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1088, 26, 15, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1089, 26, 16, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1090, 26, 17, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1091, 26, 18, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1092, 26, 19, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1093, 26, 20, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1094, 26, 21, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1095, 26, 22, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1096, 26, 23, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1097, 26, 24, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1098, 26, 25, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1099, 26, 26, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1100, 26, 27, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1101, 26, 28, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1102, 26, 29, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1103, 26, 30, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1104, 26, 31, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1105, 26, 32, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1106, 26, 33, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1107, 26, 34, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1108, 26, 35, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1109, 26, 36, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1110, 26, 37, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1111, 26, 38, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1112, 26, 95, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1113, 26, 96, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1114, 26, 97, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1115, 26, 109, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21');
INSERT INTO `scores` (`id`, `evaluation_id`, `key_performance_indicator_id`, `score_value`, `percentage_value`, `remarks`, `last_edited_by`, `last_edited_at`, `created_at`) VALUES
(1116, 26, 110, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1117, 26, 111, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1118, 26, 112, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1119, 26, 113, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1120, 26, 114, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1121, 26, 115, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1122, 26, 116, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1123, 26, 117, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1124, 26, 120, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1125, 26, 121, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1126, 26, 122, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1127, 26, 123, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1128, 26, 124, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1129, 26, 125, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1130, 26, 126, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1131, 26, 127, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1132, 26, 128, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1133, 26, 129, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1134, 26, 130, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1135, 26, 131, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1136, 26, 132, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1137, 26, 133, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1138, 26, 134, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1139, 26, 135, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1140, 26, 136, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1141, 26, 137, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1142, 26, 138, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1143, 26, 139, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1144, 26, 140, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1145, 26, 141, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1146, 26, 142, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1147, 26, 143, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1148, 26, 144, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1149, 26, 145, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1150, 26, 146, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1151, 26, 147, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1152, 26, 148, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1153, 26, 149, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1154, 26, 150, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1155, 26, 151, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1156, 26, 152, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1157, 26, 153, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1158, 26, 154, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1159, 26, 155, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1160, 26, 156, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1161, 26, 157, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1162, 26, 158, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1163, 26, 159, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1164, 26, 160, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1165, 26, 161, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21'),
(1166, 26, 162, NULL, NULL, '', NULL, '2026-05-08 02:53:21', '2026-05-08 02:53:21');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `session_token`, `ip_address`, `user_agent`, `expires_at`, `created_at`) VALUES
(1, 1, 'sil8mijtthqm02afqqanriastd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-09 07:19:15', '2026-05-08 05:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `strategic_objectives`
--

CREATE TABLE `strategic_objectives` (
  `id` int(11) NOT NULL,
  `building_block_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` text NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `strategic_objectives`
--

INSERT INTO `strategic_objectives` (`id`, `building_block_id`, `code`, `name`, `description`, `sort_order`, `is_active`, `created_at`) VALUES
(8, 7, 'SO_VII_1', '', '', 1, 1, '2026-02-18 01:10:48'),
(17, 1, 'SO_I_1', 'Organizational Structure of the Facility', 'Ensure functional management structure', 1, 1, '2026-02-27 04:59:14'),
(18, 1, 'SO_I_2', 'A Functional Management Committee', 'Active and operational management committee', 2, 1, '2026-02-27 04:59:14'),
(19, 1, 'SO_I_3', 'Compliance to the submission of LIPH/AOP 2025-2028', 'Governance requirements and related compliance items', 3, 1, '2026-02-27 04:59:14'),
(20, 2, 'SO_II_1', '', NULL, 1, 1, '2026-02-27 05:44:39'),
(21, 2, 'SO_II_2', 'Leverage digital health and technology for efficient service delivery', NULL, 2, 1, '2026-02-27 05:44:39'),
(22, 2, 'SO_II_3', '', NULL, 3, 1, '2026-02-27 05:44:39'),
(23, 2, 'SO_II_4', 'Percent of Filipinos registered to a Primary Care Provider', NULL, 4, 1, '2026-02-27 05:44:39'),
(24, 3, 'SO_III_1', 'Percent of Filipinos registered to a Primary Care Provider', NULL, 1, 1, '2026-02-27 05:59:34'),
(25, 3, 'SO_III_2', '', NULL, 2, 1, '2026-02-27 05:59:34'),
(26, 3, 'SO_III_3', 'Mainstream and Strengthen the primary health care approach. Ensuring that every Filipino has access to comprehensive health experiencing Financial hardship', NULL, 3, 1, '2026-02-27 05:59:34'),
(27, 3, 'SO_III_4', '', NULL, 4, 1, '2026-02-27 05:59:34'),
(29, 4, 'SO_IV_1', 'Ensure an adequate, competent, and committed health workforce by providing fair compensation, decent work conditions, and opportunities for career development.', NULL, 1, 1, '2026-02-27 06:38:29'),
(30, 4, 'SO_IV_2', 'Human Resource for Health Ratio', NULL, 2, 1, '2026-02-27 06:38:29'),
(34, 1, 'SO_I_4', '', NULL, 4, 1, '2026-02-27 07:06:34'),
(35, 1, 'SO_I_5', 'The presence of institutionalized and functional Units.', NULL, 5, 1, '2026-02-27 07:06:34'),
(36, 1, 'SO_I_6', '', NULL, 6, 1, '2026-02-27 07:06:34'),
(37, 5, 'SO_V_1', 'Ensure the provision of high-quality, safe, and people-centered services, which include access to affordable medicines, across the lifestages.', NULL, 1, 1, '2026-02-27 07:14:49'),
(38, 5, 'SO_V_2', 'Leverage digital health and technology for efficient and accessible health service delivery.', NULL, 2, 1, '2026-02-27 07:14:49'),
(39, 6, 'SO_VI_1', 'Ensure availability of digital health systems in RHU', NULL, 1, 1, '2026-02-27 07:30:06'),
(40, 6, 'SO_VI_2', 'Strengthen electronic patient record utilization', NULL, 2, 1, '2026-02-27 07:30:06'),
(41, 6, 'SO_VI_3', 'Improve patient referral coordination', NULL, 3, 1, '2026-02-27 07:30:06'),
(42, 6, 'SO_VI_4', 'Strengthen digital health capability of RHU staff', NULL, 4, 1, '2026-02-27 07:30:06'),
(43, 6, 'SO_VI_5', 'Strengthen referral system capability', NULL, 5, 1, '2026-02-27 07:30:06'),
(44, 6, 'SO_VI_6', 'Promote consistent use of electronic health records', NULL, 6, 1, '2026-02-27 07:30:06'),
(45, 6, 'SO_VI_7', 'Ensure completeness and quality of patient data', NULL, 7, 1, '2026-02-27 07:30:06'),
(46, 6, 'SO_VI_8', 'Ensure ICT infrastructure support', NULL, 8, 1, '2026-02-27 07:30:06'),
(47, 6, 'SO_VI_9', 'Support system connectivity', NULL, 9, 1, '2026-02-27 07:30:06'),
(48, 6, 'SO_VI_10', 'Institutionalize QMeR utilization', NULL, 10, 1, '2026-02-27 07:30:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('viewer','input','admin') DEFAULT 'viewer',
  `facility_name` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `user_facilities`
--

CREATE TABLE `user_facilities` (
  `user_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `facility_name`, `department`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@health-monitoring.local', '$2y$10$dqtbQC8ug93aUQXCkqY2Zer1UmRI5IN581Cu2v7kV4dbJMDzS/CGm', 'Dr. Maria Santos', 'admin', 'Provincial Health Office', NULL, 1, '2026-02-18 02:22:15', '2026-02-18 02:22:15'),
(2, 'input', 'input@health-monitoring.local', '$2y$10$xzXopV3qySasVo2zllixtuk3QEd0FqkhQ3APdIZWTRaR1MOeEBg1u', 'Juan Dela Cruz', 'input', 'Leyte Provincial Hospital', NULL, 1, '2026-02-18 02:22:15', '2026-02-18 02:22:15'),
(3, 'viewer', 'viewer@health-monitoring.local', '$2y$10$zDAE/JGqesFHgtweUdinFOS3sqVY.iAne3lHTYqx5g9wqe7r2uIQm', 'Anna Garcia', 'viewer', 'Ormoc City Health Center', NULL, 1, '2026-02-18 02:22:15', '2026-02-18 02:22:15'),
(12, 'adminalas', 'aaaa@gmail.com', '$2y$10$9psgQibHWw4wiyhx18Wn/e1.sV1L1jhTNwGEK1ABtOJHEIsBaj4vm', 'QMERJEMS INPUT SAMPLE', 'input', 'QMER Training Center', NULL, 1, '2026-05-08 02:29:26', '2026-05-08 02:29:26'),
(13, 'salamidaqmer', 'admina@gmail.com', '$2y$10$oI7pHm720NzR/kCnax.Lke5.DEdKqPv.beHJaGA8/cYpumpD5A2w6', 'QMERJEMS VIEWER SAMPLE', 'viewer', 'QMER Training Center', NULL, 1, '2026-05-08 02:39:02', '2026-05-08 02:39:02');

--
-- Dumping data for table `user_facilities`
--

INSERT INTO `user_facilities` (`user_id`, `facility_id`)
SELECT u.id, f.id
FROM `users` u
JOIN `facilities` f ON f.name = u.facility_name
WHERE u.facility_name IS NOT NULL AND u.facility_name != '';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `building_blocks`
--
ALTER TABLE `building_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_facility` (`facility_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_evaluation_date` (`evaluation_date`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `fk_facilities_creator` (`creator`),
  ADD KEY `fk_facilities_editor` (`editor`);

--
-- Indexes for table `key_performance_indicators`
--
ALTER TABLE `key_performance_indicators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_strategic` (`strategic_objective_id`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evaluation_kpi` (`evaluation_id`,`key_performance_indicator_id`),
  ADD KEY `last_edited_by` (`last_edited_by`),
  ADD KEY `idx_evaluation` (`evaluation_id`),
  ADD KEY `idx_kpi` (`key_performance_indicator_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `strategic_objectives`
--
ALTER TABLE `strategic_objectives`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_building_block` (`building_block_id`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_facility` (`facility_name`);

--
-- Indexes for table `user_facilities`
--

ALTER TABLE `user_facilities`
  ADD PRIMARY KEY (`user_id`,`facility_id`),
  ADD KEY `idx_user_facilities_user` (`user_id`),
  ADD KEY `idx_user_facilities_facility` (`facility_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `building_blocks`
--
ALTER TABLE `building_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `key_performance_indicators`
--
ALTER TABLE `key_performance_indicators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1167;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `strategic_objectives`
--
ALTER TABLE `strategic_objectives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evaluations_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `fk_facilities_creator` FOREIGN KEY (`creator`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_facilities_editor` FOREIGN KEY (`editor`) REFERENCES `users` (`id`);

--
-- Constraints for table `key_performance_indicators`
--
ALTER TABLE `key_performance_indicators`
  ADD CONSTRAINT `key_performance_indicators_ibfk_1` FOREIGN KEY (`strategic_objective_id`) REFERENCES `strategic_objectives` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`key_performance_indicator_id`) REFERENCES `key_performance_indicators` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`last_edited_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `strategic_objectives`
--
ALTER TABLE `strategic_objectives`
  ADD CONSTRAINT `strategic_objectives_ibfk_1` FOREIGN KEY (`building_block_id`) REFERENCES `building_blocks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
