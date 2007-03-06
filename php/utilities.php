<?php
/**
 *
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
 *
 * File: utilities.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *      Some useful functions
 *
 */


/**
    Close an HTML page.
 */
function html_close() {
    return "</body></html>";
}

/**
 * Create an HTML header
 */
function html_header($iTitle = NULL, $iStyle = NULL, $iBodyClass=NULL, $iInBody=NULL)
{
    $aHtml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
    $aHtml .= "\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
    $aHtml .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" >";
    $aHtml .= "\n<head>";

    // Charset, content type
    $aHtml .= "\n\t<meta http-equiv='content-type' content='text/html; charset=utf-8' />";

    // how to handle robots
    $aHtml .= "\n\t<meta name='robots' content='noindex,nofollow'/>";

    // define the CSS stylesheet
    if ($iStyle)
    {
        $aHtml .= "\n\t<link href='$iStyle' rel='stylesheet' type='text/css'/>";
    }

    // define the title
    if($iTitle)
    {
        $aHtml .= "\n\t<title>$iTitle</title>\n";
    }

    // define the javascript ressource
    $aHtml .= "\n\t<script type='text/javascript' src='./biborb.js'></script>";

    $aHtml .= "\n</head>";
    $aHtml .= sprintf("\n<body%s%s>",$iBodyClass,$iInBody);

    return $aHtml;
}

/**
    Replace special chars into their HTML representation.
 */
function myhtmlentities($str)
{
    $patterns = array('&','<','>','\'','"');
    $replace = array('&amp;','&lt;','&gt;','&apos;','&quot');
    return str_replace($patterns, $replace, $str);
}

function specialFiveToHtml($iString)
{
    $patterns = array('&','<','>','\'','\"');
    $replace = array('&amp;','&lt;','&gt;','&apos;','&quot');
    return str_replace($patterns, $replace, $iString);
}


function specialFiveToText($iString)
{
    $replace = array('&','<','>','\'','\"');
    $patterns = array('&amp;','&lt;','&gt;','&apos;','&quot');
    return str_replace($patterns, $replace, $iString);
}

    
function xhtml_select($name,$size,$tab,$selected,$onchange=null,$style=null,$class=null)
{
    $result = "<select name='$name' id='$name'";
    if($onchange){
        $result .= " onchange='$onchange'";
    }
    if($style){
        $result .= " style='$style'";
    }
    if($class){
        $result .= " class='$class'";
    }
    $result .= ">";
    foreach($tab as $val){
        if($val == $selected){
            $result .= "<option selected='selected'>$val</option>";
        }
        else{
            $result .= "<option>$val</option>";
        }
    }
    $result .= "</select>";
    return $result;
}


/**
 * Apply stripslashes to a variable or an array and returns the result. If $value is an
 * array, stripslashes is recursively called for each element of the array.
 */
function stripslashes_deep($value){
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
}

/**
    Flatten an array.
    If $array = ( [key1] => array('el1','el2','el3'),
                  [key2] => array('a1','a2,'a3'))
    the function will return: array('el1','el2','el3','a1','a2','a3')
 */
function flatten_array($array){
    $flat = array();
    for($i=0;$i<count($array);$i++){
        $flat = array_merge($flat,$array[$i]);
    }
    return $flat;
}

/**
    Defines the array_chunk function if not defined (PHP < 4.2)
*/
if (!function_exists('array_chunk')) {
    function array_chunk( $input, $size, $preserve_keys = false) {
        @reset( $input );
        $i = $j = 0;
        while( @list( $key, $value ) = @each( $input ) ) {
            if( !( isset( $chunks[$i] ) ) ) {
                $chunks[$i] = array();
            }
            if( count( $chunks[$i] ) < $size ) {
                if( $preserve_keys ) {
                    $chunks[$i][$key] = $value;
                    $j++;
                } else {
                    $chunks[$i][] = $value;
                }
            } else {
                $i++;
                if( $preserve_keys ) {
                    $chunks[$i][$key] = $value;
                    $j++;
                } else {
                    $j = 0;
                    $chunks[$i][$j] = $value;
                }
            }
        }
        return $chunks;
    }
}

