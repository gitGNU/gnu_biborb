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
 *
 * File: interface-index.php
 *
 * Description:
 *      Functions to generate the interface (index.php)
 *
 */


/**
 * Create the HTML header
 */
function indexHeader()
{
    $aHtmlHeaderData = array( 'title' => 'Biborb',
                              'stylesheet' => CSS_FILE,
                              'javascript' => './biborb.js');
    return HtmlToolKit::htmlHeader($aHtmlHeaderData);
}

/**
 * Create the page for authentication
 */
function index_login()
{
    $aHtml = indexHeader();
    $aHtml .= index_menu();
    $aTitle = msg('INDEX_MENU_LOGIN_TITLE');
    $aFormData = array('id' => 'login_form',
                       'action' => 'index.php',
                       'method' => 'post',
                       'onsubmit' => 'return validate_login_form(\"'.$_SESSION['i18n']->getLocale().'\")');
    $aContent = HtmlToolKit::startTag('form',$aFormData);
    $aContent .= HtmlToolKit::startTag('fieldset');
    $aContent .= HtmlToolKit::tag('legend',msg('Login'));
    $aContent .= HtmlToolKit::tag('label',msg('LOGIN_USERNAME'), array('for'=>'login'));
    $aContent .= HtmlToolKit::tagNoData('input', array('type' => 'text', 'name' => 'login', 'id' => 'login'));
    $aContent .= HtmlToolKit::tagNoData('br');
    $aContent .= HtmlToolKit::tag('label', msg('LOGIN_PASSWORD').':', array('label' => 'password'));
    $aContent .= HtmlToolKit::tagNoData('input', array('type' => 'password', 'id'=>'password', 'name'=>'mdp'));
    $aContent .= HtmlToolKit::tagNoData('br');    
    $aContent .= HtmlToolKit::tagNoData('input', array('type'=>'hidden', 'name'=>'action', 'value'=>'login'));    
    $aContent .= HtmlToolKit::tagNoData('input', array('type'=>'submit', 'value'=> msg("Login"), 'class'=>'submit'));        
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');

    $aHtml .= HtmlToolKit::main($aTitle, $aContent, $GLOBALS['error_or_message']['error']);
    $aHtml .= HtmlToolKit::htmlClose();

    return $aHtml;
}

/**
 * Display the welcome page
 * The text is loaded from ./data/index_welcome.txt
 */
function index_welcome()
{
    $aHtml = indexHeader();
    $aContent = $_SESSION['i18n']->getFile("index_welcome.txt");
    // get the version and the date
    $aStrToReplace = array( '$biborb_version' => BIBORB_VERSION,
                            '$date_release' => BIBORB_RELEASE_DATE);
    $aContent = strtr($aContent, $aStrToReplace);
    $aHtml .= index_menu();
    $aHtml .= HtmlToolKit::main('BibORB: BibTeX On-line References Browser',$aContent);
    $aHtml .= HtmlToolKit::htmlClose();

    return $aHtml;
}

/**
 * Create a form to add a new bibliography.
 */
function guiAddDatabase($availableBibs)
{
    $aJsArray = 'new Array(';
    $aIsFirstElem = true;
    foreach($availableBibs as $key => $name)
    {
        if (!$aIsFirstElem)
        {    
            $aJsArray .= ',';
        }
        
        $aJsArray .= '"'.$key.'"';
        $aIsFirstElem = false;
    }
    $aJsArray .= ')';        
        
    $aFormData = array('method'   => 'post',
                       'action'   => _PHP_SELF_,
                       'id'       => 'formAddBib');
    $aContent = HtmlToolKit::startTag('form',$aFormData);
    $aContent .= HtmlToolKit::startTag('fieldset');
    $aContent .= HtmlToolKit::tag('legend',msg('INDEX_CREATE_BIB_TITLE'));
    $aContent .= HtmlToolKit::tag('label',msg("INDEX_CREATE_BIBNAME"),array('for'=>'database_name'));
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'text','name'=>'database_name','id'=>'database_name'));
    $aContent .= HtmlToolKit::tagNoData('br');
    $aContent .= HtmlToolKit::tag('label',msg("INDEX_CREATE_DESCRIPTION"),array('for'=>'description'));
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'text','name'=>'description','id'=>'description'));
    $aContent .= HtmlToolKit::tagNoData('br');
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'hidden','name'=>'mode','value'=>'select'));
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'hidden','name'=>'action','value'=>'create'));
    $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'button','value'=>msg('Create'),'class'=>'submit','onclick'=> 'return checkAddBib('.$aJsArray.',"'.$_SESSION['i18n']->getLocale().'")'));
    $aContent .= HtmlToolKit::closeTag('fieldset');
    $aContent .= HtmlToolKit::closeTag('form');
    
    return $aContent;
}

