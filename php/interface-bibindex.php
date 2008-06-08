<?php
/**
 *
 * This file is part of BibORB
 *
 * Copyright (C) 2003-2007  Guillaume Gardey (glinmac@gmail.com)
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
 * File: interface-bibindex.php
 * Author: Guillaume Gardey (glinmac@gmail.com)
 * Licence: GPL
 *
 * Description:
 *      Functions to generate the interface (bibindex.php)
 *
 */

class_exists('HtmlToolKit') || include('./php/HtmlToolKit.php');

/********************************** BIBINDEX */

/**
 * bibindex_details()
 * Called when a given entry has to be displayed
 * 'bibindex.php?mode=details&abstract=1&menu=0&bibname=example&id=idA
 */
function bibindex_details()
{
    $html = bibheader();

    // get the bibname
    if(!array_key_exists('bibname',$_GET)){die("No bibliography name provided");}
    $bibdb = new BibORB_DataBase($_GET['bibname'],GEN_BIBTEX);

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

    $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
    $xsl_content = file_get_contents("./xsl/biborb_output_sorted_by_id.xsl");

    if(array_key_exists('bibids',$_GET)){
        // get the entries
        $bibids = explode(',',$_GET['bibids']);
		$xml_content = $bibdb->getEntriesWithIds($bibids);
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
            $aHtml.= bibindex_menu($_GET['bibname']);
            $aHtml.= main(null,$content);
        }
        else{
            $aHtml.= $content;
        }
    }
    else{
        $aHtml.= $content;
    }
    $aHtml.= html_close();

    return $aHtml;
}

/**
 * bibindex_login()
 * Display the login page
 */
function bibindex_login()
{
    $aHtml= bibheader();
    $aHtml.= bibindex_menu($_SESSION['bibdb']->getFullName());
    $aTitle = msg("INDEX_LOGIN_TITLE");
    $aContent = "<form id='login_form' action='bibindex.php' method='post' onsubmit='return validate_login_form(\"".$_SESSION['language']."\")' >";
    $aContent .= "<fieldset>";
    $aContent .= "<legend>Login</legend>";
    $aContent .= "<label for='login'>".msg("LOGIN_USERNAME").":</label>";
    $aContent .= "<input type='text' name='login' id='login' /><br/>";
    $aContent .= "<label for='password'>".msg("LOGIN_PASSWORD").":</label>";
    $aContent .= "<input type='password' id='password' name='mdp'/><br/>";
    $aContent .= "<input type='hidden' name='action' value='login'/>";
    $aContent .= "<input type='submit' value=\"".msg("Login")."\" class='submit'/>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";
    $aHtml .= main($aTitle,$aContent,$GLOBALS['error']);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * bibindex_logout()
 * Change admin mode to user and redirect to welcome page
 */
function bibindex_logout()
{
    unset($_SESSION['user']);
    bibindex_welcome();
}

/**
 * bibindex_menu($bibname)
 * Create the menu for the bibliography $bibname.
 */
function bibindex_menu($bibname)
{
    $aHtml = "<div id='menu'>";
    // title
    $aHtml .= "<span id='title'>BibORB</span>";
    // name of the current bibliography
    $aHtml .= "<span id='bibname'>".$bibname."</span>";
    $aHtml .= "<ul>";
    // first menu item => Select a bibliography
    $aHtml .= "<li><a href='index.php?mode=select'>".msg("BIBINDEX_MENU_SELECT_BIB")."</a>";
    $aHtml .= "<ul>";
    // jump to a given bibliography
    $avbibs = $_SESSION['DbManager']->getDbNames();
    $aHtml .= "<li>";
    $aHtml .= "<form id='choose_bib' action='bibindex.php'>";
    $aHtml .= "<fieldset>";
    $aHtml .= "<select onchange='javascript:change_db(this.value)'>";
    foreach($avbibs as $key=>$bib){
        if($key == $bibname){
            $aHtml .= "<option selected='selected' value='$key'>$bib</option>";
        }
        else{
            $aHtml .= "<option value='$key'>$bib</option>";
        }
    }
    $aHtml .= "</select><br/>";
    $aHtml .= "<noscript><div><input class='submit' type='submit' value='Go'/></div></noscript>";
    $aHtml .= "</fieldset>";
    $aHtml .= "</form>";
    $aHtml .= "</li>";
    $aHtml .= "</ul></li>";

    // second item
    // -> Display
    //      | -> All
    //      | -> by group
    //      | -> browse
    //      | -> search
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_HELP")."' href='bibindex.php?mode=display'>".msg("BIBINDEX_MENU_DISPLAY")."</a>";
    $aHtml .= "<ul>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_ALL_HELP")."' href='bibindex.php?mode=displayall'>".msg("BIBINDEX_MENU_DISPLAY_ALL")."</a></li>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_BY_GROUP_HELP")."'href='bibindex.php?mode=displaybygroup'>".msg("BIBINDEX_MENU_DISPLAY_BY_GROUP")."</a></li>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BROWSE_HELP")."'href='bibindex.php?mode=browse&amp;start=0'>".msg("BIBINDEX_MENU_BROWSE")."</a></li>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_SEARCH_HELP")."' href='bibindex.php?mode=displaysearch'>".msg("BIBINDEX_MENU_DISPLAY_SEARCH")."</a></li>";
//    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_DISPLAY_ADVANCED_SEARCH_HELP")."' href='bibindex.php?mode=displayadvancedsearch'>".msg("BIBINDEX_MENU_DISPLAY_ADVANCED_SEARCH")."</a></li>";
    $aHtml .= "</ul>";
    $aHtml .= "</li>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_TOOLS_HELP")."' href='bibindex.php?mode=displaytools'>".msg("BIBINDEX_MENU_TOOLS")."</a><ul><li/></ul></li>";
    // third menu item
    // -> Basket
    //      | -> Display basket
    //      | -> Modify groups (if admin)
    //      | -> Export to bibtex
    //      | -> Export to XML
    //      | -> Reset basket
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_HELP")."' href='bibindex.php?mode=basket'>".msg("BIBINDEX_MENU_BASKET")."</a>";
    $aHtml .= "<ul>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_DISPLAY_HELP")."' href='bibindex.php?mode=displaybasket'>".msg("BIBINDEX_MENU_BASKET_DISPLAY")."</a></li>";
    if($_SESSION['user_can_modify'] || DISABLE_AUTHENTICATION){
        $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_GROUP_HELP")."' class='admin' href='bibindex.php?mode=groupmodif'>".msg("BIBINDEX_MENU_BASKET_GROUP")."</a></li>";
    }
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_EXPORT_HELP")."' href='bibindex.php?mode=exportbasket'>".msg("BIBINDEX_MENU_BASKET_EXPORT")."</a></li>";
    /*
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_BIBTEX_HELP")."' href='bibindex.php?mode=exportbaskettobibtex'>".msg("BIBINDEX_MENU_BASKET_BIBTEX")."</a></li>";
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_HTML_HELP")."' href='bibindex.php?mode=exportbaskettohtml'>".msg("BIBINDEX_MENU_BASKET_HTML")."</a></li>";
     */
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_BASKET_RESET_HELP")."' href='bibindex.php?mode=".$GLOBALS['mode']."&amp;action=resetbasket";
	if($GLOBALS['mode'] == "displaybygroup" && array_key_exists('group',$_GET)){
		$aHtml  .= "&amp;group=".$_GET['group'];
	}
	if($GLOBALS['mode'] == "displaysearch"){
		if(array_key_exists('search',$_GET)){
			$aHtml .= "&amp;search=".$_GET['search'];
		}
		if(array_key_exists('author',$_GET)){
			$aHtml .= "&amp;author=".$_GET['author'];
		}
		if(array_key_exists('title',$_GET)){
			$aHtml .= "&amp;title=".$_GET['title'];
		}
		if(array_key_exists('keywords',$_GET)){
			$aHtml .= "&amp;search=".$_GET['keywords'];
		}
	}
	$aHtml .= "'>".msg("BIBINDEX_MENU_BASKET_RESET")."</a></li>";
    $aHtml .= "</ul>";
    $aHtml .= "</li>";

    // fourth menu item
    // -> Manager
    //      | -> Login (if not admin and authentication enabled
    //      | -> Add an entry (if admin)
    //      | -> Update from BibTeX (if admin)
    //      | -> Import a bibtex file (if admin)
    //      | -> Preferences
    //      | -> Logout (if admin and authentication disabled
    $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_HELP")."' href='bibindex.php?mode=manager'>".msg("BIBINDEX_MENU_ADMIN")."</a>";
    $aHtml .= "<ul>";
    if(!array_key_exists('user',$_SESSION) && !DISABLE_AUTHENTICATION){
        $aHtml .= "<li><a title=\"".msg("BIBINDEX_MENU_ADMIN_LOGIN_HELP")."\" href='bibindex.php?mode=login'>".msg("BIBINDEX_MENU_ADMIN_LOGIN")."</a></li>";
    }
    if($_SESSION['user_can_add']){
        $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_ADD_HELP")."' class='admin' href='bibindex.php?mode=addentry'>".msg("BIBINDEX_MENU_ADMIN_ADD")."</a></li>";
    }
    if($_SESSION['user_is_admin'] && GEN_BIBTEX){
        $aHtml .= "<li><a title=\"".msg("BIBINDEX_MENU_ADMIN_UPDATE_HELP")."\" class='admin' href='bibindex.php?mode=update_xml_from_bibtex'>".msg("BIBINDEX_MENU_ADMIN_UPDATE")."</a></li>";
    }
    if($_SESSION['user_can_add']){
        $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_IMPORT_HELP")."' class='admin' href='bibindex.php?mode=import'>".msg("BIBINDEX_MENU_ADMIN_IMPORT")."</a></li>";
    }
    if(array_key_exists('user',$_SESSION)){
        $aHtml .= "<li>";
        $aHtml .= "<a href='index.php?mode=preferences' title='".msg("BIBINDEX_MENU_ADMIN_PREF_HELP")."'>".msg("BIBINDEX_MENU_ADMIN_PREF")."</a>";
        $aHtml .= "</li>";
    }
    if(array_key_exists('user',$_SESSION) && !DISABLE_AUTHENTICATION){
        $aHtml .= "<li><a title='".msg("BIBINDEX_MENU_ADMIN_LOGOUT_HELP")."' href='bibindex.php?mode=welcome&amp;action=logout'>".msg("BIBINDEX_MENU_ADMIN_LOGOUT")."</a></li>";
    }
    $aHtml .= "</ul>";
    $aHtml .= "</li>";
    $aHtml .= "</ul>";

    if(DISPLAY_LANG_SELECTION && !array_key_exists("user",$_SESSION)){
        $aSelectAttribute = array( 'name' => 'lang',
                                   'onchange' => 'javascript:changeLangForBibIndex(this.value)');
        $aHtml .= "<form id='language_form' action='bibindex.php' method='get'>";
        $aHtml .= "<fieldset>";
        $aHtml .= "<label for='lang'>".msg("Language:")."</label>";
        $aHtml .= HtmlToolKit::selectTag($aSelectAttribute, $_SESSION['i18n']->getLocales(), $_SESSION['i18n']->getLocale());
        $aHtml .= "<input type='hidden' name='action' value='select_lang'/>";
        $aHtml .= "<noscript><div><input class='submit' type='submit' value='".msg("Select")."'/></div></noscript>";
        $aHtml .= "</fieldset>";
        $aHtml .= "</form>";
    }
    $aHtml .= "</div>";

  return $aHtml;
}

