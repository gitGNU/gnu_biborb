<?php
  
require_once("config.php");          // load configuration variables
require_once("config.misc.php");
require_once("php/proxyDbManager.php");    // load the db manager
require_once("php/interface-index.php");   // load function to generate the interface
require_once("php/interface-bibindex.php");   // load function to generate the interface
require_once("php/auth.php");        // load authentication class
require_once("php/i18nToolKit.php");        // load i18n functions
require_once("php/error.php");       // load biborb error handler
require_once("php/HtmlToolKit.php");
require_once("php/FileToolKit.php");
require_once("php/functions.php");
require_once("php/User.php");
require_once("php/biborbdb.php");


//require_once("php/third_party/Cache/Lite/Output.php"); // cache system


/**
 *   Load the session
 */
// do not put web pages in browser cache
session_cache_limiter('nocache');
session_name("SID");
session_start();

//set_error_handler("biborb_error_handler");

/**
 * Set the user
 */
if ( !isset($_SESSION['user']) ||
     !is_object($_SESSION['user']))
{
    $_SESSION['user'] = new User();
    if (!$_SESSION['user']->isSetPermissions())
    {
        $aAuthenticationBackend = new Auth();
        $aAuthenticationBackendÃ->fillPermissionsForUser($_SESSION['user']);
    }
    if (!$_SESSION['user']->isSetPreferences())
    {
        if (empty($aAuthenticationBackend))
            $aAuthenticationBackend = new Auth();
        $aAuthenticationBackendÃ-fillPreferencesForUser($_SESSION['user']);;
    }
}
    

/**
 * Set the errorManager
 */
if ( !isset($_SESSION['errorManager']) ||
     !is_object($_SESSION['errorManager']))
{
    $_SESSION['errorManager'] = new ErrorManager();
}


/**
 * Set the DbManager object.
 */
if ( !isset($_SESSION['DbManager']) ||
     !is_object($_SESSION['DbManager']))
{
    $_SESSION['DbManager'] = new DbManager();
}

/**
 * i18n, choose default lang if not set up
 * Try to detect it from the session, browser or fallback to default.
 */
if ( !isset($_SESSION['i18n']) ||
     !is_object($_SESSION['i18n']))
{
    $_SESSION['i18n'] = new i18nToolKit($_SESSION['user']->getPreference('language'), DEFAULT_LANG);
}

$display_images = DISPLAY_IMAGES;
$display_txt = DISPLAY_TEXT;
$display_shelf_actions = SHELF_MODE;
// global XSL parameters
if (isset($_SESSION['bibdb']))
    $xslparam = array(  'bibname' => $_SESSION['bibdb']->getName(),
                        'bibnameurl' => $_SESSION['bibdb']->getXmlFileName(),
                        'display_images' => $display_images,
                        'display_text' => $display_txt,
                        'abstract' => DISPLAY_ABSTRACT,
                        'display_add_all'=> 'true',
                        'sort' => $_SESSION['bibdb']->getSortMethod(),
                        'sort_order' => $_SESSION['bibdb']->getSortOrder(),/*
                        'can_modify' => $_SESSION['user_can_modify'] || $_SESSION['user_is_admin'],
                        'can_delete' => $_SESSION['user_can_delete'] || $_SESSION['user_is_admin'],*/
                        'shelf-mode' => $display_shelf_actions,
                        'biborb_xml_version' => BIBORB_XML_VERSION);


$action = HtmlToolKit::getFormParameter('action');

