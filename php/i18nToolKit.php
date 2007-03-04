<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2005-2007  Guillaume Gardey (ggardey@club-internet.fr)
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
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL
 */

/*
    Change the default charset to utf-8
 */

ini_set("default_charset","utf-8");
/**
 * i18nToolKit: This class is used to group all i18n needed functions.
 *
 */
class i18nToolKit
{
    // Table of translation indexed by locale
    var $_localizedStrings;
    // The current locale
    var $_locale;
    // defined locales
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
            Load locale and check the one asked exists.
            Else fallback to en_US and load the data
         */
        $aLocaleToLoad = $iLocale;
        $this->loadDefinedLocales();
        if (!isset($this->_definedLocales[$iLocale]))
        {
            trigger_error("ERROR_I18N_LOCALE_NOT_DEFINED", E_USER_NOTICE);
            if (!isset($this->_definedLocales[$iDefaultLocale]))
            {
                trigger_error("ERROR_I18N_DEFAULT_LOCALE_NOT_DEFINED", E_USER_ERROR);
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
        $aLocalesDir = dir("locale");
        while ( ($aFile = $aLocalesDir->read()) !== false)
        {
            if (is_dir($aLocalesDir->path."/".$aFile) &&
                file_exists($aLocalesDir->path."/$aFile/LC_MESSAGES/biborb.po"))
            {
                $this->_definedLocales[$aFile] = FileToolKit::getContent($aLocalesDir->path."/$aFile/$aFile.txt");
            }
        }
        $aLocalesDir->close();
    }

    /**
     * Get the set of available locales
     */
    function getLocales()
    {
        return $this->_definedLocales;
    }

    /**
     * Load localized strings for a given
     */
    function loadLocalizedData()
    {
        $aFile = file("./locale/{$this->_locale}/LC_MESSAGES/biborb.po");
        $aMsgId = null; // msgid
        $aMsgStr = "";  // msgstr
        foreach( $aFile as $aLine)
        {
            if (preg_match("/\s*msgid \"(.*)\"/", $aLine, $aMatches))
            {
                if (isset($aMsgId))
                {
                    $this->_localizedStrings[$aMsgId] = (trim($aMsgStr) == "" ? $aMsgId : $aMsgStr);
                }
                $aMsgId = $aMatches[1];
                $aMsgStr = "";
            }
            else if (preg_match("/(?:\s*msgstr)?\"(.*)\"/", $aLine,$aMatches))
            {
                $aMsgStr .= $aMatches[1];
            }
            /*else if (preg_match("/\"(.*)\"/", $aLine, $aMatches))
            {
                $aMsgStr .= $aMatches[1];
            }*/
        }
        if ($aMsgId)
        {
            $this->_localizedStrings[$aMsgId] = (trim($aMsgStr) == "" ? $aMsgId : $aMsgStr);
        }
    }

    /**
     * Get the current locale
     */
    function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Get the traduction of a string for a given locale.
     *
     * @param $iString The string to localize
     * @param $iLocale The locale to use
     */
    function msg($iString)
    {
        if (!isset($this->_localizedStrings[$iString]))
        {
            trigger_error("ERROR_I18N_STRING_NOT_DEFINED", E_USER_NOTICE);
            return $iString;
        }
        else
        {
            return $this->_localizedStrings[$iString];
        }
    }

    /**
     * Get a localized version of a file
     *
     */
    function getFile($iFileName)
    {
        return FileToolKit::getContent("./locale/{$this->_locale}/{$iFileName}");
    }

    /**
     * Try to detect the prefered language in $_SERVER
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
                return $aLocale[0]."_".strtoupper($aLocale[1]);
            }
        }
        return FALSE;
    }
}


/**
 * Translate a localized string
 * If $string doesn't exists, $string is returned.
 *  msg get the language configuration from $_SESSION
 *
 * \param $iString A string to translate
 *\return The localized version of $iString
 */
function msg($iString)
{
    return $_SESSION['i18n']->msg($iString);
}


/**
 * Parse a string and replace with localized data
 */
function replace_localized_strings($string)
{
    // ensure localisation is set up
    load_i18n_config($_SESSION['language']);
    // get all key to translate
    preg_match_all("(BIBORB_OUTPUT\w+)",$string,$matches);
    $keys = array_unique($matches[0]);
    // get the localized value for each element and replace it
    foreach($keys as $val){
        $string = str_replace($val,msg("$val"),$string);
    }
    return $string;
}



?>