/**
 * bibheader()
 * Create the HTML header
 */
function bibheader($iInBody = NULL)
{
    $aHtmlHeaderData = array( 'title' => 'BibORB'.$_SESSION['bibdb']->getFullName(),
                              'stylesheet' => CSS_FILE,
                              'javascript' => './biborb.js',
                              'body' => $iInBody);

    return HtmlToolKit::htmlHeader($aHtmlHeaderData);
}


/**
 * This is the default Welcome page.
 */
function bibindex_welcome()
{
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $aTitle = 'BibORB: '. $_SESSION['bibdb']->getFullName();
    $aContent = '';
    
    //$content = msg('This is the bibliography').': <strong>'.$_SESSION['bibdb']->name().'</strong>.<br/>';
    if(array_key_exists('user',$_SESSION) && !DISABLE_AUTHENTICATION){
        $content .= msg('You are logged as').': <em>'.$_SESSION['user'].'</em>.';
/*
        $content .= '<br/>';
        $content .= 'Allowed to add entry: '.($_SESSION['user_can_add'] ? 'YES' : 'NO');
        $content .= '<br/>';
        $content .= 'Allowed to modify entry: '.($_SESSION['user_can_modify'] ? 'YES' : 'NO');
        $content .= '<br/>';
        $content .= 'Allowed to delete entry: '.($_SESSION['user_can_delete'] ? 'YES' : 'NO');
*/
    }
	$aNbRef = $_SESSION['bibdb']->getEntryCount();
	$aNbRefPapers = $_SESSION['bibdb']->getPapersCount();

	$aContent  .= HtmlToolKit::tag('h3',msg('Statistics'));
    $aContent  .= HtmlToolKit::startTag('table');
	$aContent  .= HtmlToolKit::startTag('tbody');
	$aContent  .= HtmlToolKit::startTag('tr');
	$aContent  .= HtmlToolKit::tag('td',msg('Number of recorded articles').':');
	$aContent  .= HtmlToolKit::tag('td','<strong>'.$aNbRef.'</strong>');
	$aContent  .= HtmlToolKit::closeTag('tr');    
    $aContent  .= HtmlToolKit::startTag('tr');
	$aContent  .= HtmlToolKit::tag('td',msg('On-line available publications').':');
    $aContent  .= HtmlToolKit::tag('td','<strong>'.$aNbRefPapers.'</strong>');
	$aContent  .= HtmlToolKit::closeTag('tr');
    $aContent  .= HtmlToolKit::closeTag('tbody');
    $aContent  .= HtmlToolKit::closeTag('table');    

    $aHtml .= HtmlToolKit::main($aTitle,$aContent);
    $aHtml .= HtmlToolKit::htmlClose();
    
    return $aHtml;
}


/**
 * bibindex_operation_result()
 * Display error or message
 */
function bibindex_operation_result(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BibORB message");
    $aHtml .= main($title,null,$GLOBALS['error'],$GLOBALS['message']);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * bibindex_display_help()
 * Display a small help on items present in the 'display' menu
 */

function bibindex_display_help()
{
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $aTitle = msg('BIBINDEX_DISPLAY_HELP_TITLE');
    $aContent = $_SESSION['i18n']->getFile('display_help.txt');
    $aHtml .= main($aTitle,$aContent);
    $aHtml .= HtmlToolKit::htmlClose();
    return $aHtml;
}

/**
 * Display all entries in the bibliography.
 */
function bibindex_display_all()
{
    $aTitle = msg('BIBINDEX_DISPLAY_ALL_TITLE');
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());

    // store the ids in session if we come from an other page.
    // the bibtex keys are retreived from the database the first time that display_all is called
    if (!isset($_GET['page']))
    {
    	// split the array so that we display only $GLOBALS['max_ref']
        $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->getAllIds(),$GLOBALS['max_ref']);
        // go to the first page
        $_GET['page'] = 0;
    }

    if ($_SESSION['bibdb']->getEntryCount())
    {
    	// get the data of the references to display
        $aEntries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);
        
        // set up XSLT parameters
        $aParam = $GLOBALS['xslparam'];
        $aParam['bibindex_mode'] = $_GET['mode'];
        $aParam['basketids'] = $_SESSION['basket']->items_to_string();
        $aParam['extra_get_param'] = 'page='.$_GET['page'];
        // generate HTML render of references
        $aContent = biborb_html_render($aEntries,$aParam);

        // create the header: sort function + add all to basket
        $aStart = HtmlToolKit::startTag('div', array('class'=>'result_header'));
        // display sort if needed
        if ($GLOBALS['display_sort'] == 'yes')
        {
            $aStart = sort_div($_SESSION['bibdb']->getSortMethod(),$_SESSION['bibdb']->getSortOrder(),$_GET['mode'],null).$aStart;
        }
        $aStart .= add_all_to_basket_div($_SESSION['bibdb']->getAllIds(),$_GET['mode'],"sort=".$GLOBALS['sort'].'&amp;sort_order='.$GLOBALS['sort_order'].'&amp;page='.$_GET['page']);
        $aStart .= HtmlToolKit::closeTag('div');

        // create a nav bar to display entries
        $aStart .= create_nav_bar($_GET['page'],count($_SESSION['ids']),'displayall','sort='.$GLOBALS['sort'].'&amp;sort_order='.$GLOBALS['sort_order'].'page='.$_GET['page']);
        $aContent = $aStart.$aContent;
    }
    else
    {
        $aContent = msg("No entries.");
    }
    $aHtml .= main($aTitle,$aContent);
    $aHtml .= html_close();
    return $aHtml;
} // end bibindex_display_all


/**
 * bibindex_display_by_group()
 * Display entries by group
 */
