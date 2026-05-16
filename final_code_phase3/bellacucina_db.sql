-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 21, 2026 at 11:37 AM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

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
-- Table structure for table `blockeduser`
--

CREATE TABLE `blockeduser` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `emailaddress` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `blockeduser`
--

INSERT INTO `blockeduser` (`id`, `firstname`, `lastname`, `emailaddress`) VALUES
(4, 'abdullah', 'nasser', 'abdullah@gmail.com'),
(5, 'hessah', 'ahmad', 'hessah9@gmail.com'),
(6, 'ahmed', 'khalid', 'ahmed@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`id`, `recipeid`, `userid`, `comment`, `date`) VALUES
(10, 5, 5, 'so yummy!', '2026-04-21 09:52:05'),
(12, 4, 5, 'it was amazing thanks', '2026-04-21 10:19:25'),
(13, 5, 2, 'looks delicious ?', '2026-04-21 10:22:56'),
(14, 7, 4, 'looks delicious ?', '2026-04-21 13:41:39');

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `userid` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `favourites`
--

INSERT INTO `favourites` (`userid`, `recipeid`) VALUES
(4, 5),
(5, 5),
(4, 7);

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL,
  `ingredientname` varchar(100) NOT NULL,
  `ingredientquantity` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipeid`, `ingredientname`, `ingredientquantity`) VALUES
(144, 4, 'Almond flour', '2cups'),
(145, 4, 'Shredded mozzarella cheese', '1cup'),
(146, 4, 'Cream cheese', '2tablespoon'),
(147, 4, 'Egg', '1Large'),
(148, 4, 'Tomato sauce', '1cup'),
(149, 4, 'Fresh basil leaves', 'handful'),
(150, 4, 'Olive oil', '1tablespoon'),
(151, 4, 'salt and pepper', 'to taste'),
(152, 4, 'Garlic powder', 'to taste'),
(153, 5, 'Plain Greek Yogurt (0–2% fat)', '1cup'),
(154, 5, 'Rolled Oats (finely ground)', 'cup¾'),
(155, 5, 'Strong Black Coffee (cooled)', 'cup½'),
(156, 5, 'Raw Honey or Maple Syrup', '2tablespoon'),
(157, 5, 'Unsweetened Cocoa Powder', '1tablespoon'),
(164, 7, 'Quinoa (cooked)', '1cup'),
(165, 7, 'Cherry Tomatoes', 'cup½'),
(166, 7, 'Cucumber (diced)', 'cup½'),
(167, 7, 'Fresh Parsley (chopped)', '2tablespoons'),
(168, 7, 'Lemon Juice', '1tablespone'),
(169, 7, 'Salt & Black Pepper', 'to taste');

-- --------------------------------------------------------

--
-- Table structure for table `instructions`
--

CREATE TABLE `instructions` (
  `id` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL,
  `step` text NOT NULL,
  `steporder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `instructions`
--

INSERT INTO `instructions` (`id`, `recipeid`, `step`, `steporder`) VALUES
(130, 4, 'Preheat the oven to 200°C (400°F) and line a baking sheet with parchment paper', 1),
(131, 4, 'In a bowl, mix almond flour, shredded mozzarella, cream cheese, egg, salt, pepper, and garlic powder until a dough forms', 2),
(132, 4, 'Shape the dough into a pizza base on the parchment paper, pressing it evenly to about ¼ inch thickness', 3),
(133, 4, 'Bake the crust for 10–12 minutes until lightly golden', 4),
(134, 4, 'Remove from oven, spread tomato sauce evenly, and sprinkle shredded mozzarella on top', 5),
(135, 4, 'Bake for another 8–10 minutes until cheese melts and lightly browns', 6),
(136, 4, 'Garnish with fresh basil leaves and drizzle with olive oil if desired. Serve hot', 7),
(137, 5, 'Grind the oats into a fine powder using a blender or food processor', 1),
(138, 5, 'Brew the coffee and let it cool completely', 2),
(139, 5, 'Mix the Greek yogurt and honey in a bowl until smooth and creamy', 3),
(140, 5, 'Dip the ground oats lightly into the coffee, then layer them in a serving dish', 4),
(141, 5, 'Spread the yogurt mixture on top, repeat the layers, and finish with a light dusting of cocoa powder', 5),
(147, 7, 'Cook the quinoa according to package instructions and let it cool', 1),
(148, 7, 'Chop the tomatoes, cucumber, and parsley', 2),
(149, 7, 'In a bowl, combine the quinoa with the chopped vegetables', 3),
(150, 7, 'Add olive oil and lemon juice, then mix well', 4),
(151, 7, 'Season with salt and black pepper to taste', 5);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `userid` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`userid`, `recipeid`) VALUES
(4, 5),
(5, 5),
(4, 7);

