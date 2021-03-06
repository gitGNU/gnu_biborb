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
 * File: bibindex.php
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

include('./config.php'); // globals definitions
include('./config.misc.php');
include('./php/bibtex.php');
include('./php/functions.php'); // functions
include('./php/basket.php'); // basket functions
include('./php/proxyDbManager.php');
include('./php/biborbdb.php'); // database
include('./php/interface-bibindex.php'); // generate interface
include('./php/auth.php'); // authentication
include('./php/third_party/Tar.php'); // Create a tar.gz archive
include('./php/error.php'); // error handling
include('./php/i18nToolKit.php');       // load i18n functions

/**
 * Session
 */
session_cache_limiter('nocache');
session_name("SID");
session_start();

// Set the error_handler
//set_error_handler("biborb_error_handler");

/**
 * Set the DbManager object.
 */
if ( !isset($_SESSION['DbManager']) ||
     !is_object($_SESSION['DbManager']))
{
    $_SESSION['DbManager'] = new DbManager();
}

/**
 * Set the errorManager
 */
if ( !isset($_SESSION['errorManager']) ||
     !is_object($_SESSION['errorManager']))
{
    $_SESSION['errorManager'] = new ErrorManager();
}
$_SESSION['errorManager']->_warningStack = array();

/*
    i18n, choose default lang if not set up
    Try to detect it from the session, browser or fallback to default.
 */
if ( !isset($_SESSION['i18n']) ||
     !is_object($_SESSION['i18n']))
{
    $aPrefLang = i18nToolKit::getPreferedLanguage();
    $_SESSION['i18n'] = new i18nToolKit($aPrefLang, DEFAULT_LANG);
}

/*
    Load the locale if asked to change it.
 */
if (isset($_GET['language']) &&
    $_GET['language'] != $_SESSION['i18n']->getLocale())
{
    $_SESSION['i18n']->loadLocale($_GET['language']);
}

/*
    Global variables to store an error message or a standard message.
 */
$error = null;
$message = null;

/*
    Display an error if there is no active bibtex database
 */
if(!array_key_exists('bibdb',$_SESSION) && !array_key_exists('bibname',$_GET)){
    trigger_error("Bibliography's name is not set!",ERROR);
}

/*
    If the basket doesn't exists, create it.
 */
if(!isset($_SESSION['basket'])){
    $_SESSION['basket'] = new Basket();
}

/*
    If the session variable 'bibdb' is not set, get the bibliography name from
    GET variables and create a new Biborb_Database.
 */
$update_auth = FALSE;
if(!array_key_exists('update_authorizations',$_SESSION)){
    $update_auth = TRUE;
}

/*
    Set the authorization levels
 */
