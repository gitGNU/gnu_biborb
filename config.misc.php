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
 * File: config.misc.php
 * 
 * Description:
 *
 */

include('./php/utilities.php');

/**
 *  Set the default time zone.
 *  Only available to php >= 5.1.0
 */
if ( version_compare(phpversion(), "5.1.0", ">=") )
{
	date_default_timezone_set(BIBORB_TZ);
}

/**
 * Stripslashes
 */
if (get_magic_quotes_gpc())
{
   $_GET     = undoMagicQuotes($_GET);
   $_POST    = undoMagicQuotes($_POST);
   $_COOKIE  = undoMagicQuotes($_COOKIE);
   $_REQUEST = undoMagicQuotes($_REQUEST);
}

define("_PHP_SELF_",$_SERVER['PHP_SELF']);
?>
