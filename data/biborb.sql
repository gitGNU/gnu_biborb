# phpMyAdmin SQL Dump
# version 2.5.5-pl1
# http://www.phpmyadmin.net
#
# Serveur: localhost
# Généré le : Vendredi 20 Août 2004 à 19:53
# Version du serveur: 4.0.15
# Version de PHP: 4.3.4
# 
# Base de données: `biborb`
# 

# --------------------------------------------------------

#
# Structure de la table `biborb_auth`
#

CREATE TABLE `biborb_auth` (
  `user_id` int(11) NOT NULL default '0',
  `db_name` varchar(100) NOT NULL default '',
  `access` char(3) NOT NULL default ''
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Structure de la table `biborb_users`
#

CREATE TABLE `biborb_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(20) NOT NULL default '',
  `password` varchar(32) NOT NULL default '0',
  `name` varchar(20) NOT NULL default '',
  `firstname` varchar(20) NOT NULL default '',
  `admin` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `numero` (`id`)
) TYPE=MyISAM;

INSERT INTO `biborb_users` (`id`, `login`, `password`, `name`, `firstname`, `admin`) VALUES (1, 'admin', md5('admin'), '', '','Y');
INSERT INTO `biborb_users` (`login`, `admin`) VALUES ('_anonymous_', 'N');
