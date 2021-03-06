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
 * File: index.php
 *
 * Description:
 *      This is the page that is initially loaded when accessing BibORB.
 *
 *      It offers the following functions:
 *          - authentication ($disable_authentication set to false)
 *          - add a new bibliography
 *                  (if $disable_authentication set to true
 *                   or registered user)
 *          - delete a bibliography
 *                  (move to a trash folder)
 *          - accessing the list of available bibliographies
 *
 */

require_once("config.php");          // load configuration variables
require_once("config.misc.php");
require_once("php/proxyDbManager.php");    // load the db manager
require_once("php/interface-index.php");   // load function to generate the interface
require_once("php/auth.php");        // load authentication class
require_once("php/i18nToolKit.php");        // load i18n functions
require_once("php/error.php");       // load biborb error handler
require_once("php/HtmlToolKit.php");
require_once("php/FileToolKit.php");
require_once("php/functions.php");
require_once("php/User.php");


//require_once("php/third_party/Cache/Lite/Output.php"); // cache system


/**
 *   Load the session
 */
// do not put web pages in browser cache
session_cache_limiter('nocache');
session_name("SID");
session_start();

//set_error_handler("biborb_error_handler");

/**
 * Set the user
 */
if ( !isset($_SESSION['user']) ||
     !is_object($_SESSION['user']))
{
    $_SESSION['user'] = new User();
    if (!$_SESSION['user']->isSetPermissions())
    {
        $aAuthenticationBackend = new Auth();
        $aAuthenticationBackend�->fillPermissionsForUser($_SESSION['user']);
    }
    if (!$_SESSION['user']->isSetPreferences())
    {
        if (empty($aAuthenticationBackend))
            $aAuthenticationBackend = new Auth();
        $aAuthenticationBackend�-fillPreferencesForUser($_SESSION['user']);;
    }
}
    

/**
 * Set the errorManager
 */
if ( !isset($_SESSION['errorManager']) ||
     !is_object($_SESSION['errorManager']))
{
    $_SESSION['errorManager'] = new ErrorManager();
}


/**
 * Set the DbManager object.
 */
if ( !isset($_SESSION['DbManager']) ||
     !is_object($_SESSION['DbManager']))
{
    $_SESSION['DbManager'] = new DbManager();
}

/**
 * i18n, choose default lang if not set up
 * Try to detect it from the session, browser or fallback to default.
 */
if ( !isset($_SESSION['i18n']) ||
     !is_object($_SESSION['i18n']))
{
    $_SESSION['i18n'] = new i18nToolKit($_SESSION['user']->getPreference('language'), DEFAULT_LANG);
}

/**
 * To store an error or a message. (mode=result)
 */
$error_or_message = array('error' => null,
                          'message' => null);

/**
 * Get a value for 'mode' in the $_GET array.
 * If not in $_GET, receive null
 * The 'mode' variable sets which page to display
 */
$mode = null;
if ( isset($_GET['mode']) )
{
    $mode = $_GET['mode'];
}
else if ( isset($_POST['mode']) )
{
    $mode = $_POST['mode'];
}

/**
 * Set the access level
 * If authentication activated delegate to the Auth class.
 * Else set full power to any user.
 */
if (!DISABLE_AUTHENTICATION)
{
    // create a new Auth object if needed
    if (!isset($_SESSION['auth']) ||
        !isobject($_SESSION['auth']))
        $_SESSION['auth'] = new Auth();

    if (!isset($_SESSION['user']))
        $_SESSION['user_is_admin'] = FALSE;

}
else
{
    $_SESSION['user_is_admin'] = TRUE;
}

/**
 * Look in $_GET for an action to be performed
 */
