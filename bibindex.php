<?php
/**
 *
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
 * 
 * File: bibindex.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
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

/**
 * Session
 */
session_name($session_id);
session_start();

/**
 * Display an error if the variable 'bibname' is not set
 */
if(!array_key_exists('bibname',$_SESSION) && !array_key_exists('bibname',$_GET)){
    die("Error: bibname is not set");
}

/**
 * If the session variable 'bibname' is not set, get it from GET variables
 * Compute the list of groups present in the bibliography and record it into session
 */
if(array_key_exists('bibname',$_GET)){
    $_SESSION['bibname'] = $_GET['bibname'];
    if(array_key_exists('bibname',$_SESSION)){
        $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
    } 
}

/**
 * Register variables in session.
 * It is not really needed for most of them but easier to reference them in the code.
 * Use of 'get_value' to avoid PHP warnings when keys are not present in $_GET or $_POST
 */
$_SESSION['mode'] = get_value('mode',$_GET);            // which page to display
$_SESSION['group'] = get_value('group',$_GET);          // which group to display
$_SESSION['search'] = get_value('search',$_GET);        // which value to search for
$_SESSION['author'] = get_value('author',$_GET);        // search in author?
$_SESSION['keywords'] = get_value('keywords',$_GET);    // search in keywords?
$_SESSION['title'] = get_value('title',$_GET);          // search in title?
$_SESSION['type'] = get_value('type',$_GET);            // which type of entry to add
$_SESSION['id'] = get_value('id',$_GET);                // whih entry to display?
$_SESSION['menu'] = get_value('menu',$_GET);            // display the menu?

/**
 * Display entries with abstract?
 */
$_SESSION['abstract'] = get_value('abstract',$_GET);
if($_SESSION['abstract']==null){
    $_SESSION['abstract'] = $display_abstract;
}

/**
 *  If does not exists, create the basket
 */
if(!array_key_exists('basket',$_SESSION)){
    $_SESSION['basket'] = array();
}

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
 * Select what to do according to the mode given in parameter.
 */
switch($_SESSION["mode"])
{
    /**
     * Welcome page
     */
    case 'welcome':
        echo bibindex_welcome();   
        break;
    
    /**
     * Generice page to display operations results
     */
    case 'operationresult':
        echo bibindex_operation_result();
        break;
        
    /**
     * Help on the display menu item
     */
    case 'display':
        echo bibindex_display_help();
        break;
    /**
     * Display all entries
     */
    case 'displayall': 
        echo bibindex_display_all();
        break;
    
    /**
     * Display by group
     */
    case 'displaybygroup': 
        echo bibindex_display_by_group();
        break;
    
    /**
     * Display search page
     */
    case 'displaysearch': 
        echo bibindex_display_search();
        break;
        
    /**
     * Help on the basket menu item
     */
    case 'basket': 
        echo bibindex_basket_help();
        break;
    
    /**
     * Display the basket
     */
    case 'displaybasket': 
        echo bibindex_display_basket();
        break;
    
    /**
     * Display the page to modify groups of entries in the basket
     */
    case 'groupmodif': 
        echo bibindex_basket_modify_group();
        break;
        
    /**
     * Help on the Manager Menu
     */
    case 'manager': 
        echo bibindex_manager_help();
        break;
    
    /**
     * Add a new entry 
     */
    case 'addentry': 
        echo bibindex_entry_to_add();
        break;
    
    /** 
     * Select the type of the new entry to add
     */
    case 'select': 
        echo bibindex_add_entry();
        break;
    
    /**
     * Update an entry
     */
    case 'update':
        echo bibindex_update_entry();
        break;

    /**
     * Login page
     */
    case 'login':
        echo bibindex_login();
        break;
        
    /**
     * Logout 
     */
    case 'logout':
        echo bibindex_logout();
        break;
    
    /**
     * Update the XML file according to values present in the BibTeX file.
     */
    case 'update_xml_from_bibtex':
        update_xml($_SESSION['bibname']);
        echo bibindex_welcome();
        break;
    
    /**
     * Update the BibTeX file according to valued present in the BibTeX file.
     */
    case 'update_bibtex_from_xml':
        xml2bibtex($_SESSION['bibname']);
        echo bibindex_welcome();
        break;
        
    /**
     * Mode to access directly to an article
     */
    case 'details':
        echo bibindex_details();
        break;
    
    /**
     * Import references
     */
    case 'import':
        echo bibindex_import();
        break;
        
    case 'exportbaskettobibtex':
        echo bibindex_export_basket_to_bibtex();
        break;
        
    case 'exportbaskettohtml':
        echo bibindex_export_basket_to_html();
        break;
    /**
     * By default
     */
    default:
        echo bibindex_welcome();
        break;
}

