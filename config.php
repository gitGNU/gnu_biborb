<?php

/**

This file is part of BibORB

Copyright (C) 2003  Guillaume Gardey

BibORB is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

BibORB is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

**/

/**

File: config.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

  some config values

**/


/**
If TRUE, this will disable authentification to modify or add entries.
 */
$disable_authentication = TRUE;


/**
Should the abstract be present for each entry.
*/
$display_abstract = FALSE;

/**
Name of the PHP SESSION
*/
$session_id = "SID";

/**
Database configuration.
*/
$host = "localhost";
$dbuser = "biborb-admin";
$pass = "biborbdev";
$db = "biborb";
$table = "biborb_users";


?>
