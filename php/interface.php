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
 * File: interface.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *      Functions to generate the interface
 * 
 */

/********************************** Interface for the index.php */

/**
 * index_login()
 * Create the page to display for authentication
 */
function index_login(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $html .= index_menu();
    $title = _("INDEX_MENU_LOGIN_TITLE");

    $content = "<form id='login_form' action='index.php' method='post'>";
    $content .= "<fieldset>";
    $content .= "<label for='login'>"._("LOGIN_USERNAME").":</label>";
    $content .= "<input type='text' name='login' id='login' value='login'/><br/>";
    $content .= "<label for='password'>"._("LOGIN_PASSWORD").":</label>";
    $content .= "<input type='password' id='password' name='mdp' value='mdp'/><br/>";
    $content .= "<input type='submit' name='action' value=\""._("Login")."\" class='submit'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $html .= main($title,$content,$GLOBALS['error_or_message']['error']);
    $html .= html_close();
    
    return $html;
}

/**
 * index_welcome()
 * Display the welcome page
 * The text is loaded from ./data/index_welcome.txt
 */
function index_welcome(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $title = "BibORB: BibTeX On-line References Browser";
    $content = load_localized_file("index_welcome.txt");
    // get the version and the date
    $content = str_replace('$biborb_version',$GLOBALS['biborb_version'],$content);
    $content = str_replace('$date_release',$GLOBALS['date_release'],$content);
    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();
    
    return $html;
}

/**
 * index_add_database()
 * Create the page to add a new bibliography.
 */
function index_add_database(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $title = _("INDEX_CREATE_BIB_TITLE");
    // create the form to create a new bibliography
    $content = "<form method='get' action='index.php' id='f_bib_creation' onsubmit='return validate_bib_creation(\"".$_SESSION['language']."\")'>";
    $content .= "<fieldset>";
    $content .= "<input type='hidden' name='mode' value='result'/>";
    $content .= "<label for='database_name'>"._("INDEX_CREATE_BIBNAME").":</label>";
    $content .= "<input type='text' name='database_name' id='database_name'/><br/>";
    $content .= "<label for='description'>"._("INDEX_CREATE_DESCRIPTION").":</label>";
    $content .= "<input type='text' name='description' id='description'/><br/>";
    $content .= "<input class='submit' type='submit' name='action' value='".
        _("Create")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();
    
    return $html;
}

/**
 * index_delete_database()
 * Display the bibliographies in a combo box to select which one to delete.
 */
function index_delete_database(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $title = _("INDEX_DELETE_BIB_TITLE");
    
    // get all bibliographies and create a form to select which one to delete
    $databases = get_databases_names();
    $content = "<form method='get' action='index.php' id='f_delete_database'>";
    $content .= "<fieldset>";
    $content .= "<input type='hidden' name='mode' value='result'/>";
    $content .= "<select name='database_name'>";

    foreach($databases as $name){
        if($name != ".trash"){
            $content .= "<option value='$name'>$name</option>";
        }
    }

    $content .= "</select>";
    $content .= "&nbsp;<input class='submit' type='submit' name='action' value='"._("Delete")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();
    
    return $html;
}

/**
 * index_manager_help()
 * Display an help for the manager submenu. This help is loaded from a file.
 */
function index_manager_help(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $title = _("INDEX_MANAGER_HELP_TITLE");
    $content = load_localized_file("index_manager_help.txt");
    $html .= index_menu();
    $html .= main($title,$content);
    $html .= html_close();
    
    return $html;
}

/**
 * index_result()
 * Generic page to display the result of an operation.
 * Will only display information recorded into $error_or_message
 */