-- --------------------------------------------------------

--
-- Table structure for table `recipe`
--

CREATE TABLE `recipe` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `photofilename` varchar(255) NOT NULL,
  `videofilepath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recipe`
--

INSERT INTO `recipe` (`id`, `userid`, `categoryid`, `name`, `description`, `photofilename`, `videofilepath`) VALUES
(4, 4, 1, 'low-carb Margherita pizza.', 'A healthy low-carb Italian Margherita pizza, made with almond flour crust, fresh mozzarella, rich tomato sauce, and basil. Perfect for anyone following a low-carb lifestyle, and ideal for lunch or dinner.', '4_1776753757_mozzarella.jpg', NULL),
(5, 2, 3, 'low-Calories Tiramisu', 'A light and healthy low-calorie tiramisu, made with creamy Greek yogurt, finely ground oats, strong black coffee, and a touch of natural honey. Perfect for anyone looking for a guilt-free dessert, and ideal for a light treat or after-meal sweet.', '2_1776753952_Tir.webp', '1776753952_recipe.mp4'),
(7, 5, 2, 'Healthy quinoa salad with vegetables', 'A refreshing quinoa salad with crisp vegetables, lightly seasoned and perfectly balanced for a healthy bite', '5_1776767990_salad.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `recipecategory`
--

CREATE TABLE `recipecategory` (
  `id` int(11) NOT NULL,
  `categoryname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recipecategory`
--

INSERT INTO `recipecategory` (`id`, `categoryname`) VALUES
(1, 'Main course'),
(2, 'Appetizer'),
(3, 'Dessert');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `recipeid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`id`, `userid`, `recipeid`) VALUES
(5, 2, 4),
(6, 5, 4),
(8, 4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `usertype` varchar(10) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `emailaddress` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photofilename` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `usertype`, `firstname`, `lastname`, `emailaddress`, `password`, `photofilename`) VALUES
(1, 'admin', 'dalal', 'mohammed', 'dalal@gmail.com', '$2y$10$dYMEY908wgYXr51IAZ6HLuPBDTEbrhHScWabY8J8zb0JU9y1yRBZO', ''),
(2, 'user', 'sara', 'abdullah', 'sara123@gmail.com', '$2y$10$NwX9fAwVWTJdTdFSbwzV9OeXdJLHUoC84NYHmbIyoHG.YtzfXmeOK', ''),
(4, 'user', 'Laila', 'khalid', 'Laila@gmail.com', '$2y$10$4brHxGJxgr3ycnG487C.g.FuB/3xwDIySkpMNFiOkZNLeJw7jf9ji', 'user_69e16674cf3d26.26968275.jpg'),
(5, 'user', 'ghala', 'ibrahim', 'ghala@gmail.com', '$2y$10$fuZd/g0pFmN5SUiskEa1A.ZeaMG185.cidcYWQCNWNZeDal.smmtC', 'user_69e68078dedae7.98136685.jpg');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `instructions`
--
ALTER TABLE `instructions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT for table `recipe`
--
ALTER TABLE `recipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recipecategory`
--
ALTER TABLE `recipecategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`),
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `user` (`id`);

--
-- Constraints for table `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- Constraints for table `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);

--
-- Constraints for table `recipe`
--
ALTER TABLE `recipe`
  ADD CONSTRAINT `recipe_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `recipe_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `recipecategory` (`id`);

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`recipeid`) REFERENCES `recipe` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