function bibindex_display_by_group()
{    
	$aGroup = isset($_GET['group']) ? $_GET['group'] : null;
    
    if (isset($_GET['orphan']))
        $aGroup = null;

    $aTitle = msg('BIBINDEX_DISPLAY_BY_GROUPS_TITLE');
    
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());

    // create a form with all groups present in the bibliography

    $aContent = HtmlToolKit::startTag('form', array( 'id' => 'display_by_group_form',
                                                     'method' => 'get',
                                                     'action' => 'bibindex.php'));
    $aContent .= HtmlToolKit::startTag('fieldset');
    $aContent .= HtmlToolKit::tagNoData('input', array( 'type'=> 'hidden',
                                                        'name' => 'bibname',
                                                        'value' => $_SESSION['bibdb']->getName()));
    $aContent .= HtmlToolKit::tagNoData('input', array( 'type'=> 'hidden',
                                                        'name' => 'mode',
                                                        'value' => 'display_by_group'));
    $aContent .= HtmlToolKit::tag('label', msg('Available groups').':', array( 'for' => 'group',
                                                                              'method' => 'get',
                                                                              'action' => 'bibindex.php'));
    $aGroups = $_SESSION['bibdb']->getGroups();    
    $aContent .= HtmlToolKit::selectTag(array('name' => 'group', 'size' => '1'),
                                        array_combine($aGroups,$aGroups),
                                        $aGroup);
    // if shelf_mode on, create a popup to display which references to display
    if ($GLOBALS['display_shelf_actions'] == "yes")
    {
        $aContent .= read_status_html_select('read_status_group',
                                             isset($_GET['read_status_grp']) ? $_GET['read_status_grp'] : 'any');
        $aContent .= ownership_html_select('ownership_grp',
                                             isset($_GET['ownership_grp']) ? $_GET['ownership_grp'] : 'any');

    }
    $aContent .= '&nbsp;';
    $aContent .= HtmlToolKit::tagNoData('input', array('class'=> 'submit',
                                                       'type' => 'submit',
                                                       'value'=> msg('Display')));
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');

    // create a form to display orphans references
    // if shelf_mode on, create a popup to display which references to display
    $aContent .= HtmlToolKit::startTag('form', array( 'id'     => 'group_orphan_form',
                                                      'method' => 'get',
                                                      'action' => 'bibindex.php'));
    $aContent .= HtmlToolKit::startTag('fieldset');
    $aContent .= HtmlToolKit::tagNoData('input', array( 'type' => 'hidden',
                                                        'name' => 'bibname',
                                                       'value' => $_SESSION['bibdb']->getName()));
    $aContent .= HtmlToolKit::tagNoData('input', array( 'type' => 'hidden',
                                                        'name' => 'mode',
                                                       'value' => 'displaybygroup'));
    $aContent .= HtmlToolKit::tagNoData('input', array( 'type' => 'hidden',
                                                        'name' => 'orphan',
                                                       'value' => 1));
    
    $aContent .= HtmlToolKit::tag('label', msg("Entries associated with no group:"));
    // if shelf_mode on, create a popup to display which references to display
    if ($GLOBALS['display_shelf_actions'] == "yes")
    {
        $aContent .= read_status_html_select('read_status_group',
                                             isset($_GET['read_status_orphans']) ? $_GET['read_status_orphans'] : 'any');
        $aContent .= ownership_html_select('ownership_grp',
                                             isset($_GET['ownership_orphans']) ? $_GET['read_status_orphans'] : 'any');
    }
    $aContent .= '&nbsp;';
    $aContent .= HtmlToolKit::tagNoData('input', array( 'type'  => 'submit',
                                                        'class' => 'submit',
                                                        'value' => msg('Orphans')));
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');


    if ($GLOBALS['display_shelf_actions'] == "yes")
    {
        if (isset($_GET['read_status_orphans']))
        {
            $_SESSION['bibdb']->setReadStatus($_GET['read_status_orphans']);
        }
        if (isset($_GET['ownership_orphans']))
        {
            $_SESSION['bibdb']->setOwnership($_GET['ownership_orphans']);
        }
        if (isset($_GET['read_status_grp']))
        {
            $_SESSION['bibdb']->setReadStatus($_GET['read_status_grp']);
        }
        if (isset($_GET['ownership_grp']))
        {
            $_SESSION['bibdb']->setOwnership($_GET['ownership_grp']);
        }
    }
    
    // store the ids in session if we come from an other page.
    if (!isset($_GET['page']))
    {
        $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->getIdsForGroup($aGroup),$GLOBALS['max_ref']);
        $_GET['page'] = 0;
    }

    $aFlatIds = flatten_array($_SESSION['ids']);
    $aNb = count($aFlatIds);

    // if the group is defined, display the entries matching it
    if ( ($aGroup || isset($_GET['orphan'])) && $aNb>0)
    {
        $aParam = $GLOBALS['xslparam'];
        $aParam['group'] = $aGroup;
        $aParam['basketids'] = $_SESSION['basket']->items_to_string();
        $aParam['bibindex_mode'] = 'displaybygroup';

        // display orphans
        if (isset($_GET['orphan']))
        {
            if (isset($_GET['read_status_orphans']))
            {
                $aParam['extra_get_param'] = 'read_status_orphans='.$_GET['read_status_orphans'].'&amp;';
            }
            if (isset($_GET['ownership_orphans']))
            {
                $aParam['extra_get_param'] = 'ownership='.$_GET['ownership_orphans'].'&amp;';
            }
            $aParam['extra_get_param'] = 'orphan=1&page='.$_GET['page'];
            $aEntries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);
        }
        else
        {
            if (isset($_GET['read_status_grp']))
            {
                $aParam['extra_get_param'] = 'read_status_orphans='.$_GET['read_status_grp'].'&amp;';
            }
            if (isset($_GET['ownership_grp']))
            {
                $aParam['extra_get_param'] = 'ownership='.$_GET['ownership_grp'].'&amp;';
            }
            $aParam['extra_get_param'] = 'group=$group&page='.$_GET['page'];
            $aEntries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);
        }

        if ($aNb == 1)
        {
            if (!isset($_GET['orphan']))
            {
                $aContent .= sprintf(msg("An entry for the group %s."),$aGroup);
            }
            else
            {
                $aContent .= msg("1 orphan.");
            }
        }
        else
        {
            if(!isset($_GET['orphan'])){
                $aContent .= sprintf(msg("%d entries for the group %s."),$aNb,$aGroup);
            }
            else{
                $aContent .= sprintf(msg("%d orphans."),$aNb);
            }
        }

        if (isset($_GET['orphan']))
        {
            $aExtraParam = 'orphan=1&amp;sort='.$GLOBALS['sort'].'&amp;sort_order='.$GLOBALS['sort_order'].'&amp;page='.$_GET['page'];
        }
        else
        {
            $aExtraParam = 'group=$group&amp;sort='.$GLOBALS['sort'].'&amp;sort_order='.$GLOBALS['sort_order'].'&amp;page='.$_GET['page'];
        }

        // create the header
        $aStart = HtmlToolKit::startTag('div', array('style' => 'result_header'));
        if (isset($_GET['orphan']))
        {
            $aExtra['orphan'] = 1;
        }
        else
        {
            $aExtra['group'] = $aGroup;
        }

        if($GLOBALS['display_sort'] == "yes")
        {
            $aStart = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],$aExtra).$aStart;
        }
        $aStart .= add_all_to_basket_div($aFlatIds,$_GET['mode'],$aExtraParam);
        $aStart .= HtmlToolKit::closeTag('div');

        // create a nav bar to display entries
        $aStart .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$aExtraParam);

        $aContent .= "<br/><br/>".$aStart;
        $aContent .= biborb_html_render($aEntries,$aParam);
    }
    else
    {
        if (!isset($_GET['orphan']))
        {
            if (isset($aGroup))
            {
                $aContent .= sprintf(msg("No entry for the group %s."),$aGroup);
            }
        }
        else
        {
            $aContent .= msg("No orphan.");
        }
    }
    $aHtml .= main($aTitle,$aContent);
    $aHtml .= html_close();

    return $aHtml;
}

/**
 * bibindex_display_search
 * display the search interface
 */
