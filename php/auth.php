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
 * File: auth.php
 *
 *    Redirect to the php source matching the authentication method
 *    defined in the configuration file of BibORB.
 */

require_once("config.php");
require_once("config.misc.php");
if(!DISABLE_AUTHENTICATION){
    switch(AUTH_METHOD){
        // Use file authentication system
        case 'files':
            require_once("php/auth_backends/auth.file.php");
            break;

        // Use mysql authentication system
        case 'mysql':
            require_once("php/auth_backends/auth.mysql.php");
            break;
    }
}
?>
