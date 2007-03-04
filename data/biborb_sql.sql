-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mercredi 08 Juin 2005 à 16:05
-- Version du serveur: 4.0.24
-- Version de PHP: 4.3.10-14
-- 
-- Base de données: `biborb`
-- 
CREATE DATABASE `biborb`;
USE biborb;

-- --------------------------------------------------------

-- 
-- Structure de la table `biborb_biblio`
-- 

CREATE TABLE `biborb_biblio` (
  `id_biblio` int(11) NOT NULL auto_increment,
  `description_biblio` varchar(255) default NULL,
  `name_biblio` varchar(50) default NULL,
  PRIMARY KEY  (`id_biblio`),
  UNIQUE KEY `name_biblio` (`name_biblio`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `biborb_document`
-- 

CREATE TABLE `biborb_document` (
  `id_document` int(11) NOT NULL auto_increment,
  `abstract_document` text,
  `annote_document` text,
  `author_document` text,
  `bibtexID_document` varchar(25) default NULL,
  `booktitle_document` text,
  `chapter_document` tinyint(4) default NULL,
  `crossref_document` varchar(25) default NULL,
  `edition_document` varchar(50) default NULL,
  `editor_document` varchar(25) default NULL,
  `howpublished_document` varchar(25) default NULL,
  `id_biblio_document` int(11) NOT NULL default '0',
  `institution_document` varchar(25) default NULL,
  `journal_document` varchar(25) default NULL,
  `keywords_document` varchar(25) default NULL,
  `link_document` varchar(50) default NULL,
  `longnotes_document` text,
  `month_document` char(3) default NULL,
  `note_document` text,
  `number_document` varchar(25) default NULL,
  `organization_document` varchar(25) default NULL,
  `pages_document` varchar(25) default NULL,
  `pdf_document` varchar(100) default NULL,
  `rate_document` decimal(10,0) default NULL,
  `publisher_document` varchar(25) default NULL,
  `school_document` varchar(25) default NULL,
  `series_document` varchar(25) default NULL,
  `title_document` varchar(255) default NULL,
  `type_document` varchar(25) default NULL,
  `url_document` varchar(100) default NULL,
  `urlzip_document` varchar(100) default NULL,
  `volume_document` varchar(25) default NULL,
  `website_document` varchar(100) default NULL,
  `year_document` int(4) default NULL,
  `read_document` varchar(25) NOT NULL default '',
  `own_document` varchar(25) NOT NULL default '',
  `ad_url_document` varchar(60) default NULL,
  `ad_pdf_document` varchar(60) default NULL,
  `ad_urlzip_document` varchar(60) default NULL,
  `address_document` varchar(60) default NULL,
  `key_document` varchar(50) default NULL,
  PRIMARY KEY  (`id_document`),
  UNIQUE KEY `bibtexID_document` (`bibtexID_document`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `biborb_group`
-- 

CREATE TABLE `biborb_group` (
  `id_group` int(11) NOT NULL auto_increment,
  `description_group` varchar(100) default NULL,
  `name_group` varchar(50) default NULL,
  PRIMARY KEY  (`id_group`),
  UNIQUE KEY `name_group` (`name_group`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `biborb_group2document`
-- 

CREATE TABLE `biborb_group2document` (
  `id_document_group2document` int(11) NOT NULL default '0',
  `id_group_group2document` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_document_group2document`,`id_group_group2document`)
) TYPE=MyISAM;