function bibindex_display_search(){

    $searchvalue = array_key_exists('search',$_GET) ? trim(htmlentities(remove_accents($_GET['search']))) :"";

    $title = msg("BIBINDEX_SIMPLE_SEARCH_TITLE");
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());

    // search tabs
    $aContent = "<div id='search_tabs'>";
    $aContent .= "<ul id='tabnav'>";
    $aContent .= "<li><a class='active' href='bibindex.php?mode=displaysearch'>".msg("Simple Search")."</a></li>";
    $aContent .= "<li><a href='bibindex.php?mode=displayadvancedsearch'>".msg("Advanced Search")."</a></li>";
    $aContent .= "<li><a href='bibindex.php?mode=displayxpathsearch'>".msg("XPath Search")."</a></li>";
    $aContent .= "</ul>";
    $aContent .= "</div>";

    $aContent .= "<form id='simple_search_form' class='search_content' action='bibindex.php' method='get' style='text-align:center'>";
    $aContent .= "<fieldset>";
    $aContent .= "<input type='hidden' name='mode' value='displaysearch' />";
    $aContent .= "<input name='search' value='".$searchvalue."' />&nbsp;";
    $aContent .= msg("Sort by:")."&nbsp;<select name='sort'>";

    $aContent .= "<option value='ID' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'ID'){
        $aContent .="selected='selected'";
    }
    $aContent .= ">ID</option>";

    $aContent .= "<option value='title' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'title'){
        $aContent .="selected='selected'";
    }
    $aContent .= ">".msg("Title")."</option>";

    $aContent .= "<option value='year' ";
    $sort = null;
    if(array_key_exists('sort',$_GET)){
        $sort = $_GET['sort'];
    }
    if($sort == 'year'){
        $aContent .="selected='selected'";
    }
    $aContent .= ">".msg("Year")."</option>";
    $aContent .= "</select>&nbsp;";
    $aContent .= "&nbsp;<input class='submit' type='submit' value='".msg("Search")."' /><br/>";
    $aContent .= "<table>";
    $aContent .= "<tbody>";
    $aContent .= "<tr>";
    $aContent .= "<td><input type='checkbox' name='author' value='1'";
    if(array_key_exists('author',$_GET)){
        $aContent .= "checked='checked'";
    }
    $aContent .= " />".msg("Author")."</td>";
    $aContent .= "<td><input type='checkbox' name='title' value='1' ";
    if(array_key_exists('title',$_GET)){
        $aContent .= "checked='checked'";
    }
    $aContent .= "/>".msg("Title")."</td>";
    $aContent .= "<td><input type='checkbox' name='keywords' value='1' ";
    if(array_key_exists('keywords',$_GET)){
        $aContent .= "checked='checked'";
    }
    $aContent .= "/>".msg("Keywords")."</td>";
    $aContent .= "</tr><tr>";
    $aContent .= "<td><input type='checkbox' name='journal' value='1'";
    if(array_key_exists('journal',$_GET)){
        $aContent .= "checked='checked'";
    }
    $aContent .= " />".msg("Journal")."</td>";
    $aContent .= "<td><input type='checkbox' name='editor' value='1'";
    if(array_key_exists('editor',$_GET)){
        $aContent .= "checked='checked'";
    }
    $aContent .= " />".msg("Editor")."</td>";
    $aContent .= "<td><input type='checkbox' name='year' value='1'";
    if(array_key_exists('year',$_GET)){
        $aContent .= "checked='checked'";
    }
    $aContent .= " />".msg("Year")."</td>";
    $aContent .= "</tr></tbody></table>";

    $aContent .= "<br/>";


    $aContent .= "</fieldset>";
    $aContent .= "</form>";

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
        if(!isset($_GET['page']))
        {
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->getIdsForSearch($searchvalue,$fields),$GLOBALS['max_ref']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);

            $nb = count($flatids);
            $aParam = $GLOBALS['xslparam'];
            $aParam['bibindex_mode'] = $_GET['mode'];
            $aParam['basketids'] = $_SESSION['basket']->items_to_string();
            $extra_param .= "&page=".$_GET['page'];
            $aParam['extra_get_param'] = $extra_param;

            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extra_param);
            $start .= "</div>";

            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extra_param);

            if($nb==1){
                $aContent .= sprintf(msg("One match for %s"),$searchvalue).$start;
                $aContent .= biborb_html_render($entries,$aParam);
            }
            else if($nb>1) {
                $aContent .= sprintf(msg("%d matches for %s."),$nb,$searchvalue).$start;
                $aContent .= biborb_html_render($entries,$aParam);
            }
        }
        else{
            $aContent .= sprintf(msg("No match for %s."),$searchvalue);
        }
    }
    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();

    return $aHtml;
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
    $content = "";
    if(array_key_exists('searched',$_GET)){
        $extraparam .= "searched=1&";
        $content .= "<div style='float:right;'>";
        $content .= "<script type='text/javascript'><!--
    document.write(\"<a class='cleanref' href=\\\"javascript:toggle_element(\'search_form\')\\\">";
        $content .= msg("Display/ Hide search form")."</a>\");\n--></script>";
        $content .= "<noscript><pre> </pre></noscript>";
        $content .= "</div>";
    }

    // search tabs
    $content .= "<div id='search_tabs'>";
    $content .= "<ul id='tabnav'>";
    $content .= "<li><a href='bibindex.php?mode=displaysearch'>".msg("Simple Search")."</a></li>";
    $content .= "<li><a class='active' href='bibindex.php?mode=displayadvancedsearch'>".msg("Advanced Search")."</a></li>";
    $content .= "<li><a href='bibindex.php?mode=displayxpathsearch'>".msg("XPath Search")."</a></li>";
    $content .= "</ul>";
    $content .= "</div>";

    $content .= "<div id='search_form' class='search_content'>";
    $content .= "<form action='bibindex.php' method='get'>";
    $content .= "<fieldset>";
    $content .= "<em>".msg("Connector:")." </em>";
    $content .= "<select name='connector'>";

    if(array_key_exists('connector',$_GET)){
        $extraparam .= "connector=".$_GET['connector']."&";
        if(!strcmp($_GET['connector'],'and')){
            $content .= "<option value='and' selected='selected'>".msg("and")."</option>";
            $content .= "<option value='or'>".msg("or")."</option>";
        }
        else{
            $content .= "<option value='and'>".msg("and")."</option>";
            $content .= "<option value='or' selected='selected'>".msg("or")."</option>";
        }
    }
    else{
        $extraparam = "connector=and&";
        $content .= "<option value='and' selected='selected'>".msg("and")."</option>";
        $content .= "<option value='or'>".msg("or")."</option>";
    }
    $content .= "</select> ";
    $content .= "<em>".msg("Sort by:")." </em>";
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
    $content .= ">".msg("Year")."</option>";

    $content .= "<option value='ID' ";
    if($sort == 'ID'){
        $content .="selected='selected'";
    }
    $content .= ">ID</option>";

    $content .= "<option value='title' ";
    if($sort == 'title'){
        $content .="selected='selected'";
    }
    $content .= ">".msg("Title")."</option>";
    $content .= "</select><br/>";
	$content .= "<strong>".msg("BibTeX Fields")."</strong><br/>";

    foreach($bibtex_fields as $field){
        $content .= "<label>".msg($field)."</label>";
        if(array_key_exists($field,$_GET)){
            $thefield = trim(htmlentities(remove_accents($_GET[$field])));
            $content .= "<input name='$field' value='".$thefield."'/>";
            $extraparam .= "$field=".$thefield."&";
        }
        else{
            $content .= "<input name='$field'/>";
        }
        $content .= "<br/>";
    }
    $content .= "<strong>".msg("BibORB Fields")."</strong><br/>";
    foreach($biborb_fields as $field){
        $content .= "<label>".msg($field)."</label>";
        if(array_key_exists($field,$_GET)){
            $thefield = trim(htmlentities(remove_accents($_GET[$field])));
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
    $content .= "<input class='submit' type='submit' value='".msg("Search")."'/>";
    $content .= "</fieldset>";
    $content .= "</form>";
    $content .= "</div><br/>";

    $searchArray = array();
    foreach($bibtex_fields as $val){
        if(array_key_exists($val,$_GET) && trim(htmlentities($_GET[$val])) != ''){
            $searchArray['search_'.$val]=trim(htmlentities(remove_accents($_GET[$val])));
        }
    }

    foreach($biborb_fields as $val){
        if(array_key_exists($val,$_GET) && trim(htmlentities($_GET[$val])) != ''){
            $searchArray['search_'.$val]=trim(htmlentities(remove_accents($_GET[$val])));
        }
    }
    if(array_key_exists('connector',$_GET)){
        $searchArray['search_connector'] = $_GET['connector'];
    }
    $aContent = "";
    if(count($searchArray) > 1){
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page']))
        {
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->getIdsForAdvancedSearch($searchArray),$GLOBALS['max_ref']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);
            $nb = count($flatids);
            $aParam = $GLOBALS['xslparam'];
            $aParam['bibindex_mode'] = 'displayadvancedsearch';
            $aParam['basketids'] = $_SESSION['basket']->items_to_string();
            $extraparam .= "page=".$_GET['page'];
            $aParam['extra_get_param'] = $extraparam;

            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],$extraparam);
            $start .= "</div>";
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),$_GET['mode'],$extraparam);

            if($nb==1){
                $aContent = msg("One match.").$start;
                $aContent .= biborb_html_render($entries,$aParam);
            }
            else if($nb>1) {
                $aContent = sprintf(msg("%d matches."),$nb).$start;
                $aContent .= biborb_html_render($entries,$aParam);
            }
        }
        else{
            $aContent = msg("No match.");
        }
    }

    $title = msg("BIBINDEX_ADVANCED_SEARCH_TITLE");

    // hide the search form if some results are being displayed
    if(array_key_exists('searched',$_GET))
    {
        $aHtml = bibheader(array('onload'=>"javascript:toggle_element(\"search_form\")"));
    }
    else{
        $aHtml = bibheader();
    }
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $aHtml .= main($title,$content.$aContent);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * bibindex_basket_help()
 * Display a small help on items present in the 'basket' menu
 */

