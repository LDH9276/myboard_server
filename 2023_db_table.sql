-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- 생성 시간: 23-08-27 21:10
-- 서버 버전: 10.4.28-MariaDB
-- PHP 버전: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `testing_app`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `app_board`
--

CREATE TABLE `app_board` (
  `id` int(11) NOT NULL,
  `cat` smallint(6) NOT NULL DEFAULT 1,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `writer` varchar(255) NOT NULL,
  `comment_count` int(11) DEFAULT 0,
  `reg_date` datetime NOT NULL,
  `update_date` datetime NOT NULL,
  `nickname` varchar(100) NOT NULL DEFAULT '',
  `total_like` int(11) NOT NULL DEFAULT 0,
  `board_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 테이블 구조 `app_boardlist`
--

CREATE TABLE `app_boardlist` (
  `id` int(255) NOT NULL,
  `board_name` varchar(255) DEFAULT NULL,
  `board_thumb` varchar(255) NOT NULL,
  `board_master` varchar(400) DEFAULT NULL,
  `total_posts` int(11) NOT NULL,
  `board_detail` text NOT NULL,
  `board_subscriber` int(11) NOT NULL DEFAULT 0,
  `board_kind` tinytext NOT NULL,
  `board_category` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 테이블 구조 `app_comment`
--

CREATE TABLE `app_comment` (
  `id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `writer` varchar(255) NOT NULL DEFAULT '',
  `post_id` int(11) NOT NULL DEFAULT 0,
  `reg_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `comment_parent` int(11) DEFAULT NULL,
  `comment_depth` int(11) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 테이블 구조 `app_like`
--

CREATE TABLE `app_like` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `like_time` datetime DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_delete` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 테이블 구조 `app_token`
--

CREATE TABLE `app_token` (
  `num` int(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `refresh_token` varchar(255) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expire_refresh_token` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 테이블 구조 `app_users`
--

CREATE TABLE `app_users` (
  `id` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `profile` varchar(255) NOT NULL,
  `user_info` int(2) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `app_board`
--
ALTER TABLE `app_board`
  ADD PRIMARY KEY (`id`);

--
-- 테이블의 인덱스 `app_boardlist`
--
ALTER TABLE `app_boardlist`
  ADD PRIMARY KEY (`id`);

--
-- 테이블의 인덱스 `app_comment`
--
ALTER TABLE `app_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- 테이블의 인덱스 `app_like`
--
ALTER TABLE `app_like`
  ADD PRIMARY KEY (`id`);

--
-- 테이블의 인덱스 `app_token`
--
ALTER TABLE `app_token`
  ADD PRIMARY KEY (`num`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `app_board`
--
ALTER TABLE `app_board`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `app_boardlist`
--
ALTER TABLE `app_boardlist`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `app_comment`
--
ALTER TABLE `app_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `app_like`
--
ALTER TABLE `app_like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 AUTO_INCREMENT `app_token`
--
ALTER TABLE `app_token`
  MODIFY `num` int(255) NOT NULL AUTO_INCREMENT;

--
-- 덤프된 테이블의 제약사항
--

--
-- 테이블의 제약사항 `app_comment`
--
ALTER TABLE `app_comment`
  ADD CONSTRAINT `app_comment_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `app_board` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
