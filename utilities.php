<?php
/**
 *
 * This file is part of BibORB
 * 
 * Copyright (C) 2003  Guillaume Gardey
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
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 * 
 *      Some useful functions 
 * 
 */
 
/**
 * get values in an array, null if key does not exists
 */
function get_value($key,$tab) {
    $val = null;
    if(array_key_exists($key,$tab)){
        $val = $tab[$key];
    }
    return $val;
}

/**
 * Use to change the base name of a file, keeping its extension
 * returns the new file name
 */
function get_new_name($filename,$newbasename) {
    $elem = explode('.',$filename);
    // change the basename
    $newfilename = $newbasename;
    // copy the extensions (many possible eg: .ps.gz)
    for($i=1;$i<count($elem);$i++){
        $newfilename .= ".".$elem[$i];
    }
    return $newfilename;
}

/**
 * Close an HTML page.
 */
function html_close() {
    return "</body></html>";  
}

/**
 * Create an HTML header
 */
function html_header($title = NULL, $style = NULL, $bodyclass=NULL) {
    $html  = '<?xml version="1.0" encoding="ISO-8859-1"?>';
    $html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';  
    $html .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >';
    $html .= "<head>";
    $html .= "<meta http-equiv='content-type' content='text/html; charset=ISO-8859-1' />";
    if($style){
        $html .= "<link href='$style' rel='stylesheet' type='text/css'/>";
    }  
    if($title){
        $html .= "<title>$title</title>";
    }
    $html .= "<SCRIPT LANGUAGE=Javascript SRC='./myscripts.js'> </SCRIPT>";
    $html .= "</head>";
    $html .= "<body";
    if($bodyclass){    
        $html .= " class='$bodyclass' ";
    }
    $html .= ">";
    
    return $html;  
}

/**
 * XSLT processor
 */
function xslt_transform($xmlstring,$xslstring,$xslparam = array()) {
    $xh = xslt_create();
    xslt_set_encoding($xh,"iso-8859-1");
    xslt_set_base($xh,"file://".getcwd()."/biborb");
    $xslparam['session_name'] = session_name();
    $xslparam['session_id'] = session_id();
    $arguments = array('/_xml' => $xmlstring, '/_xsl' => $xslstring);  
    $result = xslt_process($xh,'arg:/_xml','arg:/_xsl',NULL,$arguments,$xslparam);

    if (!$result) {
        die(sprintf("Impossible de traiter le document XSLT [%d]: %s", 
                    xslt_errno($xh), xslt_error($xh)));
    }
    xslt_free($xh);
    
    return $result;  
}

/**
 * Load a text file
 */
function load_file($filename) {
  return implode('',file($filename));  
}

function myhtmlentities($str){
    $patterns = array('&','<','>');
    $replace = array('&amp;','&lt;','&gt;');    
    return str_replace($patterns,$replace,$str);
}

?>