/**
    Evaluate a string has PHP code.
    PHP code should be defined between "<?php" and "?>".

 Example:
    eval_php("<div class='aC'>Today is: <?php echo date("d/m/Y")?>. </div>")
 will return: "<div class='aC'>Today is: 01/01/2003. </div>"
 */
function eval_php($string){
    preg_match_all("/(<\?php)(.*?)\?>/si",$string,$raw_php_matches);
    $eval_string = $string;
    $php_idx = 0;
    while(isset($raw_php_matches[0][$php_idx])){
        $raw_php_str = $raw_php_matches[0][$php_idx];
        $raw_php_str = str_replace("<?php", "", $raw_php_str);
        $raw_php_str = str_replace("?>", "", $raw_php_str);
        ob_start();
        eval("$raw_php_str");
        $res = ob_get_contents();
        ob_end_clean();
        $eval_string = preg_replace("/(<\?php)(.*?)\?>/si",$res, $eval_string, 1);
        $php_idx++;
    }
    return $eval_string;
}

/**
    Remove null values or empty string from an array
 */
function remove_null_values($anArray){
    foreach($anArray as $key=>$val){
        if($val == null || trim($val) == ""){
            unset($anArray[$key]);
        }
    }
    return $anArray;
}

/**
 */
function read_status_html_select($name,$selected){
    $html = "<select size='1' name='$name'>";
    if($selected == 'any'){
        $html .= "<option value='any' selected='selected'></option>";
    }
    else{
        $html .= "<option value='any'></option>";
    }
    if($selected == 'read'){
        $html .= "<option value='read' selected='selected'>".msg("Read")."</option>";
    }
    else{
        $html .= "<option value='read'>".msg("Read")."</option>";
    }
    if($selected == 'readnext'){
        $html .= "<option value='readnext' selected='selected'>".msg("Read Next")."</option>";
    }
    else{
        $html .= "<option value='readnext'>".msg("Read Next")."</option>";
    }
    if($selected == 'notread'){
        $html .= "<option value='notread' selected='selected'>".msg("Not Read")."</option>";
    }
    else{
        $html .= "<option value='notread'>".msg("Not Read")."</option>";
    }
    $html .= "</select>";
    return $html;
}
/**
 */
function ownership_html_select($name,$selected){
    $html = "<select size='1' name='$name'>";
    if($selected == 'any'){
        $html .= "<option value='any' selected='selected'></option>";
    }
    else{
        $html .= "<option value='any'></option>";
    }
    if($selected == 'notown'){
        $html .= "<option value='notown' selected='selected'>".msg("Not Own")."</option>";
    }
    else{
        $html .= "<option value='notown'>".msg("Not Own")."</option>";
    }
    if($selected == 'borrowed'){
        $html .= "<option value='borrowed' selected='selected'>".msg("Borrowed")."</option>";
    }
    else{
        $html .= "<option value='borrowed'>".msg("Borrowed")."</option>";
    }
    if($selected == 'buy'){
        $html .= "<option value='buy' selected='selected'>".msg("Buy")."</option>";
    }
    else{
        $html .= "<option value='buy'>".msg("Buy")."</option>";
    }
    if($selected == 'own'){
        $html .= "<option value='own' selected='selected'>".msg("Own")."</option>";
    }
    else{
        $html .= "<option value='own'>".msg("Own")."</option>";
    }
    $html .= "</select>";
    return $html;
}

/**
 * Return the major PHP version number
 */
function php_major_version_number() {
	$tab = explode('.',phpversion());
	return (int) $tab[0];
}

function is_php5() {
	return php_major_version_number() == 5;
}

function is_php4() {
	return php_major_version_number() == 4;
}

/**
 * Unset the variable and set it to false.
 * 
 * We then can use if($ioData) ... without checking with isset
 */
function myUnset(&$ioData)
{
    unset($ioData);
    $ioData = null;
}

function myUnsetArray(&$ioArray, $iKey)
{
    unset($ioArray[$iKey]);
}


?>
