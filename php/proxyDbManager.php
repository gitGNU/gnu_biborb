<?php
/**
 *
 * This file is part of BibORB
 * 
 * Copyright (C) 2007  Guillaume Gardey (ggardey@club-internet.fr)
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
 * File: proxyDbManager.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *
 */

switch(DB_BACKEND)
{
    case 'xml':
        include('./php/db_backends/DbManager.xml.php');
        break;
    default:
        if (isset($_SESSION['errorManager']))
        {
            $_SESSION['errorManager']->triggerError("ERROR_INVALID_DB_MANAGER",array( 'db_manager' => DB_BACKEND));
        }
        else
        {
            trigger_error("ERROR_INVALID_DB_MANAGER", E_USER_ERROR);
        }
        break;
}

?>
