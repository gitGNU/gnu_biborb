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

	$xml_content = "<?xml version='1.0' encoding='ISO-8859-1'?>";
	$xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/'>";
	
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
			$entries_count++;
			array_push($ids,trim($matches[2]));
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
	$xml_content .= "</bibtex:file>";
	
    return array($entries_count,$ids,$xml_content);
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
 * Load an XML file
 */
function load_xml_bibfile($bibname)
{
    return load_file(xmlfilename($bibname));
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
                "¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
                "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
}

/**
 *  check_login($thelogin,$thepasswd)
 *  Authentication test
 */
function check_login($thelogin,$thepasswd){
  global $host,$dbuser,$pass,$db,$table;
  
  $connect = @mysql_connect($host,$dbuser,$pass) or die("Unable to connect to mysql");
  $base = @mysql_select_db($db,$connect);
  if(!$base){
    echo "Unable to connect to the users database";
    exit();
  }
  else {
    $query = "SELECT login,password FROM $table WHERE login='$thelogin' AND password=md5('$thepasswd')";
    $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
    return (mysql_num_rows($result)>0);
  }
}

?>
