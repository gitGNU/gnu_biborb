<?php
/**
 * 
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2004  Guillaume Gardey (ggardey@club-internet.fr)
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
$date_release = "06 October 2004";

/**
 * Name of the PHP SESSION
 * You shouldn't need to change this value.
 */
$session_id = "SID";

/**
 * Localization
 * Available: en_US, fr_FR
 */
$language = "en_US";

/**
 * Show available languages on BibORB pages
 * TRUE/FALSE
 */
$display_language_selection = TRUE;

/**
 * If TRUE, this will disable authentification.
 * All users will have the administrator status
 */
$disable_authentication = TRUE;

/**
 * Authentication methods: mysql, files
 * Used if $disable_authentication = FALSE
 */
$authentication_method = 'files';

/**
 *  Should a confirmation be displayed when deleting entries
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
    "groups",
    "website",
    "longnotes",
    "link"
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
$CSS_FILE="css/style.css";

/**
 * Display sort in all/group/search view
 * If no, displayed only on search
 * TRUE/FALSE
 */
$DISPLAY_SORT=TRUE;

/**
 * Default sort method: ID,title,year
 * and order: ascending/descending
 */
$DEFAULT_SORT="ID";
$DEFAULT_SORT_ORDER="ascending";

/**
 * Max number of references by page.
 */
$MAX_REFERENCES_BY_PAGE=10;


/**
 * Keep an up-to-date BibTeX file.
 * If true, each time a bibliography is modified, a BibTeX file is updated
 * in its 'bibs' directory.
 */
$GEN_BIBTEX=TRUE
?>
