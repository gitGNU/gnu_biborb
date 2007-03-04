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
    File: htmltoolkit.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL
 */


/**
 *
 *
 */
class HtmlToolKit
{
    /**
     * Generate a HTML select tag.
     *
     *  <select $aAttName='$aAttValue' ...>
     *      <option selected='selected' value='$aOptionVal'>$aOptionName</option>
     *      <option value='$aOptionVal'>$aOptionName</option>
     *       ...
     *  </select>
     *
     * @param $iAttributes Attributes of the select tag.
     * @param $iOptions The different options tag to put in the select.
     * @param $iSelectedOptionValue The value that is selected in the list of option.
     */
    /* static */ function selectTag($iAttributes, $iOptions, $iSelectedOptionValue = null)
    {
        $aHtml = "<select";

        foreach ($iAttributes as $aAttName => $aAttValue)
        {
            $aHtml .= " {$aAttName}='{$aAttValue}'";
        }
        $aHtml .= ">";
        array_walk($iOptions,
                   array( 'HtmlToolKit', 'optionTag'),
                   array( 'selectedOptionValue' => $iSelectedOptionValue,
                          'outputString' => &$aHtml));
        $aHtml .= "</select>";

        return $aHtml;

    }

    /**
     * Create a full <option> tag.
     *      - If $iOptionValue == $iMixedData['selectedOptionValue']:
     *          <option value='$iOptionValue' selected='selected'>$iOptionName</option>
     *      - Else
     *          <option value='$iOptionValue'>$iOptionName</option>
     *
     * If $iMixedData['outputString'] is defined, the option tag is appended to it.
     * (so that we can use array_walk function)
     *
     * @param $iOptionName The option name displaid in HTML
     * @param $iOptionValue The internal value of the option tag
     * @param $iMixedData Additional information
     * @return
     */
    /* static */ function optionTag($iOptionName, $iOptionValue, $iMixedData)
    {
        $aHtml = "";
        if (isset($iMixedData['outputString']))
        {
            $aHtml = &$iMixedData['outputString'];
        }
        $aHtml .= "<option";
        if ( isset($iMixedData['selectedOptionValue']) &&
             $iMixedData['selectedOptionValue'] == $iOptionValue)
        {
            $aHtml .= " selected='selected'";
        }
        $aHtml .= " value='{$iOptionValue}'>{$iOptionName}</option>";
        return $aHtml;
    }


    /**
     * Close an HTML page.
     */
    /* static */ function htmlClose()
    {
        return "</body></html>";
    }

    /**
     * Create an HTML header
     */
    /* static */ function htmlHeader($iData)
    {
        $aHtml = <<< EOT
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
\t<head>
\t\t<meta http-equiv='content-type' content='text/html; charset=utf-8' />
\t\t<meta name='robots' content='noindex,nofollow'/>
EOT;
        if (isset($iData['stylesheet']))
        {
            $aHtml .= "\t\t<link href='{$iData['stylesheet']}' rel='stylesheet' type='text/css'/>";
        }
        if (isset($iData['title']))
        {
            $aHtml .= "\n\t\t<title>{$iData['title']}</title>";
        }
        if (isset($iData['javascript']))
        {
            $aHtml .= "\n\t\t<script type='text/javascript' src='{$iData['javascript']}'></script>";
        }
        $aHtml .= "\n\t</head>";
        $aHtml .= "\n<body";
        if (isset($iData['body']))
        {
            array_walk($iData['body'], 'HtmlToolKit::->htmlParameterToString', $aHtml);
        }
        $aHtml .= ">";
        return $aHtml;
    }

    /**
     *
     */
    /* static */ function htmlParameterToString($iParamValue, $iParamName, &$ioHtmlString)
    {
        $ioHtmlString .= " $iParamValue='$iParamName'";
    }

}
?>