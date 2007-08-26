<?php
/**
 *
 * This file is part of BibORB
 *
 * Copyright (C) 2007  Guillaume Gardey
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
 * File: XmlConverter.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 */

class_exists('BaseConverter')  || include('./php/reference/BaseConverter.php');
class_exists('Reference')      || include('./php/reference/Reference.php');

class XmlConverter /* implements BaseConverter */
{
    /**
     * A string describing the format
     */
    function getDescription()
    {
        return "Xml BibTeX Format";
    }

    /**
     * Name of the format
     */
    function getName()
    {
        return "Xml";
    }


    /**
     * Import from a string
     */
    /* static */ function import($iXmlString)
    {
        // result
        $aReferences = array();
        // convert the string in a one line string
        $aXml = str_replace("\n",'',$iXmlString);
        // match all bibtex entries
        preg_match_all("/<bibtex:entry id=['|\"](.*)['|\"]>(.*)<\/bibtex:entry>/U", $aXml, $aEntries, PREG_PATTERN_ORDER);

        for ($i=0; $i<count($aEntries[1]); $i++)
        {
            // xml data of the current entry
            $aEntry = $aEntries[2][$i];

            // get the bibtex type
            preg_match("/<bibtex:(.[^>]*)>(.*)<\/bibtex:(.[^>]*)>/", $aEntry, $aMatches);

            // the new reference
            $aReference = new Reference($aEntries[1][$i],$aMatches[1]);
            // get groups value
            $aBibtexFields = $aMatches[2];
            preg_match("/<bibtex:groups>(.*)<\/bibtex:groups>/U", $aBibtexFields, $aGroups);
            if (isset($aGroups[1]))
            {
                preg_match_all("/<bibtex:group>(.*)<\/bibtex:group>/U", $aGroups[1], $aGroup);
                $aReference->setData('groups', implode(',',$aGroup[1]));
                $aBibtexFields = str_replace($aGroups[0],'',$aBibtexFields);

            }
            // analyse all remaining fields
            preg_match_all("/<bibtex:(.[^>]*)>(.*)<\/bibtex:(.[^>]*)>/U", $aBibtexFields, $aFields);
            // analyse each fields
            for ($j=0; $j<count($aFields[1]); $j++)
            {
                $aReference->setData($aFields[1][$j], specialFiveToText(trim($aFields[2][$j])));
            }

            $aReferences[] = $aReference;
        }
        
        return $aReferences;
    }


    /**
     * Export a reference to a string
     */
    /* static */ function export($iRef)
    {
        $aXmlContent = '<?xml version="1.0" encoding="UTF-8"?>';
        $aXmlContent .= '<bibtex:file xmlns:bibtex="http://bibtexml.sf.net/" version="'.BIBORB_XML_VERSION.'" >';
        if (is_array($iRef))
        {
            foreach($iRef as $aRef)
            {
                $aXmlContent .= XmlConverter::exportSingle($aRef);
            }
        }
        else
        {
            $aXmlContent .= XmlConverter::exportSingle($iRef);
        }
        $aXmlContent .= '</bibtex:file>';
        return $aXmlContent;
    }


    /**
     *
     */
    /* static */ function exportSingle($iRef)
    {
        $aXml = '<bibtex:entry id="'.$iRef->getId().'">';
        $aXml .= '<bibtex:'.$iRef->getType().'>';
        foreach ($iRef->getDataKeys() as $aKey)
        {
            if ($aKey != 'groups')
            {
                $aXml .= '<bibtex:'.$aKey.'>';
                $aXml .= trim(specialFiveToHtml($iRef->getData($aKey)));
                $aXml .= '</bibtex:'.$aKey.'>';
            }
            else if($aKey == 'groups')
            {
                $aXml .= '<bibtex:groups>';
                $aGroupValues = split(',',$iRef->getData($aKey));
                foreach($aGroupValues as $aGrp)
                {
                    $aXml .= '<bibtex:group>';
                    $aXml .= trim(specialFiveToHtml($aGrp));
                    $aXml .= '</bibtex:group>';
                }
                $aXml .= '</bibtex:groups>';
            }
        }
        $aXml .= '</bibtex:'.$iRef->getType().'>';
        $aXml .= '</bibtex:entry>';
        return $aXml;
    }

}

?>