function bibindex_basket_help(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_BASKET_HELP_TITLE");
    $content = $_SESSION['i18n']->getFile("basket_help.txt");
    $aHtml .= main($title,$content);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * bibindex_display_basket()
 * display entries present in the basket
 */
function bibindex_display_basket(){
    $title = msg("BIBINDEX_BASKET_DISPLAY_TITLE");
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $content = null;
    $error = null;
    $message = null;


    $aParam = $GLOBALS['xslparam'];
    $aParam['bibindex_mode'] = $_GET['mode'];
    $aParam['basketids'] = $_SESSION['basket']->items_to_string();

    // store the ids in session if we come from an other page.
    if(!isset($_GET['page'])){
        $_SESSION['ids'] = array_chunk($_SESSION['basket']->items,$GLOBALS['max_ref']);
        $_GET['page'] = 0;
    }
    $nb = $_SESSION['basket']->count_items();
    if($nb>0){
        $entries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);
        // create a nav bar to display entries
        $start = create_nav_bar($_GET['page'],count($_SESSION['ids']),"displaybasket","page=".$_GET['page']);
        $aParam['extra_get_param'] = "page=".$_GET['page'];
        $aContent = biborb_html_render($entries,$aParam);
        $content = "<div>";
        if($nb == 1){
            $content = msg("An entry in the basket.");
        }
        else{
            $content = sprintf(msg("%d entries in the basket."),$nb);
        }
        if($_SESSION['user_can_delete']){
            $content .= "<div style='float:right;display:inline;'>";
            $content .= "<a class='admin' href='bibindex.php?mode=operation_result&action=delete_basket'>";
            $content .= msg("Delete all from database");
            $content .= "</a>";
            $content .= "</div>";
        }
        $content .= "</div>".$start;
        $content .= $aContent;
    }
    else{
        $message = msg("No entry in the basket.");
    }

    $aHtml .= main($title,$content,$error,$message);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * bibindex_basket_modify_group
 * Display the page to modify groups of entries in the basket
 */
function bibindex_basket_modify_group(){
    $title = msg("BIBINDEX_BASKET_GROUPS_MANAGE_TITLE");
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());

    // reset groups
    $aContent = "<form id='reset_groups' action='bibindex.php' method='get'>";
    $aContent .= "<fieldset>";
	$aContent .= "<input type='hidden' name='mode' value='groupmodif'/>";
    $aContent .= "<input type='hidden' name='action' value='reset'/>";
	$aContent .= "<input class='submit' type='submit' value='".msg("Reset")."'/> &nbsp;".msg("Reset the groups field of each entry in the basket. ");
	$aContent .= "</fieldset>";
	$aContent .= "</form>";
	$aContent .= "<br/>";
	$aContent .= msg("Add all entries in the basket to a group:");
    $aContent .= "<br/>";
    $aContent .= "<br/>";

    //  create a new group
    $aLocale = $_SESSION['i18n']->getLocale();
	$aContent .= "<form id='add_new_group' action='bibindex.php' method='get' onsubmit='return validate_add_group(\"{$aLocale}\")'>";
	$aContent .= "<fieldset>";
	$aContent .= "<input type='hidden' name='mode' value='groupmodif'/>";
	$aContent .= "<label for='newgroupvalue'>".msg("New group:")."</label> <input name='newgroupvalue' id='newgroupvalue' class='longtextfield'/>";
    $aContent .= "<input type='hidden' name='action' value='add'/>";
	$aContent .= "&nbsp;<input class='submit' type='submit' value='".msg("Add")."'/>";
	$aContent .= "</fieldset>";
	$aContent .= "</form><br/>";
    $groups = $_SESSION['bibdb']->getGroups();

    // display available groups if at least one exists
    if(count($groups)>0){
        $aContent .= "<form id='add_group' action='bibindex.php' method='get'>";
        $aContent .= "<fieldset>";
        $aContent .= "<input type='hidden' name='mode' value='groupmodif'/>";
        $aContent .= "<label for='groupvalue'>".msg("Existing group:")."</label>";
        $aContent .= xhtml_select('groupvalue',1,$groups,"",null,null,"longtextfield");
        $aContent .= "<input type='hidden' name='action' value='add'/>";
        $aContent .= "&nbsp;<input class='submit' type='submit' value='".msg("Add")."'/>";
        $aContent .= "</fieldset>";
        $aContent .= "</form>";
    }

    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * bibindex_manager_help()
 * Display a small help on items present in the 'manager' menu
 */
function bibindex_manager_help(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_ADMIN_HELP_TITLE");
    $content = $_SESSION['i18n']->getFile("manager_help.txt");
    $aHtml .= main($title,$content);
    $aHtml .= html_close();
    return $aHtml;
}

/**
 * guiSelectReferenceType
 * get the form to select the reference type to add
 */
function guiSelectReferenceType()
{
    $aTypes = $_SESSION['DbManager']->getReferenceTypes();
    
    $content = HtmlToolKit::startTag('form',
                                     array('id' => 'formSelectReferenceType',
                                           'method' => 'post',
                                           'action' => _PHP_SELF_));
    $content .= HtmlToolKit::startTag('fieldset');
    $content .= HtmlToolKit::tag('legend',msg('Type of reference'));
	$content .= HtmlToolKit::tag('label', msg("Select a reference type: "), array('for' => 'referenceType'));
    $content .= HtmlToolKit::selectTag(array('name'=>'referenceType'),
                                       array_combine($aTypes,$aTypes));
    $content .= HtmlToolKit::tagNoData('input', array('type' => 'hidden',
                                                      'name' => 'mode',
                                                      'value'=> 'addReference'));
    $content .= HtmlToolKit::tagNoData('input', array('class' => 'submit',
                                                      'type' => 'submit',
                                                      'value'=>msg("Select")));
    $content .= HtmlToolKit::closeTag('fieldset');
    $content .= HtmlToolKit::closeTag('form');

    return $content;
}

/**
 * bibindex_add_entry
 * Display a form to edit the value of each BibTeX fields
 */
function guiAddReference($type)
{

    // locale
    $aLocale = $_SESSION['i18n']->getLocale();
    
    // get the list of fields for this type
    $aFields = Reference::getFieldsForType($type);
    

    // read status and ownership
//    $readstatus = read_status_html_select("read","notread");
//    $ownership = ownership_html_select("own","notown");
//    $fields = str_replace("#XHTMLREADSTATUS",$readstatus,$fields);
//    $fields = str_replace("#XHTMLOWNERSHIP",$ownership,$fields);
    
// a tabbed interface
    $content = HtmlToolKit::startTag('div', array('id'=>'tabnav'));
    $content .= HtmlToolKit::startTag('ul');
    $content .= HtmlToolKit::startTag('li',array('class'=>'active','id' => 'tab_required_ref'));
    $content .= HtmlToolKit::tag('a',
                                 msg("BIBORB_OUTPUT_REQUIRED_FIELDS"),
                                 array('href'=>'javascript:toggleTabEdit("required_ref")'));
    $content .= HtmlToolKit::closeTag('li');
	$content .= HtmlToolKit::startTag('li', array('id' => 'tab_optional_ref'));
    $content .= HtmlToolKit::tag('a',
                                 msg("BIBORB_OUTPUT_OPTIONAL_FIELDS"),
                                 array('href'=>'javascript:toggleTabEdit("optional_ref")'));
    $content .= HtmlToolKit::closeTag('li');
	$content .= HtmlToolKit::startTag('li',array('id'=>'tab_additional_ref'));
    $content .= HtmlToolKit::tag('a',
                                 msg("BIBORB_OUTPUT_ADDITIONAL_FIELDS"),
                                 array('href'=>'javascript:toggleTabEdit("additional_ref")'));
    $content .= HtmlToolKit::closeTag('li');
    $content .= HtmlToolKit::closeTag('ul');
    $content .= HtmlToolKit::closeTag('div');
	// beginning of the form
    $content .= HtmlToolKit::startTag('form',
                                      array('method'  => 'post',
                                            'action'  => 'bibindex.php',
                                            'enctype' => 'multipart/form-data',
                                            'onsubmit' => 'return validate_new_entry_form('.$aLocale.')',
                                            'id' => 'formAddReference'));
    $content .= HtmlToolKit::startTag('fieldset');			
	$content .= HtmlToolKit::tagNoData('input',array('name'=>'___type',
                                                     'value'=> $type,
                                                     'type'=>'hidden'));
    $content .= HtmlToolKit::tagNoData('input', array('type'  => 'hidden',
                                                      'name'  => 'mode',
                                                      'value' => 'operationresult'));
	$content .= HtmlToolKit::tagNoData('input', array('class' => 'submit',
                                                      'type'  => 'submit',
                                                      'name'  => 'cancel',
                                                      'value' => msg("Cancel")));
    $content .= HtmlToolKit::tagNoData('input', array('type' => 'button',
                                                      'name' => 'ok',
                                                      'value' => msg("Add"),
                                                      'onclick' => 'return checkAddRefForm("'.$_SESSION['i18n']->getLocale().'")'));
    $content .= HtmlToolKit::tagNoData('input', array('type'  =>'hidden',
                                                      'name'  =>'action',
                                                      'value' => 'add_entry'));
    $content .= HtmlToolKit::closeTag('fieldset');
    
	// add the input to be filled by the user
    foreach ($aFields as $type => $fields)
    {
        $content .= HtmlToolKit::startTag('fieldset', array('class' => $type,
                                                            'id' => "${type}_ref"));
        foreach ($fields as $field)
        {
            $content .= HtmlToolKit::tag('label',msg($field), array('for' => $field));
            $aProperties = array('name' => $field);
            if ($field == 'abstract' || $field == 'longnotes')
            {
                $content .= HtmlToolKit::tag('textarea', '' , array('name' => $field,
                                                                    'rows' => 5,
                                                                    'cols' => 80));
            }
            else if ($field == 'url' || $field == 'pdf' || $field == 'urlzip')
            {
                $content .= HtmlToolKit::tagNoData('input', array('name' => 'up_'.$field,
                                                                  'type' => 'file'));
            }
            else if ($field == 'groups')
            {
                // get the list of available groups
                $glist = $_SESSION['bibdb']->getGroups();
                array_push($glist,"");
                array_push($glist,"g1");
                array_push($glist,"g2");
                $content .= HtmlToolKit::tagNoData('input', array('name'=>$field));
                $content .= HtmlToolKit::tagNoData('br');
                $content .= HtmlToolKit::tag('label','', array('for' => 'listOfGroups'));
                $content .= HtmlToolKit::selectTag(array('name'=> 'listOfGroups',
                                                         'onchange' => 'addGroup()'),
                                                   array_combine($glist,$glist), '');
                $content .= HtmlToolKit::tagNoData('br');
                
            }                
            else
            {
                $content .= HtmlToolKit::tagNoData('input', array('name'=>$field));
            }
            
        }
        $content .= HtmlToolKit::closeTag('fieldset');
	}    

	$content .= HtmlToolKit::startTag('fieldset');
    $content .= HtmlToolKit::tagNoData('br');

    $content .= HtmlToolKit::closeTag('form');    
    
    return $content;
}

/**
 * bibindex_update_entry
 * Display a form to modify fields of an entry
 */
function bibindex_update_entry()
{

	// get the entry
	$aEntryXml = $_SESSION['bibdb']->getEntryWithId($_GET['id']);
    $aRef = XmlConverter::import($aEntryXml);
    $aType = $aRef->getType();
    $aId = $aRef->getId();

    // get existent types
	$aTypes = $_SESSION['DbManager']->getEntryTypes();
    
	// xslt transformation
	$aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');

	$aParam = $GLOBALS['xslparam'];
	$aParam['id'] = $aId;

	$aParam['modelfile'] = 'file://'.realpath('./xsl/model.xml');
	$aParam['update'] = 'true';
    $aParam['type'] = $aType;
    $aFields = $aXsltp->transform($aEntryXml,FileToolKit::getContent('./xsl/xml2htmledit.xsl'),$aParam);
	$aXsltp->free();

    // get existent groups
	$aGroups = $_SESSION['bibdb']->getGroups();

    // put the groups HTML select in the form
	array_push($aGroups,"");
    $groups = HtmlToolKit::selectTag(array('name' => 'groupslist', 'size' => 1, 'onchange' => 'addGroup()'),
                           array_combine($aGroups, $aGroups));

	$aFields = str_replace("#XHTMLGROUPSLIST",$groups,$aFields);

    // read status and ownership
    $readstatus = read_status_html_select('read',$aRef->getData('read') ? $aRef->getData('read') : 'notread');
    $ownership = ownership_html_select('own',$aRef->getData('own') ? $aRef->getData('own') : 'notown');
    $aFields = str_replace("#XHTMLREADSTATUS",$readstatus,$aFields);
    $aFields = str_replace("#XHTMLOWNERSHIP",$ownership,$aFields);

	$listtypes = xhtml_select('bibtex_type',1,$aTypes,$aType);

    // form to update the type
	$aContent = HtmlToolKit::startTag('form', array( 'method' => 'get',
                                                    'action' => 'bibindex.php',
                                                    'class'  => 'f_default_form'));
    $aContent .= HtmlToolKit::startTag('fieldset');
    $aContent .= HtmlToolKit::tag('label',msg('BibTeX type:').'&nbsp;');
    $aContent .= $listtypes;
    $aContent .= HtmlToolKit::tagNoData('input', array('type' => 'hidden',
                                                      'name' => 'action',
                                                      'value'=> 'update_type'));
    $aContent .= '&nbsp;'.HtmlToolKit::tagNoData('input', array('class'=>'submit',
                                                               'type' =>'submit',
                                                               'value'=> msg('Update')));
    $aContent .= HtmlToolKit::tagNoData('input',array('type' =>'hidden',
                                                     'name' =>'id',
                                                     'value'=>$aId));
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');

    // form to update the bibtex key
	$aContent .= HtmlToolKit::startTag('form', array( 'method' => 'get',
                                                    'id'     => 'new_bibtex_key',
                                                    'action' => 'bibindex.php',
                                                    'class'  => 'f_default_form',
                                                    'onsubmit' =>'return validate_new_bibtex_key("'.$_SESSION['i18n']->getLocale().'")'));
    $aContent .= HtmlToolKit::startTag('fieldset');
    $aContent .= HtmlToolKit::tag('label',msg('BibTeX Key:').'&nbsp;');
    $aContent .= HtmlToolKit::tagNoData('input',array('name' =>'bibtex_key',
                                                     'value'=>$aId));
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'hidden',
                                                    'name' =>'action',
                                                    'value'=>'update_key'));
    $aContent .= '&nbsp;'.HtmlToolKit::tagNoData('input', array('class'=>'submit',
                                                               'type' =>'submit',
                                                               'value'=> msg('Update')));
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'hidden',
                                                     'name' =>'id',
                                                     'value'=>$aId));
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');
    $aContent .= HtmlToolKit::tagNoData('br');

    // a tabbed interface
    $aContent .= HtmlToolKit::startTag('div');
    $aContent .= HtmlToolKit::startTag('ul', array('id'=>'tabnav'));
    $aContent .= HtmlToolKit::startTag('li');
    $aContent .= HtmlToolKit::tag('a',msg('BIBORB_OUTPUT_REQUIRED_FIELDS'),
                                 array('id'    =>'tab_required_ref',
                                       'class' =>'active',
                                       'href'  => 'javascript:toggle_tab_edit("required_ref")'));
    $aContent .= HtmlToolKit::closeTag('li');
    $aContent .= HtmlToolKit::startTag('li');
    $aContent .= HtmlToolKit::tag('a', msg('BIBORB_OUTPUT_OPTIONAL_FIELDS'),
                                 array('id'    =>'tab_optional_ref',
                                       'href'  => 'javascript:toggle_tab_edit("optional_ref")'));
    $aContent .= HtmlToolKit::closeTag('li');
    $aContent .= HtmlToolKit::startTag('li');
    $aContent .= HtmlToolKit::tag('a', msg('BIBORB_OUTPUT_ADDITIONAL_FIELDS'),
                                 array('id'    =>'tab_additional_ref',
                                       'href'  => 'javascript:toggle_tab_edit("additional_ref")'));
    $aContent .= HtmlToolKit::closeTag('li');
    $aContent .= HtmlToolKit::closeTag('ul');
    $aContent .= HtmlToolKit::closeTag('div');


    // form to update the different fields
	$aContent .= HtmlToolKit::startTag('form', array( 'method' => 'post',
                                                      'action' => 'bibindex.php',
                                                      'enctype'=> 'multipart/form-data',
                                                      'name'   => 'fields',
                                                      'id'     => 'f_bibtex_entry'));
    $aContent .= eval_php($aFields);
    $aContent .= HtmlToolKit::startTag('fieldset', array('class' => 'clean'));
    $aContent .= HtmlToolKit::tagNoData('input', array('class'=> 'submit',
                                                               'type' => 'submit',
                                                               'name' => 'cancel',
                                                               'value'=> msg('Cancel')));
    $aContent .= HtmlToolKit::tagNoData('input', array('name'=>'action',
                                                               'type' =>'hidden',
                                                               'value'=> 'update_entry'));
    $aContent .= '&nbsp;'.HtmlToolKit::tagNoData('input', array('class'=> 'submit',
                                                               'type' => 'submit',
                                                               'name' => 'ok',
                                                               'value'=> msg('Update')));
    $aContent .= HtmlToolKit::tagNoData('input', array('name' => 'mode',
                                                      'type' => 'hidden',
                                                      'value'=> 'operationresult'));    
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');

	// create the HTML page
	$aHtml = bibheader(array('onload'=>'javascript:toggle_element("additional")'));
	$aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
	$aTitle = msg('BIBINDEX_UPDATE_ENTRY_TITLE');
	$aHtml .= main($aTitle,$aContent);
	$aHtml .= html_close();
	echo $aHtml;
}

