{\rtf1\ansi\ansicpg1252\cocoartf1038\cocoasubrtf360
{\fonttbl\f0\fswiss\fcharset0 Helvetica;}
{\colortbl;\red255\green255\blue255;}
\paperw11900\paperh16840\margl1440\margr1440\vieww9000\viewh8400\viewkind0
\pard\tx566\tx1133\tx1700\tx2267\tx2834\tx3401\tx3968\tx4535\tx5102\tx5669\tx6236\tx6803\ql\qnatural\pardirnatural

\f0\fs24 \cf0 -- phpMyAdmin SQL Dump\
-- version 3.5.2\
-- http://www.phpmyadmin.net\
--\
-- Host: localhost\
-- Generation Time: Feb 03, 2013 at 07:34 AM\
-- Server version: 5.5.9\
-- PHP Version: 5.3.6\
\
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";\
SET time_zone = "+00:00";\
\
--\
-- Database: `yakimbi`\
--\
\
-- --------------------------------------------------------\
\
--\
-- Table structure for table `fav_images`\
--\
\
CREATE TABLE IF NOT EXISTS `fav_images` (\
  `id` int(10) NOT NULL AUTO_INCREMENT,\
  `uid` int(10) NOT NULL,\
  `image_url` varchar(150) NOT NULL,\
  `farm_id` int(50) NOT NULL,\
  `server_id` int(50) NOT NULL,\
  `img_id` varchar(50) NOT NULL,\
  `secret` varchar(50) NOT NULL,\
  `tag` text NOT NULL COMMENT 'tag',\
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\
  PRIMARY KEY (`id`)\
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;\
\
--\
-- Dumping data for table `fav_images`\
--\
\
-- --------------------------------------------------------\
\
--\
-- Table structure for table `fav_images_comments`\
--\
\
CREATE TABLE IF NOT EXISTS `fav_images_comments` (\
  `id` int(10) NOT NULL AUTO_INCREMENT,\
  `fav_id` int(10) NOT NULL,\
  `comments` text NOT NULL,\
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\
  PRIMARY KEY (`id`)\
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;\
}