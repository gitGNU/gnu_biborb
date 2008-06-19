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
 * File: proxyDbManager.php
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