/**
 * bibindex_import
 * Interface to import references (bibtex file or textfields)
 */
function bibindex_import(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_IMPORT_TITLE");

    // general help message
    $aContent = msg("BIBINDEX_IMPORT_HELP");
    $aContent .= "<br/><br/>";

    // import from a BibTeX file
//    $aContent .= "<h3 style='padding:0;margin:0'>".msg("BIBINDEX_IMPORT_FILE_TITLE")."</h3>";
    $aContent .= "<form method='post' action='bibindex.php' enctype='multipart/form-data'>";
    $aContent .= "<fieldset style='border:solid 1px navy;' title='".msg("File")."'>";
    $aContent .= "<legend style='font-weight:bold;color:navy'>".msg("File")."</legend>";
    $aContent .= "<div style='text-align:left;'>";
    $aContent .= msg("BIBINDEX_IMPORT_FILE_DESC")."&nbsp;";
    $aContent .= "<input type='file' name='bibfile'/>";
    $aContent .= "<input type='hidden' name='mode' value='operationresult'/>";
    $aContent .= "<input type='hidden' name='action' value='import'/>&nbsp;";
    $aContent .= "<input class='submit' type='submit' value='".msg("Import")."'/>";
    $aContent .= "</div>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";
    $aContent .= "<br/>";

    // import from a BibTeX string
//    $aContent .= "<h3 style='padding:0;margin:0'>".msg("BIBINDEX_IMPORT_TXT_TITLE")."</h3>";
    $aContent .= "<form method='post' action='bibindex.php'>";
    $aContent .= "<fieldset style='border:solid 1px navy;text-align:center;' title='BibTeX'>";
    $aContent .= "<legend style='font-weight:bold;color:navy'>BibTeX</legend>";
    $aContent .= "<div style='text-align:left'>".msg("BIBINDEX_IMPORT_TXT_DESC")."</div>";
    $aContent .= "<textarea name='bibval' cols='55' rows='15'></textarea>";
    $aContent .= "<input type='hidden' name='mode' value='operationresult'/>";
    $aContent .= "<div style='text-align:center'>";
    $aContent .= "<input type='hidden' name='action' value='import'/>";
    $aContent .= "<input class='submit' type='submit' value='".msg("Import")."'/>";
    $aContent .= "</div>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";

    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    echo $aHtml;
}


