<?php
/**
 *
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
require_once("functions.php"); // functions
require_once("basket.php"); // basket functions
require_once("biborbdb.php"); // database
require_once("xslt_processor.php"); // xslt processing
require_once("interface.php"); // generate interface

/**
 * Session
 */
session_name($session_id);
session_start();

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
 * If the session variable 'bibdb' is not set, get the bibliography name from 
 * GET variables and create a new Biborb_Database.
 */
if(array_key_exists('bibname',$_GET)){
	if(!array_key_exists('bibdb',$_SESSION)){
		$_SESSION['bibdb'] = new BibORB_Database($_GET['bibname']);
	}
	else if($_SESSION['bibdb']->name()!=$_GET['bibname']){
		$_SESSION['bibdb'] = new BibORB_Database($_GET['bibname']);
	}
}

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

$xslparam = array('bibname' => $_SESSION['bibdb']->name(),
				  'bibnameurl' => $_SESSION['bibdb']->xml_file(),
				  'display_images' => $GLOBALS['display_images'],
				  'display_text' => $GLOBALS['display_text'],
				  'abstract' => $abst,
				  'display_add_all'=> 'true',
                  'sort' => $sort,
                  'display_sort'=> $DISPLAY_SORT,
                  'mode' => $_SESSION['usermode']);

/**
 *  If the basket doesn't exists, create it.
 */
if(!isset($_SESSION['basket'])){
	$_SESSION['basket'] = new Basket();
}

/**
 * Select the user's mode:
 *  admin => may modify, create or delete
 *  user => only for read purpose
 */
if(!$disable_authentication){
    if(!array_key_exists('usermode',$_SESSION)){
        $_SESSION['usermode'] = "user";
		$xslparam['mode'] = "user";
    }
}
else{
    $_SESSION['usermode'] = "admin";
	$xslparam['mode'] = "admin";
}

/**
 * Action are given by GET/POST method.
 * Analyse the URL to do the corresponding action.
 */
if(isset($_GET['action'])){
	switch($_GET['action']){
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
			
		case 'delete':				// delete an entry from the database
			if(!isset($_GET['id'])){
				die("Error while deleting: no Bibtex ID selected!");
			}
			else{
				// save the bibtex entry to show which entry was deleted
				$bibtex = $_SESSION['bibdb']->entry_with_id($_GET['id']);
				// delete it
				$_SESSION['bibdb']->delete_entry($_GET['id']);
				// update message
				$message = "The following entry was deleted: <pre>".$bibtex."</pre>";
				// if present, remvove entries from the basket
				$_SESSION['basket']->remove_item($_GET['id']);
				$_GET['mode'] = "operationresult";
			}
			break;
			
		case 'Add':					// Add entries in the basket to a given group
			if(!isset($_GET['groupvalue'])){
				die("No group specified!");
			}
			else if(trim($_GET['groupvalue']) != ""){
				$_SESSION['bibdb']->add_to_group($_SESSION['basket']->items,trim($_GET['groupvalue']));
			}
			break;
			
		case 'Reset':				// Reset the groups fields of entries in the basket
			$_SESSION['bibdb']->reset_groups($_SESSION['basket']->items);
			break;
		
        case 'logout':
            $_SESSION['usermode'] = "user";
            break;
    
        case 'cancel':
            $_GET['mode'] = "welcome";
            break;
        
        default:
            break;
    }
}

// analyse POST
if(isset($_POST['action'])){
	switch($_POST['action']){
		// Add an entry to the database
		case 'add': 
			$res = $_SESSION['bibdb']->add_new_entry($_POST);
			if($res['added']){
				$message = "The following entry was added:<br/>";
				$entry = $_SESSION['bibdb']->entry_with_id($res['id']);
				$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
				$param = $GLOBALS['xslparam'];
				$param['bibindex_mode'] = "displaybasket";
				$param['display_add_all'] = "no";
				$param['mode'] = "user";
                $param['display_sort'] = 'no';
				$message .= $xsltp->transform($entry,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
				$xsltp->free();
                
			}
			else{
				$error = $res['message'];
			}
			break;
		
		// update an entry
		case 'update':
			$res = $_SESSION['bibdb']->update_entry($_POST);
			if($res['updated']){
				$message = "The following entry was updated:<br/>";
				$entry = $_SESSION['bibdb']->entry_with_id($res['id']);
				$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
				$param = $GLOBALS['xslparam'];
				$param['bibindex_mode'] = "displaybasket";
				$param['display_add_all'] = "no";
				$param['mode'] = "user";
                $param['display_sort']='no';
				$message .= $xsltp->transform($entry,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
				$xsltp->free();
			}
			else{
				$error = $res['message'];
			}
            break;

        /*
            Import bibtex entries.
         */
        case 'import':
            if(!array_key_exists('bibfile',$_FILES) && !array_key_exists('bibval',$_POST)){
                die("Error, no bibtex data provided!");
            }
            else{
			 
                if(array_key_exists('bibval',$_POST)){
                    $bibtex_data = explode("\n",$_POST['bibval']);
                }
                else{
                    $bibtex_data= file($_FILES['bibfile']['tmp_name']);
                }
                // add the new entry			 
                $res = $_SESSION['bibdb']->add_bibtex_entries($bibtex_data);
                $entries = $_SESSION['bibdb']->entries_with_ids($res);
                $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
                $param = $GLOBALS['xslparam'];
                $param['bibindex_mode'] = "displaybasket";
                $param['mode'] = "admin";
                $formated = $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
                $xsltp->free();
                if($res == 1){
                    $message = "The following entry was added to the database:";
                }
                else {
                    $message = "The following entries were added to the database:";
                }
                //$message .= "<br/><pre>".print_r($bibtex_data)."</pre>";
                $message .= $formated;
            }
			 break;
    
        /*
            Login
         */
        case 'login':
            $login = $_POST['login'];
            $mdp = $_POST['mdp'];
            if($login=="" || $mdp==""){
                $error = "You must fill both login and password!";
            }
            else {
                $loggedin = check_login($login,$mdp);
                if($loggedin){
                    $_SESSION['user'] = $login;
                    $_SESSION['usermode'] = "admin";
                    $login_success = "welcome";	    
                }
                else {
                    $error = "Wrong login or password";
                }
            }
            break;
            
        case 'cancel':
            $_GET['mode'] = "welcome";
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

    
switch($mode)
{
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
    case 'select': echo bibindex_add_entry($_GET['type']); break;
    
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
    case 'exportbaskettobibtex':
		$entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$bibtex = $xsltp->transform($entries,load_file("./xsl/xml2bibtex.xsl"));
		$bibtex = preg_replace(array('/(\s*\\1)?/','/ +/'),array("\\1",' '),$bibtex);
		$xsltp->free();
		
		header("Content-Type: text/plain");
		echo $bibtex; break;
	
	// bibtex of a given entry
    case 'bibtex':
		$entries = $_SESSION['bibdb']->entry_with_id($_GET['id']);
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$bibtex = $xsltp->transform($entries,load_file("./xsl/xml2bibtex.xsl"));
		$bibtex = preg_replace(array('/(\s*\\1)?/','/ +/'),array("\\1",' '),$bibtex);
		$xsltp->free();
		
		header("Content-Type: text/plain");
		echo $bibtex; break;
		
	// Export the basket to html
    case 'exportbaskettohtml': 
		$entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$result = $xsltp->transform($entries,load_file("./xsl/simple_html_output.xsl"),$xslparam);
		$xsltp->free();
		
		echo $result;
		break;

	// By default
    default: echo bibindex_welcome(); break;
}

?>