/**
 * unset session variables for the next page
 */
unset($_SESSION['error']);
unset($_SESSION['message']);

/************************************************************END OF THE HTML OUTPUT **/


/***************************************************** index.php specific functions **/

/**
 * bibindex_details()
 * Called when a given entry has to be displayed
 */
function bibindex_details()
{
    $html = bibheader();
    
    // get the selected entry
    $content = get_bibentry($_SESSION['bibname'],$_SESSION['id'],$_SESSION['abstract']);
    // display the menu or not
    if($_SESSION['menu'] != null){
        if($_SESSION['menu']){
            $html .= bibindex_menu();
            $html .= main(null,$content);
        }
        else{
            $html .= $content;
        }
    }
    else{
        $html .= $content;
    }
    $html .= html_close();
  
    return $html;  
}

/**
 * bibindex_login()
 * Display the login page
 */
function bibindex_login(){
    $html = bibheader();
    $html .= bibindex_menu();
    $title = "<H2>BibORB Manager</H2>";
    $html .= main($title,login_form("bibindex.php"));
    $html .= html_close();
    return $html;
}

/**
 * bibindex_logout()
 * Change admin mode to user and redirect to welcome page
 */
function bibindex_logout()
{
    $_SESSION['usermode'] = "user";
    echo header("Location: bibindex.php?mode=welcome&amp;".session_name()."=".session_id());
}

/**
 * bibindex_menu()
 * Create the menu
 */