/**
 * Display an help for the manager submenu. This help is loaded from a file.
 */
function index_manager_help()
{
    $aHtml = indexHeader();
    $aTitle = msg("INDEX_MANAGER_HELP_TITLE");
    $aContent = $_SESSION['i18n']->getFile("index_manager_help.txt");
    $aHtml .= index_menu();
    $aHtml .= HtmlToolKit::main($aTitle,$aContent);
    $aHtml .= HtmlToolKit::htmlClose();

    return $aHtml;
}

/**
 * Generic page to display the result of an operation.
 * Will only display information recorded into $error_or_message
 */
function index_result()
{
    $aHtml = indexHeader();
    $aHtml .= index_menu();
    $aHtml .= HtmlToolKit::main(msg("INDEX_RESULTS_TITLE"),null,
                  $GLOBALS['error_or_message']['error'],
                  $GLOBALS['error_or_message']['message']);
    $aHtml .= HtmlToolKit::htmlClose();

    return $aHtml;
}

/**
 * HTML gui to display the list of available bibliographies.
 * If the current user is an administrator, output is enriched with
 * elements to create and delete bibliographies.
 *
 * @param availableBibs An array of bibliographies ('shortName'=>'description')
 */
function guiAvailableBibs($availableBibs)
{
    $aContent = '';

    // if the user is admin, put everything in a form
    if ($_SESSION['user']->isAdmin())
    {
        $aFormData = array('method'=> 'post',
                           'action' => _PHP_SELF_,
                           'class' => 'available_bibliographies',
                           'id'    => 'formDeleteBibs');
        $aContent .= HtmlToolKit::startTag('form',$aFormData);
        $aContent .= HtmlToolKit::startTag('fieldset',array('class' => 'available_bibliographies'));
    };

    // header of the table
    // table content is   shortName | description  ( | checkbox)
    $aContent .= HtmlToolKit::startTag('table',array('class' => 'available_bibliographies'));
    $aContent .= HtmlToolKit::startTag('thead');
    $aContent .= HtmlToolKit::startTag('tr');
    $aContent .= HtmlToolKit::tag('th',msg('INDEX_AVAILABLE_BIBS_COL_BIBNAME'));
    $aContent .= HtmlToolKit::tag('th',msg('INDEX_AVAILABLE_BIBS_COL_BIBDESCRIPTION'));
    
    if ($_SESSION['user']->isAdmin())
    {
        // add a checkbox to select bibliographies for batch process
        $aContent .= HtmlToolKit::tagNoData('th');
    }
    
    $aContent .= HtmlToolKit::closeTag('tr');
    $aContent .= HtmlToolKit::closeTag('thead');
    $aContent .= HtmlToolKit::startTag('tbody');

    foreach($availableBibs as $aName => $aFullName)
    {
        $aDescription = FileToolKit::getContent("./bibs/{$aName}/description.txt");
        $aContent .= HtmlToolKit::startTag('tr',array('id'=>$aName));
        $aContent .= HtmlToolKit::startTag('td');
        $aContent .= HtmlToolKit::tag('a',$aFullName,array('class'=>'bibname',
                                                           'href' => './biborb.php?mode=bib&amp;bibname='.$aName));
        $aContent .= HtmlToolKit::closeTag('a');
        $aContent .= HtmlToolKit::closeTag('td');
        $aContent .= HtmlToolKit::startTag('td');
        $aContent .= HtmlToolKit::tag('span',$aDescription,array('class'=>'bib_description'));
        $aContent .= HtmlToolKit::closeTag('td');
        if ($_SESSION['user']->isAdmin())
        {
            $aContent .= HtmlToolKit::startTag('td');
            $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'checkbox','name'=>'bibs[]','value'=>$aName,
                                                              'onclick'=>'setRowColor(this,"selected")'));
            $aContent .= HtmlToolKit::closeTag('td');
        }
        $aContent .= HtmlToolKit::closeTag('tr');
    }
    $aContent .= HtmlToolKit::closeTag('tbody');
    $aContent .= HtmlToolKit::closeTag('table');

    if ($_SESSION['user']->isAdmin())
    {
        // config to delete selected bibliographies
        $aContent .= HtmlToolKit::tagNoData('input', array('type'=>'hidden','value'=>'delete','name'=>'action'));
        $aContent .= HtmlToolKit::tagNoData('input',array('type'=>'hidden','name'=>'mode','value'=>'select'));
        $aContent .= HtmlToolKit::closeTag('fieldset');
        $aContent .= HtmlToolKit::startTag('fieldset',array('id'=>'f_delete_bibliographies'));
        $aContent .= HtmlToolKit::tag('legend',msg("Delete bibliographies"));
        $aContent .= HtmlToolKit::tagNoData('input', array('type'=>'button',
                                                           'value'=> msg("Delete selected bibliographies"),
                                                           'onclick' => 'return checkDeleteBibs("'.$_SESSION['i18n']->getLocale().'")'));
        $aContent .= HtmlToolKit::closeTag('fieldset');
        $aContent .= HtmlToolKit::closeTag('form');
    }
    
    return $aContent;
}


