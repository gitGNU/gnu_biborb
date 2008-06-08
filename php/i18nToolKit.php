<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2005-2007  Guillaume Gardey (glinmac@gmail.com)
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

/*
    File: i18n.php
    Author: Guillaume Gardey (glinmac@gmail.com)
    Licence: GPL
 */

/*
    Change the default charset to utf-8
 */

class_exists('FileToolKit') || include('./php/FileToolKit.php');

ini_set('default_charset','utf-8');
/**
 * i18nToolKit:
 *      This class is used to group all i18n needed functions.
 *
 */
class i18nToolKit
{
    // Table of translation for the current locale
    var $_localizedStrings;
    // The current locale
    var $_locale;
    // All availables locales
    var $_definedLocales;

    /**
     * Constructor. Build a new i18nToolKit for a given locale. If the locale is
     * not found, a default locale is used.
     *
     * @param $iLocale The local to use.
     * @param $iDefaultLocale The default locale to use.
     */
    function i18nToolKit($iLocale, $iDefaultLocale)
    {
        /*
            Load locales and check that $iLocale exists.
            Else fallback to $iDefaultLocale and load the data.
         */
        $aLocaleToLoad = $iLocale;
        $this->loadDefinedLocales();
        if (!isset($this->_definedLocales[$iLocale]))
        {
            trigger_error('ERROR_I18N_LOCALE_NOT_DEFINED', E_USER_NOTICE);
            if (!isset($this->_definedLocales[$iDefaultLocale]))
            {
                trigger_error('ERROR_I18N_DEFAULT_LOCALE_NOT_DEFINED', E_USER_ERROR);
            }
            $aLocaleToLoad = $iDefaultLocale;
        }
        $this->_locale = $aLocaleToLoad;
        $this->loadLocalizedData();
    }

    /**
     * Get all locales present in the locale directory.
     */
    function loadDefinedLocales()
    {
        /*
            Loop on all files in locale to detect directory
            Assume this is a real locale directory if LC_MESSAGES/biborb.po exists
         */
        $this->_definedLocales = array();
        $aLocalesDir = dir('locale');
        while ( ($aFile = $aLocalesDir->read()) !== false)
        {
            if (is_dir($aLocalesDir->path.'/'.$aFile) &&
                file_exists($aLocalesDir->path.'/'.$aFile.'/LC_MESSAGES/biborb.po'))
            {
                $this->_definedLocales[$aFile] = FileToolKit::getContent($aLocalesDir->path."/$aFile/$aFile.txt");
            }
        }
        $aLocalesDir->close();
    }

    /**
     * Get the set of available locales
     *
     * @return An array containing all available locales.
     */
    function getLocales()
    {
        return $this->_definedLocales;
    }

    /**
     * Load localized strings for the current locale.
     */
    function loadLocalizedData()
    {
        myUnset($this->_localizedStrings);
        $this->_localizedStrings = array();

        $aFile = file('./locale/'.$this->_locale.'/LC_MESSAGES/biborb.po');
        $aMsgId = null; // msgid
        $aMsgStr = '';  // msgstr
        foreach( $aFile as $aLine)
        {
            if (preg_match("/\s*msgid \"(.*)\"/u", $aLine, $aMatches))
            {
                if (isset($aMsgId))
                {
                    $this->_localizedStrings[$aMsgId] = (trim($aMsgStr) == "" ? $aMsgId : $aMsgStr);
                }
                $aMsgId = $aMatches[1];
                $aMsgStr = '';
            }
            else if (preg_match("/\s*[^#](?:msgstr)?\"(.*)\"/u", $aLine, $aMatches))
            {
                $aMsgStr .= $aMatches[1];
            }
        }
        if ($aMsgId)
        {
            $this->_localizedStrings[$aMsgId] = (trim($aMsgStr) == "" ? $aMsgId : $aMsgStr);
        }
    }

    /**
     * Get the current locale.
     *
     * @return The locale.
     */
    function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Get the translation of a string for a given locale.
     *
     * @param $iString The string to localize
     * @param $iLocale The locale to use
     */
    function msg($iString)
    {
        if (!isset($this->_localizedStrings[$iString]))
        {
            $aContext = array( 'locale' => $this->_locale,
                               'string' => $iString);
            $_SESSION['errorManager']->triggerWarning('ERROR_I18N_STRING_NOT_DEFINED:'.$iString, $aContext);
            return $iString;
        }
        else
        {
            return $this->_localizedStrings[$iString];
        }
    }

    /**
     * Get a localized version of a file.
     *
     * @param $iFileName File's name.
     * @return The content of the file for the current locale.
     */
    function getFile($iFileName)
    {
        return FileToolKit::getContent('./locale/'.$this->_locale.'/'.$iFileName);
    }

    /**
     * Try to detect the prefered language in $_SERVER
     *
     * @return If defined, the local sent by the browser else, FALSE.
     */
    /* static */ function getPreferedLanguage()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            // something like xx_XX,.......
            $aPreferedLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $aElement = split(',',$aPreferedLanguages);
            $aLocale = split('-', $aElement[0]);
            if (count($aLocale)==1)
            {
                return $aLocale[0];
            }
            else
            {
                return $aLocale[0].'_'.strtoupper($aLocale[1]);
            }
        }
        return null;
    }

    /**
     * Parse a string and replace with localized data for BIBORB_OUTPUT_* elements
     *
     * @param $ioString A string to localize.
     */
    function localizeBiborbString(&$ioString)
    {
        // get all keys to translate
        preg_match_all("/(BIBORB_OUTPUT\w+)/u", $ioString, $aMatches);
        $aKeys = array_unique($aMatches[0]);
        $aStrToReplace = array_map('msg', $aKeys);
        $ioString = strtr($ioString, array_combine($aKeys, $aStrToReplace));
    }

    /**
     * Change the current locale
     */
    function loadLocale($iLocale)
    {
        if ($iLocale != $this->_locale)
        {

            $aLocaleToLoad = $iLocale;
            if (!isset($this->_definedLocales[$iLocale]))
            {
                trigger_error('ERROR_I18N_LOCALE_NOT_DEFINED', E_USER_ERROR);
                $aLocaleToLoad = $this->_defaultLocale;
            }
            $this->_locale = $aLocaleToLoad;
            $this->loadLocalizedData();
        }
    }

}


/**
 * Translate a localized string (shortcut to i18nToolKit)
 *
 * @param $iString A string to translate
 * @return The localized version of $iString
 */
function msg($iString)
{
    return $_SESSION['i18n']->msg($iString);
}

?>
