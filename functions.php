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
 * 
 * File: functions.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 *      
 *      Some generic functions
 */

/**
 * load variables and functions
 */
require_once("config.php");
require_once("utilities.php");
require_once("PARSEENTRIES.php");

/**
 * format bibtex data to xml
 */
function format_bibtex_data($field,$data){
    $res = null;
    if($field == 'groups'){
        //remove line breaks, spaces, curly brackets, quotes...
        $pattern = array("\s+","\n","\r");
        $data = trim(str_replace($pattern," ",$data));
        if(preg_match("/{(.*)}(\s*,|\s*}|\s*)/",$data,$matches) ||
           preg_match("/\"(.*)\"(\s*,|\s*}|\s*)/",$data,$matches)){
            $data = $matches[1];
        }
        else if(preg_match("/(.*),/",$data,$matches)){
            $data = $matches[1];
        }
        // split groups in several group tag
        $res = "<bibtex:groups>\n";
        $group_array = split(',',$data);
        foreach($group_array as $gr){
            if(trim($gr) != ''){
                $res .= bibfield("group",trim($gr));
            }
        }
        $res .= "</bibtex:groups>\n";
    }
    else if($field == 'abstract'){
        if($data[strlen($data)-1] == ','){
            $data = substr($data,0,strlen($data)-1);
        }
        if(strpos($data,"{") == 0 && strpos($data,"}") == strlen($data)-1){
            $data = substr($data,1,strlen($data)-2);
        }
        else if(strpos($data,"\"") == 0 && strpos($data,"\"") == strlen($data)-1){
            $data = substr($data,1,strlen($data)-2);
        }
        $res .= bibfield($field,trim($data));
    }
    else{
        //remove line breaks, spaces, curly brackets, quotes...
        $pattern = array("\s+","\n","\r");
        $data = trim(str_replace($pattern," ",$data));
        if(preg_match("/{(.*)}(\s*,|\s*}|)/",$data,$matches) ||
         preg_match("/\"(.*)\"(\s*,|\s*}|)/",$data,$matches)){
            $data = $matches[1];
        }
        else if(preg_match("/(.*),/",$data,$matches)){
            $data = $matches[1];
        }
        $res .= bibfield($field,trim($data));
    }
    return $res;
}
/**
 * bibtex2xml
 * Transform a BibTeX string into an XML string
 */
function bibtex2xml($bibtext,$group=NULL){
    
    $content = $bibtext;          // content to analyse
    
	$first = 1;							// is it the first entry analyzed?
    $xml_content = null;                // xml content
    $key = null;                        // bibtex field
    $data_content = null;               // bibtex field value
    $openfield = false;                 // true if a value is on several lines
    $type = null;                       // type of the bibtex entry being analyzed
	$entries_count = 0;
	$ids = array();
    $data = null;

	$xml_content = "<?xml version='1.0' encoding='ISO-8859-1'?>";
	$xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/'>";
	
	// remove uneeded spaces
    $content = preg_replace("/\s+/"," ",$content);
    // recode &, <, > and strip slashes
    for($i=0;$i<sizeof($content);$i++){
        // recode &, <, >
        $patterns = array('&','<','>');
        $replace = array('&amp;','&lt;','&gt;');    
        $content[$i] = trim(stripslashes(str_replace($patterns,$replace,$content[$i])));
    }
    
    $i = 0;
    while($i < sizeof($content)){
        $line = $content[$i];
        //new entry @(alphanum){(anychar),
        if(preg_match("/@\s?(\w*)\s?{(.*),/",$line,$matches)){
			$entries_count++;
			array_push($ids,trim($matches[2]));
			// If it isn't the first entry, close the previous one
            if($first==0){
                if($key){
                    // get the last curly bracket in data end remove it
                    $k = strrpos($data,"}");
                    $xml_content .= format_bibtex_data($key,substr($data,0,$k));
                    $key = null;
                }
                $xml_content .= end_bibentry($type);
            }
			// save the type to add the good closing tag
            $type = $matches[1];
			// create the xml start tag
            $xml_content .= new_bibentry($type,trim($matches[2]));
			
            $first = 0;
        }
        // detect a new field
        else if(preg_match("/\s?(\w*)\s?=\s?(.*)/",$line,$matches)){
            // close the previous field if exists
            if($key){
                $xml_content .= format_bibtex_data($key,$data);
            }
            
            $key = $matches[1];
            // new version of biborb: translate group into groups
            if($key == 'group'){
                $key = 'groups';
            }
            $data = trim($matches[2]);
        }
        else{
            //preserve line break (for abstract)
            // for the other fields, line breaks are removed in format_bibtex_data
            $data .= "\n".$line;
        }
        $i++;
    }
    
    if($first == 0){
        if($key){
            // get the last curly bracket in data end remove it
            $k = strrpos($data,"}");
            $xml_content .= format_bibtex_data($key,substr($data,0,$k));
        }
        $xml_content .= end_bibentry($type);
    }
    $xml_content .= "</bibtex:file>";

    return array($entries_count,$ids,$xml_content);
}

/**
 * new_bibentry($type,$id)
 * Create a start tag for a bibentry
 * Returns: <bitex:entry id='$id'><bibtex:$type>
 */