$aAction = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : null);
switch ($aAction)
{
    /*  Select the lang   */
    case 'select_lang':
        $_SESSION['i18n']->loadLocale($_GET['lang']);
        break;
        
        /* Create a database  */
    case 'create':
        // check we have the authorization to modify
        if ( !$_SESSION['user']->isAdmin() )
        {
            trigger_error("You are not authorized to create bibliographies!",ERROR);
        }
        
        $res = $_SESSION['DbManager']->createDb($_POST['database_name'],
                                                $_POST['description']);
        break;
        
        /* Delete a database  */
    case 'delete':
        // check we have the authorization to modify
        if ( !$_SESSION['user']->isAdmin() )
        {
            trigger_error("You are not authorized to delete bibliographies!",ERROR);
        }
        foreach($_POST['bibs'] as $aBibName)
        {    
            $error_or_message['message'] .= $_SESSION['DbManager']->deleteDb($aBibName);
        }
        break;
        
        /*  Logout  */
    case 'logout':
        $_SESSION['user_is_admin'] = FALSE;
        $_SESSION['user_can_add'] = FALSE;
        $_SESSION['user_can_modidy'] = FALSE;
        $_SESSION['user_can_delete'] = FALSE;
        unset($_SESSION['user']);
        unset($_SESSION['user_pref']);
        $_SESSION['language'] = DEFAULT_LANG;
        load_i18n_config($_SESSION['language']);
        break;
        
        /*  Login */
    case 'login':
        $login = $_POST['login'];
        $mdp = $_POST['mdp'];
        // check missing values
        if ($login=="" || $mdp=="")
        {
            $error_or_message['error'] = msg("LOGIN_MISSING_VALUES");
            $mode = "login";
        }
        else
        {
            // check the user name
            $loggedin = $_SESSION['auth']->is_valid_user($login,$mdp);
            if ($loggedin)
            {
                // set user privileges
                $_SESSION['user'] = $login;
                $_SESSION['user_is_admin'] = $_SESSION['auth']->is_admin_user($login);
                $_SESSION['user_pref'] = $_SESSION['auth']->get_preferences($login);
                // select language
                $_SESSION['language'] = $_SESSION['user_pref']['default_language'];
                load_i18n_config($_SESSION['language']);
                // redirect to the default database
                if (array_key_exists($_SESSION['user_pref']['default_database'],get_databases_names()))
                {
                    header("Location:./bibindex.php?bibname=".$_SESSION['user_pref']['default_database']);
                }
                else
                {
                    $mode = "welcome";
                }
            }
            else
            {
                $error_or_message['error'] = msg("LOGIN_WRONG_USERNAME_PASSWORD");
                $mode = "login";
            }
        }
        break;
        
        /* Update user's preferences. */
    case 'update_preferences':
        $_SESSION['auth']->set_preferences($_POST,$_SESSION['user']);
        $_SESSION['user_pref'] = $_SESSION['auth']->get_preferences($_SESSION['user']);
        $_SESSION['language'] = $_SESSION['user_pref']['default_language'];
        load_i18n_config($_SESSION['language']);
        $mode = "preferences";
        $message = msg("Preferences updated.");
        break;
        
    default:
        break;
}

/*********************************************** BEGINING OF THE HTML OUTPUT **/

/**
 * Select what to do according the value of 'mode'
 */
switch($mode)
{    
	// This is the welcome page.
    case 'welcome': echo index_welcome(); break;

	// List of available bibliograpies
    case 'select': echo guiBibsManager(); break;

    // Add a new bibliography
//    case 'add_database': echo guiAddDatabase(); break;

	// Delete a bibliography
//    case 'delete_database': echo index_delete_database(); break;

	// Little help on what is available for the administrator mode
    case 'manager_help': echo index_manager_help(); break;

	// Login form
    case 'login': echo index_login(); break;

	// Generic page to display result of operations (add, delete, ...)
    case 'result': echo index_result(); break;

    // User's Preferences panel
    case 'preferences': echo index_preferences(); break;

	// By default, load the welcome page
    default: echo index_welcome(); break;
}
$_SESSION['errorManager']->purgeAll();
?>
