# phpMyAdmin SQL Dump
# version 2.5.3
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Oct 15, 2003 at 11:13 AM
# Server version: 4.0.15
# PHP Version: 4.3.3
# 
# Database : `biborb`
# 

# --------------------------------------------------------

#
# Table structure for table `biborb_users`
#

CREATE TABLE `biborb_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(20) NOT NULL default '',
  `password` varchar(32) NOT NULL default '0',
  `name` varchar(20) NOT NULL default '',
  `firstname` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `numero` (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Dumping data for table `biborb_users`
#

INSERT INTO `biborb_users` (`id`, `login`, `password`, `name`, `firstname`) VALUES (1, 'admin', md5('admin'), '', '');
