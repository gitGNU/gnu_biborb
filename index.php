<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2003  Guillaume Gardey
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
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 *      This is the page that is initially loaded when accessing BibORB.
 *      
 *      It offers the following functions:
 *          * authentication ($disable_authentication set to false)
 *          * add a new bibliography (if $disable_authentication set to true or registered user)
 *          * delete a bibliography (it will delete the entire directory!)
 *          * accessing the list of available bibliographies 
 *
 */

require_once("config.php");     // load configuration variables
require_once("functions.php");  // load needed functions

/**
 * Load the session
 */
session_name($session_id);
session_start();

/**
 * Get a value for 'mode' in the $_GET array.
 * If not in $_GET, receive null
 * The 'mode' variable sets which page to display
 */
$_SESSION['mode'] = get_value('mode',$_GET);

/**
 * Select the user's mode:
 *  admin => may modify, create or delete
 *  user => only for consultation purpose
 */
if(!$disable_authentication){
  if(!array_key_exists('usermode',$_SESSION)){
      $_SESSION['usermode'] = "user";
  }
}
else{
  $_SESSION['usermode'] = "admin";
}

/****************************************************** BEGINING OF THE HTML OUTPUT **/

/**
 * Select what to do according the value of 'mode'
 */
switch($_SESSION['mode']){
    /**
     * This is the welcome page.
     */
    case 'welcome':
        echo index_welcome();
        break;
    
    /**
     * List of available bibliograpies
     */
    case 'select':
        echo index_select();
        break;
    
    /**
     *  Add a new bibliography
     */
    case 'add_database':
        echo index_add_database();
        break;
    
    /**
     * Delete a bibliography
     */
    case 'delete_database':
        echo index_delete_database();
        break;
    
    /**
     * Litlle help on what is available for the administrator mode
     */
    case 'manager_help':
        echo index_manager_help();
        break;
    
    /**
     * Login form
     */
    case 'login':
        echo index_login();
        break;
    
    /**
     * Logout
     */
    case 'logout':
        echo index_logout();
        break;
    
    /**
     * Generic page to display result of operations (add, delete, ...)
     */
    case 'result':
        echo index_result();
        break;
    
    /**
     * By default, load the welcome page
     */
    default:
        echo index_welcome();
        break;
}

/**
 * End of the display.
 * Reset 'error' and 'message'.
 */
$_SESSION['error'] = null;
$_SESSION['message'] = null;

/************************************************************END OF THE HTML OUTPUT **/


/***************************************************** index.php specific functions **/

/**
 * index_login()
 * Create the page to display for authentication
 */
function index_login(){
    // html header
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    // create the menu
    $html .= index_menu();
    // set the title
    $title = "Login";
    // create the main data 
    $html .= main($title,login_form("index.php"));
    // close the html
    $html .= html_close();
    return $html;
}

/**
 * index_logout()
 */
function index_logout()
{
    // change admin mode to user mode
    $_SESSION['usermode'] = "user";
    // redirect to welcome page
    echo header("Location: index.php?mode=welcome&".session_name()."=".session_id());
}

/**
 * index_welcome()
 * Display the welcome page
 * The text is loaded from ./data/index_welcome.txt
 */
function index_welcome(){
    // html header
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    // set the title
    $title = "BibORB: BibTeX On-line References Browser";
    // the main content
    $content = load_file("./data/index_welcome.txt");
    // create the menu;
    $html .= index_menu();
    // create the main data
    $html .= main($title,$content);
    // close html
    $html .= html_close();
    
    return $html;
}

/**
 * index_add_database()
 * Create the page to add a new bibliography.
 * The creation is delegated to action_proxy.php.
 */
function index_add_database(){
    // html header
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    // set title
    $title = "Create a new bibliography";
    // create the form to create a new bibliography
    // creation is performed by action_proxy.php
    $content = "<form method='get' action='action_proxy.php'>";
    $content .= "<table><tbody>";
    $content .= "<tr>";
    $content .= "<td>Database name: </td>";
    $content .= "<td>";
    $content .= "<input type='hidden' name='".session_name()."' value='".session_id()."'/>";
    $content .= "<input type='text' size='40' name='database_name'/></td>";
    $content .= "</tr>";
    $content .= "<tr>";
    $content .= "<td>Description: </td>";
    $content .= "<td><input type='text' size='40' name='description'/></td>";
    $content .= "</tr>";
    $content .= "<tr><td><input type='submit' name='action' value='create'/></td></tr>";
    $content .= "</tbody></table>";
    $content .= "</form>";
    // create the menu
    $html .= index_menu();
    // create the main panel
    $html .= main($title,$content);
    // close html
    $html .= html_close();
    return $html;
}