function index_result(){
    $html = html_header("Biborb",$GLOBALS['CSS_FILE']);
    $html .= index_menu();
    $html .= main(_("INDEX_RESULTS_TITLE"),null,
                  $GLOBALS['error_or_message']['error'],
                  $GLOBALS['error_or_message']['message']);
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
    $title = _("INDEX_AVAILABLE_BIBS_TITLE");
    $html .= index_menu();

    // get all bibliographies and create an array
    $databases = get_databases_names();
    $content = "<table id='available_bibliographies'>";
    $content .= "<thead>";
    $content .= "<tr>";
    $content .= "<th>"._("INDEX_AVAILABLE_BIBS_COL_BIBNAME")."</th>";
    $content .= "<th>"._("INDEX_AVAILABLE_BIBS_COL_BIBDESCRIPTION")."</th>";
    $content .= "<th>"._("INDEX_AVAILABLE_BIBS_COL_SOURCES")."</th>";
    $content .= "</tr>";
    $content .= "</thead>";
    $content .= "<tbody>";
    
    foreach($databases as $name){
        // do not parse the trash directory
        if($name != ".trash"){
            $description = load_file("./bibs/$name/description.txt");
            $content .= "<tr>";
            $content .= "<td><a class='bibname' href='./bibindex.php?mode=welcome&amp;bibname=$name'>$name</a></td>";
            $content .= "<td><span class='bib_description'>$description</span></td>";
            $content .= "<td><a class='download' href='./bibs/$name/$name.bib'>"._("Download")."</a></td>";
            $content .= "</tr>";
        }
    }
    $content .= "</tbody></table>";
    
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
    $html .= "<li><a title=\""._("INDEX_MENU_WELCOME_HELP")."\" href='index.php?mode=welcome'>"._("INDEX_MENU_WELCOME")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='"._("INDEX_MENU_BIBS_LIST_HELP")."' href='index.php?mode=select'>"._("INDEX_MENU_BIBS_LIST")."</a></li>";
    $html .= "</ul></li>";
    
    // Second menu item:
    // -> Manager
    //      | -> Login              (if not administrator)
    //      | -> Add a bibliography (if administrator)
    //      | -> Delete a bibliography (if administrator)
    //      | -> Logout     (if administrator and $disable_authentication set to false)
    $html .= "<li><a title='"._("INDEX_MENU_MANAGER_HELP")."' href='index.php?mode=manager_help'>"._("INDEX_MENU_MANAGER")."</a>";
    $html .= "<ul>";
    if(!$GLOBALS['disable_authentication'] && !array_key_exists('user',$_SESSION)){
        $html .= "<li><a title=\""._("INDEX_MENU_LOGIN_HELP")."\" href='index.php?mode=login'>"._("INDEX_MENU_LOGIN")."</a></li>";
    }
    if($_SESSION['user_is_admin']){
        $html .= "<li><a title='"._("INDEX_MENU_ADD_BIB_HELP")."' class='admin' href='index.php?mode=add_database'>"._("INDEX_MENU_ADD_BIB")."</a></li>";
        $html .= "<li><a title='"._("INDEX_MENU_DELETE_BIB_HELP")."' class='admin' href='index.php?mode=delete_database'>"._("INDEX_MENU_DELETE_BIB")."</a></li>";
    }
    if(!$GLOBALS['disable_authentication'] && array_key_exists('user',$_SESSION)){
        $html .= "<li><a title='"._("INDEX_MENU_LOGOUT_HELP")."' href='index.php?mode=welcome&amp;action=logout'>"._("INDEX_MENU_LOGOUT")."</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";
    if($GLOBALS['display_language_selection']){
        $html .= "<form id='language_form' action='index.php' method='get'>";
        $html .= "<fieldset>";
        $html .= "<label for='lang'>"._("Language:")."</label>";
        $html .= xhtml_select("lang",1,get_locales(),$_SESSION['language'],"javascript:change_lang_index(this.value)");
        $html .= "<input type='hidden' name='action' value='select_lang'/>";
        $html .= "<noscript><div><input class='submit' type='submit' value='"._("Select")."'/></div></noscript>";
        $html .= "</fieldset>";
        $html .= "</form>";
    }
    $html .= "</div>";
    
    return $html;  
}




/********************************** BIBINDEX */

/**
 * bibindex_details()
 * Called when a given entry has to be displayed
 'bibindex.php?mode=details&abstract=1&menu=0&bibname=example&id=idA
 */
function bibindex_details()
{
    $html = bibheader();

    // get the bibname
    if(!array_key_exists('bibname',$_GET)){die("No bibliography name provided");}
    $bibdb = new BibORB_DataBase($_GET['bibname']);
    
    // get the parameters
    $param = $GLOBALS['xslparam'];
    $param['bibname'] = $bibdb->name();
    $param['bibnameurl'] = $bibdb->xml_file();
    $param['display_basket_actions'] = "no";
    
    if(array_key_exists('abstract',$_GET)){
        $param['abstract'] = $_GET['abstract'] ? "true" : "false";
    }
    if(array_key_exists('basket',$_GET)){
        $param['display_basket_actions'] = $_GET['basket'] ? "true" : "no";
    }
    
    $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
    $xsl_content = load_file("./xsl/biborb_output_sorted_by_id.xsl");
    
    if(array_key_exists('bibids',$_GET)){
        // get the entries
        $bibids = explode(',',$_GET['bibids']);
		$xml_content = $bibdb->entries_with_ids($bibids);
		$content = $xsltp->transform($xml_content,$xsl_content,$param);
	}
	else if(array_key_exists('id',$_GET)){
		// get the selected entry
        $xml_content = $bibdb->entry_with_id($_GET['id']);
        $content = $xsltp->transform($xml_content,$xsl_content,$param);
	}
	// display the menu or not
    if(array_key_exists('menu',$_GET)){
        if($_GET['menu'] == 1){
            $html .= bibindex_menu($_GET['bibname']);
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
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("INDEX_LOGIN_TITLE");
    $content = "<form id='login_form' action='bibindex.php' method='post'>";
    $content .= "<fieldset>";
    $content .= "<label for='login'>"._("LOGIN_USERNAME").":</label>";
    $content .= "<input type='text' name='login' id='login' value='login'/><br/>";
    $content .= "<label for='password'>"._("LOGIN_PASSWORD").":</label>";
    $content .= "<input type='password' id='password' name='mdp' value='mdp'/><br/>";
    $content .= "<input type='submit' name='action' value=\""._("Login")."\" class='submit'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $html .= main($title,$content,$GLOBALS['error']);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_logout()
 * Change admin mode to user and redirect to welcome page
 */
function bibindex_logout(){
    unset($_SESSION['user']);
    bibindex_welcome();
}

/**
 * bibindex_menu($bibname)
 * Create the menu for the bibliography $bibname.
 */
function bibindex_menu($bibname)
{
    $html = "<div id='menu'>";
    // title
    $html .= "<span id='title'>BibORB</span>";
    // name of the current bibliography
    $html .= "<span id='bibname'>".$bibname."</span>";
    $html .= "<ul>";
    // first menu item => Select a bibliography
    $html .= "<li><a href='index.php?mode=select'>"._("BIBINDEX_MENU_SELECT_BIB")."</a>";
    $html .= "<ul>";
    // jump to a given bibliography
    $avbibs = get_databases_names();
    $html .= "<li>";
    $html .= "<form id='choose_bib' action='bibindex.php'>";
    $html .= "<fieldset>";
    $html .= "<select onchange='javascript:change_db(this.value)'>";
    foreach($avbibs as $bib){
        if($bib == $bibname){
            $html .= "<option selected='selected'>$bib</option>";
        }
        else{
            $html .= "<option>$bib</option>";
        }
    }
    $html .= "</select><br/>";
    $html .= "<noscript><div><input class='submit' type='submit' value='Go'/></div></noscript>";
    $html .= "</fieldset>";
    $html .= "</form>";
    $html .= "</li>";
    $html .= "</ul></li>";
    
    // second item
    // -> Display
    //      | -> All
    //      | -> by group
    //      | -> search
    $html .= "<li><a title='"._("BIBINDEX_MENU_DISPLAY_HELP")."' href='bibindex.php?mode=display'>"._("BIBINDEX_MENU_DISPLAY")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_DISPLAY_ALL_HELP")."' href='bibindex.php?mode=displayall'>"._("BIBINDEX_MENU_DISPLAY_ALL")."</a></li>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_DISPLAY_BY_GROUP_HELP")."'href='bibindex.php?mode=displaybygroup'>"._("BIBINDEX_MENU_DISPLAY_BY_GROUP")."</a></li>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_DISPLAY_SEARCH_HELP")."' href='bibindex.php?mode=displaysearch'>"._("BIBINDEX_MENU_DISPLAY_SEARCH")."</a></li>";
//    $html .= "<li><a title='"._("BIBINDEX_MENU_DISPLAY_ADVANCED_SEARCH_HELP")."' href='bibindex.php?mode=displayadvancedsearch'>"._("BIBINDEX_MENU_DISPLAY_ADVANCED_SEARCH")."</a></li>";
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_TOOLS_HELP")."' href='bibindex.php?mode=displaytools'>"._("BIBINDEX_MENU_TOOLS")."</a><ul><li/></ul></li>";
    // third menu item
    // -> Basket
    //      | -> Display basket
    //      | -> Modify groups (if admin)
    //      | -> Export to bibtex
    //      | -> Export to XML
    //      | -> Reset basket
    $html .= "<li><a title='"._("BIBINDEX_MENU_BASKET_HELP")."' href='bibindex.php?mode=basket'>"._("BIBINDEX_MENU_BASKET")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_BASKET_DISPLAY_HELP")."' href='bibindex.php?mode=displaybasket'>"._("BIBINDEX_MENU_BASKET_DISPLAY")."</a></li>";
    if($_SESSION['user_can_modify'] || $GLOBALS['disable_authentication']){
        $html .= "<li><a title='"._("BIBINDEX_MENU_BASKET_GROUP_HELP")."' class='admin' href='bibindex.php?mode=groupmodif'>"._("BIBINDEX_MENU_BASKET_GROUP")."</a></li>";
    }
    $html .= "<li><a title='"._("BIBINDEX_MENU_BASKET_BIBTEX_HELP")."' href='bibindex.php?mode=exportbaskettobibtex'>"._("BIBINDEX_MENU_BASKET_BIBTEX")."</a></li>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_BASKET_HTML_HELP")."' href='bibindex.php?mode=exportbaskettohtml'>"._("BIBINDEX_MENU_BASKET_HTML")."</a></li>";
    $html .= "<li><a title='"._("BIBINDEX_MENU_BASKET_RESET_HELP")."' href='bibindex.php?mode=".$GLOBALS['mode']."&amp;action=resetbasket";
	if($GLOBALS['mode'] == "displaybygroup" && array_key_exists('group',$_GET)){
		$html  .= "&amp;group=".$_GET['group'];
	}
	if($GLOBALS['mode'] == "displaysearch"){
		if(array_key_exists('search',$_GET)){
			$html .= "&amp;search=".$_GET['search'];
		}
		if(array_key_exists('author',$_GET)){
			$html .= "&amp;author=".$_GET['author'];
		}
		if(array_key_exists('title',$_GET)){
			$html .= "&amp;title=".$_GET['title'];
		}
		if(array_key_exists('keywords',$_GET)){
			$html .= "&amp;search=".$_GET['keywords'];
		}	
	}
	$html .= "'>"._("BIBINDEX_MENU_BASKET_RESET")."</a></li>";
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
    $html .= "<li><a title='"._("BIBINDEX_MENU_ADMIN_HELP")."' href='bibindex.php?mode=manager'>"._("BIBINDEX_MENU_ADMIN")."</a>";
    $html .= "<ul>";
    if(!array_key_exists('user',$_SESSION) && !$GLOBALS['disable_authentication']){
        $html .= "<li><a title=\""._("BIBINDEX_MENU_ADMIN_LOGIN_HELP")."\" href='bibindex.php?mode=login'>"._("BIBINDEX_MENU_ADMIN_LOGIN")."</a></li>";
    }
    if($_SESSION['user_can_add']){
        $html .= "<li><a title='"._("BIBINDEX_MENU_ADMIN_ADD_HELP")."' class='admin' href='bibindex.php?mode=addentry'>"._("BIBINDEX_MENU_ADMIN_ADD")."</a></li>";
    }
    if($_SESSION['user_is_admin']){
        $html .= "<li><a title=\""._("BIBINDEX_MENU_ADMIN_UPDATE_HELP")."\" class='admin' href='bibindex.php?mode=update_xml_from_bibtex'>"._("BIBINDEX_MENU_ADMIN_UPDATE")."</a></li>";
    }
    if($_SESSION['user_can_add']){
        $html .= "<li><a title='"._("BIBINDEX_MENU_ADMIN_IMPORT_HELP")."' class='admin' href='bibindex.php?mode=import'>"._("BIBINDEX_MENU_ADMIN_IMPORT")."</a></li>";
    }
    if(array_key_exists('user',$_SESSION) && !$GLOBALS['disable_authentication']){
        $html .= "<li><a title='"._("BIBINDEX_MENU_ADMIN_LOGOUT_HELP")."' href='bibindex.php?mode=welcome&amp;action=logout'>"._("BIBINDEX_MENU_ADMIN_LOGOUT")."</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";
    
    if($GLOBALS['display_language_selection']){
        $html .= "<form id='language_form' action='bibindex.php' method='get'>";
        $html .= "<fieldset>";
        $html .= "<label for='lang'>"._("Language:")."</label>";
        $html .= xhtml_select("lang",1,get_locales(),$_SESSION['language'],"javascript:change_lang(this.value)");
        $html .= "<input type='hidden' name='action' value='select_lang'/>";
        $html .= "<noscript><div><input class='submit' type='submit' value='"._("Select")."'/></div></noscript>";
        $html .= "</fieldset>";
        $html .= "</form>";
    }
    $html .= "</div>";
   
  return $html;  
}

/**
 * bibheader()
 * Create the HTML header
 */
function bibheader($inbody = NULL)
{
  $html = html_header("BibORB - ".$_SESSION['bibdb']->name(),$GLOBALS['CSS_FILE'],NULL,$inbody);
  return $html;  
}


/**
 * This is the default Welcome page.
 */
function bibindex_welcome()
{
    $html = bibheader();  
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = "BibORB: BibTeX On-line References Browser";
    $content = _("This is the bibliography").": <strong>".$_SESSION['bibdb']->name()."</strong>.<br/>";
    if(array_key_exists('user',$_SESSION) && !$GLOBALS['disable_authentication']){      
        $content .= _("You are logged as").": <em>".$_SESSION['user']."</em>.";
/*
        $content .= "<br/>";
        $content .= "Allowed to add entry: ".($_SESSION['user_can_add'] ? "YES" : "NO");
        $content .= "<br/>";
        $content .= "Allowed to modify entry: ".($_SESSION['user_can_modify'] ? "YES" : "NO");
        $content .= "<br/>";
        $content .= "Allowed to delete entry: ".($_SESSION['user_can_delete'] ? "YES" : "NO");
*/
    }
	$nb = $_SESSION['bibdb']->count_entries();
	$nbpapers = $_SESSION['bibdb']->count_epapers();
	
	$content  .= "<h3>"._("Statistics")."</h3>";
    $content  .= "<table>";
	$content  .= "<tbody>";
	$content  .= "<tr>";
	$content  .= "<td>"._("Number of recorded articles").":</td>";
	$content  .= "<td><strong>$nb</strong></td>";
	$content  .= "</tr>";
	$content  .= "<tr>";
	$content  .= "<td>"._("On-line available publications").":</td>";
	$content  .= "<td><strong>$nbpapers</strong></td>";
	$content  .= "</tr>";
	$content  .= "</tbody>";
    $content  .= "</table>";

    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}


/**
 * bibindex_operation_result()
 * Display error or message
 */
function bibindex_operation_result(){
    $html = bibheader();  
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BibORB message");
    $html .= main($title,null,$GLOBALS['error'],$GLOBALS['message']);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_help()
 * Display a small help on items present in the 'display' menu
 */

function bibindex_display_help(){
    $html = bibheader();  
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_DISPLAY_HELP_TITLE");
    $content = load_localized_file("display_help.txt");
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_all
 * Display all entries in the bibliography
 */
function bibindex_display_all(){
    $title = _("BIBINDEX_DISPLAY_ALL_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    
    // store the ids in session if we come from an other page.
    if(!isset($_GET['page'])){
        $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->all_bibtex_ids(),$GLOBALS['MAX_REFERENCES_BY_PAGE']);
        $_GET['page'] = 0;
    }
    
    // get HTML representation of entries
    $flatids = flatten_array($_SESSION['ids']);
    if(count($flatids)>0){
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $param = $GLOBALS['xslparam'];
        $param['bibindex_mode'] = $_GET['mode'];
        $param['basketids'] = $_SESSION['basket']->items_to_string();
        $param['extra_get_param'] = "page=".$_GET['page'];
        $content = $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
        $xsltp->free();
    
        // create the header
        $start = "<div class='result_header'>";
        if($GLOBALS['DISPLAY_SORT']){
            $start = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],null).$start;
        }
        $start .= add_all_to_basket_div($flatids,$_GET['mode'],"sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page']);
        $start .= "</div>";
    
        // create a nav bar to display entries
        $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"displayall","sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."page=".$_GET['page']);

        $content = $start.replace_localized_strings($content);
    }
    else{
        $content = _("No entries.");
    }
    $html .= main($title,$content);
    $html .= html_close();
    return $html;  
}

/**
 * bibindex_display_by_group()
 * Display entries by group
 */
function bibindex_display_by_group(){
    
	$group = get_value('group',$_GET);
    if(isset($_GET['orphan'])){$group=null;}
    
    $title = _("BIBINDEX_DISPLAY_BY_GROUPS_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    
    // create a form with all groups present in the bibliography
    $main_content = "<form id='display_by_group_form' method='get' action='bibindex.php'>";
    $main_content .="<fieldset>";
    $main_content .= "<input type='hidden' name='bibname' value='".$_SESSION['bibdb']->name()."'/>";
    $main_content .= "<input type='hidden' name='mode' value='displaybygroup'/>";
    $main_content .= "<label for='group'>"._("Available groups").":</label> ";
    $main_content .= xhtml_select('group',1,$_SESSION['bibdb']->groups(),$group);
    $main_content .= "&nbsp;<input class='submit' type='submit' value='"._("Display")."'/>";
    $main_content .= "</fieldset>";
    $main_content .= "</form>";
    $main_content .= "<form id='group_orphan_form' method='get' action='bibindex.php'>";
    $main_content .= "<fieldset>";
    $main_content .= "<input type='hidden' name='bibname' value='".$_SESSION['bibdb']->name()."'/>";
    $main_content .= "<input type='hidden' name='mode' value='displaybygroup'/>";
    $main_content .= "<input type='hidden' name='orphan' value='1'/>";
    $main_content .= "<label>"._("Entries associated with no group:")."</label>";
    $main_content .= "&nbsp;<input type='submit' class='submit' value='"._("Orphans")."'/>";
    $main_content .= "</fieldset>";
    $main_content .= "</form>";
    
    
    // store the ids in session if we come from an other page.
    if(!isset($_GET['page'])){
        $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_group($group),$GLOBALS['MAX_REFERENCES_BY_PAGE']);
        $_GET['page'] = 0;
    }
    $flatids = flatten_array($_SESSION['ids']);
    $nb=count($flatids);
    // if the group is defined, display the entries matching it
    if(($group || isset($_GET['orphan'])) && $nb>0){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $param = $GLOBALS['xslparam'];
        $param['group'] = $group;
        $param['basketids'] = $_SESSION['basket']->items_to_string();
        $param['bibindex_mode'] = "displaybygroup";
        if(isset($_GET['orphan'])){
            $param['extra_get_param'] = "orphan=1&page=".$_GET['page'];
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        }
        else{
            $param['extra_get_param'] = "group=$group&page=".$_GET['page'];
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        }
        
        if($nb == 1){
            if(!isset($_GET['orphan'])){
                $main_content .= sprintf(_("An entry for the group %s."),$group);
            }
            else{
                $main_content .= sprintf(_("1 orphan."));
            }
        }
        else{
            if(!isset($_GET['orphan'])){
                $main_content .= sprintf(_("%d entries for the group %s."),$nb,$group);
            }
            else{
                $main_content .= sprintf(_("%d orphans."),$nb);
            }
        }
            
        if(isset($_GET['orphan'])){
            $extraparam = "orphan=1&amp;sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page'];
        }
        else{
            $extraparam = "group=$group&amp;sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page'];
        }
        
        // create the header
        $start = "<div style='result_header'>";
        if(isset($_GET['orphan'])){
            $extra['orphan'] = 1;
        }
        else{
            $extra['group'] = $group;
        }
                
        if($GLOBALS['DISPLAY_SORT']){
            $start = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],$extra).$start;
        }
        $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extraparam);
        $start .= "</div>";
        
        // create a nav bar to display entries
        $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extraparam);
        
        $main_content .= "<br/><br/>".$start;
        $main_content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
    }
    else{
        if(!isset($_GET['orphan'])){
            $main_content .= sprintf(_("No entry for the group %s."),$group);
        }
        else{
            $main_content .= sprintf(_("No orphan."));
        }
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

    $searchvalue = array_key_exists('search',$_GET) ? remove_accents(trim($_GET['search'])) :"";
    
    $title = _("BIBINDEX_SIMPLE_SEARCH_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    
    $main_content = "<form id='simple_search_form' action='bibindex.php' method='get' style='text-align:center'>";
    $main_content .= "<fieldset>";
    $main_content .= "<input type='hidden' name='mode' value='displaysearch' />";
    $main_content .= "<input name='search' value='".$searchvalue."' />";
    $main_content .= "&nbsp;<input class='submit' type='submit' value='"._("Search")."' /><br/>";
    $main_content .= "<div class='items'>";
    $main_content .= "<div class='item'><input type='checkbox' name='author' value='1'";
    if(array_key_exists('author',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />"._("Author")."</div>";
    $main_content .= "<div class='item'><input type='checkbox' name='title' value='1' ";
    if(array_key_exists('title',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= "/>"._("Title")."</div>";
    $main_content .= "<div class='item'><input type='checkbox' name='keywords' value='1' ";
    if(array_key_exists('keywords',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= "/>"._("Keywords")."</div>";
    $main_content .= "<br/>";
    $main_content .= "<div class='item'><input type='checkbox' name='journal' value='1'";
    if(array_key_exists('journal',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />"._("Journal")."</div>";
    $main_content .= "<div class='item'><input type='checkbox' name='editor' value='1'";
    if(array_key_exists('editor',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />"._("Editor")."</div>";
    $main_content .= "<div class='item'><input type='checkbox' name='year' value='1'";
    if(array_key_exists('year',$_GET)){
        $main_content .= "checked='checked'";
    }
    $main_content .= " />"._("Year")."</div>";
    $main_content .= "</div>";
    
    $main_content .= "<br/><br/>";
    $main_content .= _("Sort by:")."&nbsp;<select name='sort'>";
    
    $main_content .= "<option value='ID' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'ID'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">ID</option>";
    
    $main_content .= "<option value='title' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'title'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">"._("Title")."</option>";
    
    $main_content .= "<option value='year' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'year'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">"._("Year")."</option>";
    $main_content .= "</select><br/>";
    
    $main_content .= "</fieldset>";
    $main_content .= "</form>";
    
    $main_content .= "<a href='bibindex.php?mode=displayadvancedsearch'>"._("Advanced Search")."</a>, <a href='bibindex.php?mode=displayxpathsearch'>"._("XPath Search")."</a>.<br/><br/>";
    
    if($searchvalue){
        $fields =array();
        $extra_param ="search=$searchvalue";
        
        if(array_key_exists('author',$_GET)){
            array_push($fields,'author');
                $extra_param .= "&author=1";
        }
        if(array_key_exists('title',$_GET)){
            array_push($fields,'title');
                $extra_param .= "&title=1";
        }
        if(array_key_exists('keywords',$_GET)){
            array_push($fields,'keywords');
                $extra_param .= "&keywords=1";
        }
        if(array_key_exists('editor',$_GET)){
            array_push($fields,'editor');
                $extra_param .= "&editor=1";
        }
        if(array_key_exists('journal',$_GET)){
            array_push($fields,'journal');
                $extra_param .= "&journal=1";
        }
        if(array_key_exists('year',$_GET)){
            array_push($fields,'year');
                $extra_param .= "&year=1";
        }
        if(array_key_exists('sort',$_GET)){
            array_push($fields,'sort');
            $extra_param .= "&sort=".$_GET['sort'];
        }
        if(array_key_exists('sort_order',$_GET)){
            $extra_param .= "&sort_order=".$_GET['sort_order'];
        }
	
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_search($searchvalue,$fields),$GLOBALS['MAX_REFERENCES_BY_PAGE']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
            $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
            $nb = count($flatids);
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = $_GET['mode'];
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $extra_param .= "&page=".$_GET['page'];
            $param['extra_get_param'] = $extra_param;
            
            // add all
            $start = "<div class='result_header'>"; 
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extra_param);
            $start .= "</div>";
            
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extra_param);
            
            if($nb==1){
                $main_content .= sprintf(_("One match for %s"),$searchvalue).$start;
                $main_content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
            }
            else if($nb>1) {
                $main_content .= sprintf(_("%d matches for %s."),$nb,$searchvalue).$start;
                $main_content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
            }
        }
        else{
            $main_content .= sprintf(_("No match for %s."),$searchvalue);
        }
    }
    $html .= main($title,$main_content);
    $html .= html_close();
    
    return $html;
}

/**
 Display advanced search
*/
function bibindex_display_advanced_search(){
    
    $bibtex_fields = array('author','booktitle','edition','editor','journal','publisher','series','title','year');
    
    $biborb_fields = array('abstract','keywords','groups','longnotes');
    
    $extraparam = "";
    
    // if the result of a search is being displayed, hide the search form
    // display a link to show it again
    $content ="";
    if(array_key_exists('searched',$_GET)){
        $extraparam .= "searched=1&";
        $content .= "<script type='text/javascript'><!--
    document.write(\"<a class='cleanref' href=\\\"javascript:toggle_element(\'search_form\')\\\">";
        $content .= _("Display/ Hide search form")."</a>\");\n--></script>";
        $content .= "<noscript><pre> </pre></noscript>";

    }
    $content .= "<div id='search_form'>";
    $content .= "<form action='bibindex.php' method='get'>";
    $content .= "<fieldset>";
    $content .= "<em>"._("Connector:")." </em>";
    $content .= "<select name='connector'>";

    if(array_key_exists('connector',$_GET)){
        $extraparam .= "connector=".$_GET['connector']."&";
        if(!strcmp($_GET['connector'],'and')){
            $content .= "<option value='and' selected='selected'>"._("and")."</option>";
            $content .= "<option value='or'>"._("or")."</option>";
        }
        else{
            $content .= "<option value='and'>"._("and")."</option>";
            $content .= "<option value='or' selected='selected'>"._("or")."</option>";
        }
    }
    else{
        $extraparam = "connector=and&";
        $content .= "<option value='and' selected='selected'>"._("and")."</option>";
        $content .= "<option value='or'>"._("or")."</option>";
    }
    $content .= "</select><br/>";
    $content .= "<em>"._("Sort by:")." </em>";
    $content .= "<select name='sort' >";
    $content .= "<option value='year' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
	$extraparam .= "sort=".$_GET['sort']."&";
    }
    else{
	$extraparam .= "sort=year&";
    }
    
    if($sort == 'year'){
        $content .="selected='selected'";
    }
    $content .= ">"._("Year")."</option>";

    $content .= "<option value='ID' ";
    if($sort == 'ID'){
        $content .="selected='selected'";
    }
    $content .= ">ID</option>";
    
    $content .= "<option value='title' ";
    if($sort == 'title'){
        $content .="selected='selected'";
    }
    $content .= ">"._("Title")."</option>";
    $content .= "</select><br/>";    
	$content .= "<strong>"._("BibTeX Fields")."</strong><br/>";
    
    foreach($bibtex_fields as $field){
        $content .= "<label>$field</label>";
        if(array_key_exists($field,$_GET)){
            $thefield = remove_accents(trim($_GET[$field]));
            $content .= "<input name='$field' value='".$thefield."'/>";
            $extraparam .= "$field=".$thefield."&";
        }
        else{
            $content .= "<input name='$field'/>";
        }
        $content .= "<br/>";
    }
    $content .= "<strong>"._("BibORB Fields")."</strong><br/>";
    foreach($biborb_fields as $field){
        $content .= "<label>$field</label>";
        if(array_key_exists($field,$_GET)){
            $thefield = remove_accents(trim($_GET[$field]));
            $content .= "<input name='$field' value='".$thefield."'/>";
            $extraparam .= "$field=".$thefield."&";
        }
        else{
            $content .= "<input name='$field'/>";
        }
        $content .= "<br/>";
    }
    
    $content .= "<input type='hidden' name='mode' value='displayadvancedsearch'/>";
    $content .= "<input type='hidden' name='searched' value='1'/>";
    $content .= "<input class='submit' type='submit' value='"._("Search")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "</div><br/>";
    $content .= "<a href='bibindex.php?mode=displaysearch'>"._("Simple Search")."</a>, <a href='bibindex.php?mode=displayxpathsearch'>"._("XPath Search")."</a>.<br/><br/>";
    
    $searchArray = array();
    foreach($bibtex_fields as $val){
        if(array_key_exists($val,$_GET) && trim($_GET[$val]) != ''){
            $searchArray['search_'.$val]=remove_accents(trim($_GET[$val]));
        }
    }
    
    foreach($biborb_fields as $val){
        if(array_key_exists($val,$_GET) && trim($_GET[$val]) != ''){
            $searchArray['search_'.$val]=remove_accents(trim($_GET[$val]));
        }
    }
    if(array_key_exists('connector',$_GET)){
        $searchArray['search_connector'] = $_GET['connector'];
    }
    $main_content = "";
    if(count($searchArray) > 1){
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_advanced_search($searchArray),$GLOBALS['MAX_REFERENCES_BY_PAGE']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
            $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
            $nb = count($flatids);
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = 'displayadvancedsearch';
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $extraparam .= "page=".$_GET['page'];
            $param['extra_get_param'] = $extraparam;
            
            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extraparam);
            $start .= "</div>";
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extraparam);
            
            if($nb==1){
                $main_content = _("One match.").$start;
                $main_content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
            }
            else if($nb>1) {
                $main_content = sprintf(_("%d matches."),$nb).$start;
                $main_content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
            }
        }
        else{
            $main_content = _("No match.");
        }
    }
    
    $title = _("BIBINDEX_ADVANCED_SEARCH_TITLE");

    // hide the search form if some results are being displayed
    if(array_key_exists('searched',$_GET)){
        $html = bibheader("onload='javascript:toggle_element(\"search_form\")'");
    }
    else{
        $html = bibheader();
    }
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $html .= main($title,$content.$main_content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_basket_help()
 * Display a small help on items present in the 'basket' menu
 */

function bibindex_basket_help(){
    $html = bibheader();  
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_BASKET_HELP_TITLE");
    $content = load_localized_file("basket_help.txt");
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_basket()
 * display entries present in the basket
 */
function bibindex_display_basket(){
    $title = _("BIBINDEX_BASKET_DISPLAY_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $content = null;
    $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
    $param = $GLOBALS['xslparam'];
    $param['bibindex_mode'] = $_GET['mode'];
    $param['basketids'] = $_SESSION['basket']->items_to_string();
    
    // store the ids in session if we come from an other page.
    if(!isset($_GET['page'])){
        $_SESSION['ids'] = array_chunk($_SESSION['basket']->items,$GLOBALS['MAX_REFERENCES_BY_PAGE']);
        $_GET['page'] = 0;
    }
    $flatids = flatten_array($_SESSION['ids']);
    $nb = count($flatids);
    if($nb>0){
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
        // create a nav bar to display entries
        $start = create_nav_bar($_GET['page'],count($_SESSION['ids']),"displaybasket","page=".$_GET['page']);
        $param['extra_get_param'] = "page=".$_GET['page'];
        $main_content = replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
        $content = "<div>";
        if($nb == 1){
            $content = _("An entry in the basket.");
        }
        else{
            $content = sprintf(_("%d entries in the basket."),$nb);
        }
        if($_SESSION['user_can_delete']){
            $content .= "<div style='float:right;display:inline;'>";
            $content .= "<a class='admin' href='bibindex.php?mode=operation_result&action=delete_basket'>";
            $content .= _("Delete all from database");
            $content .= "</a>";
            $content .= "</div>";
        }
        $content .= "</div>".$start;
        $content .= $main_content;
    }
    else{
        $content = _("No entry in the basket.");
    }
    
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_basket_modify_group
 * Display the page to modify groups of entries in the basket
 */
function bibindex_basket_modify_group(){
    $title = _("BIBINDEX_BASKET_GROUPS_MANAGE_TITLE");
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());

    $main_content = "<form id='reset_groups' action='bibindex.php' method='get'>";
    $main_content .= "<fieldset>";
	$main_content .= "<input type='hidden' name='mode' value='groupmodif'/>";
	$main_content .= "<input class='submit' type='submit' name='action' value='"._("Reset")."'/> &nbsp;"._("Reset the groups field of each entry in the basket. ");
	$main_content .= "</fieldset>";
	$main_content .= "</form>";
	$main_content .= "<br/>";
	$main_content .= _("Add all entries in the basket to a group:");
    $main_content .= "<br/>";
    $main_content .= "<br/>";
	$main_content .= "<form id='add_new_group' action='bibindex.php' method='get' onsubmit='return validate_add_group(\"".$_SESSION['language']."\")'>";
	$main_content .= "<fieldset>";
	$main_content .= "<input type='hidden' name='mode' value='groupmodif'/>";
	$main_content .= "<label for='newgroupvalue'>"._("New group:")."</label> <input name='newgroupvalue' id='newgroupvalue' class='longtextfield'/>";
	$main_content .= "&nbsp;<input class='submit' type='submit' name='action' value='"._("Add")."'/>";
	$main_content .= "</fieldset>";
	$main_content .= "</form><br/>";
    $groups = $_SESSION['bibdb']->groups();
    
    // display available groups if at least one exists
    if(count($groups)>0){
        $main_content .= "<form id='add_group' action='bibindex.php' method='get'>";
        $main_content .= "<fieldset>";
        $main_content .= "<input type='hidden' name='mode' value='groupmodif'/>";
        $main_content .= "<label for='groupvalue'>"._("Existing group:")."</label>";
        $main_content .= xhtml_select('groupvalue',1,$groups,"",null,null,"longtextfield");
        $main_content .= "&nbsp;<input class='submit' type='submit' name='action' value='"._("Add")."'/>";
        $main_content .= "</fieldset>";
        $main_content .= "</form>";
    }

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
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_ADMIN_HELP_TITLE");
    $content = load_localized_file("manager_help.txt");
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
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_SELECT_NEW_ENTRY_TITLE");
    $types = xhtml_select('type',1,$_SESSION['bibdb']->entry_types(),"");
	$content = "<form id='select_entry_type' method='get' action='bibindex.php'>";
	$content .= "<fieldset>";
	$content .= "<label for='mode'>"._("Select an entry type: ")."</label>".$types;
    $content .= "&nbsp;<input class='submit' type='submit' name='mode' id='mode' value='"._("Select")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
	
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_add_entry
 * Display a form to edit the value of each BibTeX fields
 */
function bibindex_add_entry($type){
	
    // xslt transformation
    $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
    $param = $GLOBALS['xslparam'];
    $xml_content = load_file("./xsl/model.xml");
    $xsl_content = load_file("./xsl/model.xsl");
    $param = array("typeentry"=>$type);
    $fields = $xsltp->transform($xml_content,$xsl_content,$param);
    $fields = replace_localized_strings($fields);
    $xsltp->free();
    $glist = $_SESSION['bibdb']->groups();
    array_push($glist,"");
    $groups=xhtml_select("groupslist",1,$glist,"","addGroup()");
	$fields = str_replace("#XHTMLGROUPSLIST",$groups,$fields);
    $html = bibheader("");
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_ADD_ENTRY_TITLE");
    $content = "<form method='post' action='bibindex.php' enctype='multipart/form-data' onsubmit='return validate_new_entry_form(\"".$_SESSION['language']."\")' id='f_bibtex_entry'>";
	$content .= "<fieldset class='clean'>";
	$content .= "<input name='type' value='$type' type='hidden'/>";
    $content .= "</fieldset>";
	$content .= $fields;
	$content .= "<fieldset class='clean'>";
	$content .= "<input type='hidden' name='mode' value='operationresult'/>";
	$content .= "<input class='submit' type='submit' name='action' value='"._("Cancel")."'/>&nbsp;";
	$content .= "<input class='submit' type='submit' name='action' value='"._("Add")."'/>";
	$content .= "</fieldset>";
	$content .= "</form>";

    $html .= main($title,$content);
    $html .= html_close();
    return $html;
}

/**
 * bibindex_update_entry
 * Display a form to modify fields of an entry
 */
function bibindex_update_entry(){
    
	// get the entry
	$entry = $_SESSION['bibdb']->entry_with_id($_GET['id']);
    // get existent types
	$types = $_SESSION['bibdb']->entry_types();
    
	// xslt transformation
	$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
	$param = $GLOBALS['xslparam'];
	$param['id'] = $_GET['id'];
	$param['modelfile'] = "file://".realpath("./xsl/model.xml");
	$param['update'] = "true";
	$thetype = trim($xsltp->transform($entry,load_file("./xsl/get_bibtex_type.xsl")));
    $param['type'] = $thetype;
    $fields = $xsltp->transform($entry,load_file("./xsl/xml2htmledit.xsl"),$param);
    $fields = replace_localized_strings($fields);
	$xsltp->free();
    
    // get existent groups
	$glist = $_SESSION['bibdb']->groups();
    // put the groups HTML select in the form
	array_push($glist,"");
	$groups=xhtml_select("groupslist",1,$glist,"","addGroup()");
	$fields = str_replace("#XHTMLGROUPSLIST",$groups,$fields);
	
	$listtypes = xhtml_select('bibtex_type',1,$types,$thetype);
	
	$theid = $_GET['id'];
    // form to update the type
	$content = "<form method='get' action='bibindex.php' class='f_default_form'>";
    $content .= "<fieldset>";
    $content .= "<label>"._("BibTeX type:")."</label>&nbsp;";
    $content .= $listtypes;
    $content .= "&nbsp;<input class='submit' type='submit' name='action' value='"._("Update")."'/>";
    $content .= "<input type='hidden' name='id' value='$theid'/>";
    $content .= "<input type='hidden' name='object' value='type'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $content .= "<form method='get' id='new_bibtex_key' action='bibindex.php' class='f_default_form' onsubmit='return validate_new_bibtex_key(\"".$_SESSION['language']."\")'>";
    $content .= "<fieldset>";
    $content .= "<label>"._("BibTeX Key:")."</label>";
    $content .= "&nbsp;<input name='bibtex_key' value='$theid'/>";
    $content .= "&nbsp;<input class='submit' type='submit' name='action' value='"._("Update")."'/>";
    $content .= "<input type='hidden' name='id' value='$theid'/>";
    $content .= "<input type='hidden' name='object' value='key'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "<form method='post' action='bibindex.php' enctype='multipart/form-data' name='fields' id='f_bibtex_entry'>";
    $content .= $fields;
    $content .= "<fieldset class='clean'>";
    $content .= "<input class='submit' type='submit' name='action' value='"._("Cancel")."'/>";
    $content .= "&nbsp;<input class='submit' type='submit' name='action' value='"._("Update")."'/>";
    $content .= "<input type='hidden' name='mode' value='operationresult'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
	
	// create the HTML page
	$html = bibheader("onload='javascript:toggle_element(\"additional\")'");
	$html .= bibindex_menu($_SESSION['bibdb']->name());
	$title = _("BIBINDEX_UPDATE_ENTRY_TITLE");
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
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_IMPORT_TITLE");
    $content = _("Select a BibTeX file or edit entries in the text area. Entries will be added to the current bibliography.");
    $content .= "<br/><br/>";
    $content .= "<h3 style='padding:0;margin:0'>"._("File")."</h3>";
    $content .= "<form method='post' action='bibindex.php' enctype='multipart/form-data'>";
    $content .= "<fieldset class='fieldset-bordered' title='"._("File")."'>";
    $content .= "<input type='file' name='bibfile'/>";
    $content .= "<input type='hidden' name='mode' value='operationresult'/>";
    $content .= "<br/>";
    $content .= "<br/>";
    $content .= "<input class='submit' type='submit' name='action' value='"._("Import")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "<br/>";
    $content .= "<h3 style='padding:0;margin:0'>BibTeX</h3>";
    $content .= "<form method='post' action='bibindex.php'>";
    $content .= "<fieldset class='fieldset-bordered' title='BibTeX'>";
    $content .= "<textarea name='bibval' cols='55' rows='15'></textarea>";
    $content .= "<input type='hidden' name='mode' value='operationresult'/>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input class='submit' type='submit' name='action' value='"._("Import")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
	
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * bibindex_export_basket_to_bibtex
 */
function bibindex_export_basket_to_bibtex(){
    
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_EXPORT_TO_BIBTEX_TITLE");
    
    $content = "<span class='emphit'>"._("Select fields to include in the exported BibTeX:")."</span>";
    $content .= "<form action='bibindex.php' method='post'>";
    $content .= "<fieldset style='border:none'>";
    $content .= "<table>";
    $content .= "<tbody>";
    $cpt = 0;
    for($i=0;$i<count($GLOBALS['bibtex_entries']);$i++){
	if(strcmp($GLOBALS['bibtex_entries'][$i],'id') != 0){
	    $field = $GLOBALS['bibtex_entries'][$i];
	    if($cpt == 0){
            $content .= "<tr>";
	    }
	    $content .= "<td><input type='checkbox' name='$field'";
	    if(!(array_search($field,$GLOBALS['fields_to_export']) === false)){
            $content .= " checked='checked' ";
	    }
	    $content .= "/>$field</td>";
	    $cpt++;
	    if($cpt == 4){
		$content .= "</tr>";
		$cpt = 0;
	    }
	}
    }
    if($cpt != 0){
	while($cpt != 4){
	    $cpt++;
	    $content .= "<td/>"; 
	}
	$content .= "</tr>";
    }

    $content .= "</tbody>";
    $content .= "</table>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input type='submit' name='action' value='"._("Export")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * bibindex_export_basket_to_html
 */
function bibindex_export_basket_to_html(){

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
		$content = $xsltp->transform($entries,load_file("./xsl/simple_html_output.xsl"),$param);
		$xsltp->free();
		
		// HTML output
		$html = html_header(null,$GLOBALS['CSS_FILE'],null);
		$html .= $content;
		$html .= html_close();
		echo $html;
	}
	else{
		echo bibindex_display_basket();
	}
}


/**

*/
function bibindex_display_xpath_search()
{
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_XPATH_SEARCH_TITLE");
    
    $content = "<h4 class='tool_name'>"._("TOOL_XPATH_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= _("TOOL_XPATH_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='get' action='bibindex.php' id='xpath_form' onsubmit='return validate_xpath_form(\"".$_SESSION['language']."\")'>";
    $content .= "<fieldset>";
    $content .= "<textarea cols='50' rows='5' name='xpath_query'>";
    if(array_key_exists('xpath_query',$_GET)){
        $content .= $_GET['xpath_query'];
    }
    else{
        $content .= "contains(*/bibtex:author, 'someone') and */bibtex:year=2004";
    }
    $content .= "</textarea><br/>";
    $content .= "<input type='hidden' name='mode' value='displayxpathsearch'/>";
    $content .= "<input type='submit' class='submit' value='"._("Search")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "<a href='bibindex.php?mode=displaysearch'>"._("Simple Search")."</a>, <a href='bibindex.php?mode=displayadvancedsearch'>"._("Advanced Search")."</a>.<br/><br/>";
    
    // execute an Xpath query
    if(array_key_exists("xpath_query",$_GET)){
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->ids_for_xpath_search(myhtmlentities($_GET['xpath_query'])),$GLOBALS['MAX_REFERENCES_BY_PAGE']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['ids'][$_GET['page']]);
            $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
            $nb = count($flatids);
            $param = $GLOBALS['xslparam'];
            $param['bibindex_mode'] = "displayxpathsearch";
            $param['basketids'] = $_SESSION['basket']->items_to_string();
            $extraparam = "xpath_query=".urlencode($_GET['xpath_query']);
            $extraparam .= "&page=".$_GET['page'];
            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,"displayxpathsearch",$extraparam);
            $start .= "</div>";
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"displayxpathsearch",$extraparam);
            $param['extra_get_param'] = $extraparam;
            if($nb==1){
                $content .= _("One match.").$start;
                $content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
            }
            else if($nb>1) {
                $content .= sprintf(_("%d matches."),$nb).$start;
                $content .= replace_localized_strings($xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
            }
            $xsltp->free();
        }
        else{
            $content .= _("No match.");
        }
    }
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}


/**

*/
function bibindex_display_tools(){
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = _("BIBINDEX_TOOLS_TITLE");
    
    $content = "<h4 class='tool_name'>"._("TOOL_AUX2BIBTEX_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= _("TOOL_AUX2BIBTEX_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='post' enctype='multipart/form-data' action='bibindex.php'  onsubmit='return validate_bibtex2aux_form(\"".$_SESSION['language']."\")' id='bibtex2aux_form'>";
    $content .= "<fieldset>";
    $content .= "<input type='file' name='aux_file'/>";
    $content .= "<input type='hidden' name='action' value='bibtex_from_aux'/>";
    $content .= "&nbsp;<input type='submit' class='submit' value='"._("Download")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $content .= "<h4 class='tool_name'>"._("TOOL_GET_ARCHIVE_TITLE")."</h4>";
    $content .= "<div class='tool_help'>";
    $content .= _("TOOL_GET_ARCHIVE_HELP");
    $content .= "</div>";
    $content .= "<form class='tool_form' method='post' action='bibindex.php'>";
    $content .= "<fieldset>";
    $content .= "<input type='hidden' name='action' value='get_archive'/>";
    $content .= "<input type='submit' class='submit' value='"._("Download")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

?>
