<?php
/**
 *
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2004  Guillaume Gardey (ggardey@club-internet.fr)
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
 * File: bibindex.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 * 
 *      The aim of bibindex.php is to allows the consultation of a given bibliography.
 *
 *      If the user has not the adminstrator status (not logged in), he is only 
 *  able to consult the bibliography. Otherwise, he may edit, add or modify entries
 *  in the bibliography.
 *
 *      Some basic operations on the bibliography are supported:
 *          * a basket allows to record a subset of the bibliography, you may
 *              - reset groups to which the entries of the basket belong,
 *              - add a group to each entries of the basket,
 *              - export the selection to BibTeX,
 *              - export the selection to a simple HTML output.
 *          * display all the entries present in the bibliography, ordered by BibTeX key,
 *          * display all entries belonging to a given group,
 *          * basic search engine (one word) over authors, titles, and keywords.
 *
 *      BibORB may be used to create a new bibliography, but also support importation of a 
 *  well-formed BibTeX bibliography (update from BibTeX in the manager menu).
 *
 *      BibORB also support access to a given article in a given bibliography directly:
 *  'bibindex.php?mode=details&abstract=1&menu=0&bibname=example&id=idA', will display the
 *  article of ID idA of the bibliography 'example'. The article will be displayed with its 
 *  abstract if defined and the BibORB menu will not be displayed.
 *  
 *
 *      Concerning the method that is used to manipulate the bibliography, everything is done
 *  using XML/XSLT. Each time a modification is performed, the BibTeX file is updated by converting
 *  the XML file into BibTeX. For XSLT experts, there are some 'curious' XSLT stylesheet. This is 
 *  mainly because I encountered problems using some transformations (xsl:copy and namespace) with 
 *  the PHP XSLT processor, and also because I have not currently the time to investigate more. Any
 *  comments, solutions to deal with this will be welcomed...
 *
 */

/**
 * loads some functions
 */

require_once("config.php"); // globals definitions
require_once("php/functions.php"); // functions
require_once("php/basket.php"); // basket functions
require_once("php/biborbdb.php"); // database
require_once("php/xslt_processor.php"); // xslt processing
require_once("php/interface.php"); // generate interface
require_once("php/auth.php");

/**
 * Session
 */
session_name($session_id);
session_start();

if(get_magic_quotes_gpc()) {
    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
}


/**
 *  i18n
 */
if(!array_key_exists('language',$_GET)){
    if(!array_key_exists('language',$_SESSION) || !$GLOBALS['display_language_selection']){
        $_SESSION['language'] = $GLOBALS['language'];
    }
}
else{
    $_SESSION['language'] = $_GET['language'];
}
load_i18n_config($_SESSION['language']);


/**
 * Global variables to store an error message or a standad message.
 */
$error = null;
$message = null;

/**
 * Display an error if there is no active bibtex database
 */
if(!array_key_exists('bibdb',$_SESSION) && !array_key_exists('bibname',$_GET)){
    die("Error: bibname is not set");
}

/**
 *  If the basket doesn't exists, create it.
 */
if(!isset($_SESSION['basket'])){
    $_SESSION['basket'] = new Basket();
} 

/**
 * If the session variable 'bibdb' is not set, get the bibliography name from 
 * GET variables and create a new Biborb_Database.
 */
$update_auth = FALSE;
if(!array_key_exists('update_authorizations',$_SESSION)){
    $update_auth = TRUE;
}

if(array_key_exists('bibname',$_GET)){
    if(!array_key_exists('bibdb',$_SESSION)){
        $_SESSION['bibdb'] = new BibORB_Database($_GET['bibname']);
        $_SESSION['basket']->reset();
    }
    else if($_SESSION['bibdb']->name()!=$_GET['bibname']){
        $_SESSION['bibdb'] = new BibORB_Database($_GET['bibname']);
        $_SESSION['basket']->reset();
    }
    $update_auth = TRUE;
}

/**
 * Select the user's mode:
 *  admin => may modify, create or delete
 *  user => only for read purpose
 */