function new_bibentry($type,$id){
  return "<bibtex:entry id=\"".$id."\">\n"."<bibtex:".strtolower($type).">\n";
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
 * Extract information from an array to produce an XML string
 */
function bibtex_array_to_xml($tab){
    
    // get keys present in the tab
    $newtab = extract_bibtex_data($tab);
    
    $xml  = "<?xml version='1.0' encoding='iso-8859-1'?>";
    $xml .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' name='temp'>";
    $xml .= "<bibtex:entry id='".$newtab['id']."'>";
    $xml .= "<bibtex:".$tab['type'].">";
    foreach($newtab as $key => $value){
        if($key != 'groups' && $key!= 'type' && $key != 'id'){
            $xml .= "<bibtex:".$key.">";
            $xml .= stripslashes(trim(myhtmlentities($value)));
            $xml .= "</bibtex:".$key.">";
        }
        else if($key == 'groups') {
            $xml .= "<bibtex:groups>";
            $groupvalues = split(',',$value);
            foreach($groupvalues as $gr){
                $xml .= "<bibtex:group>";
                $xml .= stripslashes(trim(myhtmlentities($gr)));
                $xml .= "</bibtex:group>";
            }
            $xml .= "</bibtex:groups>";
        }
    }
    $xml .= "</bibtex:".$tab['type'].">";
    $xml .= "</bibtex:entry>";
    $xml .= "</bibtex:file>";
    return $xml;
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
 * Create the main panel
 */
function main($title,$content,$error = null,$message = null)
{
  $html = "<div id='main'>";
  if($title != null){
    $html .= "<div class='main_title'>";
    $html .= "<h2>$title</h2>";
    $html .= "</div>";
  }
  if($error){
	$html .= "<div id='error'>$error</div>";
  }
  if($message){
      $html .= "<div id='message'>$message</div>";
  }
  
  if($content != null) {
    $html .= "<div id='content'>$content</div>";
  }
  
  $html .= "</div>";
  return $html;  
}


/**
    Del a directory
 */
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

/**
    Remove accents of a string.
 */
function remove_accents($string){
    return strtr($string,
                "•µ¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷ÿŸ⁄€‹›ﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘˙˚¸˝ˇ",
                "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
}

/**
    Generate the add all to basket div section
*/

function add_all_to_basket_div($ids,$mode,$extraparam=null){
    // ensure localization is set up
    load_i18n_config($_SESSION['language']);

    $html = "<div class='addtobasket'>";
    $html .= _("Add all entries to the basket.");
    $addalllink = "bibindex.php?mode=$mode&action=add_to_basket&id=";
    foreach($ids as $id){
        $addalllink .= "$id*";
    }
    if($extraparam){
        $addalllink .= "&$extraparam";
    }
    $html .= "<a href='".$addalllink."'>";
    $html .= "<img src='./data/images/add.png' alt='add' />";
    $html .= "</a>";
    $html .= "</div>";
    return $html;
}

/**
    Generate the sort div section
*/
function sort_div($selected_sort,$mode,$group){
    // ensure the localization is set up
    load_i18n_config($_SESSION['language']);

    $html = "<div class='sort'>";
    $html .= _("Sort by:");
    $html .= "<form method='get' action='bibindex.php'>";
    $html .= "<fieldset>";
    $html .= "<select name='sort' size='1'>";
    
    if($selected_sort == 'ID'){
        $html .= "<option value='ID' selected='selected'>ID</option>";
    }
    else{
        $html .= "<option value='ID'>ID</option>";
    }
    
    if($selected_sort == 'title'){
        $html .= "<option value='title' selected='selected'>"._("Title")."</option>";
    }
    else{
        $html .= "<option value='title'>"._("Title")."</option>";
    }
    
    if($selected_sort == 'year'){
        $html .= "<option value='year' selected='selected'>"._("Year")."</option>";
    }
    else{
        $html .= "<option value='year'>"._("Year")."</option>";
    }
    $html .= "</select>";
    $html .= "<input type='hidden' name='mode' value='$mode'/>";
    if($group){
        $html .= "<input type='hidden' name='group' value='$group'/>";
    }
    $html .= "<input type='submit' value='"._("Sort")."'/>";
    $html .= "</fieldset>";
    $html .= "</form>";
    $html .= "</div>";
    
    return $html;
}

function convert_array_to_xml_entry($tab){
    
    $xml = "<bibtex:entry id='".$tab['id']."'>";
    $xml .= "<bibtex:".$tab['type'].">";
    foreach($tab as $key => $value){
        if($key != 'groups' && $key!= 'type' && $key != 'id'){
            $xml .= "<bibtex:".$key.">";
            $xml .= stripslashes(trim(myhtmlentities($value)));
            $xml .= "</bibtex:".$key.">";
        }
        else if($key == 'groups') {
            $xml .= "<bibtex:groups>";
            $groupvalues = split(',',$value);
            foreach($groupvalues as $gr){
                $xml .= "<bibtex:group>";
                $xml .= stripslashes(trim(myhtmlentities($gr)));
                $xml .= "</bibtex:group>";
            }
            $xml .= "</bibtex:groups>";
        }
    }
    $xml .= "</bibtex:".$tab['type'].">";
    $xml .= "</bibtex:entry>";
    return $xml;
}

function convert_bibtex_to_xml($string){
    $bibtex_parser = new PARSEENTRIES();
	$bibtex_parser->loadString($string);
	$bibtex_parser->extractEntries();
    $res = $bibtex_parser->returnArrays();

    $ids = array();
    $xml_content = "<?xml version='1.0' encoding='ISO-8859-1'?>";
    $xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/'>";
    foreach($res[2] as $entry){
        $xml_content .= convert_array_to_xml_entry($entry);
        array_push($ids,$entry['id']);
    }
    $xml_content .= "</bibtex:file>";
    return array(count($res[2]),$ids,$xml_content);;
}

?>