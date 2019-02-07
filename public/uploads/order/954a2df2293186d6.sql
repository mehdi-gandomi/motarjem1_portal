-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 07, 2019 at 12:39 AM
-- Server version: 5.6.41
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `motarje6_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `exam_result`
--

CREATE TABLE `exam_result` (
  `translator_id` int(11) NOT NULL,
  `correct_answers` varchar(3) DEFAULT NULL,
  `incorrect_answers` varchar(3) DEFAULT NULL,
  `points` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `forgot_password`
--

CREATE TABLE `forgot_password` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `token` varchar(40) NOT NULL,
  `expire_date` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `forgot_password`
--

INSERT INTO `forgot_password` (`id`, `user_id`, `user_type`, `token`, `expire_date`) VALUES
(2, 12, 1, '95ef7b09c3cbc7162088ec3b23c1a81e10861509', '1549551330'),
(3, 2, 1, 'ca57d2d379123b3c600c83f7a277f1c9f45705b1', '1549550359');

-- --------------------------------------------------------

--
-- Table structure for table `messaging`
--

CREATE TABLE `messaging` (
  `msg_id` int(11) NOT NULL,
  `parent_msg_id` int(11) DEFAULT '0',
  `sender_id` int(11) DEFAULT NULL,
  `reciever_id` int(11) NOT NULL DEFAULT '0',
  `user_type` tinyint(1) NOT NULL DEFAULT '1',
  `create_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `create_date_persian` varchar(16) DEFAULT NULL,
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date_persian` varchar(16) NOT NULL,
  `subject` varchar(50) DEFAULT NULL,
  `body` text,
  `attach_files` varchar(250) DEFAULT NULL,
  `is_answered` tinyint(1) DEFAULT '0',
  `is_read` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messaging`
--