if(!$disable_authentication){
    if(!array_key_exists('auth',$_SESSION)){
        $_SESSION['auth'] = new Auth();
    }
    
    if($update_auth){
        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_is_admin'] = FALSE;
        } 
        else{
            $_SESSION['user_is_admin'] = $_SESSION['auth']->is_admin_user($_SESSION['user']);
        }
        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_add'] = FALSE;
        }
        else{
            $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }
        
        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_delete'] = FALSE;
        }
        else{
            $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }
        
        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_modify'] = FALSE;
        }
        else{
            $_SESSION['user_can_modify'] = $_SESSION['auth']->can_modify_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }
    }
}
else{
    $_SESSION['user_can_delete'] = TRUE;
    $_SESSION['user_can_add'] = TRUE;
    $_SESSION['user_can_modify'] = TRUE;
    $_SESSION['user_is_admin'] = TRUE;
}

$_SESSION['update_authorizations'] = FALSE;


/**
* Default paramaters for XSLT transformation
 */
$abst = get_value('abstract',$_GET);
if($abst==null){
    $abst = $GLOBALS['display_abstract'];
} 

$sort = $DEFAULT_SORT;
if(array_key_exists('sort',$_GET)){
    $sort = $_GET['sort'];
}
else if(array_key_exists('sort',$_POST)){
    $sort = $_POST['sort'];
}

$xslparam = array(  'bibname' => $_SESSION['bibdb']->name(),
                    'bibnameurl' => $_SESSION['bibdb']->xml_file(),
                    'display_images' => $GLOBALS['display_images'],
                    'display_text' => $GLOBALS['display_text'],
                    'abstract' => $abst,
                    'display_add_all'=> 'true',
                    'sort' => $sort,
                    'display_sort'=> $DISPLAY_SORT,
                    'can_modify' => $_SESSION['user_can_modify'] || $_SESSION['user_is_admin'],
                    'can_delete' => $_SESSION['user_can_delete'] || $_SESSION['user_is_admin']);

/**
 * Action are given by GET/POST method.
 * Analyse the URL to do the corresponding action.
 */
