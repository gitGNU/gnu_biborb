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
 * File: functions.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 * 
 *   This file encapsulates some functions to transform a raw text
 * bibfile into 'nice' html pages linked against electronic version of
 * papers specified in the bibfile.
 * 
 * 
 */

/**
 * load variables and functions
 */
require_once("config.php");
require_once("utilities.php");


/**
 * Update the XML file
 * If more than one bibfile is present, content is merged in one XML file
 * To each bibentry of abibfile.bib is assigned the group abibfile
 */
function update_xml($bibname){
    $ar = opendir("./bibs/".$bibname."/");
    $tab = array();    
    while($file = readdir($ar)) {
        if(!is_dir($file) && $file != 'papers'){      
            $inf = pathinfo($file);
            if($inf['extension'] == 'bib'){
                array_push($tab,$file);      
            }
        }
    }
  
    if(count($tab) > 1){
        $fp = fopen("./bibs/".$bibname."/".$bibname.".xml","w");
        fwrite($fp,"<?xml version='1.0' encoding='iso-8859-1'?>\n");
        fwrite($fp,"<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' name='".$bibname."'>\n");
        fclose($fp);      
        foreach($tab as $bibfile){      
            write_xml_file("./bibs/".$bibname."/".$bibname.".xml","./bibs/".$bibname."/".$bibfile,true);
        }    
        $fp = fopen("./bibs/".$bibname."/".$bibname.".xml","a");   
        fwrite($fp,"</bibtex:file>");
        fclose($fp);
    }
    else{
        write_xml_file("./bibs/".$bibname."/".$bibname.".xml","./bibs/".$bibname."/".$tab[0]);
    }
}

/**
 * Convert a bibtex file to an xml file.
 * 
 */
function write_xml_file($xmlfile,$bibfile,$append = false){  
  $inf = pathinfo($bibfile);  
  $bibname = explode('.',$inf['basename']);
  $bibname = $bibname[0];
  
  if($append){
    $xml_content = bibtex2xml($bibfile,$bibname);    
    $fp = fopen($xmlfile,"a");   
    fwrite($fp,$xml_content);
    fclose($fp);    
  }
  else{
    $inf = pathinfo($xmlfile);
    $xml_content ="<?xml version='1.0' encoding='iso-8859-1'?>\n";
    $xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' name='".$bibname."'>\n";    
    $xml_content .= bibtex2xml($bibfile);
    $xml_content .= "</bibtex:file>\n";    
    $fp = fopen($xmlfile,"w");
    fwrite($fp,$xml_content);
    fclose($fp);
  }    
}


/**
 * xml2bibtex($bibname)
 * update the .bib file according to data present in the xml file.
 */
function xml2bibtex($bibname){
    $xmlfile = xmlfilename($bibname);
    $bibfile = bibfilename($bibname);
    // get the number of entries
    // xsl_process seems to return null both if an error occur or the file is empty
    // so we have to check with the number of entries
    $record = get_number_of_entries($bibname);
    $nb = $record[0];

    $xml_content = load_file($xmlfile);
//    $xml_content = ereg_replace("<br/>","\n",$xml_content);
    $xsl_content = load_file("./xsl/xml2bibtex.xsl");

    $xh = xslt_create();
    xslt_set_encoding($xh,"iso-8859-1");
    $arguments = array('/_xml' => $xml_content, '/_xsl' => $xsl_content);  
    $bibtex = xslt_process($xh,'arg:/_xml','arg:/_xsl',NULL,$arguments);

    if (!$bibtex) {
        if($nb!=0){
            die(sprintf("Impossible de traiter le document XSLT [%d]: %s", 
		                xslt_errno($xh), xslt_error($xh)));
        }
    }

    xslt_free($xh);
  
    // write the bibtex file
    $fp = fopen($bibfile,"w");
    fwrite($fp,$bibtex);
    fclose($fp);
}

/**
 * bibtex2xml
 * Transform a BibTeX string into an XML string
 */