function bibindex_menu()
{
    $html = "<div id='menu'>";
    // title
    $html .= "<span id='title'>BibORB</span>";
    // name of the current bibliography
    $html .= "<span id='bibname'>".$_SESSION['bibname']."</span>";
    $html .= "<ul>";
    // first menu item => Select a bibliography
    $html .= "<li><a href='index.php?mode=select'>Select a bibliography</a><ul><li></li></ul></li>";
    // second item
    // -> Display
    //      | -> All
    //      | -> by group
    //      | -> search
    $html .= "<li><a href='".bibindex_href('display')."'>Display</a>";
    $html .= "<ul>";
    $html .= "<li><a href='".bibindex_href('displayall')."'>All</a></li>";
    $html .= "<li><a href='".bibindex_href('displaybygroup')."'>Groups</a></li>";
    $html .= "<li><a href='".bibindex_href('displaysearch')."'>Search</a></li>";
    $html .= "</ul>";
    $html .= "</li>";
    // third menu item
    // -> Basket
    //      | -> Display basket
    //      | -> Modify groups (if admin)
    //      | -> Export to bibtex
    //      | -> Export to XML
    //      | -> Reset basket
    $html .= "<li><a href='".bibindex_href('basket')."'>Basket</a>";
    $html .= "<ul>";
    $html .= "<li><a href='".bibindex_href('displaybasket')."'>Display Basket</a></li>";
    if($_SESSION['usermode']=='admin' || $GLOBALS['disable_authentication']){
        $html .= "<li><a class='admin' href='".bibindex_href('groupmodif').".'>Group Modification</a></li>";
    }
    //$html .= "<li><a href='action_proxy.php?action=exportbaskettobibtex'>Export to BibTeX</a></li>";
    $html .= "<li><a href='".bibindex_href('exportbaskettobibtex')."'>Export to BibTeX</a></li>";
    $html .= "<li><a href='".bibindex_href('exportbaskettohtml')."'>Export to HTML</a></li>";
    $html .= "<li><a href='action_proxy.php?action=resetbasket'>Reset basket</a></li>";
    $html .= "</ul>";
    $html .= "</li>";
    
    // fourth menu item
    // -> Manager
    //      | -> Login (if not admin and authentication enabled
    //      | -> Add an entry (if admin)
    //      | -> Update from BibTeX (if admin)
    //      | -> Update from XML (if admin)
    //      | -> Import a bibtex file (if admin)
    //      | -> Logout (if admin and authentication disabled
    $html .= "<li><a href='".bibindex_href('manager')."'>Manager</a>";
    $html .= "<ul>";
    if($_SESSION['usermode']=='user' && !$GLOBALS['disable_authentication']){
        $html .= "<li><a href='".bibindex_href('login')."'>Login</a></li>";
    }
    if($_SESSION['usermode']=='admin'){
        $html .= "<li><a class='admin' href='".bibindex_href('addentry')."'>Add an entry</a></li>";
        $html .= "<li><a class='admin' href='".bibindex_href('update_xml_from_bibtex')."'>Update from BibTeX</a></li>";
        $html .= "<li><a class='admin' href='".bibindex_href('update_bibtex_from_xml')."'>Update from XML</a></li>";
        $html .= "<li><a class='admin' href='".bibindex_href('import')."'>Import BibTeX</a></li>";
    }
    if($_SESSION['usermode']=='admin' && !$GLOBALS['disable_authentication']){
        $html .= "<li><a href='".bibindex_href('logout')."'>Logout</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";
    $html .= "</div>";
   
  return $html;  
}

/**
 * bibheader()
 * Create the HTML header
 */
function bibheader($inbody = NULL)
{
  $html = html_header("BibORB - ".$_SESSION['bibname'],$GLOBALS['CSS_FILE'],NULL,$inbody);
  return $html;  
}

/**
 * search_menu
 * Create Search menu
 */
function search_menu()
{
    $html = "<form action='bibindex.php' method='get'>";
    $html .= "<fieldset style='border:none'>";
    $html .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />";
    $html .= "<input type='hidden' name='mode' value='displaysearch' />";  
    $html .= "<input name='search' size='40' value='".$_SESSION['search']."' />";
    $html .= "<input type='submit' value='Search' /><br/>";
    $html .= "<input type='checkbox' name='author' value='author' ";
    if($_SESSION['author'] != null){
        $html .= "checked='checked'";
    }
    $html .= " />Author";
    $html .= "<input type='checkbox' name='title' value='title' ";
    if($_SESSION['title'] != null){
        $html .= "checked='checked'";
    }
    $html .= "/>Title";
    $html .= "<input type='checkbox' name='keywords' value='keywords' ";
    if($_SESSION['keywords'] != null){
        $html .= "checked='checked'";
    }
    $html .= " />Keywords";
    $html .= "</fieldset>";
    $html .= "</form>";
  
  return $html;  
}

/**
 * select_entry_type()
 * Create the form to select which type of entry to add.
 */
function select_entry_type(){
    $html = "<form method='get' action='bibindex.php'>
        <fieldset style='border:none'>
    <input name='".session_name()."' value='".session_id()."' type='hidden'/>
Select an entry type:
    <select name='type' size='1'>
        <option value='article'>article</option>
        <option value='book'>book</option>
        <option value='booklet'>booklet</option>
        <option value='conference'>conference</option>
        <option value='inbook'>inbook</option>
        <option value='incollection'>incollection</option>
        <option value='inproceedings'>inproceedings</option>
        <option value='manual'>manual</option>
        <option value='mastersthesis'>mastersthesis</option>
        <option value='misc'>misc</option>
        <option value='phdthesis'>phdthesis</option>
        <option value='proceedings'>proceedings</option>
        <option value='techreport'>techreport</option>
        <option value='unpublished'>unpublished</option>
    </select>
    <br/>
    <div style='text-align:center'>
        <input type='submit' name='mode' value='cancel'/>
        <input type='submit' name='mode' value='select'/>
    </div>
    </fieldset>
</form>";
    return $html;
}

/**
 * get_entry_fields
 * Return the input fields corresponding to a given type for edition
 */
function get_entry_fields($type)
{
  $xml_content = load_file("./xsl/model.xml");
  $xsl_content = load_file("./xsl/model.xsl");
  $param = array("typeentry"=>$type);
  return xslt_transform($xml_content,$xsl_content,$param);
}


/**
 * bibindex_href
 * return an url to bibindex.php with a given mode and passing needed values by GET method
 */
function bibindex_href($mode){
    return "./bibindex.php?mode=".$mode."&amp;bibname=".$_SESSION['bibname']."&amp;".session_name()."=".session_id();
}


/**
 * This is the default Welcome page.
 */
function bibindex_welcome()
{
    $html = bibheader();  
    $html .= bibindex_menu();
    $title = "BibORB: BibTeX On-line References Browser";
    $content = "This is the bibliography: <b>".$_SESSION['bibname']."</b>.<br/>";
    if($_SESSION['usermode'] == 'admin' && !$GLOBALS['disable_authentication']) {
        if(array_key_exists('user',$_SESSION)){      
            $content .= "You are logged as <em>".$_SESSION['user']."</em>.";
        }
    }
    $content .= get_stat($_SESSION['bibname']);
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}


/**
 * bibindex_operation_result()
 * Only display $_SESSION['error'] and $_SESSION['message']
 */
function bibindex_operation_result(){
    $html = bibheader();  
    $html .= bibindex_menu();
    $title = null;
    $html .= main($title,null);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_help()
 * Display a small help on items present in the 'display' menu
 */

function bibindex_display_help(){
    $html = bibheader();  
    $html .= bibindex_menu();
    $title = "Display menu";
    $content = load_file("./data/display_help.txt");
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_all()
 * Display all entries.
 */
function bibindex_display_all(){
    $title = "List of all entries";
    $html = bibheader();
    $html .= bibindex_menu();
    $html .= main($title,get_all_bibentries($_SESSION['bibname'],$_SESSION['usermode'],$_SESSION['abstract']));
    $html .= html_close();
    return $html;  
}

/**
 * bibindex_display_by_group()
 * Display entries by group
 */
function bibindex_display_by_group(){
    $title = "Display entries by group";
    $html = bibheader();
    $html .= bibindex_menu();
    // create a form with all groups present in the bibliography
    $main_content = "<form method='get' action='bibindex.php'>";
    $main_content .="<fieldset style='border:none'>";
    $main_content .= "<input type='hidden' name='bibname' value='".$_SESSION['bibname']."'/>";
    $main_content .= "<input type='hidden' name='".session_name()."' value='".session_id()."'/>";
    $main_content .= "<input type='hidden' name='mode' value='displaybygroup'/>";
    $main_content .= "<h3 style='display:inline;'>Available groups:</h3>";
    $main_content .= "<select name='group' size='1'>";
    // set Select values to groups available
    foreach($_SESSION['group_list'] as $gr){
        $main_content .= "<option value='".$gr."' ";
        if($gr == $_SESSION['group']){
            $main_content .= "selected='selected'";
        }
        $main_content .= ">".$gr."</option>";
    }
    $main_content .= "</select>";
    $main_content .= "<input type='submit' value='Display'/>";
//    $main_content .= "<input type='submit' value=''/>";
    $main_content .= "</fieldset>";
    $main_content .="</form><br/>";
    
    // if the group is defined, display the entries matching it
    if($_SESSION['group']){
        $main_content .= get_bibentries_of_group($_SESSION['bibname'],$_SESSION['group'],$_SESSION['usermode'],$_SESSION['abstract']);
    }
    $html .= main($title,$main_content);
    $html .= html_close();
    
    return $html;
}

/**
 * bibindex_display_search
 * display the search interface
 */
function bibindex_display_search(){

    $title = "Search";
    $html = bibheader();
    $html .= bibindex_menu();
    $main_content = search_menu();
    if($_SESSION['search'] != null){
        $main_content .= search_bibentries($_SESSION['bibname'],$_SESSION['search'],
                                     $_SESSION['author'],$_SESSION['title'],
    	                             $_SESSION['keywords'],$_SESSION['usermode'],
				                     $_SESSION['abstract']);
    }
    $html .= main($title,$main_content);
    $html .= html_close();

    return $html;
}

/**
 * bibindex_basket_help()
 * Display a small help on items present in the 'basket' menu
 */

function bibindex_basket_help(){
    $html = bibheader();  
    $html .= bibindex_menu();
    $title = "Basket menu";
    $content = load_file("./data/basket_help.txt");
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_basket()
 * display entries present in the basket
 */
function bibindex_display_basket(){
    $title = "Entries in the basket";
    $html = bibheader();
    $html .= bibindex_menu();
    $content = null;
    $content = basket_to_html($_SESSION['usermode'],$_SESSION['abstract']);
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_basket_modify_group
 * Display the page to modify groups of entries in the basket
 */
function bibindex_basket_modify_group(){
    $title = null;
    $html = bibheader();
    $html .= bibindex_menu();
    
    $main_content = load_file("./data/basket_group_modify.txt");

    $html .= main($title,$main_content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_manager_help()
 * Display a small help on items present in the 'manager' menu
 */
function bibindex_manager_help(){
    $html = bibheader();  
    $html .= bibindex_menu();
    $title = "Manager menu";
    $content = load_file("./data/manager_help.txt");
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}


/**
 * bibindex_entry_to_add
 * display the page to select which type of entry to add
 */
function bibindex_entry_to_add(){
    $html = bibheader();
    $html .= bibindex_menu();
    $title = null;
    $content = select_entry_type();
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_add_entry
 * Display a form to edit the value of each BibTeX fields
 */
function bibindex_add_entry(){
    $html = bibheader("onLoad=\"javascript:toggle_element('additional')\"");
    $html .= bibindex_menu();
    $title = null;
    $content = "<form method='post' action='action_proxy.php' enctype='multipart/form-data'>";
    $content .= "<fieldset style='border:none'>";
    $content .= "<input name='".session_name()."' value='".session_id()."' type='hidden'/>";
    $content .= "<input name='type' value='".$_SESSION['type']."' type='hidden'/>";
    $content .= get_entry_fields($_SESSION['type']);
    $content .= "<p/><div style='text-align:center;'><input type='submit' name='action' value='cancel'/>";
    $content .= "<input type='submit' name='action' value='add'/>";
    $content .= "</div></fieldset></form>";
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_update_entry
 * Display a form to modify fields of an entry
 */
function bibindex_update_entry(){
    $html = bibheader("onLoad=\"javascript:toggle_element('additional')\"");
    $html .= bibindex_menu();
    $title = null;
    $content = "<form method='post' action='action_proxy.php' enctype='multipart/form-data'>";
    $content .= "<fieldset style='border:none'>";
    $content .= get_bibentry_for_edition($_SESSION['bibname'],$_SESSION['id'],0);
    $content .= "<div style='text-align:center'>";
    $content .= "<input type='submit' name='action' value='cancel'/>";
    $content .= "<input type='submit' name='action' value='update' />";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * bibindex_import
 * Interface to import references (bibtex file or textfields)
 */
function bibindex_import(){
    $html = bibheader();
    $html .= bibindex_menu();
    $title = "Import References";
    $content = "Select a BibTeX file or edit entries in the text area. Entries will be added to the current bibliography.";
    $content .= "<h3>File</h3>";
    $content .= "<form method='post' action='action_proxy.php' enctype='multipart/form-data'><fieldset title='file'>";
    $content .= "<input type='file' name='bibfile'/>";
    $content .= "<br/>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input type='submit' name='action' value='import'/>";
    $content .= "</div>";
    $content .= "</fieldset></form>";
    $content .="<h3>BibTeX</h3>";
    $content .= "<form method='get' action='action_proxy.php'><fieldset title='BibTeX'>";
    $content .= "<textarea name='bibval' cols='60' rows='15'></textarea>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input type='submit' name='action' value='import'/>";
    $content .= "</div>";
    $content .= "</fieldset></form>";
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * bibindex_export_basket_to_bibtex
 */
function bibindex_export_basket_to_bibtex(){
    if(count($_SESSION['basket']) != 0){
        echo header("Location: action_proxy.php?action=exportbaskettobibtex");
    }
    else{
        $_SESSION['message'] = "<h2>Basket empty!</h2>";
        echo header("Location: bibindex.php?mode=operationresult&bibname=".$_SESSION['bibname']."&".session_name()."=".session_id());
        exit();
    }
}

/**
* bibindex_export_basket_to_html
 */
function bibindex_export_basket_to_html(){
    if(count($_SESSION['basket']) != 0){
        echo header("Location: action_proxy.php?action=exportbaskettohtml");
    }
    else{
        $_SESSION['message'] = "<h2>Basket empty!</h2>";
        echo header("Location: bibindex.php?mode=operationresult&bibname=".$_SESSION['bibname']."&".session_name()."=".session_id());
        exit();
    }
}
?>