if(isset($_GET['action'])){
    switch($_GET['action']){
        case 'select_lang':
            $_SESSION['language'] = $_GET['lang'];
            load_i18n_config($_SESSION['language']);
            break;
            
        case 'add_to_basket':		// Add an item to the basket
            if(!isset($_GET['id'])){
                die("Error in add_to_basket: id not set");
            }
            else{
                $_SESSION['basket']->add_items(explode("*",$_GET['id']));
            }
        break;
	
        case 'delete_from_basket':  // delete an entry from the basket
        if(!isset($_GET['id'])){
            die("Error in delete_from_basket: id not set");
        }
        else{
            $_SESSION['basket']->remove_item($_GET['id']);
        }
        break;
	
        case 'resetbasket':			// reset the basket
            $_SESSION['basket']->reset();
            break;
        
        /**
            Delete an entry from the database
        */
        case 'delete':
            if(!isset($_GET['id'])){
                die("Error while deleting: no Bibtex ID selected!");
            }  
            else{
                $confirm = FALSE;
                if(array_key_exists('confirm_delete',$_GET)){
                    $confirm = (strcmp($_GET['confirm_delete'],_("Yes")) == 0);
                }
            
                $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");		
                // save the bibtex entry to show which entry was deleted
                $xml_content = $_SESSION['bibdb']->entry_with_id($_GET['id']);
                $bibtex = $xsltp->transform($xml_content,load_file("./xsl/xml2bibtex.xsl"));
                if(!$GLOBALS['warn_before_deleting'] || $confirm){		    
                    // delete it
                    $_SESSION['bibdb']->delete_entry($_GET['id']);
                    // update message
                    $message = sprintf(_("The following entry was deleted: <pre>%s</pre>"),$bibtex);
                    // if present, remvove entries from the basket
                    $_SESSION['basket']->remove_item($_GET['id']);
                    $_GET['mode'] = "operationresult";
            }
            else if(array_key_exists('confirm_delete',$_GET) && strcmp($_GET['confirm_delete'],_("No")) == 0){
                $_GET['mode'] = "welcome";
            }
            else{
                $theid = $_GET['id'];
                $message = sprintf(_("Delete this entry? <pre>%s</pre>"),$bibtex);
                $message .= "<form action='bibindex.php' method='get' style='margin:auto;'>";
                $message .= "<fieldset style='border:none;text-align:center'>";
                $message .= "<input type='hidden' name='action' value='delete'/>";
                $message .= "<input type='hidden' name='id' value='$theid'/>";
                $message .= "<input type='submit' name='confirm_delete' value='"._("No")."'/>";
                $message .= "&nbsp;";
                $message .= "<input type='submit' name='confirm_delete' value='"._("Yes")."'/>";
                $message .= "</fieldset>";
                $message .= "</form>";

                $_GET['mode'] = "operationresult";
            }
            $xsltp->free();		
        }
        break;
	
        case _("Add"):					// Add entries in the basket to a given group
            if(isset($_GET['groupvalue'])){
                $gval = trim($_GET['groupvalue']);
            }
            if(isset($_GET['newgroupvalue'])){
                $gval = trim($_GET['newgroupvalue']);
            }
            if(!isset($gval)){
                die(_("No group specified!"));
            }
            else if($gval != ""){
                $_SESSION['bibdb']->add_to_group($_SESSION['basket']->items,$gval);
            }
            break;
	
        case _("Reset"):				// Reset the groups fields of entries in the basket
            $_SESSION['bibdb']->reset_groups($_SESSION['basket']->items);
            break;
	
        case 'logout':
            unset($_SESSION['user']);
            $_SESSION['user_can_add'] = FALSE;
            $_SESSION['user_can_delete'] = FALSE;
            $_SESSION['user_can_modify'] = FALSE;
            $_SESSION['user_is_admin'] = FALSE;
            break;
	    
        case _("Cancel"):
            $_GET['mode'] = "welcome";
            break;
	
        case _("Update"):
            if($_GET['object'] == 'type'){
            // get the entry
                $_SESSION['bibdb']->change_type($_GET['id'],$_GET['bibtex_type']);
                $_GET['mode']='update';
            }
            else if($_GET['object'] == 'key'){
                if(!$_SESSION['bibdb']->is_bibtex_key_present($_GET['bibtex_key'])){
                    $_SESSION['bibdb']->change_id($_GET['id'],$_GET['bibtex_key']);
                    $_GET['mode']='update';
                    $_GET['id'] = $_GET['bibtex_key'];
                }
                else{
                    $error = sprintf(_("BibTeX key <code>%s</code> already exists."),$_GET['bibtex_key']);
                    $_GET['mode'] = 'operationresult';
                }
            }
            break;

        case 'delete_basket':
            $confirm = FALSE;
            if(array_key_exists('confirm_delete',$_GET)){
                $confirm = (strcmp($_GET['confirm_delete'],_("Yes"))==0);
            }
            $ids_to_remove = $_SESSION['basket']->items;
            $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
            $xml_content = $_SESSION['bibdb']->entries_with_ids($ids_to_remove);
        
            if(!$GLOBALS['warn_before_deleting'] || $confirm){
                $_SESSION['bibdb']->delete_entries($ids_to_remove);
                // update message
                $bibtex = $xsltp->transform($xml_content,load_file("./xsl/xml2bibtex.xsl"));
                $message = sprintf(_("The following entries were deleted: <pre>%s</pre>"),$bibtex);
                $_SESSION['basket']->reset();
                $_GET['mode'] = "operationresult";
            }
            else if(array_key_exists('confirm_delete',$_GET) && strcmp($_GET['confirm_delete'],_("No")) == 0){
                $_GET['mode'] = "welcome";
            }
            else{
                $html_entries = replace_localized_strings($xsltp->transform($xml_content,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$GLOBALS['xslparam']));
                $message = _("Delete the following entries?");
                $message .= $html_entries;
                $message .= "<form action='bibindex.php' method='get' style='margin:auto;'>";
                $message .= "<fieldset style='border:none;'>";
                $message .= "<input type='hidden' name='action' value='delete_basket'/>";
                $message .= "<input type='submit' name='confirm_delete' value='"._("No")."'/>";
                $message .= "<input type='submit' name='confirm_delete' value='"._("Yes")."'/>";
                $message .= "</fieldset>";
                $message .= "</form>";
        		  $_GET['mode'] = "operationresult";
            }
            $xsltp->free();
            break;
	
        default:
            break;
    }
}