function bibtex2xml($bibfile,$group=NULL){
	
	
    $content = file($bibfile);          // content to analyse
    
	$first = 1;							// is it the first entry analyzed?
    $xml_content = null;                // xml content
    $key = null;                        // bibtex field
    $data_content = null;               // bibtex field value
    $openfield = false;                 // true if a value is on several lines
    $type = null;                       // type of the bibtex entry being analyzed                

	
	// remove uneeded spaces
	for($i=0;$i<sizeof($content);$i++){
		$content = preg_replace("/\s+/"," ",$content);
	}
	
    for($i=0;$i<sizeof($content);$i++){
        // recode &, <, >
        $patterns = array('&','<','>');
        $replace = array('&amp;','&lt;','&gt;');    
        $line = str_replace($patterns,$replace,$content[$i]);

        //new entry @(alphanum){(anychar),
        if(preg_match("/@\s?(\w*)\s?{(.*),/",$line,$matches)){
			// If it isn't the first entry, close the previous one
            if($first==0){
                $xml_content .= end_bibentry($type);
            }
			// save the type to add the good closing tag
            $type = $matches[1];
			
            $xml_content .= new_bibentry($type,trim($matches[2]));
			
            if($group!=NULL){
                $xml_content .= bibfield("group",$group);	
            }

            $first = 0;
        }
		// detect a line defining a field
        else if(!$openfield && 
				(preg_match("/\s?(\w*)\s?=\s?{(.*)},?/",$line,$matches) ||
				 preg_match("/\s?(\w*)\s?=\s?\"(.*)\",?/",$line,$matches))){
            $key = $matches[1];
            // new version of biborb: translate group into groups
            if($key == 'group'){
                $key = 'groups';
            }
            $data = $matches[2];
		
			// if groups key, split into several <bibtex:group>
			// groups must be separated by a comma
			if($key == 'groups'){
				$xml_content .= "<bibtex:groups>\n";
				$group_array = split(',',$data);
				foreach($group_array as $gr){
					if(trim($gr) != ''){
						$xml_content .= bibfield("group",trim($gr));
					}
				}
				$xml_content .= "</bibtex:groups>\n";
			}
			else {
				$xml_content .= bibfield($key,trim($data));
			}
		}
		// field set in several lines (data)
		else if(!$openfield && 
				(preg_match("/\s?(\w*)\s?=\s?{(.*)/",$line,$matches) ||
				 preg_match("/\s?(\w*)\s?=\s?\"(.*)/",$line,$matches))){
			$openfield = true;
			$key = $matches[1];
			$data_content = trim($matches[2])."\n";
		 }
        // detec the end of an entry
        else if(preg_match("/(.*)[}\"],?/",$line,$matches)){
            //if $data_content is null, end of a an entry
            // or additionnal brace or quote, who knows :)
            if($openfield){	
                $data_content .= $matches[1];
                //keeps formatting for abstract
                if($key != 'abstract'){
                    $data_content = preg_replace("/\s+/"," ",$data_content);
                }
                // new version of biborb: need to create group field if multiple groups defined
                if($key == 'groups'){
                    $xml_content .= "<bibtex:groups>\n";
                    $group_array = split(',',$data_content);
                    foreach($group_array as $gr){
                        if(trim($gr) != ''){
                            $xml_content .= bibfield("group",trim($gr));
                        }
                    }
                    $xml_content .= "</bibtex:groups>\n";
                }
                else{
                    $xml_content .= bibfield($key,$data_content);
                }
                $openfield = false;
            }
			else {
				$data_content .= trim($matches[1])."\n";
			}
        }
        else {
            $data_content .= trim($line)."\n";	
        }
    }
  
    if($first == 0){
        $xml_content .= end_bibentry($type);
    }

    return $xml_content;
}

/**
 * new_bibentry($type,$id)
 * Create a start tag for a bibentry
 * Returns: <bitex:entry id='$id'><bibtex:$type>
 */
function new_bibentry($type,$id){
  return "<bibtex:entry id='".$id."'>\n"."<bibtex:".strtolower($type).">\n";
}

/**
 * end_bibentry($type)
 * Create an end tag for bibentry
 * Returns: </bibtex:$type></bibtex:entry>
 */
function end_bibentry($type){
  return "</bibtex:".strtolower($type).">\n</bibtex:entry>\n";
}

/**
 * bibfield($type,$value)
 * Create a new bibfield tag
 */
function bibfield($type,$value){
  return "<bibtex:".strtolower($type).">".$value."</bibtex:".strtolower($type).">\n";
}

/**
 * get_group_list
 * Get groups present in the bibtex file
 */
function get_group_list($bibname)
{
    // Get groups from the xml bibtex file
    $xml_content = load_file("./bibs/".$bibname."/".$bibname.".xml");
    $xsl_content = load_file("./xsl/group_list.xsl");  
    $group_list = xslt_transform($xml_content,$xsl_content);
 
    // Remove doublons
    $group_list = split("[,~]",$group_list);
    $list = array();
    $j=0;
    for($i=0;$i<sizeof($group_list);$i++){
        $group_list[$i] = trim($group_list[$i]);
        if($group_list[$i] != ""){
            if(!in_array($group_list[$i],$list)){
                $list[$j] = $group_list[$i];
                $j++;
            }
        }
    }
 
    return $list;    
}

