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
    $title = "Login";
    $content = <<<HTML
<form action='index.php' method='post'>
    <table style='margin:auto;'>
        <tr>
            <td class='emphit'>Username:</td>
            <td><input class='misc_input' type='text' name='login' size='15' maxlength='20' value='login'/></td>
        </tr>
        <tr>
            <td class='emphit'>Password:</td>
            <td>
                <input class='misc_input' type='password' name='mdp' size='15' maxlength='20' value='mdp'/>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;"><input class='misc_button' type='submit' name='action' value='login'/
            </td>
        </tr>
	</table>
</form>
HTML;
    
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
    $content = load_file("./data/index_welcome.txt");
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
    $title = "Create a new bibliography";
    
    // create the form to create a new bibliography
    $content = <<<HTML
<form method='get' action='index.php'>
    <input type='hidden' name='mode' value='result'/>
    <table style='margin:auto;'>
        <tbody>
            <tr>
                <td class='emphit'>Database name: </td>
                <td><input class='misc_input' type='text' size='40' name='database_name'/></td>
            </tr>
            <tr>
                <td class='emphit'>Description: </td>
                <td><input class='misc_input' type='text' size='40' name='description'/></td>
            </tr>
            <tr>
                <td style='text-align:center' colspan='2'><input class='misc_button' type='submit' name='action' value='create'/></td>
            </tr>
        </tbody>
    </table>
</form>
HTML;
    
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
    $title = "Delete a bibliography";
    
    // get all bibliographies and create a form to select which one to delete
    $databases = get_databases_names();
    $content = <<<HTML
<div style='text-align:center;'>
    <form method='get' action='index.php'>
        <input type='hidden' name='mode' value='result'/>
        <fieldset style='border:none;'>
            <select class='misc_button' name='database_name' size='1'>
HTML;
    foreach($databases as $name){
        if($name != ".trash"){
            $content .= "<option value='$name'>$name</option>";
        }
    }
    $content .= <<<HTML
            </select>
            <input class='misc_button' type='submit' name='action' value='delete'/>
        </fieldset>
    </form>