switch ($action)
{    
    case 'create':
        // check we have the authorization to modify
        if ( !$_SESSION['user']->isAdmin() )
        {
            //          die();
            
//            $_SESSION['errorManager']->triggerError("You are not authorized to create bibliographies!",null,_PHP_SELF_."?mode=error");
        }

        $dbName = HtmlToolKit::getFormParameter('database_name');
        $dbDescription = HtmlToolKit::getFormParameter('description');

        if (isset($dbName) && trim($dbName) != "")
        {
            $res = $_SESSION['DbManager']->createDb(HtmlToolKit::getFormParameter('database_name'),
                                                    HtmlToolKit::getFormParameter('description'));
        }
        else
        {
            $_SESSION['errorManager']->triggerError("Empty name provided for new bibliography",null);
        }
        
            
        break;

    case 'delete':
        if ( !$_SESSION['user']->isAdmin() )
        {
            $_SESSION['errorManager']->triggerError("You are not authorized to delete bibliographies!");
        }
        
        $bibs = HtmlToolKit::getFormParameter('bibs');
            
        if (count($bibs) > 0)
        {    
            foreach($_POST['bibs'] as $aBibName)
            {    
                $error_or_message['message'] .= $_SESSION['DbManager']->deleteDb($aBibName);
            }
         
        }
        else
        {
            $_SESSION['errorManager']->triggerError("No bibliographies selected!",null);
        }
        
        break;

    default:
        break;
        
}


$mode = HtmlToolKit::getFormParameter('mode');

switch ($mode)
{
	case 'info_display':
		$pageTitle = msg('BIBINDEX_DISPLAY_HELP_TITLE');
		$pageContent = $_SESSION['i18n']->getFile('display_help.txt');
		break;
	
	case 'info_basket':
		$pageTitle = msg("BIBINDEX_BASKET_HELP_TITLE");
		$pageContent = $_SESSION['i18n']->getFile("basket_help.txt");
		break;
	
	case 'info_manager':
		$pageTitle = msg("BIBINDEX_ADMIN_HELP_TITLE");
		$pageContent = $_SESSION['i18n']->getFile("manager_help.txt");
		break;
		
    case 'select':
        $pageTitle = msg("INDEX_AVAILABLE_BIBS_TITLE");
        $pageContent = '';
        $aDbNames = $_SESSION['DbManager']->getDbNames();       
        if (!empty($aDbNames))
        {        
            $pageContent .= guiAvailableBibs($aDbNames);
        }
        else
        {
            $pageContent .= msg("No bibliographies defined.");
        }
        if ($_SESSION['user']->isAdmin())
        {
            $pageContent .= guiAddDatabase($aDbNames);
        }
        break;

    case 'error':
        echo "eazer";
        die();

    case 'bib':
        $pageTitle = msg("INDEX_AVAILABLE_BIBS_TITLE");
        $pageContent = '';
        $bibName = HtmlToolKit::getFormParameter('bibname');
        if (!$_SESSION['DbManager']->exists($bibName))
        {
            $_SESSION['errorManager']->triggerError("No bibliography named $bibName",null);
        }
        else
        {
            $_SESSION['bibdb'] = new BibORB_DataBase($bibName);
        }
        break;

    case 'displayall':
        $pageTitle = msg('BIBINDEX_DISPLAY_ALL_TITLE');
        $pageContent = '';
        if (!isset($_SESSION['bibdb']))
        {
            $_SESSION['errorManager']->triggerError("No bibliography selected",null);
        }
        else
        {
			$pageContent .= 'eaezrer';		
        }
        break;

    case 'selectRefType':
        $pageTitle = msg('BIBINDEX_SELECT_NEW_ENTRY_TITLE');
        $pageContent = '';
        if (!isset($_SESSION['bibdb']))
        {
            $_SESSION['errorManager']->triggerError("No bibliography selected",null);
        }
        else
        {
            $pageContent .= guiSelectReferenceType();
        }
        break;

    case 'addReference':
        $pageTitle = msg('BIBINDEX_ADD_ENTRY_TITLE');
        $pageContent = '';
        if (!isset($_SESSION['bibdb']))
        {
            $_SESSION['errorManager']->triggerError("No bibliography selected",null);
        }
        else
        {
            $pageContent .= guiAddReference(HtmlToolKit::getFormParameter('referenceType'));
        }
        break;        
    default:
      $pageTitle = 'BibORB: BibTeX On-line References Browser';
      $pageContent = $_SESSION['i18n']->getFile("index_welcome.txt");
      break;
}

// 
require_once("parts/main.inc.php");

// 
$_SESSION['errorManager']->purgeAll();

?>