// analyse POST
if(isset($_POST['action'])){
    switch($_POST['action']){
        /**
            Add an entry to the database
        */
        case _("Add"): 
            $res = $_SESSION['bibdb']->add_new_entry($_POST);
            if($res['added']){
                $message = _("ENTRY_ADDED_SUCCESS")."<br/>";
                $entry = $_SESSION['bibdb']->entry_with_id($res['id']);
                $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
                $param = $GLOBALS['xslparam'];
                $param['bibindex_mode'] = "displaybasket";
                $param['mode'] = "user";
                $message .= replace_localized_strings($xsltp->transform($entry,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
                $xsltp->free();
            }
            else{
                $error = $res['message'];
            }
            break;
    
        // update an entry
        case _("Update"):
            $res = $_SESSION['bibdb']->update_entry($_POST);
            if($res['updated']){
                $message = _("The following entry was updated:")."<br/>";
                $entry = $_SESSION['bibdb']->entry_with_id($res['id']);
                $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
                $param = $GLOBALS['xslparam'];
                $param['bibindex_mode'] = "displaybasket";
                $param['mode'] = "user";
                $message .= replace_localized_strings($xsltp->transform($entry,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
                $xsltp->free();
            }
            else{
                $error = $res['message'];
            }
            break;
	
        /*
            Import bibtex entries.
        */
        case _("Import"):
            if(!array_key_exists('bibfile',$_FILES) && !array_key_exists('bibval',$_POST)){
                die("Error, no bibtex data provided!");
            }
            else{
	    
                if(array_key_exists('bibval',$_POST)){
                    $bibtex_data = explode("\n",$_POST['bibval']);
                    
                }
                else{
                    $bibtex_data= addslashes_deep(file($_FILES['bibfile']['tmp_name']));
                }
                // add the new entry	
                $res = $_SESSION['bibdb']->add_bibtex_entries($bibtex_data);

                $entries = $_SESSION['bibdb']->entries_with_ids($res);
                $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
                $param = $GLOBALS['xslparam'];
                $param['bibindex_mode'] = "displaybasket";
                $param['mode'] = "admin";
                $formated = replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
                $xsltp->free();
                if($res == 1){
                    $message = _("The following entry was added to the database:");
                }
                else {
                    $message = _("The following entries were added to the database:");
                }
                $message .= $formated;
            }
            break;
	
        /*
            Login
        */
        case _("Login"):
            $login = $_POST['login'];
            $mdp = $_POST['mdp'];
            if($login=="" || $mdp==""){
                $error = _("LOGIN_MISSING_VALUES");
            }
            else {
                $loggedin = $_SESSION['auth']->is_valid_user($login,$mdp);
                if($loggedin){
                    $_SESSION['user'] = $login;
                    $login_success = "welcome";
                    $_SESSION['user_is_admin'] = $_SESSION['auth']->is_admin_user($login);
                    $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry($login,$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
                    $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry($login,$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
                    $_SESSION['user_can_modify'] = $_SESSION['auth']->can_modify_entry($login,$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
                }
                else {
                    $error = _("LOGIN_WRONG_USERNAME_PASSWORD");
                }
            }
            break;
	
        case _("Cancel"):
            $_GET['mode'] = "welcome";
            break;
	
        /**
         * Export the basket to bibtex
         */
        case _("Export"):
            if($_SESSION['basket']->count_items() != 0){
                // basket not empty -> processing
                // get entries
                $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
	    
                // xslt transformation
                $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
                $param = $GLOBALS['xslparam'];
                // hide basket actions
                $param['display_basket_actions'] = 'no';
                // hide edition/delete
                $param['mode'] = 'user';
                // create a parameter containing fields to export
                $toexport = ".";
                foreach($GLOBALS['bibtex_entries'] as $field){
                    if(array_key_exists($field,$_POST)){
                        $toexport .= $field.".";
                    }
                }
	    
                $param['fields_to_export'] = $toexport;
                //process
                $content = $xsltp->transform($entries,load_file("./xsl/xml2bibtex_advanced.xsl"),$param);
                $xsltp->free();
                
                // bibtex output
                header("Content-Type: text/plain");
                echo $content;
                exit();
            }
            else{
                $_GET['mode'] = 'displaybasket';
            }
            break;
    
        default:
            break;
    }
}


/**
 * Select what to do according to the mode given in parameter.
 */
if(isset($login_success)){
    $mode = "welcome";
}
else if(array_key_exists('mode',$_GET)){
    $mode = $_GET['mode'];
}
else if(array_key_exists('mode',$_POST)){
    $mode = $_POST['mode'];
}
else {
    $mode = "welcome";
}

switch($mode) {
    // Welcome page
    case 'welcome': echo bibindex_welcome(); break;
     
    // Generice page to display operations results
    case 'operationresult': echo bibindex_operation_result(); break;
    
    // Help on the display menu item
    case 'display': echo bibindex_display_help(); break;
    
    // Display all entries
    case 'displayall': echo bibindex_display_all(); break;
    
    // Display by group
    case 'displaybygroup': echo bibindex_display_by_group(); break;
    
    // Display search page
    case 'displaysearch': echo bibindex_display_search(); break;
    
    case 'displayadvancedsearch': echo bibindex_display_advanced_search(); break;
    
    // Help on the basket menu item
    case 'basket': echo bibindex_basket_help(); break;
    
    // Display the basket
    case 'displaybasket': echo bibindex_display_basket(); break;
    
    // Display the page to modify groups of entries in the basket
    case 'groupmodif': echo bibindex_basket_modify_group(); break;
    
    // Help on the Manager Menu
    case 'manager': echo bibindex_manager_help(); break;
    
    // Add a new entry 
    case 'addentry':echo bibindex_entry_to_add(); break;
    
    // Select the type of the new entry to add
    case _("Select"):
        echo bibindex_add_entry($_GET['type']); break;
    
    // Update an entry
    case 'update': echo bibindex_update_entry(); break;
    
    // Login page
    case 'login': echo bibindex_login(); break;
    
    // Logout 
    case 'logout': echo bibindex_logout(); break;
    
    // Update the XML file according to values present in the BibTeX file.
    case 'update_xml_from_bibtex':
        $_SESSION['bibdb']->reload_from_bibtex();
        echo bibindex_welcome();
        break;
    
    // Mode to access directly to an article
    case 'details': echo bibindex_details(); break;
    
    // Import references
    case 'import': echo bibindex_import(); break;
    
    // Export the basket to bibtex
    case 'exportbaskettobibtex': echo bibindex_export_basket_to_bibtex(); break;
    
    // bibtex of a given entry
    case 'bibtex':
        $entries = $_SESSION['bibdb']->entry_with_id($_GET['id']);
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $bibtex = $xsltp->transform($entries,load_file("./xsl/xml2bibtex.xsl"));
        $bibtex = preg_replace(array('/(\s*\\1)?/','/ +/'),array("\\1",' '),$bibtex);
        $xsltp->free();
        header("Content-Type: text/plain");
        echo $bibtex;
        break;
    
    // Export the basket to html
    case 'exportbaskettohtml': echo bibindex_export_basket_to_html();break;
    
    // By default
    default: echo bibindex_welcome(); break;
}

?>