INSERT INTO `messaging` (`msg_id`, `parent_msg_id`, `sender_id`, `reciever_id`, `user_type`, `create_date`, `create_date_persian`, `update_date`, `update_date_persian`, `subject`, `body`, `attach_files`, `is_answered`, `is_read`) VALUES
(1, 0, 2, 0, 1, '2019-01-23 20:03:59', '1397/10/03 23:30', '2019-01-23 20:03:59', '1397/10/03 23:30', 'تست', 'ابنراثقارنقاراقنپرازوتیدزنورزیسسنورزپیسنزویس', NULL, 1, 1),
(2, 1, 0, 2, 1, '2019-01-28 19:42:43', '1397/10/03 23:30', '2019-01-28 19:42:43', '1397/10/08 23:00', 'تست 2', 'تارپیاذریپر ذ پذرپی ر', NULL, 0, 1),
(3, 0, 2, 0, 1, '2019-01-30 15:31:02', '1397/11/10 19:01', '2019-01-30 15:31:02', '1397/11/10 19:01', 'تست میشه', '<p>nvhfk,vbk,rbck</p>', NULL, 0, 0),
(4, 0, 2, 0, 1, '2019-01-30 15:37:13', '1397/11/10 19:07', '2019-01-30 15:37:13', '1397/11/10 19:07', 'تست میشه', '<p>فاک یو</p>', NULL, 0, 0),
(5, 0, 2, 0, 1, '2019-01-30 15:38:11', '1397/11/10 19:08', '2019-01-30 15:38:11', '1397/11/10 19:08', 'تست میشه', '<p>فاک یو</p>', NULL, 0, 0),
(6, 1, 2, 0, 1, '2019-01-30 15:57:46', '1397/11/10 19:27', '2019-01-30 15:57:46', '1397/11/10 19:27', 'تست میشه', '<p>شت</p>', NULL, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `body` text,
  `attach_files` varchar(250) DEFAULT NULL,
  `sent_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_date_persian` varchar(16) DEFAULT NULL,
  `notif_type` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notif_translator`
--

CREATE TABLE `notif_translator` (
  `translator_id` int(11) DEFAULT NULL,
  `notif_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `orderer_id` int(11) NOT NULL,
  `translator_id` int(11) DEFAULT '0',
  `word_numbers` varchar(10) NOT NULL,
  `translation_quality` tinyint(1) NOT NULL,
  `translation_lang` tinyint(1) NOT NULL,
  `translation_kind` tinyint(1) NOT NULL,
  `delivery_type` tinyint(1) NOT NULL,
  `delivery_days` varchar(3) NOT NULL,
  `order_files` varchar(250) DEFAULT NULL,
  `description` text,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_date_persian` varchar(16) NOT NULL,
  `field_of_study` varchar(3) DEFAULT NULL,
  `discount_code` varchar(10) DEFAULT NULL,
  `order_price` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `orderer_id`, `translator_id`, `word_numbers`, `translation_quality`, `translation_lang`, `translation_kind`, `delivery_type`, `delivery_days`, `order_files`, `description`, `order_date`, `order_date_persian`, `field_of_study`, `discount_code`, `order_price`) VALUES
(1, 2, 1, '250', 5, 1, 1, 2, '5', '', 'تست توضحات', '2019-01-23 15:12:01', '1397/11/10 14:59', '120', '', '100000000'),
(2, 2, 0, '500', 5, 1, 2, 1, '1', '9d7c42a7715ea227.pdf', '', '2019-02-06 16:56:29', '1397/11/17 20:26', '90', '', '20000'),
(3, 12, 0, '250', 5, 1, 1, 1, '1', '', '', '2019-02-06 20:06:51', '1397/11/17 23:36', '90', '', '5000'),
(4, 12, 0, '250', 5, 1, 1, 1, '1', '', '', '2019-02-06 20:11:23', '1397/11/17 23:41', '90', '', '5000'),
(5, 12, 0, '250', 5, 1, 1, 1, '1', '', '', '2019-02-06 20:14:05', '1397/11/17 23:44', '90', '', '5000');

-- --------------------------------------------------------

--
-- Table structure for table `order_logs`
--

CREATE TABLE `order_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_code` varchar(50) DEFAULT '0',
  `is_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `accept_date` varchar(15) DEFAULT NULL,
  `accept_date_persian` varchar(16) DEFAULT NULL,
  `order_step` tinyint(1) NOT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_logs`
--

INSERT INTO `order_logs` (`id`, `order_id`, `transaction_code`, `is_accepted`, `accept_date`, `accept_date_persian`, `order_step`, `is_done`) VALUES
(1, 1, 'gjfghdfkfgh2', 1, NULL, NULL, 1, 0),
(2, 2, '0', 0, NULL, NULL, 1, 0),
(3, 3, '0', 0, NULL, NULL, 1, 0),
(4, 4, '0', 0, NULL, NULL, 1, 0),
(5, 5, '0', 0, NULL, NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `translator_id` int(11) NOT NULL,
  `price` varchar(10) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `date_persian` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `previewText` mediumtext NOT NULL,
  `link` varchar(100) NOT NULL,
  `date` varchar(10) NOT NULL,
  `thumbnail` varchar(200) NOT NULL,
  `categories` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `previewText`, `link`, `date`, `thumbnail`, `categories`) VALUES
(1, 'آموزش کامل ایجاد و تغییر پاورقی در ورد', '<p>در بسیاری از نوشته ها از جمله تالیف پایان نامه، مقاله، کتاب و حتی تحقیق، نیاز به این دارید که از پاورقی برای توضیح برخی اصطلاحات و عبارات استفاده کنید. نرم افزار WORD به عنوان قوی ترین برنامه ویرایش متن دارای امکان گذاشتن پاورقی در&#8230; </p>\n', 'http://www.motarjem1.com/blog/?p=2106', '14 بهمن 13', 'http://www.motarjem1.com/blog/wp-content/uploads/2019/02/Microsoft-Word-w600-350x195.jpg', '[{\"link\":\"http:\\/\\/www.motarjem1.com\\/blog\\/category\\/%d8%a2%d9%85%d9%88%d8%b2%d8%b4%db%8c\\/\",\"name\":\"\\u0622\\u0645\\u0648\\u0632\\u0634\\u06cc\"}]'),
(2, 'ترجمه تخصصی مقالات رشته کامپیوتر در مترجم وان', '<p>همانطور که می دانید رشته های مرتبط با تکنولوژی همه روزه در حال پیشرفت و نوآوری های جدید هستند. بنابراین برای اینکه بتوانید در این رشته ها پیش بروید نیاز به این دارید که دائما علم خودتان را به روزرسانی کنید. رشته کامپیوتر در راس&#8230; </p>\n', 'http://www.motarjem1.com/blog/?p=2096', '11 بهمن 13', 'http://www.motarjem1.com/blog/wp-content/uploads/2019/01/Computer-Sciences-Translation-Tarjomeyar-Com-350x195.jpg', '[{\"link\":\"http:\\/\\/www.motarjem1.com\\/blog\\/category\\/%d8%a2%d9%85%d9%88%d8%b2%d8%b4%db%8c\\/\",\"name\":\"\\u0622\\u0645\\u0648\\u0632\\u0634\\u06cc\"}]'),
(3, 'نکات مهم در تقویت رایتینگ جهت موفقیت در آزمون آیلتس', '<p>همانطور که می دانید قبولی در آزمون آیلتس آکادمیک و آیلتس جنرال نیاز به این دارد که شما تمامی مهارت های زبان انگلیسی تان را تقویت کنید. رایتینگ زبان یکی از مهارت های مهم در این آزمون می باشد. در این مقاله نکات مهمی را&#8230; </p>\n', 'http://www.motarjem1.com/blog/?p=2085', '7 بهمن 139', 'http://www.motarjem1.com/blog/wp-content/uploads/2019/01/تقوی-نوشتن-350x195.jpg', '[{\"link\":\"http:\\/\\/www.motarjem1.com\\/blog\\/category\\/%d8%a2%d9%85%d9%88%d8%b2%d8%b4%db%8c\\/\",\"name\":\"\\u0622\\u0645\\u0648\\u0632\\u0634\\u06cc\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `translators`
--

CREATE TABLE `translators` (
  `translator_id` int(11) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `cell_phone` varchar(11) DEFAULT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `meli_code` varchar(10) DEFAULT NULL,
  `melicard_photo` varchar(20) DEFAULT NULL,
  `avatar` varchar(20) DEFAULT NULL,
  `degree` varchar(20) DEFAULT NULL,
  `degree_field` varchar(3) DEFAULT NULL,
  `exp_years` varchar(2) DEFAULT NULL,
  `address` text,
  `register_date` timestamp NULL DEFAULT NULL,
  `register_date_persian` varchar(16) DEFAULT NULL,
  `en_to_fa` tinyint(1) DEFAULT NULL,
  `fa_to_en` tinyint(1) DEFAULT NULL,
  `revenue` varchar(10) DEFAULT NULL,
  `level` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `is_employed` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `translators`
--

INSERT INTO `translators` (`translator_id`, `username`, `password`, `fname`, `lname`, `email`, `cell_phone`, `phone`, `meli_code`, `melicard_photo`, `avatar`, `degree`, `degree_field`, `exp_years`, `address`, `register_date`, `register_date_persian`, `en_to_fa`, `fa_to_en`, `revenue`, `level`, `is_active`, `is_employed`) VALUES
(1, 'coderguy-translator', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'coderguy1999@gmail.com', '09389318493', '05632313094', '0640617743', 'kfrgfjl.t.jpg', 'default-avatar.svg', 'کارشناسی', '100', '2', 'تتدنزلتوذذتپب', NULL, '1397/10/30 19:52', 1, 1, '100000000', 2, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `translator_account`
--

CREATE TABLE `translator_account` (
  `translator_id` int(11) NOT NULL,
  `account_number` varchar(16) DEFAULT NULL,
  `bank_name` varchar(20) DEFAULT NULL,
  `account_owner` varchar(50) DEFAULT NULL,
  `account_credit` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(256) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `avatar` varchar(20) DEFAULT 'default-avatar.svg',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `register_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `register_date_persian` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `fname`, `lname`, `email`, `phone`, `avatar`, `is_active`, `register_date`, `register_date_persian`) VALUES
