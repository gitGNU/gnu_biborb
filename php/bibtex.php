<?php
/**
 *
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2004  Guillaume Gardey
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
    File: bibtex.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL
    
    Description:
 
        This file defines the BibTeX_Tools class. It provides functions to
    deal with bibtex data:
            * parse a string/file (using PARSEENTRIES from bibliophile.sf.net)
            * convert to xml
 
 */

require_once("php/third_party/PARSEENTRIES.php");
require_once("php/utilities.php");

class BibTeX_Tools
{
    /**
        Return an array of entries.
        $string is a BibTeX string
     */
    function get_array_from_string($string){
        $bibtex_parser = new PARSEENTRIES();
        for($i=0;$i<count($string);$i++){
            $string[$i] = $string[$i];
        }
        $bibtex_parser->loadBibtexString($string);
        $bibtex_parser->expandMacro = TRUE;
        $bibtex_parser->extractEntries();
        $res = $bibtex_parser->returnArrays();
        $entries = $res[2];
        for($i=0;$i<count($entries);$i++){
            foreach($entries[$i] as $key => $value){
                $entries[$i][$key] = $entries[$i][$key];
            }
        }
        return $entries;
    }
        
    /**
        Return an array of entries
        $filename is a BibTeX file
     */
    function get_array_from_file($filename){
        $bibtex_parser = new PARSEENTRIES();
        $bibtex_parser->openBib($filename);
        $bibtex_parser->extractEntries();
        $bibtex_parser->expandMacro = TRUE;
        $bibtex_parser->closeBib();
        $res = $bibtex_parser->returnArrays();
        return $res[2];
    }
    
    /**
        Convert an array representation of an entry in XML.
     */
    function entry_array_to_xml($tab){
        $xml = "<bibtex:entry id='".$tab['id']."'>";
        $xml .= "<bibtex:".$tab['type'].">";
        foreach($tab as $key => $value){
            if($key != 'groups' && $key!= 'type' && $key != 'id'){
                $xml .= "<bibtex:".$key.">";
                $xml .= trim(myhtmlentities($value));
                $xml .= "</bibtex:".$key.">";
            }
            else if($key == 'groups') {
                $xml .= "<bibtex:groups>";
                $groupvalues = split(',',$value);
                foreach($groupvalues as $gr){
                    $xml .= "<bibtex:group>";
                    $xml .= trim(myhtmlentities($gr));
                    $xml .= "</bibtex:group>";
                }
                $xml .= "</bibtex:groups>";
            }
        }
        $xml .= "</bibtex:".$tab['type'].">";
        $xml .= "</bibtex:entry>";
        return $xml;
    }
    
    /**
        Convert an array of entries to XML.
        Return: array(number of entries, array of ids, xml string)
     */
    function entries_array_to_xml($tab){
        $ids = array();
        $xml_content = "<?xml version='1.0' encoding='ISO-8859-1'?>";
        $xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/'>";
        foreach($tab as $entry){
            $xml_content .= $this->entry_array_to_xml($entry);
            array_push($ids,$entry['id']);
        }
        $xml_content .= "</bibtex:file>";
        return array(count($tab),$ids,$xml_content);
    }
    
    /**
        Convert a bibtex string to xml.
        Return: array(number of entries, array of ids, xml string)
     */
    function bibtex_string_to_xml($string){
        $entries = $this->get_array_from_string($string);
        return $this->entries_array_to_xml($entries);
    }
    
    /**
        Convert a bibtex file to xml.
        Return: array(number of entries, array of ids, xml string)
     */
    function bibtex_file_to_xml($filename){
        $entries = $this->get_array_from_file($filename);
        return $this->entries_array_to_xml($entries);
    }
    
    /**
        Convert a XML string to an array
     */
    function xml_to_bibtex_array($xmlstring){
        echo "<pre>";
        // result
        $res = array();
        $xml = str_replace("\n","",$xmlstring);
        // match all entries
        preg_match_all("/<bibtex:entry id=['|\"](.*)['|\"]>(.*)<\/bibtex:entry>/U",$xml,$entries,PREG_PATTERN_ORDER);
        for($i=0;$i<count($entries[1]);$i++){
            $entry = $entries[2][$i];

            $ref_tab = array('id'=> $entries[1][$i]);
            // get the bibtex type
            preg_match("/<bibtex:(.[^>]*)>(.*)<\/bibtex:(.[^>]*)>/",$entry,$matches);
            $ref_tab['type'] = $matches[1];

            // get groups value
            preg_match("/<bibtex:groups>(.*)<\/bibtex:groups>/U",$matches[2],$groups);
            preg_match_all("/<bibtex:group>(.*)<\/bibtex:group>/U",$groups[1],$group);
            $ref_tab['groups'] = implode(',',$group[1]);
            $bibtex_fields = str_replace($groups[0],"",$matches[2]);

            preg_match_all("/<bibtex:(.[^>]*)>(.*)<\/bibtex:(.[^>]*)>/U",$bibtex_fields,$fields);
            // analyse each fields
            for($j=0;$j<count($fields[1]);$j++){
                $ref_tab[$fields[1][$j]]=trim($fields[2][$j]);
            }
            $res[] = $ref_tab;
        }
        
        return $res;
    }
    
    function array_to_bibtex_string($tab,$fields_to_export){
        $export = "";
        foreach($tab as $entry){
            $entry_exported = "";
            $export .= "@".$entry['type']."{".$entry['id'].",\n";
            foreach($fields_to_export as $field){

                if(array_key_exists($field,$tab)){
                    $export .= "    ".$field." = {".$entry[$field]." }";
                }
            }
        }
    }
    
}
?>
