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
 * File: biborbdb.php
 * 
 * Description:
 *
 *      Select which backend to use for recording references.
 *
 *      Presently, only single XML file is supported.
 */

switch(DB_BACKEND)
{
    case 'xml':
        include('php/db_backends/biborbdb.xmlfile.php');
        break;
    case 'mysql':
        include('php/db_backends/biborbdb.sql.php');
        break;
    default:
        if (isset($_SESSION['errorManager']))
        {
            $_SESSION['errorManager']->triggerError("ERROR_INVALID_DB_BACKEND",array( 'db_backend' => DB_BACKEND));
        }
        else
        {
            trigger_error("ERROR_INVALID_DB_BACKEND", E_USER_ERROR);
        }
        break;
}

?>