if(!DISABLE_AUTHENTICATION){
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
            $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry("",$_SESSION['bibdb']->name());
        }
        else{
            $_SESSION['user_can_add'] = $_SESSION['auth']->can_add_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }

        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry("",$_SESSION['bibdb']->name());
        }
        else{
            $_SESSION['user_can_delete'] = $_SESSION['auth']->can_delete_entry($_SESSION['user'],$_SESSION['bibdb']->name()) || $_SESSION['user_is_admin'];
        }

        if(!array_key_exists('user',$_SESSION)){
            $_SESSION['user_can_modify'] = $_SESSION['auth']->can_modify_entry("",$_SESSION['bibdb']->name());
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


// user preferences
if(array_key_exists('user_pref',$_SESSION)){
    $max_ref = $_SESSION['user_pref']['max_ref_by_page'];
}
else{
    $max_ref = MAX_REFERENCES_BY_PAGE;
}

//abstract
if(array_key_exists('user_pref',$_SESSION)){
    $abst = $_SESSION['user_pref']['display_abstract'] == "yes";
}
else{
    $abst = array_key_exists('abstract',$_GET) ? $_GET['abstract'] : DISPLAY_ABSTRACT;
}

// sort
$display_sort = DISPLAY_SORT;
$sort = DEFAULT_SORT;
$sort_order = DEFAULT_SORT_ORDER;

// sort order
if(array_key_exists('user_pref',$_SESSION)){$display_sort = $_SESSION['user_pref']['display_sort'];}
// sort ID
if(array_key_exists('sort',$_GET)){$sort = $_GET['sort'];}
if(array_key_exists('sort',$_POST)){$sort = $_POST['sort'];}
else if(array_key_exists('user_pref',$_SESSION)){$sort = $_SESSION['user_pref']['default_sort'];}
// sort order

if(array_key_exists('sort_order',$_POST)){$sort_order = $_POST['sort_order'];}
else if(array_key_exists('user_pref',$_SESSION)){$sort_order = $_SESSION['user_pref']['default_sort_order'];}


/**
 * Create the bibdb object if necessary
 */
if (isset($_GET['bibname']) || !isset($_SESSION['bibdb']) || !is_object($_SESSION['bibdb']))
{
    if (!isset($_SESSION['bibdb']) || !is_object($_SESSION['bibdb']))
    {
        // new db
        $_SESSION['bibdb'] = new BibORB_Database($_GET['bibname'],GEN_BIBTEX);
        $_SESSION['bibdb']->set_BibORB_fields($GLOBALS['bibtex_entries']);
        $_SESSION['bibdb']->setSortMethod($sort);
        $_SESSION['bibdb']->setSortOrder($sort_order);
        $_SESSION['basket']->reset();
    }
    else if ($_SESSION['bibdb']->getName() != $_GET['bibname'])
    {
        $_SESSION['bibdb'] = new BibORB_Database($_GET['bibname'],GEN_BIBTEX);
        $_SESSION['bibdb']->set_BibORB_fields($GLOBALS['bibtex_entries']);
        $_SESSION['basket']->reset();
    }
    $update_auth = TRUE;
}

// Update the sort method if necessary
if (isset($_GET['sort']) && $_GET['sort'] != $_SESSION['bibdb']->getSortMethod())
{   
    $_SESSION['bibdb']->setSortMethod($_GET['sort']);
    unset($_GET['page']);// to force reload of ids
}
if (isset($_GET['sort_order']) && $_GET['sort_order'] != $_SESSION['bibdb']->getSortOrder())
{
    $_SESSION['bibdb']->setSortOrder($_GET['sort_order']);
    unset($_GET['page']); // to force reload of ids
}






$display_images = DISPLAY_IMAGES;
$display_txt = DISPLAY_TEXT;
$display_shelf_actions = SHELF_MODE;
if(array_key_exists('user_pref',$_SESSION)){
    $display_images = ($_SESSION['user_pref']['display_images'] == "yes");
    $display_txt = $_SESSION['user_pref']['display_txt'] == "yes";
    $display_shelf_actions = $_SESSION['user_pref']['display_shelf_actions'] == "yes";
}

// global XSL parameters
$xslparam = array(  'bibname' => $_SESSION['bibdb']->getName(),
                    'bibnameurl' => $_SESSION['bibdb']->getXmlFileName(),
                    'display_images' => $display_images,
                    'display_text' => $display_txt,
                    'abstract' => $abst,
                    'display_add_all'=> 'true',
                    'sort' => $_SESSION['bibdb']->getSortMethod(),
                    'sort_order' => $_SESSION['bibdb']->getSortOrder(),
                    'can_modify' => $_SESSION['user_can_modify'] || $_SESSION['user_is_admin'],
                    'can_delete' => $_SESSION['user_can_delete'] || $_SESSION['user_is_admin'],
                    'shelf-mode' => $display_shelf_actions,
                    'biborb_xml_version' => BIBORB_XML_VERSION);

/**
 * Action are given by GET/POST method.
 * Analyse the URL to do the corresponding action.
 */

// GET action
if(isset($_GET['action'])){
    switch($_GET['action']){

        /*
            Select the GUI language
         */
        case 'select_lang':
            $_SESSION['language'] = $_GET['lang'];
            load_i18n_config($_SESSION['language']);
            break;

        /*
            Add an item to the basket
         */
        case 'add_to_basket':
            if(!isset($_GET['id'])){
                trigger_error("Trying to add a null value in basket!",ERROR);
            }
            else{
                $_SESSION['basket']->add_items(explode("*",$_GET['id']));
            }
        break;

        /*
            Delete an entry from the basket
         */
        case 'delete_from_basket':
            if(!isset($_GET['id'])){
                trigger_error("Trying to remove a null value from basket!",ERROR);
            }
            else{
                $_SESSION['basket']->remove_item($_GET['id']);
            }
            break;

        /*
            Reset the basket
         */
        case 'resetbasket':
            $_SESSION['basket']->reset();
            break;

        /*
            Delete an entry from the database
         */
        case 'delete':

            $aId = isset($_GET['id']) ? $_GET['id'] : null;

            // check that there is an id
            if (!$aId)
            {
                trigger_error('BibTeX key not set. Can not remove a reference from the database.',ERROR);
            }

            // check we have the authorization to delete
            if (!array_key_exists('user_can_delete',$_SESSION) || !$_SESSION['user_can_delete'])
            {
                trigger_error('You are not authorized to delete references!',ERROR);
            }

            $confirm = FALSE;
            if (array_key_exists('confirm_delete',$_GET))
            {
                $confirm = (strcmp($_GET['confirm_delete'],msg('Yes')) == 0);
            }

            $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
            // save the bibtex entry to show which entry was deleted
            $aXml = $_SESSION['bibdb']->getEntryWithId($aId);
            $aBibtex = $aXsltp->transform($aXml,FileToolKit::getContent('./xsl/xml2bibtex.xsl'));
            if (!WARN_BEFORE_DELETING || $confirm)
            {
                // delete it
                $_SESSION['bibdb']->deleteEntry($aId);
                // update message
                $message = sprintf(msg('The following entry was deleted: <pre>%s</pre>'),$aBibtex);
                // if present, remvove entries from the basket
                $_SESSION['basket']->remove_item($aId);
                $_GET['mode'] = 'operationresult';
            }
            else if(array_key_exists('confirm_delete',$_GET) && strcmp($_GET['confirm_delete'],msg('No')) == 0)
            {
                $_GET['mode'] = 'welcome';
            }
            else
            {
                $message = sprintf(msg('Delete this entry? <pre>%s</pre>'),$aBibtex);
                $message .= "<form action='bibindex.php' method='get' style='margin:auto;'>";
                $message .= "<fieldset style='border:none;text-align:center'>";
                $message .= "<input type='hidden' name='action' value='delete'/>";
                $message .= "<input type='hidden' name='id' value='$aId'/>";
                $message .= "<input type='submit' name='confirm_delete' value='".msg('No')."'/>";
                $message .= "&nbsp;";
                $message .= "<input type='submit' name='confirm_delete' value='".msg('Yes')."'/>";
                $message .= "</fieldset>";
                $message .= "</form>";

                $_GET['mode'] = 'operationresult';
            }
            $aXsltp->free();
            break;

        /*
            Add entries in the basket to a given group
         */
        case 'add':
            if(isset($_GET['groupvalue'])){
                $gval = htmlentities(trim($_GET['groupvalue']));
            }
            if(isset($_GET['newgroupvalue'])){
                $gval = htmlentities(trim($_GET['newgroupvalue']));
            }
            if(!isset($gval)){
                trigger_error(msg("No group specified!"),ERROR);
            }
            else if($gval != ""){
                $_SESSION['bibdb']->add_to_group($_SESSION['basket']->items,$gval);
            }
            break;

        /*
         * Reset the group field of entries in the basket.
         */
        case 'reset':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify']){
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->reset_groups($_SESSION['basket']->items);
            break;

        /*
         * Logout
         */
        case 'logout':
            $_SESSION['user_can_add'] = FALSE;
            $_SESSION['user_can_delete'] = FALSE;
            $_SESSION['user_can_modify'] = FALSE;
            $_SESSION['user_is_admin'] = FALSE;
            unset($_SESSION['user']);
            unset($_SESSION['user_pref']);
            break;

        /*
         * Change the BibTeX type of an entry
         */
        case 'update_type':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify'])
            {
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->changeType($_GET['id'],$_GET['bibtex_type']);
            $_GET['mode']='update';
            break;

        /*
         * Change the BibTeX key of a reference
         */
        case 'update_key': // update the BibTeX key of a reference
            // check we have the authorization to modify
            if (!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify'])
            {
                trigger_error('You are not authorized to modify references!',ERROR);
            }
            $aOldId = $_GET['id'];
            $aNewId = $_GET['bibtex_key'];
            if (!$_SESSION['bibdb']->is_bibtex_key_present($aNewId))
            {
                $_SESSION['bibdb']->changeId($aOldId,$aNewId);
                $_GET['mode'] = 'update';
                $_GET['id'] = $aNewId;
                // change the value in the basket
                $_SESSION['basket']->remove_item($aOldId);
                $_SESSION['basket']->add_item($aNewId);
            }
            else
            {
                $error = sprintf(msg('BibTeX key <code>%s</code> already exists.'),$aNewId);
                $_GET['mode'] = 'operationresult';
            }
            break;

        case 'delete_basket':

            // check we have the authorization to delete
            if(!array_key_exists('user_can_delete',$_SESSION) || !$_SESSION['user_can_delete']){
                trigger_error("You are not authorized to delete references!",ERROR);
            }

            $confirm = FALSE;
            if(array_key_exists('confirm_delete',$_GET)){
                $confirm = (strcmp($_GET['confirm_delete'],msg("Yes"))==0);
            }
            $ids_to_remove = $_SESSION['basket']->items;
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
            $xml_content = $_SESSION['bibdb']->entries_with_ids($ids_to_remove);

            if(!WARN_BEFORE_DELETING || $confirm){
                $_SESSION['bibdb']->delete_entries($ids_to_remove);
                // update message
                $bibtex = $xsltp->transform($xml_content,FileToolKit::getFileContents("./xsl/xml2bibtex.xsl"));
                $message = sprintf(msg("The following entries were deleted: <pre>%s</pre>"),$bibtex);
                $_SESSION['basket']->reset();
                $_GET['mode'] = "operationresult";
            }
            else if(array_key_exists('confirm_delete',$_GET) && strcmp($_GET['confirm_delete'],msg("No")) == 0){
                $_GET['mode'] = "welcome";
            }
            else{
                $html_entries = biborb_html_render($xml_content,$GLOBALS['xslparam']);
                $message = msg("Delete the following entries?");
                $message .= $html_entries;
                $message .= "<form action='bibindex.php' method='get' style='margin:auto;'>";
                $message .= "<fieldset style='border:none;'>";
                $message .= "<input type='hidden' name='action' value='delete_basket'/>";
                $message .= "<input type='submit' name='confirm_delete' value='".msg("No")."'/>";
                $message .= "<input type='submit' name='confirm_delete' value='".msg("Yes")."'/>";
                $message .= "</fieldset>";
                $message .= "</form>";
        		  $_GET['mode'] = "operationresult";
            }
            $xsltp->free();
            break;

        /*
            Shelf mode: update the owner ship
         */
        case 'update_ownership':
            // check we have the authorization to modify
            if (!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify'])
            {
                trigger_error('You are not authorized to modify references!',ERROR);
            }
            $_SESSION['bibdb']->changeOwnership($_GET['id'], $_GET['ownership']);
            break;

        /*
            Shelf mode: update the read status of a reference
         */
        case 'update_readstatus':
            // check we have the authorization to modify
            if (!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify'])
            {
                trigger_error("You are not authorized to modify references!",ERROR);
            }
            $_SESSION['bibdb']->changeReadStatus($_GET['id'], $_GET['readstatus']);
            break;

        /*
            Add a browse item.
        */
        case 'add_browse_item':
            if ( isset($_GET['type']) && isset($_GET['value']))
            {
                $aType = $_GET['type'];
                $aValue = $_GET['value'];
                $aFound = false;
                $aCpt = 0;
                if (isset($_SESSION['browse_history']))
                {
                    for ($aCpt=0; $aCpt< count($_SESSION['browse_history']) && !$aFound; $aCpt++)
                    {
                        $aFound = $_SESSION['browse_history'][$aCpt]['type'] == $aType;
                    }
                }

                if($aFound)
                {
                    $_SESSION['browse_history'][$aCpt-1]['value'] = $aValue;
                    $_GET['start'] = $aCpt;
                    array_splice($_SESSION['browse_history'],$aCpt);
                    /*for($i=$cpt;$i<count($_SESSION['browse_history']);$i++){
                        unset($_SESSION['browse_history'][$i]);
                    }*/
                    $_SESSION['browse_ids'] = $_SESSION['bibdb']->getAllIds();
                    for ($i=0; $i < count($_SESSION['browse_history']); $i++)
                    {
                        $_SESSION['browse_ids'] = $_SESSION['bibdb']->filter($_SESSION['browse_ids'],$aType,$aValue);
                    }
                }
                else
                {
                    $_SESSION['browse_history'][] = array('type'=>$aType,'value'=>$aValue);
                    $_SESSION['browse_ids'] = $_SESSION['bibdb']->filter($_SESSION['browse_ids'],$aType,$aValue);
                }
                $_GET['start'] = count($_SESSION['browse_history']);
            }
            break;

        default:
            break;
    }
}

// analyse POST
if (isset($_POST['action']))
{
    switch($_POST['action'])
    {
        /*
            Add an entry to the database
         */
        case 'add_entry':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_add',$_SESSION) || !$_SESSION['user_can_add'])
            {
                trigger_error("You are not authorized to add references!",ERROR);
            }
            if (isset($_POST['ok']))
            {
                $res = $_SESSION['bibdb']->add_new_entry($_POST);

                if($res['added'])
                {
                    $message = msg("ENTRY_ADDED_SUCCESS")."<br/>";
                    $entry = $_SESSION['bibdb']->getEntryWithId($res['id']);
                    $param = $GLOBALS['xslparam'];
                    $param['bibindex_mode'] = "displaybasket";
                    $param['mode'] = "user";
                    $message .= biborb_html_render($entry,$param);
                    $error = $res['message'];
                }
                else{
                    $error = $res['message'];
                }
            }
            else{
                $_GET['mode'] = 'welcome';
            }
            break;

        /*
         * Update a reference
         */
        case 'update_entry':
            if (isset($_POST['ok']))
            {
                // check we have the authorization to modify
                if (!array_key_exists('user_can_modify',$_SESSION) || !$_SESSION['user_can_modify'])
                {
                    trigger_error("You are not authorized to modify references!",ERROR);
                }
                $aRes = $_SESSION['bibdb']->updateEntry($_POST);
                if ($aRes['updated'])
                {
                    $message = msg('The following entry was updated:').'<br/>';
                    $aEntry = $_SESSION['bibdb']->getEntryWithId($aRes['id']);
                    $aParam = $GLOBALS['xslparam'];
                    $aParam['bibindex_mode'] = 'displaybasket';
                    $aParam['mode'] = 'user';
                    $message .= biborb_html_render($aEntry,$aParam);
                    $error = $aRes['message'];
                }
                else
                {
                    $error = $aRes['message'];
                }
            }
            else
            {
                $_GET['mode'] = 'welcome';
            }
            break;

        /*
            Import bibtex entries.
        */
        case 'import':
            // check we have the authorization to modify
            if(!array_key_exists('user_can_add',$_SESSION) || !$_SESSION['user_can_add']){
                trigger_error("You are not authorized to add references!",ERROR);
            }
            // Error if no value given
            if((!array_key_exists('bibfile',$_FILES) || !file_exists($_FILES['bibfile']['tmp_name'])) && !array_key_exists('bibval',$_POST)){
                trigger_error("Error, no bibtex data provided!",ERROR);
            }
            else{
                // get bibtex data from $_POST or $_FILES
                if(array_key_exists('bibval',$_POST)){
                    $bibtex_data = explode("\n",$_POST['bibval']);
                }
                else{
                    $bibtex_data= file($_FILES['bibfile']['tmp_name']);
                }
                // add the new entry
                $res = $_SESSION['bibdb']->addBibtexEntries($bibtex_data);

                if(count($res['added']) > 0 && count($res['added']) <= 20){
                    $entries = $_SESSION['bibdb']->getEntriesWithIds($res['added']);
                    $param = $GLOBALS['xslparam'];
                    $param['bibindex_mode'] = "displaybasket";
                    $param['mode'] = "admin";
                    $formated = biborb_html_render($entries,$param);
                    if(count($res['added']) == 1){
                        $message = msg("The following entry was added to the database:");
                    }
                    else if(count($res['added']) > 1){
                        $message = msg("The following entries were added to the database:");
                    }
                    $message .= $formated;
                }
                else{
                    $message .= sprintf(msg("%d entries were added to the database."),count($res['added']));
                }


                if(count($res['notadded']) != 0){
                    $error = msg("Some entries were not imported. Their BibTeX keys were already present in the bibliography. ");
                    $error .= "<br/>";
                    $error .= msg("BibTeX keys in conflict: ");
                    $lg = count($res['notadded']);
                    for($i=0;$i<$lg;$i++){
                        $error .= $res['notadded'][$i];
                        $error .= ($i!=$lg-1 ? ", " : ".");
                    }
                }
            }
            break;

        /*
            Login
        */
        case 'login':
            $login = $_POST['login'];
            $mdp = $_POST['mdp'];
            if($login=="" || $mdp==""){
                $error = msg("LOGIN_MISSING_VALUES");
                $_GET['mode'] = 'login';
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
                    $error = msg("LOGIN_WRONG_USERNAME_PASSWORD");
                    $_GET['mode'] = 'login';
                }
            }
            break;

        /*
         * Export the basket to bibtex
         */
        case 'export':
            if($_SESSION['basket']->count_items() != 0){
                // Get which fields to export
                $bibtex_fields = array();
                foreach($GLOBALS['fields_to_export'] as $field){
                    if(array_key_exists($field,$_POST)){
                        $bibtex_fields[] = $field;
                    }
                }


                // basket not empty -> processing
                // get entries
                $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
                $bt = new BibTeX_Tools();
                $tab = $bt->xml_to_bibtex_array($entries);
                header("Content-Type: text/plain");
                echo $bt->array_to_bibtex_string($tab,$bibtex_fields);
                exit();
            }
            else{
                $_GET['mode'] = 'displaybasket';
            }
            break;

        /**
         * Select which export format for basket
         */
        case 'export_basket':
            switch($_POST['export_format']){
                case 'bibtex':
                    $_GET['mode'] = 'exportbaskettobibtex';
                    break;
                case 'ris':
                    $_GET['mode'] = 'exportbaskettoris';
                    break;
                case 'html':
                    $_GET['mode'] = 'exportbaskettohtml';
                    break;
                case 'docbook':
                    $_GET['mode'] = 'exportbaskettodocbook';
                    break;
                default:
                    $_GET['mode'] = 'welcome';
                    break;
            }
            break;

            /**
             * Export all references.
             */
        case 'export_all':
            $bt = new BibTeX_Tools();
            $_GET['mode'] = "displaytools";
            $entries = $bt->xml_to_bibtex_array($_SESSION['bibdb']->getAllEntries());
            $filename = $_SESSION['bibdb']->getName();
            switch($_POST['export_format']){
                case 'bibtex':
                    $filename .= ".bib";
                    $content = $bt->array_to_bibtex_string($entries,$GLOBALS['fields_to_export']);
                    break;
                case 'ris':
                    $filename .= ".ris";
                    $content = $bt->array_to_RIS($entries);
                    break;
                case 'docbook':
                    $filename .= ".xml";
                    $content = $bt->array_to_DocBook($entries);
                    break;
                default:
                    trigger_error("Unknown export format.",ERROR);
                    break;
            }
            header("Content-Type:text/plain");
            header("Content-Disposition:attachment;filename=$filename");
            echo $content;
            break;

        /*
         * Get BibTeX references from .aux LaTeX file.
         */
        case 'bibtex_from_aux':
            $bibtex_keys = bibtex_keys_from_aux($_FILES['aux_file']['tmp_name']);
            $xmldata = $_SESSION['bibdb']->entries_with_ids($bibtex_keys);
            $bt = new BibTeX_Tools();
            header("Content-disposition: attachment; filename=".$_FILES['aux_file']['name'].".bib");
            header("Content-Type: application/force-download");
            echo $bt->array_to_bibtex_string($bt->xml_to_bibtex_array($xmldata),$fields_to_export);
            die();

       /*
           Create an archive of a given bibliography.
        */
        case 'get_archive':
            // move to bibs
            chdir("./bibs");
            // tar name
            $tar_name = $_SESSION['bibdb']->name().".tar.gz";
            // delete it if it already exists
            if(file_exists($tar_name)){ unlink($tar_name);}
            // create the archive
            $tar = new Archive_Tar($tar_name,"gz");
            $tar->create($_SESSION['bibdb']->name()) or trigger_error("Failed to create an archive of the Bibliography", FATAL);
            // Save as...
            header("Content-disposition: attachment; filename=".$tar_name);
            header("Content-Type: application/octed-stream");
            readfile($tar_name);
            die();

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
    case 'displayxpathsearch': echo bibindex_display_xpath_search();break;

    // Help on the basket menu item
    case 'basket': echo bibindex_basket_help(); break;

    // Display the basket
    case 'displaybasket': echo bibindex_display_basket(); break;

    // Display the page to modify groups of entries in the basket
    case 'groupmodif':
        if($_SESSION['basket']->count_items() != 0){
            echo bibindex_basket_modify_group();
        }
        else{
            echo bibindex_display_basket();
        }
        break;

    // Help on the Manager Menu
    case 'manager': echo bibindex_manager_help(); break;

    // Add a new entry
    case 'addentry':echo bibindex_entry_to_add(); break;

    // Select the type of the new entry to add
    case msg("Select"):
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

    // Page to select which export
    case 'exportbasket':
        // Display export selection form if some entries in the basket
        if($_SESSION['basket']->count_items() != 0){
            echo bibindex_export_basket();
        }
        else{
            echo bibindex_display_basket();
        }
        break;

    // Export the basket to RIS format
    case 'exportbaskettoris':
        $bt = new BibTeX_Tools();
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
        $tab = $bt->xml_to_bibtex_array($entries);
        header("Content-Type: text/plain");
        echo $bt->array_to_RIS($tab);
        break;

    case 'exportbaskettodocbook':
        $bt = new BibTeX_Tools();
        $entries = $_SESSION['bibdb']->entries_with_ids($_SESSION['basket']->items);
        $tab = $bt->xml_to_bibtex_array($entries);
        header("Content-Type: text/plain");
        echo $bt->array_to_DocBook($tab);
        break;

    // bibtex of a given entry
    case 'bibtex':
        $bt = new BibTeX_Tools();
        $entries = $_SESSION['bibdb']->getEntryWithId($_GET['id']);
        $tab = $bt->xml_to_bibtex_array($entries);
        header("Content-Type: text/plain");
        echo $bt->array_to_bibtex_string($tab,$GLOBALS['fields_to_export']);
        break;

    // Export the basket to html
    case 'exportbaskettohtml': echo bibindex_export_basket_to_html();break;


    // Display Tools
    case 'displaytools': echo bibindex_display_tools();break;

    // Browse mode
    case 'browse':
        if (isset($_GET['start']))
        {
            if ($_GET['start'] == 0)
            {
                unset($_SESSION['ids']);
                unset($_SESSION['browse_history']);
                $_SESSION['browse_ids'] = $_SESSION['bibdb']->getAllIds();
                // extract values from the database
                // save them into session
                $_SESSION['misc']['years'] = $_SESSION['bibdb']->getAllValuesFor('year');
                $_SESSION['misc']['groups'] = $_SESSION['bibdb']->getAllValuesFor('group');
                $_SESSION['misc']['series'] = $_SESSION['bibdb']->getAllValuesFor('series');
                $_SESSION['misc']['journals'] = $_SESSION['bibdb']->getAllValuesFor('journal');
                $_SESSION['misc']['authors'] = $_SESSION['bibdb']->getAllValuesFor('author');
            }

            if (isset($_SESSION['browse_history']))
            {
                for($i=0;$i<$_GET['start'];$i++)
                {
                    $_SESSION['browse_ids'] = $_SESSION['bibdb']->filter($_SESSION['browse_ids'],$_SESSION['browse_history'][$i]['type'],$_SESSION['browse_history'][$i]['value']);
                }
                
                for($i=$_GET['start'];$i<count($_SESSION['browse_history']);$i++)
                {
                    unset($_SESSION['browse_history'][$i]);
                }
            }
        }
        echo bibindex_browse();break;

    // By default
    default: echo bibindex_welcome(); break;
}

?>