/**
 * Load an XML file
 */
function load_xml_bibfile($bibname)
{
    return load_file(xmlfilename($bibname));
}

/**
 * Return an HTML table of all entries of a bibfile
 */
function get_all_bibentries($bibname,$mode,$abstract)
{
    $xml_content = load_xml_bibfile($bibname);
    $xsl_content = load_file("./xsl/all_sorted_by_id2html_table.xsl");
    $param = array('mode' => $mode,
                   'bibname' => $bibname,
                   'display_images' => $GLOBALS['display_images'],
                   'display_text' => $GLOBALS['display_text']);
    
    if($abstract){
        $param['abstract'] = "true";
    }
  
    return xslt_transform($xml_content,$xsl_content,$param);
}

/**
 * Return an HTML output of entries of a given group
 */
function get_bibentries_of_group($bibname,$groupname,$mode,$abstract)
{
    $xml_content = load_xml_bibfile($bibname);    
    $xsl_content = load_file("./xsl/by_group2html_table.xsl");  
    $param = array('group'=>$groupname,
                   'mode' => $mode, 
                   'bibname' => $bibname,
                   'display_images' => $GLOBALS['display_images'],
                   'display_text' => $GLOBALS['display_text']);
    if($abstract){
        $param['abstract'] = "true";
    }
 
    return xslt_transform($xml_content,$xsl_content,$param);
}

/**
 * Return an HTML output of entries matching search paramters
 */
function search_bibentries($bibname,$value,$forauthor,$fortitle,$forkeywords,$mode,$abstract){
    $xml_content = load_xml_bibfile($bibname);
    $xsl_content = load_file("./xsl/search2html_table.xsl");  
    $param = array();
    if($forauthor!=null){
        $param["author"]=$value;
    }
    if($fortitle!=null){
        $param["title"]=$value;
     }
    if($forkeywords!=null){
        $param["keywords"]=$value;
    }
    $param["mode"] = $mode;
    $param['bibname'] = $bibname;
    if($abstract){
        $param['abstract'] = "true";
    }
    $param['display_images'] = $GLOBALS['display_images'];
    $param['display_text'] = $GLOBALS['display_text'];

    return xslt_transform($xml_content,$xsl_content,$param);
}

/**
 * Translate an entry into bibtex format
 */
function get_bibtex($bibname,$bibid)
{
    $xml_content = load_xml_bibfile($bibname);
    $xsl_content = load_file("./xsl/xml2bibtex.xsl");
//    $xml_content = ereg_replace("<br/>","\n",$xml_content);
    $param = array('id'=>$bibid);
    $result = xslt_transform($xml_content,$xsl_content,$param); 
    //remove not needed spaces
    $result = preg_replace(array('/(\s*\\1)?/','/ +/'),array("\\1",' '),$result);
    return $result;  
}

/**
 * Return an html output of a given entry
 */
function get_bibentry($bibname,$bibid,$abstract,$basket = 'no', $mode = 'user')
{
    $xml_content = load_xml_bibfile($bibname);
    $xsl_content = load_file("./xsl/one_entry2html.xsl");
    $param = array( 'bibname' => $bibname,
                    'id' => $bibid,
					'mode' => $mode,
                    'basket' => $basket,
                    'display_images' => $GLOBALS['display_images'],
                    'display_text' => $GLOBALS['display_text']);
    if($abstract){
        $param['abstract'] = "true";
    }
  
    return xslt_transform($xml_content,$xsl_content,$param);
}

/**
 * Return a nice formulary to modify an entry
 */
function get_bibentry_for_edition($bibname,$bibid,$add=1)
{
    $xml_content = load_file("./xsl/model.xml");
    $xsl_content = load_file("./xsl/xml2htmledit.xsl");
    $param = array('id' => $bibid,'bibname' => "file://".realpath("bibs/".$bibname."/".$bibname.".xml"));
    $param['add'] = $add;
  
    return xslt_transform($xml_content,$xsl_content,$param);
}
 
/**
 * get_number_of_entries
 * Returns the number of articles and electronic papers in the bibliography
 */
