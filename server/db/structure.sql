-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Generation Time: Dec 10, 2011 at 04:07 PM
-- Server version: 5.0.91
-- PHP Version: 5.3.3-7+squeeze3
-- 
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `Platforms`
-- 

CREATE TABLE `Platforms` (
  `id` int(11) NOT NULL auto_increment,
  `rb2id` varchar(16) collate latin1_german2_ci NOT NULL,
  `tbrbid` varchar(16) collate latin1_german2_ci NOT NULL,
  `name` varchar(50) collate latin1_german2_ci NOT NULL,
  `rb3id` varchar(16) collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `RB3CurrentScores`
-- 

CREATE TABLE `RB3CurrentScores` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL,
  `song` int(11) NOT NULL,
  `instrument` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `first_recorded` datetime NOT NULL,
  `last_recorded` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53538 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `RB3HistoricalScores`
-- 

CREATE TABLE `RB3HistoricalScores` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL,
  `song` int(11) NOT NULL,
  `instrument` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `first_recorded` datetime NOT NULL,
  `last_recorded` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3523 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `RB3Instruments`
-- 

CREATE TABLE `RB3Instruments` (
  `id` int(11) NOT NULL auto_increment,
  `rbid` varchar(16) collate latin1_german2_ci NOT NULL,
  `name` varchar(50) collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `RB3RawScores`
-- 

CREATE TABLE `RB3RawScores` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL,
  `song` int(11) NOT NULL,
  `instrument` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `recorded` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=591074 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `RB3Songs`
-- 

CREATE TABLE `RB3Songs` (
  `id` int(11) NOT NULL auto_increment,
  `rbid` int(11) NOT NULL,
  `name` varchar(255) collate latin1_german2_ci NOT NULL,
  `sourceid` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2930 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `RB3Sources`
-- 

CREATE TABLE `RB3Sources` (
  `id` int(11) NOT NULL auto_increment,
  `rbid` varchar(16) collate latin1_german2_ci NOT NULL,
  `name` varchar(255) collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `Users`
-- 

CREATE TABLE `Users` (
  `id` int(11) NOT NULL auto_increment,
  `platform` int(11) NOT NULL default '0',
  `name` varchar(16) collate latin1_german2_ci NOT NULL,
  `arsname` varchar(50) collate latin1_german2_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
