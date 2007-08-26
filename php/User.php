<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2003-2007  Guillaume Gardey (ggardey@club-internet.fr)
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
 * This class represents a Biborb users.
 * Stores permissions and preferences.
 *
 */
class User
{
    // username
    var $_username;
    /*
      Permission is stored in a string with the following mask "Aade"
      A -> admin user
      a -> user is allowed to add elements
      d -> user is allowed to delete elements
      e -> user is allowed to edit telements
    */
    var $_permissions;
    // user preference
    var $_preferences;
    

    /**
     * Default constructor.
     */
    function User($username = "")
    {
        if (DISABLE_AUTHENTICATION)
        {
            $this->_usernmae = "admin";
            $this->_permissions = "1000";
            $this->_preferences = array('language'=> i18nToolKit::getPreferedLanguage());
        }
        else
        {
            $this->_usernmae = $username;
        }
    }
    
    /**
     * Get the username
     */
    function getUsername()
    {
        return $this->_username;
    }

    /**
     * Is the user an administrator?
     */
    function isAdmin()
    {
        return $this->_permissions[0] === '1';
    }

    function isSetPermissions()
    {
        return !empty($this->_permissions);
    }

    function isSetPreferences()
    {
        return !empty($this->_preferences);
    }

    function getPreference($iKey)
    {
        if (!isset($this->_preferences[$iKey]))
            $_SESSION['errorManager']->triggerError("Preference not defined.", "preference key: $iKey", 'index.php?mode=select');
        return $this->_preferences[$iKey];
    }
}


?>