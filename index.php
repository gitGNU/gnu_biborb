<?php
/**
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
 * File: index.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
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

require_once("config.php");     // load configuration variables
require_once("functions.php");  // load needed functions
require_once("biborbdb.php");   // load biborb database
require_once("interface.php");  // load function to generate the interface

/**
 * Load the session
 */
session_name($session_id);
session_start();

/**
 *  i18n
 */
if(!array_key_exists('language',$_SESSION) || !$GLOBALS['display_language_selection']){
    $_SESSION['language'] = $GLOBALS['language'];
}
load_i18n_config($_SESSION['language']);

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
if(array_key_exists('mode',$_GET)){
	$mode = $_GET['mode'];
}
else{
	$mode = null;
}

/**
 * Select the user's mode:
 *  admin => may modify, create or delete
 *  user => only for read purpose
 */
if(!$disable_authentication){
  if(!array_key_exists('usermode',$_SESSION)){
      $_SESSION['usermode'] = "user";
  }
}
else{
  $_SESSION['usermode'] = "admin";
}

/*
    Look in $_GET for an action to be performed
 */
if(isset($_GET['action'])){
    switch($_GET['action']){
        case 'select_lang':
            $_SESSION['language'] = $_GET['lang'];
            load_i18n_config($_SESSION['language']);
            break;
            
        /*
            Create a database
         */
        case _("Create"):
            $error_or_message = create_database($_GET['database_name'],
                                                $_GET['description']);
            break;
            
        /*
            Delete a database
         */
        case _("Delete"):
            $error_or_message['message'] = delete_database($_GET['database_name']);
            break;
        
        /*
            Logout
         */
        case "logout":
            $_SESSION['usermode'] = "user";
            break;
            
        default:
            break;
    }
}

/*
    Look in $_POST for an action to be performed
 */
if(isset($_POST['action'])){
    switch($_POST['action']){
        
        /*
            Login
         */
        case _("Login"):
            $login = $_POST['login'];
            $mdp = $_POST['mdp'];
            if($login=="" || $mdp==""){
                $error_or_message['error'] = _("LOGIN_MISSING_VALUES");
                $mode = "login";
            }
            else {
                $loggedin = check_login($login,$mdp);
                if($loggedin){
                    $_SESSION['user'] = $login;
                    $_SESSION['usermode'] = "admin";
                }
                else {
                    $error_or_message['error'] = _("LOGIN_WRONG_USERNAME_PASSWORD");
                    $mode = "login";
                }
            }
            break;
            
        default:
            break;
    }
}



/*********************************************** BEGINING OF THE HTML OUTPUT **/

/**
 * Select what to do according the value of 'mode'
 */
switch($mode){
	// This is the welcome page.
    case 'welcome': echo index_welcome(); break;
    
	// List of available bibliograpies
    case 'select': echo index_select(); break;
    
    // Add a new bibliography
    case 'add_database': echo index_add_database(); break;
    
	// Delete a bibliography
    case 'delete_database': echo index_delete_database(); break;
    
	// Litlle help on what is available for the administrator mode
    case 'manager_help': echo index_manager_help(); break;
    
	// Login form
    case 'login': echo index_login(); break;
    
	// Generic page to display result of operations (add, delete, ...)
    case 'result': echo index_result(); break;
    
	// By default, load the welcome page
    default: echo index_welcome(); break;
}

?>