/**
 * function bibindex_export_basket
 *
 */
function bibindex_export_basket()
{
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_EXPORT_BASKET");
    $aContent = "<span class='emphit'>".msg("")."</span>";
    // create the form to select which fields to export
    $aContent .= "<form action='bibindex.php' method='post'>";
    $aContent .= "<fieldset style='border:none;'>";
    $aContent .= "<div style='text-align:center'>";
    $aContent .= msg("Select an export format:")."&nbsp;";
    $aContent .= "<select size='1' name='export_format'>";
    $aContent .= "<option value='bibtex'>BibTeX</option>";
    $aContent .= "<option value='ris'>RIS</option>";
    $aContent .= "<option value='html'>HTML</option>";
    $aContent .= "<option value='docbook'>DocBook</option>";
    $aContent .= "</select>";
    $aContent .= "<input type='hidden' name='action' value='export_basket'/>";
    $aContent .= "<input type='submit' value='".msg("Select")."'/>";
    $aContent .= "</div>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";
    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    echo $aHtml;
}



/**
 * bibindex_export_basket_to_bibtex
 */
function bibindex_export_basket_to_bibtex(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_EXPORT_TO_BIBTEX_TITLE");
    $aContent = "<span class='emphit'>".msg("Select fields to include in the exported BibTeX:")."</span>";
    // create the form to select which fields to export
    $aContent .= "<form action='bibindex.php' method='post'>";
    $aContent .= "<fieldset style='border:solid 1px navy;'>";
    $aContent .= "<legend style='font-weight:bold;color:navy;'>".msg("Available BibTeX fields")."</legend>";
    $aContent .= "<table>";
    $aContent .= "<tbody>";
    $cpt = 0;
    for($i=0;$i<count($GLOBALS['bibtex_entries']);$i++){
        if(strcmp($GLOBALS['bibtex_entries'][$i],'id') != 0){
            $field = $GLOBALS['bibtex_entries'][$i];
            if($cpt == 0){
                $aContent .= "<tr>";
            }
            $aContent .= "<td title='$field'><input type='checkbox' name='$field'";
            if(!(array_search($field,$GLOBALS['fields_to_export']) === false)){
                $aContent .= " checked='checked' ";
            }
            $aContent .= " />".msg($field)."</td>";
            $cpt++;
            if($cpt == 6){
                $aContent .= "</tr>";
                $cpt = 0;
            }
        }
    }
    if($cpt != 0){
        while($cpt != 6){
            $cpt++;
            $aContent .= "<td/>";
        }
        $aContent .= "</tr>";
    }

    $aContent .= "</tbody>";
    $aContent .= "</table>";
    $aContent .= "<div style='text-align:center'>";
    $aContent .= "<input type='hidden' name='action' value='export'/>";
    $aContent .= "<input type='submit' value='".msg("Export")."'/>";
    $aContent .= "</div>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";
    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    echo $aHtml;
}

/**
 * bibindex_export_basket_to_html
 */
function bibindex_export_basket_to_html(){

	if($_SESSION['basket']->count_items() != 0){
		// basket not empty -> processing
		// get entries
		$entries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['basket']->items);

		// xslt transformation
		$xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
		$aParam = $GLOBALS['xslparam'];
		// hide basket actions
		$aParam['display_basket_actions'] = 'no';
		// hide edition/delete
		$aParam['mode'] = 'user';
		$aContent = $xsltp->transform($entries,load_file("./xsl/simple_html_output.xsl"),$aParam);
		$xsltp->free();

		// HTML output
		$aHtml = html_header(null,CSS_FILE,null);
		$aHtml .= $aContent;
		$aHtml .= html_close();
		echo $aHtml;
	}
	else{
		echo bibindex_display_basket();
	}
}


/**

*/
function bibindex_display_xpath_search()
{
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_XPATH_SEARCH_TITLE");

    //tabs
    $aContent = "<div id='search_tabs'>";
    $aContent .= "<ul id='tabnav'>";
    $aContent .= "<li><a href='bibindex.php?mode=displaysearch'>".msg("Simple Search")."</a></li>";
    $aContent .= "<li><a href='bibindex.php?mode=displayadvancedsearch'>".msg("Advanced Search")."</a></li>";
    $aContent .= "<li><a class='active' href='bibindex.php?mode=displayxpathsearch'>".msg("XPath Search")."</a></li>";
    $aContent .= "</ul>";
    $aContent .= "</div>";
    $aContent .= "<div class='search_content'>";
    $aContent .= "<h4 class='tool_name'>".msg("TOOL_XPATH_TITLE")."</h4>";
    $aContent .= "<div class='tool_help'>";
    $aContent .= msg("TOOL_XPATH_HELP");
    $aContent .= "</div>";
    $aContent .= "<form class='tool_form' method='get' action='bibindex.php' id='xpath_form' onsubmit='return validate_xpath_form(\"".$_SESSION['i18n']->getLocale()."\")'>";
    $aContent .= "<fieldset>";
    $aContent .= "<textarea cols='50' rows='5' name='xpath_query'>";
    if(array_key_exists('xpath_query',$_GET)){
        $aContent .= $_GET['xpath_query'];
    }
    else{
        $aContent .= "contains(*/bibtex:author, 'someone') and */bibtex:year=2004";
    }
    $aContent .= "</textarea><br/>";
    $aContent .= "<input type='hidden' name='mode' value='displayxpathsearch'/>";
    $aContent .= "<input type='submit' class='submit' value='".msg("Search")."'/>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";
    $aContent .= "</div>";

    // execute an Xpath query
    if(array_key_exists("xpath_query",$_GET)){
        // store the ids in session if we come from an other page.
        if(!isset($_GET['page'])){
            $_SESSION['ids'] = array_chunk($_SESSION['bibdb']->getIdsForXpathSearch($_GET['xpath_query']),$GLOBALS['max_ref']);
            $_GET['page'] = 0;
        }
        $flatids = flatten_array($_SESSION['ids']);
        if(count($flatids)>0){
            $entries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);

            $nb = count($flatids);
            $aParam = $GLOBALS['xslparam'];
            $aParam['bibindex_mode'] = "displayxpathsearch";
            $aParam['basketids'] = $_SESSION['basket']->items_to_string();
            $extraparam = "xpath_query=".urlencode($_GET['xpath_query']);
            $extraparam .= "&page=".$_GET['page'];
            // add all
            $start = "<div class='result_header'>";
            $start .= add_all_to_basket_div($flatids,"displayxpathsearch",$extraparam);
            $start .= "</div>";
            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"displayxpathsearch",$extraparam);
            $aParam['extra_get_param'] = $extraparam;
            if($nb==1){
                $aContent .= msg("One match.").$start;
                $aContent .= biborb_html_render($entries,$aParam);
            }
            else if($nb>1) {
                $aContent .= sprintf(msg("%d matches."),$nb).$start;
                $aContent .= biborb_html_render($entries,$aParam);
            }
        }
        else{
            $aContent .= msg("No match.");
        }
    }
    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    echo $aHtml;
}


/**
 * Display the Tool page. Mis functions over the current bibliographies.
 */
