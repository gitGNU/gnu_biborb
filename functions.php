<?
/**

This file is part of BibORB

Copyright (C) 2003  Guillaume Gardey

BibORB is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

BibORB is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

**/

/**

File: functions.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

  This file encapsulates some functions to transform a raw text
bibfile into 'nice' html pages linked against electronic version of
papers specified in the bibfile.

  Have a look to README for more details.

**/
?>



<?

// Update the XML file
// If more than one bibfile is present, content is merged in one XML file
// To each bibentry of abibfile.bib is assigned the group abibfile
function update_xml($bibname)
{
  $ar = opendir("./bibs/".$bibname."/");
  $tab = array();    
  while($file = readdir($ar)) {
    $inf = pathinfo($file);
    if($inf['extension']=='bib'){
      array_push($tab,$file);      
    }
  }

  if(count($tab) > 1){
    $fp = fopen("./bibs/".$bibname."/".$bibname.".xml","w");
    fwrite($fp,"<?xml version='1.0' encoding='iso-8859-1'?>\n");
    fwrite($fp,"<file name='".$bibname."'>\n");
    fclose($fp);      
    foreach($tab as $bibfile){      
      write_xml_file("./bibs/".$bibname."/".$bibname.".xml","./bibs/".$bibname."/".$bibfile,true);
    }    
    $fp = fopen("./bibs/".$bibname."/".$bibname.".xml","a");   
    fwrite($fp,"</file>");
    fclose($fp);
  }
  else{
    write_xml_file("./bibs/".$bibname."/".$bibname.".xml","./bibs/".$bibname."/".$tab[0]);
  }   
}

function write_xml_file($xmlfile,$bibfile,$append = false)
{  
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
    $xml_content .= "<file name='".$bibname."'>";    
    $xml_content .= bibtex2xml($bibfile);
    $xml_content .= "</file>\n";    
    $fp = fopen($xmlfile,"w");
    fwrite($fp,$xml_content);
    fclose($fp);
  }    
}

function bibtex2xml($bibfile,$group=NULL)
{
  $content = file($bibfile); 
  $first=1;
  $xml_content = null;
  $key = null;
  $data_content = null;
  $open_field = false;

  for($i=0;$i<sizeof($content);$i++){
    // recode &, <, >
    $patterns = array('&','<','>');
    $replace = array('&amp;','&lt;','&gt;');    
    $line = str_replace($patterns,$replace,$content[$i]);

    //new entry (spaces)@(spaces)(alphanum)(spaces){(spaces)(anychar)(spaces),
    if(preg_match("/\s*@\s*(\w*)\s*{\s*(.*)\s*,/",$line,$matches)){
      if($first==0){
	$xml_content .= end_bibentry();
      }      
      $xml_content .= new_bibentry($matches[1],$matches[2]);
      if($group!=NULL){	
	$xml_content .= bibfield("group",$group);	
      }
      
      $first = 0;
    } else if(preg_match("/\s*(\w*)\s*=(.*)/",$line,$matches)){
      $key = $matches[1];
      $data = $matches[2];
            
      // bibfield in an single line
      if(preg_match("/\s*{\s*(.*)\s*},?/",$data,$matches) ||
	 preg_match("/\s*\"\s*(.*)\s*\",?/",$data,$matches)){
	$xml_content .= bibfield($key,$matches[1]);
      }
      // bibfield in several lines (data)
      else if(preg_match("/\s*{\s*(.*)/",$data,$matches) ||
	      preg_match("/\s*\"\s*(.*)/",$data,$matches)){	
	$data_content = $matches[1];	
	$openfield=true;
      }
    }
    // end of an entry
    else if(preg_match("/\s*(.*)\s*},?/",$line,$matches) ||
	    preg_match("/\s*(.*)\s*\",?/",$line,$matches)){
      //if $data_content is null, end of a an entry
      // or additionnal brace or quote, who knows :)
      if($openfield){	
	$data_content .= $matches[1];
	$data_content = preg_replace("/\s+/"," ",$data_content);	
	$xml_content .= bibfield($key,$data_content);
	$openfield = false;
      }      
    }
    else{
      $data_content .= $line;	
    }
  }
  
  if($first == 0){
    $xml_content .= end_bibentry();
  }

  return $xml_content;
}

// Create a new bibentry
function new_bibentry($type,$id)
{
  return "<bibentry type='".strtolower($type)."' id='".$id."'>\n";
}
// Create an end tag for bibentry
function end_bibentry()
{
  return $buffer."</bibentry>\n";
}

// Create a new bibfield tag
function bibfield($type,$value)
{
  if(strtolower($type) == "author"){
    $value = ereg_replace(" and ",", ",$value);
  }

  return "<".strtolower($type).">".$value."</".strtolower($type).">\n";
}

function load_file($filename)
{
  return implode('',file($filename));  
}

function xslt_transform($xmlstring,$xslstring,$xslparam = array())
{
  $xh = xslt_create();
  xslt_set_encoding($xh,"iso-8859-1");  
  $arguments = array('/_xml' => $xmlstring, '/_xsl' => $xslstring);  
  $result = xslt_process($xh,'arg:/_xml','arg:/_xsl',NULL,$arguments,$xslparam);
  xslt_free($xh);
  return $result;  
}

function html_header($title = NULL, $style = NULL, $bodyclass=NULL)
{
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
  $html .= "</head>";
  $html .= "<body";
  if($bodyclass){    
    $html .= " class='$bodyclass' ";
  }
  $html .= ">";
    
  return $html;  
}

function html_close()
{
  return "</body></html>";  
}

function get_group_list()
{
  $xml_content = load_file("./bibs/".$GLOBALS['bibname']."/".$GLOBALS['bibname'].".xml");
  $xsl_content = load_file("./xsl/group_list.xsl");  
  $group_list = xslt_transform($xml_content,$xsl_content);
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

function load_xml_bibfile($bibname)
{
  return load_file("./bibs/".$bibname."/".$bibname.".xml");
}

function get_all_bibentries($bibname)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");
  return xslt_transform($xml_content,$xsl_content);
}

function get_bibentries_of_group($bibname,$groupname)
{
  $xml_content = load_xml_bibfile($bibname);    
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");  
  $param = array('groupval'=>$groupname);
  return xslt_transform($xml_content,$xsl_content,$param);
}

function search_bibentries($bibname,$value,$forauthor,$fortitle,$forkeywords){
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");  
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

  return xslt_transform($xml_content,$xsl_content,$param);
}


function get_bibtex($bibname,$bibid)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2bibtex.xsl");
  $param = array('id'=>$bibid);
  return xslt_transform($xml_content,$xsl_content,$param);  
}

function get_bibentry($bibname,$bibid)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");
  $param = array('id'=>$bibid,'mode'=>"abstract");
  return xslt_transform($xml_content,$xsl_content,$param);
}

function get_stat($bibname)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/count.xsl");  
  //Nombre d'enregistrement
  $nb_record = xslt_transform($xml_content,$xsl_content);  
  $record = explode(".",$nb_record);
  
  $html  = "<h3>Statistics</h3>";
  $html .= "<table>";
  $html .= "<tbody>";
  $html .= "<tr>";
  $html .= "<td>Number of recorded bibtex entries:</td>";
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

?>