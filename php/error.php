<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2003-2007  Guillaume Gardey (ggardey@club-internet.fr)
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
 * File: error.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    Defines an error handler for biborb.
 */


// Define constant for errors
define("FATAL", E_USER_ERROR);
define("ERROR", E_USER_WARNING);
define("WARNING", E_USER_NOTICE);

// add E_ALL for debugging
error_reporting(FATAL | ERROR | WARNING| E_ALL);

/**
 * Handler for biborb errors.
 * Generate a verbose output.
 */
function biborb_error_handler($iErrNo, $iErrStr, $iErrFile, $iErrLine)
{
    $aPhpVersion = PHP_VERSION;
    $aPhpOs = PHP_OS;
    $aBiborbVersion = BIBORB_VERSION;
    $aBiborbReleaseDate = BIBORB_RELEASE_DATE;

	switch ($iErrNo)
	{
        case E_USER_NOTICE:
            /*
            $aHtmlHeaderData = array( 'title' => 'Biborb',
                                      'stylesheet' => CSS_FILE,
                                      'javascript' => './biborb.js');
            $aHtml = HtmlToolKit::htmlHeader($aHtmlHeaderData);
            $aErrorString = $_SESSION['errorManager'];
            $aErrorContext = print_r($_SESSION['errorManager']->_warningStack, TRUE);
            $aHtml .= <<< EOT
<div class='error_report'>
    <b>An error occurred</b><br />
    Aborting...<br />
    <div class='error_content'>
    <b>Error: </b> {$aErrorString}<br /><br />
    <pre>{$aErrorContext}</pre><br/><br/>
    PHP {$aPhpVersion} ({$aPhpOs})<br />
    BibORB {$aBiborbVersion} {$aBiborbReleaseDate}<br/>
    Consider reporting this error at <a href='http://savannah.nongnu.org/projects/biborb'>http://savannah.nongnu.org/projects/biborb</a> if it is reproductible.<br/><br/>
Go Back to <a href='index.php'>BibORB</a>
</div>
EOT;
            $aHtml .= HtmlToolKit::htmlClose();
            echo $aHtml;
            print_r($_SESSION['errorManager']);
            exit(1);*/
            break;
            
		case ERROR:
		case FATAL:
		case E_ALL:
		default:
			$aBaseName = basename($iErrFile);
			$aHtml = html_header("BibORB - Error",CSS_FILE,null,null);
			$aHtml .= <<< EOT
<div class='error_report'>
	<b>An error occurred</b><br />
	Aborting...<br />
	<div class='error_content'>
	<b>Error: </b> {$iErrStr}<br /><br />
	Error at line {$iErrLine} of file {$aBaseName}<br/><br/>
	PHP {$aPhpVersion} ({$aPhpOs})<br />
	BibORB {$aBiborbVersion} {$aBiborbReleaseDate}<br/>
	Consider reporting this error at <a href='http://savannah.nongnu.org/projects/biborb'>http://savannah.nongnu.org/projects/biborb</a> if it is reproductible.<br/><br/>
	sGo Back to <a href='index.php'>BibORB</a>
</div>
EOT;
            $aHtml .= html_close();
            echo $aHtml;
            exit(1);
            break;
    }
}

/**
 * Error Manager
 *
 */
class ErrorManager
{
    var $_errorStack;
    var $_warningStack;
    var $_noticeStack;


    function ErrorManager()
    {
        $this->_errorStack = array();
        $this->_warningStack = array();
        $this->_noticeStack = array();
    }

    function hasErrors()
    {
        return !empty($this->_errorStack);
    }

    function hasWarnings()
    {
        return !empty($this->_warningStack);
    }

    function hasNotices()
    {
        return !empty($this->_noticeStack);
    }

    function purgeAll()
    {
        $this->purgeErrors();
        $this->purgeWarnings();
        $this->purgeNotices();
    }

    function purgeErrors()
    {
        $this->_errorStack = array();
    }

    function purgeWarnings()
    {
        $this->_warningStack = array();
    }

    function purgeNotices()
    {
        $this->_noticeStack = array();
    }
    /**
     * Trigger a new warning
     *
     */
    function triggerWarning($iString, $iContext, $iGoTo = null)
    {
        $this->_warningStack[] = array('string'=>$iString, 'context'=>$iContext, 'level'=>E_USER_WARNING);
//        header('Location:index.php');
    }

    
    /**
     * Trigger a new error
     *
     */
    function triggerError($iString, $iContext, $iGoTo = null)
    {
        $this->_errorStack[] = array('string'=>$iString, 'context'=>$iContext, 'level'=>E_USER_ERROR);
        if (isset($iGoTo))
        {
            header("Location:${iGoTo}");
            die();
        }
    }

    /**
     * Display all warnings
     */
    function outputWarnings()
    {
        $aHtmlContent = "";
        foreach ($this->_warningStack as $aWarning)
        {
            $aHtmlContent .= "Warning:";
            $aHtmlContent .= HtmlToolKit::tag('pre',$aWarning['string']);
            $aHtmlContent .= HtmlToolKit::tag('pre',print_r($aWarning['context'],true));
            $aHtmlContent .= HtmlToolKit::tagNoData('br');
        }
        return $aHtmlContent;
    }

    /**
     * Display all warnings
     */
    function outputErrors()
    {
        
        $aHtmlContent = HtmlToolKit::startTag('div',array('id'=>'error'));
        foreach ($this->_errorStack as $aError)
        {
            $aHtmlContent .= "Error:";
            $aHtmlContent .= HtmlToolKit::tag('pre',$aError['string']);
            $aHtmlContent .= HtmlToolKit::tag('pre',print_r($aError['context'],true));
        }
        $aHtmlContent .= HtmlToolKit::closeTag('div');
        
        return $aHtmlContent;
    }
            
}

?>