function bibindex_display_tools(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_TOOLS_TITLE");

    // Export the whole bibliography
    $aContent = "<h4 class='tool_name'>".msg("TOOL_EXPORT_TITLE")."</h4>";
    $aContent .= "<div class='tool_help'>";
    $aContent .= msg("TOOL_EXPORT_HELP");
    $aContent .= "</div>";
    $aContent .= "<form class='tool_form' method='post' ' action='bibindex.php'  id='export_form'>";
    $aContent .= "<fieldset>";
    $aContent .= "<select size='1' name='export_format'>";
    $aContent .= "<option value='bibtex'>BibTeX</option>";
    $aContent .= "<option value='ris'>RIS</option>";
    $aContent .= "<option value='docbook'>DocBook</option>";
    $aContent .= "</select>";
    $aContent .= "<input type='hidden' name='action' value='export_all'/>";
    $aContent .= "<input type='submit' value='".msg("Select")."'/>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";


    // Get BibTeX references according to a .aux LaTeX file.
    $aContent .= "<h4 class='tool_name'>".msg("TOOL_AUX2BIBTEX_TITLE")."</h4>";
    $aContent .= "<div class='tool_help'>";
    $aContent .= msg("TOOL_AUX2BIBTEX_HELP");
    $aContent .= "</div>";
    $aLocale = $_SESSION['i18n']->getLocale();
    $aContent .= "<form class='tool_form' method='post' enctype='multipart/form-data' action='bibindex.php'  onsubmit='return validate_bibtex2aux_form(\"{$aLocale}\")' id='bibtex2aux_form'>";
    $aContent .= "<fieldset>";
    $aContent .= "<input type='file' name='aux_file'/>";
    $aContent .= "<input type='hidden' name='action' value='bibtex_from_aux'/>";
    $aContent .= "&nbsp;<input type='submit' class='submit' value='".msg("Download")."'/>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";

    // Get a full copy of the bibliography (references + attached documents)
    $aContent .= "<h4 class='tool_name'>".msg("TOOL_GET_ARCHIVE_TITLE")."</h4>";
    $aContent .= "<div class='tool_help'>";
    $aContent .= msg("TOOL_GET_ARCHIVE_HELP");
    $aContent .= "</div>";
    $aContent .= "<form class='tool_form' method='post' action='bibindex.php'>";
    $aContent .= "<fieldset>";
    $aContent .= "<input type='hidden' name='action' value='get_archive'/>";
    $aContent .= "<input type='submit' class='submit' value='".msg("Download")."'/>";
    $aContent .= "</fieldset>";
    $aContent .= "</form>";

    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    echo $aHtml;
}

/**
 * Browse the bibliography using filters
 */
function bibindex_browse(){
    $aHtml = bibheader();
    $aHtml .= bibindex_menu($_SESSION['bibdb']->getFullName());
    $title = msg("BIBINDEX_BROWSE_TITLE");

    // filter history
    $aContent = "<div class='browse_history'>";
    $aContent .= " &nbsp;&#187;&nbsp;<a href='./bibindex.php?mode=browse&amp;start=0'>Start</a>";
    if(array_key_exists('browse_history',$_SESSION)){
        $cpt = 1;
        foreach($_SESSION['browse_history'] as $hist){
            $aContent .= "&nbsp;&#187;&nbsp;<a href='./bibindex.php?mode=browse&amp;start=$cpt'>".$hist['value']."</a>";
        }
    }
    $aContent .= "</div>";
    if(!isset($_GET['type']))
        $_GET['type'] = 'year';

    // filters available
    $aContent .= "<div class='browse'>";
    $aContent .= "<ul id='tabnav'>";
    $aContent .= "<li><a id='tab_years' ".($_GET['type'] == 'year' ? "class='active'": "")." href=\"javascript:display_browse('years');\">".msg("Years")."</a></li>";
    $aContent .= "<li><a id='tab_authors' ".($_GET['type'] == 'authors' ? "class='active'": "")." href=\"javascript:display_browse('authors');\">".msg("Authors")."</a></li>";
    $aContent .= "<li><a id='tab_series' ".($_GET['type'] == 'series' ? "class='active'": "")." href=\"javascript:display_browse('series');\">".msg("Series")."</a></li>";
    $aContent .= "<li><a id='tab_journals' ".($_GET['type'] == 'journals' ? "class='active'": "")." href=\"javascript:display_browse('journals');\">".msg("Journals")."</a></li>";
    $aContent .= "<li><a id='tab_groups' ".($_GET['type'] == 'groups' ? "class='active'": "")." href=\"javascript:display_browse('groups');\">".msg("Groups")."</a></li>";
    $aContent .= "</ul>";
    $aContent .= "</div>";

    $aContent .= "<div class='browse_items'>";

    // years
    // years are listed by decades
    $aContent .= "<ul id='years' style='display:".($_GET['type'] != 'year' ? "none": "block").";'>".msg("Existing years:");
    for($i=0;$i<count($_SESSION['misc']['years']);$i++){
        $year = $_SESSION['misc']['years'][$i];
        if(!isset($oldyear)){
            $oldyear = $year;
            $aContent .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=year&value=$year'>$year</a>";
        }
        else{
            if( floor($oldyear/10) != floor($year/10)){
                $aContent .= "</li><li>";
            }
            else{
                $aContent .= ", ";
            }
            $aContent .= "<a href='bibindex.php?mode=browse&action=add_browse_item&type=year&value=$year'>$year</a>";
        }
        $oldyear = $year;
        if($i==count($_SESSION['misc']['years'])-1){
            $aContent .= "</li>";
        }

    }
    $aContent .= "</ul>";

    // authors are listed by alphabetic order
    // one list item by letter of the alphabet
    $aContent .= "<ul id='authors' style='display:".($_GET['type'] != 'authors' ? "none": "block").";'>".msg("Existing authors:");

    for($i=0;$i<count($_SESSION['misc']['authors']);$i++){
        $author = remove_accents($_SESSION['misc']['authors'][$i]);
        if(!isset($oldauthor)){
            $oldauthor = $author;
            $aContent .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=author&value=$author'>$author</a>";
        }
        else{
            if($author[0] != $oldauthor[0]){
                $aContent .= "</li><li>";
            }
            else{
                $aContent .= ", ";
            }
            $aContent .= "<a href='bibindex.php?mode=browse&action=add_browse_item&type=author&value=$author'>$author</a>";
            $oldauthor = $author;
        }

        if($i == count($_SESSION['misc']['authors'])-1){
            $aContent .= "</li>";
        }
    }
    $aContent .= "</ul>";

    // series
    $aContent .= "<ul id='series' style='display:".($_GET['type'] != 'series' ? "none": "block").";'>".msg("Existing series:");
    foreach($_SESSION['misc']['series'] as $serie){
        $aContent .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=series&value=$serie'>$serie</a></li>";
    }
    $aContent .= "</ul>";

    // journal
    $aContent .= "<ul id='journals' style='display:".($_GET['type'] != 'journals' ? "none": "block").";'>".msg("Existing journals:");
    foreach($_SESSION['misc']['journals'] as $journal){
        $aContent .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=journal&value=$journal'>$journal</a></li>";
    }
    $aContent .= "</ul>";

    // groups
    $aContent .= "<ul id='groups' style='display:".($_GET['type'] != 'groups' ? "none": "block")."';>".msg("Existing groups:");
    foreach($_SESSION['misc']['groups'] as $group){
        $aContent .= "<li><a href='bibindex.php?mode=browse&action=add_browse_item&type=group&value=$group'>$group</a></li>";
    }
    $aContent .= "</ul>";
    $aContent .= "</div>";

    // store the ids in session if we come from an other page.
    // the bibtex keys are retreived from the database the first time that display_all is called
    if(!isset($_GET['page'])){
    	// split the array so that we display only $GLOBALS['max_ref']
        $_SESSION['ids'] = array_chunk($_SESSION['browse_ids'],$GLOBALS['max_ref']);
        // go to the first page
        $_GET['page'] = 0;
    }

    $flatids = flatten_array($_SESSION['ids']);

    if(isset($_GET['page']) || $_GET['start']>0){
        if(count($flatids)>0){
            // get the data of the references to display
            $entries = $_SESSION['bibdb']->getEntriesWithIds($_SESSION['ids'][$_GET['page']]);
            // set up XSLT parameters
            $aParam = $GLOBALS['xslparam'];
            $aParam['bibindex_mode'] = $_GET['mode'];
            $aParam['basketids'] = $_SESSION['basket']->items_to_string();
            $aParam['extra_get_param'] = "page=".$_GET['page'];
            $aHtml_content = biborb_html_render($entries,$aParam);

            // create the header: sort function + add all to basket
            $start = "<div class='result_header'>";
            /*if(DISPLAY_SORT){
                $start = sort_div($GLOBALS['sort'],$GLOBALS['sort_order'],$_GET['mode'],null).$start;
            }*/
            $start .= add_all_to_basket_div($flatids,$_GET['mode'],"sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."&amp;page=".$_GET['page']);
            $start .= "</div>";

            // create a nav bar to display entries
            $start .= create_nav_bar($_GET['page'],count($_SESSION['ids']),"browse","sort=".$GLOBALS['sort']."&amp;sort_order=".$GLOBALS['sort_order']."page=".$_GET['page']);
            $aContent .= $start.$aHtml_content;
        }
        else{
            $aContent .= msg("No entries.");
        }
    }



    $aHtml .= main($title,$aContent);
    $aHtml .= html_close();
    echo $aHtml;
}

/**
 * Generate the HTML biborb code of entries.
 * @param $entries Entries to render
 * @param $options Some option for the rendering
 */
function biborb_html_render($entries,$options)
{
    // init an XSLT processor
    $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
    // interpret LaTeX code in HTML
    $entries = latex_macro_to_html($entries);
    // do the transformation
    $aContent = $xsltp->transform($entries,FileToolKit::getContent("./xsl/biborb_output_sorted_by_id.xsl"),$options);
    // replace localized string
    $_SESSION['i18n']->localizeBiborbString($aContent);
    $xsltp->free();
    return $aContent;
}



?>