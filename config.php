<?php
/**
 * 
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2004  Guillaume Gardey
 * 
 * BibORB is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * BibORB is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 */

/**
 * 
 * File: config.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *
 *      Globals configurations variables. See each for details.
 *    
 */

/**
 * Version of Biborb
 */
$biborb_version = "CVS";
$date_release = "29 May 2004";


/**
 * If TRUE, this will disable authentification.
 * All users will have the administrator status
 */
$disable_authentication = FALSE;


/**
 *  Sould a confirmation may be displayed when deleting an entry
 */
$warn_before_deleting = TRUE;

/**
 * Should the abstract be present for each entry.
 */
$display_abstract = FALSE;

/**
 * Should action be represented by icons or not.
 */
$display_images = TRUE;

/**
 *  Sould action be represented by text or not.
 */
$display_text = FALSE;

/**
 * Name of the PHP SESSION
 */
$session_id = "SID";

/**
 * Database configuration.
 * Used when $disable_authentication set to FALSE.
 * The table biborb_users references 'administrator' users.
 */
$host = "localhost";
$dbuser = "biborb-admin";
$pass = "biborbdev";
$db = "biborb";
$table = "biborb_users";

/**
 * List of all possible fields in a BibTeX record.
 * The '_' is mandatory. 
 */
$bibtex_entries = array(
    "_id",
    "_address", 
    "_annote", 
    "_author", 
    "_booktitle", 
    "_chapter", 
    "_crossref",
    "_edition",
    "_editor",
    "_howpublished",
    "_institution",
    "_journal",
    "_key",
    "_month",
    "_note", 
    "_number", 
    "_organisation", 
    "_pages", 
    "_publisher", 
    "_school", 
    "_series", 
    "_title", 
    "_type", 
    "_volume", 
    "_year",
    "_abstract", 
    "_keywords",
    "_url",
    "_urlzip",
    "_pdf",
    "_groups",
    "_website",
    "_longnotes",
    "_link"
);

/**
 * Choose which fields to save when exporting an entry to bibtex
 * By default all fields are exported
 */
$fields_to_export = array('author',
			  'address',
			  'annote',
			  'author',
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
			  'organisation',
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
$CSS_FILE="style.css";

/**
 * Display sort in all/group/search view
 * If no, displayed only on search
 * true/no
 */
$DISPLAY_SORT="no";

/**
 * Default sort method: ID,title,year
 */
$DEFAULT_SORT="ID";

?>