function get_number_of_entries($bibname){
    $xml_content = load_xml_bibfile($bibname);
    $xsl_content = load_file("./xsl/count.xsl");
    $nb_record = xslt_transform($xml_content,$xsl_content);  
    $record = explode(".",$nb_record);
    
    return $record;
}

/**
 * get_stat
 * Print statistics in HTML
 */
function get_stat($bibname)
{ 
    $record = get_number_of_entries($bibname);
  
    $html  = "<h3>Statistics</h3>";
    $html .= "<table>";
    $html .= "<tbody>";
    $html .= "<tr>";
    $html .= "<td>Number of recorded articles:</td>";
    $html .= "<td><strong>".$record[0]."</strong></td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>On-line available publications:</td>";
    $html .= "<td><strong>".$record[1]."</strong></td>";
    $html .= "</tr>";
    $html .= "</tbody>";
    $html .= "</table>";

  return $html;  
}

/**
 * delete only a bibtex entry, do not erase files
 * 1) delete from the bibtex file
 * 2) update the bibtex file according to the xml file
 */
function delete_only_bibtex_entry($bibname,$id)
{
    $record = get_number_of_entries($bibname);
    $nb = $record[0];
  
    $xml_content = load_file(xmlfilename($bibname));
    $xsl_content = load_file("./xsl/delete.xsl");
    $param = array('id'=>$id);
    $xh = xslt_create();
    xslt_set_encoding($xh,"iso-8859-1");
    $arguments = array('/_xml' => $xml_content, '/_xsl' => $xsl_content);  
    $result = xslt_process($xh,'arg:/_xml','arg:/_xsl',NULL,$arguments,$param);

    if(!$result) {
        if($nb!=1){
            die(sprintf("Impossible de traiter le document XSLT [%d]: %s",xslt_errno($xh),xslt_error($xh)));
        }
      }
    xslt_free($xh);

    // update the xml file.
    $fp = fopen("./bibs/".$bibname."/".$bibname.".xml","w");
    fwrite($fp,$result);
    fclose($fp);
  
    //update the bibtex file.
    xml2bibtex($bibname);
    // update the list of groups
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

/**
 * Delete a bibtex entry
 * 1) delete from the bibtex file
 * 2) update the bibtex file according to the xml file
 * 3) delete papers from the database
 */
function delete_bibtex_entry($bibname,$id)
{
    $xml_content = load_xml_bibfile($bibname,$id);
    $xsl_content = load_file("./xsl/delete.xsl");
    $param = array('id'=>$id);  
    $newxml = xslt_transform($xml_content,$xsl_content,$param);
  
    // detect all file corresponding to this id.
    $papersdirectory = "./bibs/".$bibname."/papers/";
    $ar = opendir($papersdirectory);
    $tab = array(); 
    while($file = readdir($ar)) {
        $inf = pathinfo($file);
        if(strcmp(substr($inf['basename'],0,strlen($id)+1),$id.".")==0){
            array_push($tab,$file);
        }
    }
  
    foreach($tab as $file){
        unlink($papersdirectory.$file);
    }
 
    // update the xml file.
    $fp = fopen(xmlfilename($bibname),"w");
    fwrite($fp,$newxml);
    fclose($fp);
  
    //update the bibtex file.
    xml2bibtex($bibname);
    // update the list of groups
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

/**
 * Test if a bibtex entry with a given ID exists in the bibtex file.
 */
function exists_entry_with_id($bibname,$id){
    $content = load_file(xmlfilename($bibname));
    $xsl = load_file("./xsl/search_entry.xsl");
    $param = array('id' => $id);
    $result = xslt_transform($content,$xsl,$param);
    
    return (strrpos($result,'true'));
}


/**
 * Get the url of the .bib file
 */
function bibfilename($bibname){
    return "./bibs/".$bibname."/".$bibname.".bib";
}

/**
 * Get the url of the .xml file
 */
function xmlfilename($bibname){
    return "./bibs/".$bibname."/".$bibname.".xml";
}

/**
 * Add a bibtex entry to a bibtex file
 * type -> the type's entry
 * tab -> an array containing information about the entry
 * urlfile -> address of the url file
 * urlzipfile -> address of the urlzip file
 * pdffile -> address of the pdffile
 */
function add_bibtex_entry($bibname,$type,$tab,$urlfile,$urlzipfile,$pdffile){
    // open the bibfile
    $filename = bibfilename($bibname);
    $file = fopen($filename,"a+");
    // append data
    fwrite($file,"\n\n");
    fwrite($file,to_bibtex($type,$tab,$urlfile,$urlzipfile,$pdffile));
    fclose($file);
    // update XML file
    update_xml($_SESSION['bibname']);
    // update the list of groups
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

/**
 * Extract bibtex field from an array
 */
function extract_bibtex_data($tab){
    $result = array();
    foreach($tab as $key => $value){
        if(in_array($key,$GLOBALS['bibtex_entries']) && trim($value)!= ''){
            $result[substr($key,1)] = trim($value);
        }
    }
    return $result;   
}

/**
 * Add an entry to the XML file
 */
function add_new_entry($bibname,$type,$tab,$urlfile,$urlzipfile,$pdffile){
    $xml = to_xml($type,$tab,$urlfile,$urlzipfile,$pdffile);
    $xsl = load_file("./xsl/add_entry.xsl");
    $param = array('bibname' => xmlfilename($bibname));
    $result = xslt_transform($xml,$xsl,$param);
    $fp = fopen(xmlfilename($bibname),"w");
    fwrite($fp,$result);
    fclose($fp);
    // update bibtex file
    xml2bibtex($bibname);
    // update groups
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

/**
 * Update an entry
 */
function update_entry($bibname,$type,$tab,$urlfile,$urlzipfile,$pdffile){
    $xml = to_xml($type,$tab,$urlfile,$urlzipfile,$pdffile);
    $xsl = load_file("./xsl/update_xml.xsl");
    $param = array('bibname' => xmlfilename($bibname));
    $result = xslt_transform($xml,$xsl,$param);
    $fp = fopen(xmlfilename($bibname),"w");
    fwrite($fp,$result);
    fclose($fp);
    // update bibtex file
    xml2bibtex($bibname);
    // update groups
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

/**
 * Extract information from an array to produce an XML string
 */
function to_xml($type,$tab,$urlfile,$urlzipfile,$pdffile){
    if ($urlfile != null){
        $tab['_url'] = $urlfile;
    }
    if ($urlzipfile != null){
        $tab['_urlzip'] = $urlzipfile;
    }
    if ($pdffile != null){
        $tab['_pdf'] = $pdffile;
    }
    // get keys present in the tab
    $tab['_type'] = $type;
    $newtab = extract_bibtex_data($tab);
    
    $xml  = "<?xml version='1.0' encoding='iso-8859-1'?>";
    $xml .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' name='temp'>";
    $xml .= "<bibtex:entry id='".$tab['_id']."'>";
    $xml .= "<bibtex:".$type.">";
    foreach($newtab as $key => $value){
        if($key != 'groups' && $key!= 'type' && $key != 'id'){
            $xml .= "<bibtex:".$key.">";
            $xml .= myhtmlentities($value,null,"utf-8");
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
    $xml .= "</bibtex:".$type.">";
    $xml .= "</bibtex:entry>";
    $xml .= "</bibtex:file>";
    return $xml;
}

/**
 * Translate information into a BibTeX entry
 * This function produces a BibTeX string representing data contained in the "tab" array.
 *
 * type -> the type's entry
 * tab -> an array containing information about the entry
 * urlfile -> address of the url file
 * urlzipfile -> address of the urlzip file
 * pdffile -> address of the pdffile
 */
function to_bibtex($type,$tab,$urlfile,$urlzipfile,$pdffile){
    
    if ($urlfile != null){
        $tab['_url'] = $urlfile;
    }
    if ($urlzipfile != null){
        $tab['_urlzip'] = $urlzipfile;
    }
    if ($pdffile != null){
        $tab['_pdf'] = $pdffile;
    }
    $tab['_type'] = $type;
    $newtab = extract_bibtex_data($tab);
    
    $txt = "@".$type."{".$tab['_id'].",\n";
    $first = 1;
    foreach($newtab as $key => $value){
    // get keys present in the tab
        if($key != "id" && $key != 'type' && trim($value) != ''){
            if($first == 0){
                $txt .= ",\n";
            }
            $first = 0;
            $txt .= "\t".$key." = {".myhtmlentities($value)."}";
        }
    }
    $txt .= "\n}\n";
  
    return $txt;
}

/**
 * Translate information into an HTML array
 * This function produces an HTML array string representing data contained in the "tab" array.
 *
 * type -> the type's entry
 * tab -> an array containing information about the entry
 * urlfile -> address of the url file
 * urlzipfile -> address of the urlzip file
 * pdffile -> address of the pdffile
 */
function to_bibtex_tab($type,$tab,$urlfile,$urlzipfile,$pdffile){
    if ($urlfile != null){
        $tab['_url'] = $urlfile;
    }
    if ($urlzipfile != null){
        $tab['_urlzip'] = $urlzipfile;
    }
    if ($pdffile != null){
        $tab['_pdf'] = $pdffile;
    }
    
    // get keys present in the tab
    $array_key = array_keys($tab);
    
    // start the table
    $txt = "<table class='bibtex'>";
    $txt .= "<tbody>";
    $txt .= "<tr>";
    $txt .= "<td>"."@".$type."{".$tab['_id'].",</td>";
    $txt .= "</tr>";

    // for each present key in the array tab, create the corresponding BibTeX field
    foreach($array_key as $key){
        if($key != "_id" && in_array($key,$GLOBALS['bibtex_entries'])){
            if($tab[$key] != ""){
                $txt .= "<tr>";
                $txt .= "<td>".substr($key,1)."</td><td> = {".myhtmlentities(stripslashes($tab[$key]))."},</td>";
                $txt .= "</tr>";
            }
        }
    }
    // end the table
    $txt .= "</tbody></table>}<br/>";
  
    return $txt;
}

/**
 * Upload a file.	
 * If successful, return the name of the file, otherwise null.
 * Overwrite if the file is already present.
 *
 * bibname -> name of the bibliography
 * type -> type of file to upload (url,urlzip,pdf)
 * id -> id of the paper
 */
function upload_file($bibname,$type,$id)
{
    $res = null;
    $infofile = pathinfo($_FILES[$type]['name']);
    $extension = $infofile['extension'];
    $file = get_new_name($infofile['basename'],$id);
    $path = "./bibs/".$bibname."/papers/".$file;
    // If file already exists, delete it
    if(file_exists($path)){
        unlink($path);
    }
    // upload the file
    $is_uploaded = move_uploaded_file($_FILES[$type]["tmp_name"],$path);
    // change it to be readable/writable to the owner and readable for others
    chmod($path,0644);
    if($is_uploaded){
  	     $res = $file;
    }
    return $res;
}

/**
 * Return the login form
 */
function login_form($from){
    $content = "<div style='text-align:center;'>";
    $content .= "<form action='action_proxy.php?".session_name()."=".session_id()."' method='post'>";
    $content .= "<table style='margin:auto;'>";
    $content .= "<tr>";
    $content .= "<td>";
    $content .= "<input type='hidden' name='from' value='".$from."'/>";
    $content .= "<input type='text' name='login' size='15' maxlength='20' value='login'/><br/>";
    $content .= "<input type='password' name='mdp' size='15' maxlength='20' value='mdp'/>";
    $content .= "</td></tr>";
    $content .= "<tr><td><div style='text-align:center;'><input type='submit' name='action' value='login'/></div>";
    $content .= "</td></tr>";
    $content .= "</table>";
    $content .= "</form>";
    $content .= "</div>";
    return $content;
}

/**
 * Create the main panel
 */
function main($title,$content)
{
  $html = "<div id='main'>";
  if($title != null){
    $html .= "<div class='main_title'>";
    $html .= "<h2>".$title."</h2>";
    $html .= "</div>";
  }
  if(array_key_exists('error',$_SESSION)){
    if($_SESSION['error'] != null){
      $html .= "<div id='error'>";
//      $html .= "<span id='error_title'>Error!</span>";
      $html .= $_SESSION['error'];
      $html .= "</div>";
    }
  }
  if(array_key_exists('message',$_SESSION)){
    if($_SESSION['message'] != null){
//      $html .= "<span id='message_title'>Message:</span>";
      $html .= "<div id='message'>";
      $html .= $_SESSION['message'];
      $html .= "</div>";
    }
  }
  
  if($content != null) {
    $html .= "<div id='content'>";
    $html .= $content;
    $html .= "</div>";
  }
  
  $html .= "</div>";
  return $html;  
}

function get_databases_names(){
    $dir = opendir("./bibs/");
    $databases_names = array();
    while($file = readdir($dir)){
        if(is_dir("./bibs/".$file) && $file != '.' && $file != '..'){
            array_push($databases_names,$file);
        }
    }
    return $databases_names;
}

function deldir($dir) {
    $current_dir = opendir($dir);
    while($entryname = readdir($current_dir)){
        if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){
            deldir("${dir}/${entryname}");
        }
        elseif($entryname != "." and $entryname!=".."){
            unlink("${dir}/${entryname}");
        }
    }
    closedir($current_dir);
    rmdir($dir);
} 
?>
