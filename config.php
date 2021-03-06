<?php
/**
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2008 Guillaume Gardey <glinmac+biborb@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

/**
 * File: config.php
 * 
 * Description:
 *
 *      Globals configurations variables. See each for details.
 *    
 */

/**
 * Version of Biborb
 */
define("BIBORB_XML_VERSION","1.1");
define("BIBORB_VERSION","CVS");
define("BIBORB_RELEASE_DATE","5 May 2005");

/**
 * Path where is install biborb
 * You shouldn't modify it
 */
define("BIBORB_PATH",realpath("./index.php"));

/**
 * Localization
 * Available: en_US, fr_FR, de_DE, it_IT
 */
define("DEFAULT_LANG",'en_US');

/**
 * Show available languages on BibORB pages
 * TRUE/FALSE
 */
define("DISPLAY_LANG_SELECTION",TRUE);

/**
 * If TRUE, this will disable authentification.
 * All users will have the administrator status
 */
define("DISABLE_AUTHENTICATION",TRUE);

/**
 * Authentication methods: mysql, files
 * Used if DISABLE_AUTHENTICATION = FALSE
 */
define("AUTH_METHOD",'files');

/**
 * Database backend (xml, mysql)
 */
define("DB_BACKEND","xml");

/**
 *  Should a confirmation be displayed when deleting entries
 */
define("WARN_BEFORE_DELETING",TRUE);

/**
 * Should the abstract be present for each entry.
 */
define("DISPLAY_ABSTRACT",FALSE);

/**
 * Should action be represented by icons or not.
 */
define("DISPLAY_IMAGES",TRUE);

/**
 *  Sould action be represented by text or not.
 */
define("DISPLAY_TEXT",FALSE);

/**
 * List of all possible fields in a BibTeX record.
 * The '_' is mandatory. 
 */
$bibtex_entries = array(
    "id",
    "address", 
    "annote", 
    "author", 
    "booktitle", 
    "chapter", 
    "crossref",
    "edition",
    "editor",
    "howpublished",
    "institution",
    "journal",
    "key",
    "month",
    "note", 
    "number", 
    "organization", 
    "pages", 
    "publisher", 
    "school", 
    "series", 
    "title", 
    "type", 
    "volume", 
    "year",
    "abstract", 
    "keywords",
    "url",
    "urlzip",
    "pdf",
    "ad_url",
    "ad_pdf",
    "ad_urlzip",
    "groups",
    "website",
    "longnotes",
    "link",
    "own",
    "read"
);

/**
 * Choose which fields to save when exporting an entry to bibtex
 * By default all fields are exported
 */
$fields_to_export = array('author',
			  'address',
			  'annote',
			  'booktitle',
			  'chapter',
			  'crossref',
			  'edition',
			  'editor',
			  'howpublished',
			  'institution',
			  'journal',
			  'key',
			  'month',
			  'note',
			  'number',
			  'organization',
			  'pages',
			  'publisher',
			  'school',
			  'series',
			  'title',
			  'type',
			  'volume',
			  'year');

/**
 * The CSS style file to use.
 */
define("CSS_FILE","css/style.css");

/**
 * Display sort in all/group/search view
 * If no, displayed only on search
 * TRUE/FALSE
 */
define("DISPLAY_SORT",TRUE);

/**
 * Default sort method: ID,title,year
 * and order: ascending/descending
 */
define("DEFAULT_SORT","ID");
define("DEFAULT_SORT_ORDER","ascending");

/**
 * Max number of references by page.
 */
define("MAX_REFERENCES_BY_PAGE",10);

/**
 * Keep an up-to-date BibTeX file.
 * If true, each time a bibliography is modified, a BibTeX file is updated
 * in its 'bibs' directory.
 */
define("GEN_BIBTEX",TRUE);

/**
 *  Activate the shelf mode.
 *  Additional action will be available to set the ownership of a reference
 *  and its reading status(read, read next, not read)
 * value: TRUE/FALSE
 */
define("SHELF_MODE",TRUE);

/**
 * umask - Set the mask to use when creating files.
 *
 */
define("UMASK",0111);

/**
 * dmask - Set the mask to use when creating directories.
 */
define("DMASK",0000);

/**
 *  List of file types that can be uploaded
 */
$valid_upload_extensions = array('ps','pdf','gz','bz2','zip');

/**
 * Set the timezone
 */
define("BIBORB_TZ", "Europe/Paris");

?>