</div>
HTML;
    
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
    $title = "Manager Help";
    $content = load_file("./data/index_manager_help.txt");
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
    $html .= main("Results",null,
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
    $title = "Available bibliographies";
    $html .= index_menu();

    // get all bibliographies and create an array
    $databases = get_databases_names();
    $content = <<<HTML
<div id='available_bibliographies'>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Sources(BibTeX)</th>
            </tr>
        </thead>
        <tbody>
HTML;
    
    foreach($databases as $name){
        // do not parse the trash directory
        if($name != ".trash"){
            $description = load_file("./bibs/$name/description.txt");
            $content .= <<<HTML
                
            <tr>
                <td><a class='bibname' href="./bibindex.php?mode=welcome&amp;bibname=$name">$name</a></td>
                <td><span class='bib_description'>$description</span></td>
                <td><a class='download' href="./bibs/$name/$name.bib">Download</a></td>
            </tr>
HTML;
        }
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
        $html .= "<li><a href='index.php?mode=welcome&action=logout'>Logout</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";
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
    if(!array_key_exists('bibname',$_GET)){
        die("No bibliography name provided");
    }
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
//      $content = ereg_replace("<div class=\"result\">(.)*</div><br/>","",$content);
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
    $title = "BibORB Manager";
    $content = <<<HTML
<form action='bibindex.php' method='post'>
	<table style='margin:auto;'>
        <tr>
            <td class='emphit'>Username:</td>
            <td><input type='hidden' name='mode' value='login'/>
                <input  class='misc_input' type='text' name='login' size='15' maxlength='20' value='login'/></td>
        </tr>
        <tr>
            <td class='emphit'>Password:</td>
            <td><input class='misc_input' type='password' name='mdp' size='15' maxlength='20' value='mdp'/></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center"><input  class='misc_button' type='submit' name='action' value='login'/>
            </td>
        </tr>
	</table>
</form>
HTML;
    
    $html .= main($title,$content,$GLOBALS['error']);
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
    $_SESSIION['user'] = null;
    
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
    $html .= "<li><a href='index.php?mode=select'>Select a bibliography</a><ul><li></li></ul></li>";
    // second item
    // -> Display
    //      | -> All
    //      | -> by group
    //      | -> search
    $html .= "<li><a href='bibindex.php?mode=display'>Display</a>";
    $html .= "<ul>";
    $html .= "<li><a href='bibindex.php?mode=displayall'>All</a></li>";
    $html .= "<li><a href='bibindex.php?mode=displaybygroup'>Groups</a></li>";
    $html .= "<li><a href='bibindex.php?mode=displaysearch'>Simple Search</a></li>";
    $html .= "<li><a href='bibindex.php?mode=displayadvancedsearch'>Advanced Search</a></li>";
    $html .= "</ul>";
    $html .= "</li>";
    // third menu item
    // -> Basket
    //      | -> Display basket
    //      | -> Modify groups (if admin)
    //      | -> Export to bibtex
    //      | -> Export to XML
    //      | -> Reset basket
    $html .= "<li><a href='bibindex.php?mode=basket'>Basket</a>";
    $html .= "<ul>";
    $html .= "<li><a href='bibindex.php?mode=displaybasket'>Display Basket</a></li>";
    if($_SESSION['usermode']=='admin' || $GLOBALS['disable_authentication']){
        $html .= "<li><a class='admin' href='bibindex.php?mode=groupmodif'>Group Modification</a></li>";
    }
    $html .= "<li><a href='bibindex.php?mode=exportbaskettobibtex'>Export to BibTeX</a></li>";
    $html .= "<li><a href='bibindex.php?mode=exportbaskettohtml'>Export to HTML</a></li>";
    $html .= "<li><a href='bibindex.php?mode=".$GLOBALS['mode']."&action=resetbasket";
	if($GLOBALS['mode'] == "displaybygroup" && array_key_exists('group',$_GET)){
		$html  .= "&group=".$_GET['group'];
	}
	if($GLOBALS['mode'] == "displaysearch"){
		if(array_key_exists('search',$_GET)){
			$html .= "&search=".$_GET['search'];
		}
		if(array_key_exists('author',$_GET)){
			$html .= "&author=".$_GET['author'];
		}
		if(array_key_exists('title',$_GET)){
			$html .= "&title=".$_GET['title'];
		}
		if(array_key_exists('keywords',$_GET)){
			$html .= "&search=".$_GET['keywords'];
		}	
	}
	$html .= "'>Reset basket</a></li>";
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
    $html .= "<li><a href='bibindex.php?mode=manager'>Manager</a>";
    $html .= "<ul>";
    if($_SESSION['usermode']=='user' && !$GLOBALS['disable_authentication']){
        $html .= "<li><a href='bibindex.php?mode=login'>Login</a></li>";
    }
    if($_SESSION['usermode']=='admin'){
        $html .= "<li><a class='admin' href='bibindex.php?mode=addentry'>Add an entry</a></li>";
        $html .= "<li><a class='admin' href='bibindex.php?mode=update_xml_from_bibtex'>Update from BibTeX</a></li>";
        $html .= "<li><a class='admin' href='bibindex.php?mode=import'>Import BibTeX</a></li>";
    }
    if($_SESSION['usermode']=='admin' && !$GLOBALS['disable_authentication']){
        $html .= "<li><a href='bibindex.php?mode=welcome&action=logout'>Logout</a></li>";
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
    $content = "This is the bibliography: <b>".$_SESSION['bibdb']->name()."</b>.<br/>";
    if($_SESSION['usermode'] == 'admin' && !$GLOBALS['disable_authentication']) {
        if(array_key_exists('user',$_SESSION)){      
            $content .= "You are logged as <em>".$_SESSION['user']."</em>.";
        }
    }
	$nb = $_SESSION['bibdb']->count_entries();
	$nbpapers = $_SESSION['bibdb']->count_epapers();
	
	$content  .= <<<HTML
<h3>Statistics</h3>
<table>
	<tbody>
		<tr>
			<td>Number of recorded articles:</td>
			<td><strong>$nb</strong></td>
		</tr>
		<tr>
			<td>On-line available publications:</td>
			<td><strong>$nbpapers</strong></td>
		</tr>
		</tbody>
</table>
HTML;

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
    $title = "BibORB message";
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
    $title = "Display menu";
    $content = load_file("./data/display_help.txt");
    $html .= main($title,$content);
    $html .= html_close();    
    return $html;
}

/**
 * bibindex_display_all
 * Display all entries in the bibliography
 */
function bibindex_display_all(){
    $title = "List of all entries";
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());

	$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
	$param = $GLOBALS['xslparam'];
	$param['bibindex_mode'] = $_GET['mode'];
	$param['basketids'] = $_SESSION['basket']->items_to_string();
	$html .= main($title,$xsltp->transform($_SESSION['bibdb']->all_entries(),load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param));
	$xsltp->free();
	
    $html .= html_close();
    return $html;  
}

/**
 * bibindex_display_by_group()
 * Display entries by group
 */
function bibindex_display_by_group(){
	$group = get_value('group',$_GET);
    $title = "Display entries by group";
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    // create a form with all groups present in the bibliography
    $main_content = "<div style='text-align:center;'>";
    $main_content .= "<form method='get' action='bibindex.php'>";
    $main_content .="<fieldset style='border:none'>";
    $main_content .= "<input type='hidden' name='bibname' value='".$_SESSION['bibdb']->name()."'/>";
    $main_content .= "<input type='hidden' name='mode' value='displaybygroup'/>";
    $main_content .= "<h3 style='display:inline;'>Available groups:</h3> ";
    $main_content .= "<select name='group' size='1'>";
    // set Select values to groups available
    foreach($_SESSION['bibdb']->groups() as $gr){
        $main_content .= "<option value='".$gr."' ";
        if($gr == $group){
            $main_content .= "selected='selected'";
        }
        $main_content .= ">".$gr."</option>";
    }
    $main_content .= "</select>";
    $main_content .= "<input class='misc_button' type='submit' value='Display'/>";
//    $main_content .= "<input type='submit' value=''/>";
    $main_content .= "</fieldset>";
    $main_content .="</form></div><br/>";
    
    // if the group is defined, display the entries matching it
    if($group){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$param = $GLOBALS['xslparam'];
		$param['group'] = $group;
		$param['basketids'] = $_SESSION['basket']->items_to_string();
		$param['bibindex_mode'] = "displaybygroup";
		$param['extra_get_param'] = "group=$group";
		$entries = $_SESSION['bibdb']->entries_for_group($group);
		$nb = trim($xsltp->transform($entries,load_file("./xsl/count_entries.xsl")));
		if($nb == 0){
			$main_content .= "No entry for the group $group.";
		}
		else if($nb == 1){
			$main_content .= "An entry for the group $group.";
		}
		else{
			$main_content .= "$nb entries for the group $group.";
		}
		
		$main_content .= $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);;
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
	
    $title = "Search";
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
	
	
	$main_content = "<form action='bibindex.php' method='get' style='text-align:center'>";
	$main_content .= "<fieldset style='border:none'>";
	$main_content .= "<input type='hidden' name='mode' value='displaysearch' />";
	$main_content .= "<input class='misc_input' name='search' size='40' value='".$searchvalue."' />";
	$main_content .= "<input class='misc_button' type='submit' value='Search' /><br/>";
	//	$main_content .= "<span style='font-weight:bold'>Search in fields:</span>";
	$main_content .= "<table style='margin:auto;text-align:left;'>";
	$main_content .= "<tbody>";
	$main_content .= "<tr>";
	$main_content .= "<td style='width:80px;'><input type='checkbox' name='author' value='1'";
	if(array_key_exists('author',$_GET)){
		$main_content .= "checked='checked'";
	}
	$main_content .= " />Author</td>";
	$main_content .= "<td style='width:80px;'><input type='checkbox' name='title' value='1' ";
	if(array_key_exists('title',$_GET)){
		$main_content .= "checked='checked'";
	}
	$main_content .= "/>Title</td>";
	$main_content .= "<td style='width:80px;'><input type='checkbox' name='keywords' value='1' ";
	if(array_key_exists('keywords',$_GET)){
		$main_content .= "checked='checked'";
	}
	$main_content .= "/>Keywords</td>";
	$main_content .= "<tr>";
	$main_content .= "<td><input type='checkbox' name='journal' value='1'";
	if(array_key_exists('journal',$_GET)){
		$main_content .= "checked='checked'";
	}
	$main_content .= " />Journal</td>";
	$main_content .= "<td><input type='checkbox' name='editor' value='1'";
	if(array_key_exists('editor',$_GET)){
		$main_content .= "checked='checked'";
	}
	$main_content .= " />Editor</td>";
	$main_content .= "<td><input type='checkbox' name='year' value='1'";
	if(array_key_exists('year',$_GET)){
		$main_content .= "checked='checked'";
	}
	$main_content .= " />Year</td>";
	
	$main_content .= "</tr>";
    $main_content .=  "<tr style='text-align:center'><td colspan='3'>Sort by:";
    $main_content .= "<select name='sort' size='1'>";
    
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
    $main_content .= ">Title</option>";
    
    $main_content .= "<option value='year' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'year'){
        $main_content .="selected='selected'";
    }
    $main_content .= ">Year</option>";
    $main_content .= "</select></td>";
    
    $main_content .= "</tr>";
	$main_content .= "</tbody>";
	$main_content .= "</table>";
	$main_content .= "</fieldset>";
	$main_content .= "</form>";

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

		$entries = $_SESSION['bibdb']->search_entries($searchvalue,$fields);
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $nb = trim($xsltp->transform($entries,load_file("./xsl/count_entries.xsl")));
		$param = $GLOBALS['xslparam'];
		$param['bibindex_mode'] = $_GET['mode'];
		$param['basketids'] = $_SESSION['basket']->items_to_string();
        $param['display_sort'] = 'no';

        $param['extra_get_param'] = $extra_param;
        if($nb==1){
            $main_content .= "One match for $searchvalue.";
            $main_content .= $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
        }
        else if($nb>1) {
            $main_content .= "$nb match for $searchvalue.";
            $main_content .= $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
        }
        else{
            $main_content .= "No match for $searchvalue.";
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
                            

    $content = <<< HTML
<form action='bibindex.php' method='post'>
    <fieldset style='border:none;'>
        <span class='emphit'>Connector:</span>
        <select name='connector' size='1'>
HTML;
    if(array_key_exists('connector',$_POST)){
	if(!strcmp($_POST['connector'],'and')){
	    $content .= "<option value='and' selected='selected'>and</option>";
	    $content .= "<option value='or'>or</option>";
	}
	else{
	    $content .= "<option value='and'>and</option>";
	    $content .= "<option value='or' selected='selected'>or</option>";
	}
    }
    else{
	$content .= "<option value='and' selected='selected'>and</option>";
	$content .= "<option value='or'>or</option>";
    }
    $content .= "</select>";
    
    $content .= <<<HTML
     </select>
        <table width='100%'>
            <tbody>
            <tr colspan='2'><td><span class='emphit'>BibTeX Fields</span></td></tr>
HTML;

    foreach($bibtex_fields as $field){
        $content .= "<tr><td style='width:100px;'>$field</td><td>";
	if(array_key_exists($field,$_POST)){
		$content .= "<input style='width:85%;' name='$field' value='".$_POST[$field]."'/></td></tr>";
	}
	else{
		$content .= "<input style='width:85%;' name='$field'/></td></tr>";
	}
    }
    $content .= "<tr colspan='2'><td><span class='emphit'>BibORB Fields</span></td></tr>";
    foreach($biborb_fields as $field){
	$content .= "<tr><td>$field</td><td>";
	if(array_key_exists($field,$_POST)){
		$content .= "<input style='width:85%;' name='$field' value='".$_POST[$field]."'/></td></tr>";
	}
	else{
		$content .= "<input style='width:85%;' name='$field'/></td></tr>";
	}
    }
    
    $content .= "</tbody></table>";
    $content .= "<input type='hidden' name='mode' value='displayadvancedsearch'/>";
    $content .= "<div style='text-align:center;'><input type='submit' value='search'/></div>";
    $content .= "</fieldset>";
    $content .= "</form>";

    $searchArray = array();
    foreach($bibtex_fields as $val){
        if(array_key_exists($val,$_POST) && trim($_POST[$val]) != ''){
            $searchArray['search_'.$val]=trim($_POST[$val]);
        }
    }
    
    foreach($biborb_fields as $val){
        if(array_key_exists($val,$_POST) && trim($_POST[$val]) != ''){
            $searchArray['search_'.$val]=trim($_POST[$val]);
        }
    }
    if(array_key_exists('connector',$_POST)){
        $searchArray['search_connector'] = $_POST['connector'];
    }
    $main_content = "";
    if(count($searchArray) > 1){
        $entries = $_SESSION['bibdb']->advanced_search_entries($searchArray);
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $nb = trim($xsltp->transform($entries,load_file("./xsl/count_entries.xsl")));
        $param = $GLOBALS['xslparam'];
        $param['bibindex_mode'] = 'displayadvancedsearch';
        $param['basketids'] = $_SESSION['basket']->items_to_string();
        $param['display_sort'] = 'no';
        
//        $param['extra_get_param'] = $extra_param;
        if($nb==1){
            $main_content = "One match.";
            $main_content .= $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
        }
        else if($nb>1) {
            $main_content = "$nb matches.";
            $main_content .= $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
        }
        else{
            $main_content = "No match.";
        }
    }
    
    $title = "Advanced Search";
    $html = bibheader();
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
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $content = null;
	$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
	$param = $GLOBALS['xslparam'];
	$param['bibindex_mode'] = $_GET['mode'];
	$param['basketids'] = $_SESSION['basket']->items_to_string();
	$entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
	$nb = $_SESSION['basket']->count_items();
	$content = $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
	if($nb == 0){
		$content = "No entry in the basket.";
	}
	else if($nb == 1){
		$content = "An entry in the basket.".$content;
	}
	else{
		$content = "$nb entries in the basket.".$content;
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
    $title = "Groups management";
    $html = bibheader();
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    
    //$main_content = load_file("./data/basket_group_modify.txt");

	$main_content = <<<HTML_TEXT
		<form style='margin:0;padding:0;' action='bibindex.php' method='get'>
			<fieldset style='border:none;margin:O;padding:0;'>
				<input type="hidden" name="mode" value="groupmodif"/>
				<input  class='misc_button' type='submit' name='action' value='Reset'/> &nbsp;Reset the groups field of each entry in the basket. 
			</fieldset>
		</form>
		<br/>
		Add all entries in the basket to a group:
		<form style='margin-left:70px;margin-bottom:O;' action='bibindex.php' method='get'>
			<fieldset style='border:none;margin:0;margin-top:1em;padding:0'>
				<input type="hidden" name="mode" value="groupmodif"/>
				<span style='font-style:italic'>New group: </span> <input class='misc_input' name='groupvalue' size='20'/>
				<input  class='misc_button' type='submit' name='action' value='Add'/>
			</fieldset>
		</form>
		<form style='margin-left:70px;' action='bibindex.php' method='get'>
			<fieldset style='border:none;margin:0;padding:0;'>
				<input type="hidden" name="mode" value="groupmodif"/>
				<span style='font-style:italic'>Existing group: </span> <select name='groupvalue' size='1'>
HTML_TEXT;
			
	foreach($_SESSION['bibdb']->groups() as $gr){
		$main_content .= "<option value='".$gr."'>".$gr."</option>";
	}

	$main_content .= <<<HTML_TEXT
				</select>
				<input class="misc_button" type='submit' name='action' value='Add'/>
			</fieldset>
		</form>
HTML_TEXT;

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
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = "New entry";
    $content =<<<HTML
<div style='text-align:center'>
	<form method='get' action='bibindex.php'>
		<fieldset style='border:none'>
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
			<br/>
			<input class='misc_button' type='submit' name='mode' value='cancel'/>
			<input class='misc_button' type='submit' name='mode' value='select'/>
		</fieldset>
	</form>
</div>
HTML;
	
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
	$xsltp->free();
	
    $html = bibheader("onload='javascript:toggle_element(\"additional\")'");
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = "New entry";
    $content = <<<HTML
<form method='post' action='bibindex.php' enctype='multipart/form-data'>
	<fieldset style='border:none'>
		<input name='type' value='$type' type='hidden'/>
		$fields
		<p/>
		<div style='text-align:center;'>
			<input type='hidden' name='mode' value='operationresult'/>
			<input class='misc_button' type='submit' name='action' value='cancel'/>
			<input class='misc_button' type='submit' name='action' value='add'/>

		</div>
	</fieldset>
</form>
HTML;

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
	
	// xslt transformation
	$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
	$param = $GLOBALS['xslparam'];
	$param['id'] = $_GET['id'];
	$param['modelfile'] = "file://".realpath("./xsl/model.xml");
	$param['update'] = "true";
	$fields = $xsltp->transform($entry,load_file("./xsl/xml2htmledit.xsl"),$param);
	$xsltp->free();
	
	
    $content = <<<HTML
		<form method='post' action='bibindex.php' enctype='multipart/form-data'>
			<fieldset style='border:none'>
				$fields
				<div style='text-align:center'>
					<input class='misc_button' type='submit' name='action' value='cancel'/>
					<input class='misc_button' type='submit' name='action' value='update'  />
					<input type='hidden' name='mode' value='operationresult'/>
				</div>
			</fieldset>
		</form>
HTML;
	
	// create the HTML page
	$html = bibheader("onload='javascript:toggle_element(\"additional\")'");
    $html .= bibindex_menu($_SESSION['bibdb']->name());
    $title = "Update an entry";
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
    $title = "Import References";
    $content = <<<HTML
Select a BibTeX file or edit entries in the text area. Entries will be added to the current bibliography.
<h3>File</h3>
<form method='post' action='bibindex.php' enctype='multipart/form-data'>
	<fieldset title='file'>
		<input type='file' name='bibfile'/>
		<input type='hidden' name='mode' value='operationresult'/>
		<br/>
        <br/>
		<div style='text-align:center'>
			<input class='misc_button' type='submit' name='action' value='import'/>
		</div>
	</fieldset>
</form>
<h3>BibTeX</h3>
<form method='post' action='bibindex.php'>
	<fieldset title='BibTeX'>
		<textarea class='misc_input' name='bibval' cols='55' rows='15'></textarea>
		<input type='hidden' name='mode' value='operationresult'/>
		<div style='text-align:center'>
			<input class='misc_button' type='submit' name='action' value='import'/>
		</div>
	</fieldset>
</form>
HTML;
	
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
}

/**
 * bibindex_export_basket_to_bibtex
 */
function bibindex_export_basket_to_bibtex(){
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
		$content = $xsltp->transform($entries,load_file("./xsl/xml2bibtex.xsl"));
		$xsltp->free();
		
		// bibtex output
		header("Content-Type: text/plain");
		echo $content;
	}
	else{
		echo bibindex_display_basket();
	}
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
		$content = $xsltp->transform($entries,load_file("./xsl/biborb_output_sorted_by_id.xsl"),$param);
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

?>