(2, 'coderguy', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'coderguy1999@gmail.com', '09389318493', '898905b65ed4a096.jpg', 1, '2019-01-20 16:22:21', '1397/10/30 19:52'),
(8, 'coderguy1999', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'coderguy1998@gmail.com', '05632313094', NULL, 0, '2019-01-24 09:52:48', '1397/11/4 13:22'),
(12, 'mehdi', '81d93b8220c41d7a3b911024ac34464c', 'مهدی', 'گندمی', 'watch.dogs3030@gmail.com', '09389318493', 'default-avatar.svg', 1, '2019-02-06 12:03:49', '1397/11/17 15:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD PRIMARY KEY (`translator_id`);

--
-- Indexes for table `forgot_password`
--
ALTER TABLE `forgot_password`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messaging`
--
ALTER TABLE `messaging`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`);

--
-- Indexes for table `notif_translator`
--
ALTER TABLE `notif_translator`
  ADD KEY `translator_id` (`translator_id`),
  ADD KEY `notif_id` (`notif_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_ibfk_1` (`orderer_id`);

--
-- Indexes for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`translator_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `translators`
--
ALTER TABLE `translators`
  ADD PRIMARY KEY (`translator_id`);

--
-- Indexes for table `translator_account`
--
ALTER TABLE `translator_account`
  ADD PRIMARY KEY (`translator_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `forgot_password`
--
ALTER TABLE `forgot_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messaging`
--
ALTER TABLE `messaging`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_logs`
--
ALTER TABLE `order_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `translators`
--
ALTER TABLE `translators`
  MODIFY `translator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `translator_account`
--
ALTER TABLE `translator_account`
  MODIFY `translator_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD CONSTRAINT `exam_result_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notif_translator`
--
ALTER TABLE `notif_translator`
  ADD CONSTRAINT `notif_translator_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notif_translator_ibfk_2` FOREIGN KEY (`notif_id`) REFERENCES `notifications` (`notif_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`orderer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD CONSTRAINT `order_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`translator_id`) REFERENCES `translators` (`translator_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
