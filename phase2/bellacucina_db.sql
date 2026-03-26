-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 26 مارس 2026 الساعة 01:53
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bellacucina_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `blockeduser`
--

CREATE TABLE `blockeduser` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `emailaddress` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `blockeduser`
--

INSERT INTO `blockeduser` (`id`, `firstname`, `lastname`, `emailaddress`) VALUES
(1, 'noura', 'saud', 'noura@gmail.com'),
(2, 'lama', 'fahad', 'lama1@gmail.com'),
(3, 'mohammed', 'faisal', 'mohaFF@gmail.com');

-- --------------------------------------------------------

--
-- بنية الجدول `comment`
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `comment`
--

INSERT INTO `comment` (`id`, `recipeid`, `userid`, `comment`, `date`) VALUES
(1, 2, 2, 'it was amazing thanks', '2026-03-17 18:58:25'),
(2, 3, 3, 'looks delicious 😋 ', '2026-03-17 18:58:25'),
(3, 1, 2, '!such a helpful website', '2026-03-17 18:58:25');

-- --------------------------------------------------------

--
-- بنية الجدول `favourites`
--

CREATE TABLE `favourites` (
  `userid` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `favourites`
--

INSERT INTO `favourites` (`userid`, `recipeid`) VALUES
(2, 1),
(2, 1),
(3, 2);

-- --------------------------------------------------------

--
-- بنية الجدول `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL,
  `ingredientname` varchar(100) NOT NULL,
  `ingredientquantity` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipeid`, `ingredientname`, `ingredientquantity`) VALUES
(1, 3, ' Quinoa (cooked)\r\n \r\n', '1cup\r\n'),
(2, 3, ' Cherry Tomatoes ', ' cup½'),
(3, 3, 'Cucumber (diced)', 'cup½'),
(4, 3, ' Fresh Parsley (chopped)', '2tablespoons'),
(5, 3, '  Lemon Juice', '1tablespone '),
(6, 3, ' Salt & Black Pepper', 'to taste'),
(16, 2, 'Plain Greek Yogurt (0–2% fat)', '1cups'),
(17, 2, 'Rolled Oats (finely ground)', ' cup¾ '),
(18, 2, 'Strong Black Coffee (cooled)', 'cup½'),
(19, 2, 'Raw Honey or Maple Syrup', '2tablespoon'),
(20, 2, 'Unsweetened Cocoa Powder', '1tablespoon'),
(135, 1, 'Almond flour', '2cups'),
(136, 1, 'Shredded mozzarella cheese', '1cup'),
(137, 1, 'Cream cheese', '2tablespoon'),
(138, 1, 'Egg', '1Large'),
(139, 1, 'Tomato sauce', '1cup'),
(140, 1, 'Fresh basil leaves', 'handful'),
(141, 1, 'Olive oil', '1tablespoon'),
(142, 1, 'salt and pepper', 'to taste'),
(143, 1, 'Garlic powder', 'to taste');

-- --------------------------------------------------------

--
-- بنية الجدول `instructions`
--

