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
 * File: basket.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 *
 *      Basket functions 
 * 
 */

/********************************************************************************/
/*                                                                              */
/* Functions concerning the BibTeX basket.                                      */
/* The basket is recorded in a SESSION variable: 'basket'                       */
/*                                                                              */
/********************************************************************************/

/**
 * Reset the basket.
 */
function reset_basket(){
    for($i=0;$i<count($_SESSION['basket']);$i++){
        unset($_SESSION['basket'][$i]);
    }
    $_SESSION['basket'] = array();
}

/**
 * Add a BibTeX id to the basket
 */
function add_to_basket($bibid){
    if(!in_array($bibid,$_SESSION['basket'])){
        array_push($_SESSION['basket'],$bibid);
    }
}

/**
 * Remove a BibTeX id from the basket
 */
function delete_from_basket($bibid){
    $index = array_search($bibid,$_SESSION['basket']);
    unset($_SESSION['basket'][$index]);
    $_SESSION['basket'] = array_values($_SESSION['basket']);
}

 
/**
 * Returns an xml representation of id present in the basket
 */
function basket_to_xml(){
    // create an xml string containing id present in the basket
    $xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
    $xml_content .= '<entrylist>';
    for($i=0;$i<count($_SESSION['basket']);$i++){
        $xml_content .= '<id>'.$_SESSION['basket'][$i].'</id>';
    }
    $xml_content .= '</entrylist>';
    return $xml_content;
}

/**
 * Extract information form the XML file and produces an HTML table of the basket
 */
function basket_to_html($usermode,$abstract){
    // create an xml string containing id present in the basket
    $xml_content = basket_to_xml();
    //load the xsl file
    $xsl_content = load_file("./xsl/basket2html_table.xsl");
    // set paramters
    $param = array( 'bibnameurl' => "file:".realpath(xmlfilename($_SESSION['bibname'])),
                    'bibname' => $_SESSION['bibname'],
                    'session_name' => session_name(),
                    'session_id' => session_id(),
                    'basket' => 'true',
                    'mode' => $usermode,
                    'abstract' => $abstract);
    
    //return the HTML table
    return xslt_transform($xml_content,$xsl_content,$param);    
}

/**
 * Simple display of a basket
 */
function basket_to_simple_html(){
    // create an xml string containing id present in the basket
    $xml_content = basket_to_xml();
    //load the xsl file
    $xsl_content = load_file("./xsl/basket2simple_html.xsl");
    // set paramters
    $param = array( 'bibnameurl' => "file:".realpath(xmlfilename($_SESSION['bibname'])),
                    'bibname' => $_SESSION['bibname']);
    
    //return the HTML table
    return xslt_transform($xml_content,$xsl_content,$param);   
}

/**
 * Convert the basket into BibTeX 
 */
function basket_to_bibtex(){
    $text = "";
    for($i=0;$i<count($_SESSION['basket']);$i++){
        $text .= get_bibtex($_SESSION['bibname'],$_SESSION['basket'][$i]);
    }
    return $text;
}

/**
 * Reset group of entries in the basket
 */
function basket_reset_group(){
    // create an xml string containing id present in the basket
    $in_basket = basket_to_xml();
    //load the xsl file
    $xsl_content = load_file("./xsl/resetgroup.xsl");
    // set paramters
    $param = array( 'bibname' => xmlfilename($_SESSION['bibname']));
    // new xml file
    $result = xslt_transform($in_basket,$xsl_content,$param); 
        
    // update the xml file
    $xsl_content = load_file("./xsl/update_xml.xsl");
    $result = xslt_transform($result,$xsl_content,$param);
    
    $fp = fopen(xmlfilename($_SESSION['bibname']),"w");
    fwrite($fp,$result);
    fclose($fp);
    //update group in SESSION
    $_SESSION['group_list'] = get_group_list($_SESSION['bibname']);
}

/**
 * Add a group to the entries present in the basket
 */
function basket_add_group($group){
    // create an xml string containing id present in the basket
    $in_basket = basket_to_xml();
    //load the xsl file
    $xsl_content = load_file("./xsl/addgroup.xsl");
    // set paramters
    $param = array( 'bibname' => "file:".realpath(xmlfilename($_SESSION['bibname'])),
                    'group' => $group);
    // new xml file
    $result = xslt_transform($in_basket,$xsl_content,$param); 
    
    // update the xml file
    $xsl_content = load_file("./xsl/update_xml.xsl");
    $result = xslt_transform($result,$xsl_content,$param);
    
    $fp = fopen(xmlfilename($_SESSION['bibname']),"w");
    fwrite($fp,$result);
    fclose($fp);
    //update group in SESSION
    $_SESSION['group_list'] = get_group_list($_SESSION['bibname']);
}
?>