/**
 * List of available bibliographies.
 */
function guiBibsManager()
{
    $aHtml = indexHeader();
    $aTitle = msg("INDEX_AVAILABLE_BIBS_TITLE");
    $aHtml .= index_menu();

    $aContent = '';
    if ($_SESSION['errorManager']->hasErrors())
    {   
        $aContent .= $_SESSION['errorManager']->outputErrors() ;
    }

    $aDbNames = $_SESSION['DbManager']->getDbNames();
    if (!empty($aDbNames))
    {        
        $aContent .= guiAvailableBibs($aDbNames);
    }
    else
    {
        $aContent .= msg("No bibliographies defined.");
    }
    
        
    if ($_SESSION['user']->isAdmin())
    {
        $aContent .= guiAddDatabase();
    }
    if ($_SESSION['errorManager']->hasWarnings())
    {
        $aContent .= $_SESSION['errorManager']->outputWarnings();
    }
    
    $aHtml .= HtmlToolKit::main($aTitle,$aContent);
    $aHtml .= HtmlToolKit::htmlClose();
    return $aHtml;
}

/**
 * Create the menu for each page generated. It is placed into a <div> tag of ID 'menu'.
 */
function index_menu()
{
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
    $html .= "<li><a title=\"".msg("INDEX_MENU_WELCOME_HELP")."\" href='index.php?mode=welcome'>".msg("INDEX_MENU_WELCOME")."</a>";
    $html .= "<ul>";
    $html .= "<li><a title='".msg("INDEX_MENU_BIBS_LIST_HELP")."' href='index.php?mode=select'>".msg("INDEX_MENU_BIBS_LIST")."</a></li>";
    $html .= "</ul></li>";

    // Second menu item:
    // -> Manager
    //      | -> Login              (if not administrator)
    //      | -> Add a bibliography (if administrator)
    //      | -> Delete a bibliography (if administrator)
    //      | -> Logout     (if administrator and $disable_authentication set to false)
    $html .= "<li><a title='".msg("INDEX_MENU_MANAGER_HELP")."' href='index.php?mode=manager_help'>".msg("INDEX_MENU_MANAGER")."</a>";
    $html .= "<ul>";
    if(!DISABLE_AUTHENTICATION && !array_key_exists('user',$_SESSION)){
        $html .= "<li><a title=\"".msg("INDEX_MENU_LOGIN_HELP")."\" href='index.php?mode=login'>".msg("INDEX_MENU_LOGIN")."</a></li>";
    }
//    if($_SESSION['user_is_admin']){
//        $html .= "<li><a title='".msg("INDEX_MENU_ADD_BIB_HELP")."' class='admin' href='index.php?mode=add_database'>".msg("INDEX_MENU_ADD_BIB")."</a></li>";
//        $html .= "<li><a title='".msg("INDEX_MENU_DELETE_BIB_HELP")."' class='admin' href='index.php?mode=delete_database'>".msg("INDEX_MENU_DELETE_BIB")."</a></li>";
//    }
    if(!DISABLE_AUTHENTICATION && array_key_exists('user',$_SESSION)){
        $html .= "<li>";
        $html .= "<a href='index.php?mode=preferences' title='".msg("INDEX_MENU_PREFERENCES_HELP")."' >".msg("INDEX_MENU_PREFERENCES")."</a>";
        $html .= "</li>";
    }
    if ( !DISABLE_AUTHENTICATION && array_key_exists('user',$_SESSION))
    {
        $html .= "<li><a title='".msg("INDEX_MENU_LOGOUT_HELP")."' href='index.php?mode=welcome&amp;action=logout'>".msg("INDEX_MENU_LOGOUT")."</a></li>";
    }
    $html .= "</ul>";
    $html .= "</li>";
    $html .= "</ul>";

    // Display language selection if needed & and if the user is not logged in.
    if(DISPLAY_LANG_SELECTION && !array_key_exists("user",$_SESSION)){
        $aSelectAttribute = array( 'name' => 'lang',
                                   'onchange' => 'javascript:changeLangForIndex(this.value)');
        $html .= "<form id='language_form' action='index.php' method='get'>";
        $html .= "<fieldset>";
        $html .= "<label for='lang'>".msg("Language:")."</label>";
        $html .= HtmlToolKit::selectTag($aSelectAttribute, $_SESSION['i18n']->getLocales(), $_SESSION['i18n']->getLocale());
        $html .= "<input type='hidden' name='action' value='select_lang'/>";
        $html .= "<noscript><div><input class='submit' type='submit' value='".msg("Select")."'/></div></noscript>";
        $html .= "</fieldset>";
        $html .= "</form>";
    }
    $html .= "</div>";

    return $html;
}
/**
 * Display preferences.
 */