CREATE TABLE `instructions` (
  `id` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL,
  `step` text NOT NULL,
  `steporder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `instructions`
--

INSERT INTO `instructions` (`id`, `recipeid`, `step`, `steporder`) VALUES
(14, 3, 'Cook the quinoa according to package instructions and let it cool', 1),
(15, 3, 'Chop the tomatoes, cucumber, and parsley', 2),
(16, 3, 'In a bowl, combine the quinoa with the chopped vegetables', 3),
(17, 3, 'Add olive oil and lemon juice, then mix well', 4),
(18, 3, 'Season with salt and black pepper to taste', 5),
(19, 2, 'Grind the oats into a fine powder using a blender or food processor\r\n\r\n\r\n', 1),
(20, 2, 'Brew the coffee and let it cool completely', 2),
(21, 2, 'Mix the Greek yogurt and honey in a bowl until smooth and creamy', 3),
(22, 2, 'Dip the ground oats lightly into the coffee, then layer them in a serving dish', 4),
(23, 2, '\r\nSpread the yogurt mixture on top, repeat the layers, and finish with a light dusting of cocoa powder', 5),
(123, 1, 'Preheat the oven to 200°C (400°F) and line a baking sheet with parchment paper', 1),
(124, 1, 'In a bowl, mix almond flour, shredded mozzarella, cream cheese, egg, salt, pepper, and garlic powder until a dough forms', 2),
(125, 1, 'Shape the dough into a pizza base on the parchment paper, pressing it evenly to about ¼ inch thickness', 3),
(126, 1, 'Bake the crust for 10–12 minutes until lightly golden', 4),
(127, 1, 'Remove from oven, spread tomato sauce evenly, and sprinkle shredded mozzarella on top', 5),
(128, 1, 'Bake for another 8–10 minutes until cheese melts and lightly browns', 6),
(129, 1, 'Garnish with fresh basil leaves and drizzle with olive oil if desired. Serve hot', 7);

-- --------------------------------------------------------

--
-- بنية الجدول `likes`
--

CREATE TABLE `likes` (
  `userid` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `likes`
--

INSERT INTO `likes` (`userid`, `recipeid`) VALUES
(2, 2),
(3, 1),
(2, 3);

-- --------------------------------------------------------

--
-- بنية الجدول `recipe`
--

CREATE TABLE `recipe` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `photofilename` varchar(255) NOT NULL,
  `videofilepath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `recipe`
--

INSERT INTO `recipe` (`id`, `userid`, `categoryid`, `name`, `description`, `photofilename`, `videofilepath`) VALUES
(1, 1, 1, 'low-carb Margherita pizza.', 'A healthy low-carb Italian Margherita pizza, made with almond flour crust, fresh mozzarella, rich tomato sauce, and basil. Perfect for anyone following a low-carb lifestyle, and ideal for lunch or dinner.', 'mozzarella.jpg', ''),
(2, 2, 3, 'low-Calories Tiramisu', 'A light and healthy low-calorie tiramisu, made with creamy Greek yogurt, finely ground oats, strong black coffee, and a touch of natural honey. Perfect for anyone looking for a guilt-free dessert, and ideal for a light treat or after-meal sweet.', 'Tir.webp', 'recipe.mp4'),
(3, 3, 2, 'Healthy quinoa salad with vegetables', 'A refreshing quinoa salad with crisp vegetables, lightly seasoned and perfectly balanced for a healthy bite', '', '');

-- --------------------------------------------------------

--
-- بنية الجدول `recipecategory`
--

CREATE TABLE `recipecategory` (
  `id` int(11) NOT NULL,
  `categoryname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `recipecategory`
--

INSERT INTO `recipecategory` (`id`, `categoryname`) VALUES
(1, 'Main course'),
(2, 'Appetizer'),
(3, 'Dessert');

-- --------------------------------------------------------

--
-- بنية الجدول `report`
--

CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `report`
--

INSERT INTO `report` (`id`, `userid`, `recipeid`) VALUES
(1, 1, 2),
(2, 1, 1),
(3, 1, 3);

-- --------------------------------------------------------

--
-- بنية الجدول `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `usertype` varchar(10) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `emailaddress` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photofilename` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `user`
--

INSERT INTO `user` (`id`, `usertype`, `firstname`, `lastname`, `emailaddress`, `password`, `photofilename`) VALUES
(1, 'admin', 'dalal', 'mohammed', 'dalal@gmail.com', '$2y$10$dYMEY908wgYXr51IAZ6HLuPBDTEbrhHScWabY8J8zb0JU9y1yRBZO', ''),
(2, 'user', 'sara', 'abdullah', 'sara123@gmail.com', '$2y$10$NwX9fAwVWTJdTdFSbwzV9OeXdJLHUoC84NYHmbIyoHG.YtzfXmeOK', ''),
(3, 'user', 'hessah', 'ahmad', 'hessah9@gmail.com', '$2y$10$kPKnM72dKCelWwbdgwDqxOpOIzimCC8RgqLeqXbCJwfWcirO8h846', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blockeduser`
--
ALTER TABLE `blockeduser`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipeid` (`recipeid`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD KEY `userid` (`userid`),
  ADD KEY `recipeid` (`recipeid`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipeid` (`recipeid`);

--
-- Indexes for table `instructions`
--
ALTER TABLE `instructions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipeid` (`recipeid`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD KEY `userid` (`userid`),
  ADD KEY `recipeid` (`recipeid`);

--
-- Indexes for table `recipe`
--
ALTER TABLE `recipe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_ibfk_1` (`userid`),
  ADD KEY `categoryid` (`categoryid`);

--
-- Indexes for table `recipecategory`
--
ALTER TABLE `recipecategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`),
  ADD KEY `recipeid` (`recipeid`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blockeduser`
--
ALTER TABLE `blockeduser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `instructions`
--
ALTER TABLE `instructions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `recipe`
--
ALTER TABLE `recipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `recipecategory`
--
ALTER TABLE `recipecategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`recipeid`) REFERENCES `recipecategory` (`id`),
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

--
-- قيود الجداول `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- قيود الجداول `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- قيود الجداول `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- قيود الجداول `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- قيود الجداول `recipe`
--
ALTER TABLE `recipe`
  ADD CONSTRAINT `recipe_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `recipe_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `recipecategory` (`id`);

--
-- قيود الجداول `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
