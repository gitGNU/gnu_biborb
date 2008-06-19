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
 * File: bibtex.php
 *
 * Description:
 *
 */

class_exists('BaseConverter')  || include('./php/BaseConverter.php');
class_exists('Reference')      || include('./php/Reference.php');
class_exists('PARSEENTRIES')   || include('./php/third_party/bibtexParse/PARSEENTRIES.php');


class BibtexConverter /* implements BaseFormat */
{
    /**
     * A string describing the format
     */
    function getDescription()
    {
        return 'BibTex Format';
    }
    
    /**
     * Name of the format
     */
    function getName()
    {
        return 'BibTeX';
    }
    

    /**
     * Import from a string
     */
    function import($iString)
    {

        $aParser = new PARSEENTRIES();
        $aParser->loadBibtexString($iString);
        $aParser->expandMacro = FALSE;
        $aParser->extractEntries();
        list($aPreamble, $aStrings, $aEntries) = $aParser->returnArrays();  
        BibtexConverter::postImport($aEntries);
        $aReferences = array();
        foreach ($aEntries as $aEntry)
        {
            // do some clean up 
            $aType = $aEntry['___type'];
            unset($aEntry['___type']);
            $aId = $aEntry['id'];
            unset($aEntry['id']);
            // add it
            $aReferences[] = new Reference($aId, $aType, $aEntry);
        }
        
        return $aReferences;

    }
    

    /**
     * Export a reference to a string
     */
    function export($iRef)
    {

    }

    /**
     * Some transformations to perform after importing BibTeX entries.
     * @param &$entries A reference to an array of imported entries.
     */
    function postImport(&$iEntries)
    {
        foreach ($iEntries as $aKey => $aEntry)
        {
            $aTypes = array('pdf','url','urlzip');
            foreach ($aTypes as $aType)
            {
                
                if ( isset($aEntry[$aType]) &&
                     preg_match('/(http[s]?|ftp)\:\//U', $aEntry[$aType]))
                {
                    $iEntries[$aKey]['ad_'.$aType] = $iEntries[$aKey][$aType];
                    unset($iEntries[$aKey][$aType]);
                }
            }
        }
    }
    
    
}

?>