function index_preferences()
{
    $aHtml = indexHeader();
    $aHtml .= index_menu();
    if (isset($GLOBALS['message']))
    {
        $aHtml .= HtmlToolKit::main(msg("PREFERENCES_TITLE"),pref_content(),null,$GLOBALS['message']);
    }
    else
    {
        $aHtml .= HtmlToolKit::main(msg("PREFERENCES_TITLE"),pref_content());
    }
    $aHtml .= HtmlToolKit::htmlClose();

    return $aHtml;
}

/**
 * Preferences panel
 * Generate the HTML content for the preference panel.
 */
function pref_content()
{
    // load the preferences of the current user
    $pref = $_SESSION['auth']->get_preferences($_SESSION['user']);

    $content = "<form id='preferences' method='post' action='index.php'>";
    $content .= "<fieldset>";
    $content .= "<legend>".msg("Preferences")."</legend>";
    $content .= "<table>";

    // CSS File
    //$content .= "<tr>";
    //$content .= "<td>".msg("Select a CSS stype.")."</td>";
    //$content .= "<td><select name='css_file'>";
    //if($pref['css_file']=='style.css'){
    //    $content .= "<option value='style.css' selected='selected'>".msg("Default")."</option>";
    //}
    //$content .= "</select></td>";
    //$content .= "</tr>";

    // Default language
    $content .= "<tr>";
    $content .= "<td>".msg("Select your language.")."</td>";
    $content .= "<td>".lang_html_select($pref['default_language'],'default_language')."</td>";
    $content .= "</tr>";

    // Default database
    $content .= "<tr>";
    $content .= "<td>".msg("Select the default database to open once logged in.")."</td>";
    $names = get_databases_names();
    $content .= "<td>";
    $content .= HtmlToolKit::selectTag(array( 'name' => 'default_database'),
                                       $names,
                                       $pref['default_database']);
    $content = "</td>";
    $content .= "<tr/>";

    // Display images
    $content .= "<tr>";
    $content .= "<td>".msg("Display icons commands.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_images' value='yes' ".($pref['display_images'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_images' value='no' ".($pref['display_images'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Display text
    $content .= "<tr>";
    $content .= "<td>".msg("Display text commands.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_txt' value='yes' ".($pref['display_txt'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_txt' value='no' ".($pref['display_txt'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Display abstract
    $content .= "<tr>";
    $content .= "<td>".msg("Display abstract.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_abstract' value='yes' ".($pref['display_abstract'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_abstract' value='no' ".($pref['display_abstract'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Warn before deleting
    $content .= "<tr>";
    $content .= "<td>".msg("Warn before deleting.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='warn_before_deleting' value='yes' ".($pref['warn_before_deleting'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='warn_before_deleting' value='no' ".($pref['warn_before_deleting'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Display sort forms
    $content .= "<tr>";
    $content .= "<td>".msg("Display sort functions.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_sort' value='yes' ".($pref['display_sort'] == "yes" ? "checked='checked'" : "").">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_sort' value='no' ".($pref['display_sort'] == "no" ? "checked='checked'" : "").">".msg("No")."</input>";
    $content .= "</td></tr>";

    // Sort id
    $content .= "<tr>";
    $content .= "<td>".msg("Default sort attribute.")."</td>";
    $content .= "<td>";
    $sortMethods = array_flip(array_map("msg",array_flip($GLOBALS['sort_values'])));
    $content .= HtmlToolKit::selectTag(array( 'name' => 'default_sort'),
                                       array_combine($sortMethods, $sortMethods),
                                       $pref['default_sort']);
    $content .= "</td>";
    $content .= "</tr>";

    // sort order
    $content .= "<tr>";
    $content .= "<td>".msg("Default sort order.")."</td>";
    $content .= "<td>";
    $sortOrder = array( msg('ascending') => 'ascending',
                        msg('descending') => 'descending');
    $content .= HtmlToolKit::selectTag( array('name' => 'default_sort_order'),
                                        $sortOrder,
                                        $pref['default_sort_order']);
    $content .= "</td>";
    $content .= "</tr>";

    // max ref by pages
    $content .= "<tr>";
    $content .= "<td>".msg("Number of references by page.")."</td>";
    $content .= "<td><input size='3' name='max_ref_by_page' value='".$pref['max_ref_by_page']."'/></td>";
    $content .= "</tr>";

    // shelf mode
    $content .= "<tr>";
    $content .= "<td>".msg("Display shelf actions.")."</td>";
    $content .= "<td>";
    $content .= "<input type='radio' name='display_shelf_actions' value='yes' ".($pref['display_shelf_actions'] == "yes" ? "checked='checked'" : "" ).">".msg("Yes")."</input>";
    $content .= "<input type='radio' name='display_shelf_actions' value='no' ".($pref['display_shelf_actions'] == "no" ? "checked='checked'" : "" ).">".msg("No")."</input>";
    $content .= "</td></tr>";

    $content .= "</table>";
    $content .= "<input type='hidden' name='action' value='update_preferences'/>";
    $content .= "<div style='text-align:center'>";
    $content .= "<input class='submit' type='submit' value='".msg("Update")."'/>";
    $content .= "</div>";
    $content .= "</fieldset>";
    $content .= "</form>";
    return $content;
}
?>