/**
 * index_delete_database()
 * Display the bibliographies in a combo box to select which one to delete.
 * The deletion is delegated to action_proxy.php
 */
function index_delete_database(){
    // html header
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    // set the title
    $title = "Delete a bibliography";
    
    // get all bibliographies and create a form to select which one to delete
    $databases = get_databases_names();
    $content  = "<form method='get' action='action_proxy.php'>";
    $content .= "<fieldset style='border:none;'>";
    $content .= "<input type='hidden' name='".session_name()."' value='".session_id()."'/>";
    $content .= "<select name='database_name' size='1'>";
    foreach($databases as $name){
        $content .= "<option value='$name'>$name</option>";
    }
    $content .= "</select>";
    $content .= "<input type='submit' name='action' value='remove'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    // create the menu
    $html .= index_menu();
    // create the main panel
    $html .= main($title,$content);
    // close html
    $html .= html_close();
    return $html;
}

/**
 * index_manager_help()
 * Display an help for the manager submenu. This help is loaded from a file.
 */
function index_manager_help(){
    // html header
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    // set title
    $title = "Manager Help";
    // load the content of the help
    $content = load_file("./data/index_manager_help.txt");
    // create the menu
    $html .= index_menu();
    // create the main panel
    $html .= main($title,$content);
    // close html
    $html .= html_close();
    
    return $html;
}

/**
 * index_result()
 * Generic page to display the result of an operation.
 * Will only display information recorded into $_SESSION['error'] and $_SESSION['message']
 */
function index_result(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $html .= index_menu();
    $html .= main(null,null);
    $html .= html_close();
    
    return $html;
}

/**
 * index_select()
 * Page to consult available bibliographies. They are placed into a table.
 * CSS => The ID 'available_bibliographies' is used for the table
 */
function index_select(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $title = "Available bibliographies";
    $html .= index_menu();

    // get all bibliographies and create an array
    $databases = get_databases_names();
    $content = "<div style='text-align:center;'>";
    $content .= "<table id='available_bibliographies'>";
    $content .= "<thead><tr><th>Name</th><th>Description</th><th>Sources(BibTeX)</th></tr></thead>";
    $content .= "<tbody>";
    foreach($databases as $name){
        $description = load_file("./bibs/$name/description.txt");
        $content .= "<tr>";
        $content .= "<td><a href='./bibindex.php?mode=welcome&amp;bibname=$name&amp;".session_name()."=".session_id()."'>$name</a></td>";
        $content .= "<td>$description</td>";
        $content .= "<td><a href='./bibs/$name/$name.bib'>Download</td>";
        $content .= "</tr>";
    }
    $content .= "</tbody></table>";
    $content .= "</div>";
    
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * index_menu()
 * Create the menu for each page generated. It is placed into a <div> tag of ID 'menu'.
 */
function index_menu(){
    
    // start of the div tag
    $html = "<div id='menu'>";
    // title to display => use ID 'title'
    $html .= "<span id='title'>BibORB</span>";
    // no bibliography currently displayed
    $html .= "<span id='bibname'></span>";
    
    // First menu item:
    // -> Welcome
    //      | -> Available bibliographies
    $html .= "<ul>";
    $html .= "<li><a href='index.php?mode=welcome'>Welcome</a>";
    $html .= "<ul>";
    $html .= "<li><a href='index.php?mode=select'>Available bibliographies</a></li>";
    $html .= "</ul></li>";
    
    // Second menu item:
    // -> Manager
    //      | -> Login              (if not administrator)
    //      | -> Add a bibliography (if administrator)
    //      | -> Delete a bibliography (if administrator)
    //      | -> Logout     (if administrator and $disable_authentication set to false)
    $html .= "<li><a href='index.php?mode=manager_help'>Manager</a>";
    $html .= "<ul>";
    if($_SESSION['usermode']=='user'){
        $html .= "<li><a href='index.php?mode=login'>Login</a></li>";
    }
    if($_SESSION['usermode']=='admin'){
        $html .= "<li><a class='admin' href='index.php?mode=add_database'>Add a bibliography</a></li>";
        $html .= "<li><a class='admin' href='index.php?mode=delete_database'>Delete a bibliography</a></li>";
    }
    if($_SESSION['usermode']=='admin' && !$GLOBALS['disable_authentication']){
        $html .= "<li><a href='index.php?mode=logout'>Logout</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";
    $html .= "</div>";
    
    return $html;  
}

?>