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


class BibTeX_Tools
{
    
    /**
        Return an array of entries.
     */
    function get_array_from_string($string){
        $bibtex_parser = new PARSEENTRIES();
        for($i=0;$i<count($string);$i++){
            $string[$i] = stripslashes($string[$i]);
        }
        $bibtex_parser->loadBibtexString($string);
        $bibtex_parser->expandMacro = TRUE;
        $bibtex_parser->extractEntries();
        $res = $bibtex_parser->returnArrays();
        $entries = $res[2];
        for($i=0;$i<count($entries);$i++){
            foreach($entries[$i] as $key => $value){
                $entries[$i][$key] = addslashes($entries[$i][$key]);
            }
        }
        return $entries;
    }
        
    /**
        Return an array of entries
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
                //$xml .= stripslashes(trim(myhtmlentities($value)));
                $xml .= trim(myhtmlentities($value));
                $xml .= "</bibtex:".$key.">";
            }
            else if($key == 'groups') {
                $xml .= "<bibtex:groups>";
                $groupvalues = split(',',$value);
                foreach($groupvalues as $gr){
                    $xml .= "<bibtex:group>";
                    //$xml .= stripslashes(trim(myhtmlentities($gr)));
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
}


/*
Inspired by an awk BibTeX parser written by Nelson H. F. Beebe over 20 years ago although little of that 
remains other than a highly edited braceCount().

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

Mark Grimshaw 2004
(Amendments to file reading Daniel Pozzi for v1.1)

21/08/2004 Guillaume Gardey, Added string parsing and expand macro features.
 Fix bug with comments, strings macro.

*/
class PARSEENTRIES
{
	function PARSEENTRIES()
	{
		$this->preamble = $this->strings = $this->entries = array();
		$this->count = 0;
		$this->fieldExtract = TRUE;
		$this->removeDelimit = TRUE;
	        $this->expandMacro = FALSE;
	        $this->parseFile = TRUE;
	}
// Open bib file
	function openBib($file)
	{
		if(!is_file($file))
			die;
		$this->fid = fopen ($file,'r');
// 22/08/2004 Mark Grimshaw - commented out as set in constructor.
//		$this->parseFile = TRUE;
	}
// Load a bibtex string to parse it
    function loadBibtexString($bibtex_string)
    {
        if(!is_array($bibtex_string)){
            $this->bibtexString = explode('\n',$bibtex_string);    
        }
        else{
            $this->bibtexString = $bibtex_string;   
        }
        $this->parseFile = FALSE;
        $this->currentLine = 0;
    }
    // set strings macro
    function loadStringMacro($macro_array){
        $this->strings = $macro_array;
    }
// Close bib file
	function closeBib()
	{
		fclose($this->fid);
	}
// Get a line from bib file
    function getLine()
    {
// 21/08/2004 G.Gardey
// remove comments from parsing
        if($this->parseFile){
            if(!feof($this->fid)){
                do{
                    $line = trim(fgets($this->fid));
                    $isComment = (strlen($line)>0) ? $line[0] == '%' : FALSE;
                }
                while(!feof($this->fid) && $isComment);
                return $line;
            }
            return FALSE;
        }
        else{
            do{
                $line = trim($this->bibtexString[$this->currentLine]);
                $isComment = (strlen($line)>0) ? $line[0] == '%' : FALSE;
                $this->currentLine++;
            }
            while($this->currentLine <count($this->bibtexString) && $isComment);
            $val = ($this->currentLine < count($this->bibtexString)) ? $line : FALSE;
            return $val;
        }
	}
// Count entry delimiters
	function braceCount($line, $delimitStart)
	{
		if($delimitStart == '{')
			$delimitEnd = '}';
		else
		{
			$delimitStart = '(';
			$delimitEnd = ')';
		}
		$count = 0;
		$count = substr_count($line, $delimitStart);
		$count += 0 - substr_count($line, $delimitEnd);
		return $count;
	}
// Extract a field
	function fieldSplit($seg)
	{
		$array = preg_split("/,\s*(\w+)\s*={1}\s*/U", $seg, PREG_SPLIT_DELIM_CAPTURE);
		if(!array_key_exists(1, $array))
			return array($array[0], FALSE);
		return array($array[0], $array[1]);
	}
// Extract and format fields
	function reduceFields($oldString)
	{
		$oldString = rtrim($oldString, "}),");
		$split = preg_split("/=/", $oldString, 2);
		$string = $split[1];
		while($string)
		{
			list($entry, $string) = $this->fieldSplit($string);
			$values[] = $entry;
		}
		foreach($values as $value)
		{
			$pos = strpos($oldString, $value);
			$oldString = substr_replace($oldString, '', $pos, strlen($value));
		}
		$rev = strrev(trim($oldString));
		if($rev{0} != ',')
			$oldString .= ',';
		$keys = preg_split("/=,/", $oldString);
// 22/08/2004 - Mark Grimshaw
// I have absolutely no idea why this array_pop is required but it is.  Seems to always be an empty key at the end after the split 
// which causes problems if not removed.
		array_pop($keys);
		foreach($keys as $key)
		{
			$value = trim(array_shift($values));
			$rev = strrev($value);
// remove any dangling ',' left on final field of entry
			if($rev{0} == ',')
				$value = rtrim($value, ",");
			if(!$value)
				continue;
// 21/08/2004 G.Gardey -> expand macro
// Don't remove delimiters now
// needs to know if the value is a string macro
//			$this->entries[$this->count][strtolower(trim($key))] = trim($this->removeDelimiters(trim($value)));
			$this->entries[$this->count][strtolower(trim($key))] = trim($value);

		}
	}
// Start splitting a bibtex entry into component fields.
// Store the entry type and citation.
	function fullSplit($entry)
	{
		$matches = preg_split("/@(.*)\s*[{(](.*),/U", $entry, 2, PREG_SPLIT_DELIM_CAPTURE);
		$this->entries[$this->count]['type'] = strtolower($matches[1]);
		$this->entries[$this->count]['id'] = $matches[2];
		$matches = $this->reduceFields($matches[3]);
	}
// Grab a complete bibtex entry
	function getEntry($line)
	{
		$entry = '';
		$count = 0;
		$lastLine = FALSE;
		if(preg_match("/@(.*)\s*([{(])/", $line, $matches))
		{
			do
			{
				$count += $this->braceCount($line, $matches[2]);
				$entry .= ' ' . $line;
				if(($line = $this->getLine()) === FALSE)
					break;
				$lastLine = $line;
			}
			while($count);
		}
		else
		{
			$line .= $this->getLine();
			$this->getEntry($line);
		}
		if(!array_key_exists(1, $matches))
			return $lastLine;
		if(preg_match("/string/i", $matches[1]))
			$this->strings[] = $entry;
		else if(preg_match("/preamble/i", $matches[1]))
			$this->preamble[] = $entry;
		else
		{
			if($this->fieldExtract)
				$this->fullSplit($entry);
			else
				$this->entries[$this->count] = $entry;
			$this->count++;
		}
		return $lastLine;
	}
// Remove enclosures around entry field values.  Additionally, expand macros if flag set.
	function removeDelimiters($string)
	{
// Remove any enclosing double quotes or braces around entry field values
		if($this->removeDelimit && ($string{0} == "\""))
		{
			$string = substr($string, 1);
			$string = substr($string, 0, -1);
		}
		else if($this->removeDelimit && ($string{0} == "{"))
		{
			$string = substr($string, 1);
			$string = substr($string, 0, -1);
		}
// expand the macro if defined
		else if($this->expandMacro && isset($this->strings))
		{
// macro are case insensitive
			foreach($this->strings as $key => $value)
                		$string = eregi_replace($key,$value,$string);
// 22/08/2004 Mark Grimshaw - make sure a '#' surrounded by any number of spaces is replaced by just one space.
                	$string = preg_replace("/\s*#\s*/", " ", $string);
//            		$string = str_replace('#',' ',$string);
        	}
		return $string;
	}
// This method starts the whole process
	function extractEntries()
	{
        $lastLine = FALSE;
        if($this->parseFile)
        {
            while(!feof($this->fid))
            {
                $line = $lastLine ? $lastLine : $this->getLine();
                if(!preg_match("/^@/i", $line))
                    continue;
                if(($lastLine = $this->getEntry($line)) !== FALSE)
                    continue;
            }
        }
        else{
            while($this->currentLine < count($this->bibtexString))
            {
                $line = $lastLine ? $lastLine : $this->getLine();
                if(!preg_match("/^@/i", $line))
                    continue;
                if(($lastLine = $this->getEntry($line)) !== FALSE)
                    continue;
            }
        }
	}
// Return arrays of entries etc. to the calling process.
	function returnArrays()
	{
		foreach($this->preamble as $value)
		{
			preg_match("/.*[{(](.*)/", $value, $matches);
			$preamble = substr($matches[1], 0, -1);
			$preambles['bibtexPreamble'] = trim($this->removeDelimiters(trim($preamble)));
		}
		if(isset($preambles))
			$this->preamble = $preambles;
		if($this->fieldExtract)
		{
			foreach($this->strings as $value)
			{
// changed 21/08/2004 G. Gardey
				$value = trim($value);
				$matches = preg_split("/@string\s*[{(]/i", $value, -1, PREG_SPLIT_NO_EMPTY);
				foreach($matches as $val)
				{
                    			$string = substr($val, 0, -1);
                    			preg_match("/\s*(.*)\s*=\s*[{\"](.*)[}\"]/",$string,$tab);
                    			$strings[trim($tab[1])] = trim($tab[2]);
                		}
			}
		}
	        if(isset($strings))
			$this->strings = $strings;
        
// changed 21/08/2004 G. Gardey
// 22/08/2004 Mark Grimshaw - stopped useless looping.
		if($this->removeDelimit || $this->expandMacro)
		{
			for($i=0;$i<count($this->entries);$i++)
			{
                foreach($this->entries[$i] as $key => $value)
                    $this->entries[$i][$key] = trim($this->removeDelimiters($this->entries[$i][$key])); 
            }
		}
		if(empty($this->preamble))
			$this->preamble = FALSE;
		if(empty($this->strings))
			$this->strings = FALSE;
		if(empty($this->entries))
			$this->entries = FALSE;
        
		return array($this->preamble, $this->strings, $this->entries);
	}
}
?>
