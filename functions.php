<?php
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

include("config.php");

// Update the XML file
// If more than one bibfile is present, content is merged in one XML file
// To each bibentry of abibfile.bib is assigned the group abibfile
function update_xml($bibname)
{
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

/*
Convert a bibtex file to an xml file.
*/
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
    $xml_content .= "<bibtex:file xmlns:bibtex='http://bibtexml.sf.net/' name='".$bibname."'>\n";    
    $xml_content .= bibtex2xml($bibfile);
    $xml_content .= "</bibtex:file>\n";    
    $fp = fopen($xmlfile,"w");
    fwrite($fp,$xml_content);
    fclose($fp);
  }    
}


/*
update the .bib file according to data present in the xml file.
 */
function xml2bibtex($bibname)
{

  $xmlfile = "./bibs/".$bibname."/".$bibname.".xml";
  $bibfile = "./bibs/".$bibname."/".$bibname.".bib";
  // get the number of entries
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/count.xsl");  
  //Nombre d'enregistrement
  $nb_record = xslt_transform($xml_content,$xsl_content);  
  $record = explode(".",$nb_record);
  $nb = $record[0];
  $xml_content = load_file($xmlfile);
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
  
  $fp = fopen($bibfile,"w");
  fwrite($fp,$bibtex);
  fclose($fp);
}

/*
Translate a bibtex file into XML
*/
function bibtex2xml($bibfile,$group=NULL)
{
  $content = file($bibfile); 
  $first=1;
  $xml_content = null;
  $key = null;
  $data_content = null;
  $openfield = false;
  $type = null;

  for($i=0;$i<sizeof($content);$i++){
    // recode &, <, >
    $patterns = array('&','<','>');
    $replace = array('&amp;','&lt;','&gt;');    
    $line = str_replace($patterns,$replace,$content[$i]);

    //new entry (spaces)@(spaces)(alphanum)(spaces){(spaces)(anychar)(spaces),
    if(preg_match("/\s*@\s*(\w*)\s*{\s*(.*)\s*,/",$line,$matches)){
      if($first==0){
        $xml_content .= end_bibentry($type);
      }
      $type = $matches[1];
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
    $xml_content .= end_bibentry($type);
  }

  return $xml_content;
}

// Create a new bibentry
function new_bibentry($type,$id)
{
  return "<bibtex:entry id='".$id."'>\n"."<bibtex:".strtolower($type).">\n";
}
// Create an end tag for bibentry
function end_bibentry($type)
{
  return "</bibtex:".strtolower($type).">\n</bibtex:entry>\n";
}

// Create a new bibfield tag
function bibfield($type,$value)
{
  /*  if(strtolower($type) == "author"){
    $value = ereg_replace(" and ",", ",$value);
  }*/

  return "<bibtex:".strtolower($type).">".$value."</bibtex:".strtolower($type).">\n";
}

//load a text file
function load_file($filename)
{
  return implode('',file($filename));  
}

// XSLT processor
function xslt_transform($xmlstring,$xslstring,$xslparam = array())
{
  $xh = xslt_create();
  xslt_set_encoding($xh,"iso-8859-1");

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

// Create an HTML header
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

// Close an HTML page.
function html_close()
{
  return "</body></html>";  
}

function get_group_list($bibname)
{
  $xml_content = load_file("./bibs/".$bibname."/".$bibname.".xml");
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

/*
Load an XML file
 */
function load_xml_bibfile($bibname)
{
  return load_file("./bibs/".$bibname."/".$bibname.".xml");
}

/*
Return an html output of all entries of a bibfile
*/
function get_all_bibentries($bibname,$mode,$abstract)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");
  $param = array();
  $param["mode"] = $mode;
  if($abstract){
    $param['type'] = "details";
  }
  
  return xslt_transform($xml_content,$xsl_content,$param);
}

/*
Return an html output of entries of a given group
*/
function get_bibentries_of_group($bibname,$groupname,$mode,$abstract)
{
  $xml_content = load_xml_bibfile($bibname);    
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");  
  $param = array();
  $param["groupval"]=$groupname;
  $param["mode"] = $mode;
  if($abstract){
    $param['type'] = "details";
  }
 
  return xslt_transform($xml_content,$xsl_content,$param);
}

/*
Return an html output of entries matching search paramters
*/
function search_bibentries($bibname,$value,$forauthor,$fortitle,$forkeywords,$mode,$abstract){
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
  $param["mode"] = $mode;
  if($abstract){
    $param['type'] = "details";
  }

  return xslt_transform($xml_content,$xsl_content,$param);
}

/*
Translate an entry into bibtex format
*/
function get_bibtex($bibname,$bibid)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2bibtex.xsl");
  $param = array('id'=>$bibid);
  return xslt_transform($xml_content,$xsl_content,$param);  
}

/*
Return an html output of a given entry
*/
function get_bibentry($bibname,$bibid,$abstract)
{
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/xml2htmltab.xsl");
  if($abstract){
    $param = array('id'=>$bibid,'type'=>"details");
  }
  else{
    $param = array('id'=>$bibid);
  }
  
  return xslt_transform($xml_content,$xsl_content,$param);
}

/*
Return a nice formulary to modify an entry
*/
function get_bibentry_for_edition($bibname,$bibid,$add=1)
{
  $xml_content = load_file("./xsl/model.xml");
  $xsl_content = load_file("./xsl/xml2htmledit.xsl");
  $param = array('id' => $bibid,'bibname' => "file:".realpath("bibs/".$bibname."/".$bibname.".xml"));
  $param['add'] = $add;
  
  return xslt_transform($xml_content,$xsl_content,$param);
}

/*
Get some stats of the database
*/
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

/*
delete only a bibtex entry, do not erase files
1) delete from the bibtex file
2) update the bibtex file according to the xml file
*/
function delete_only_bibtex_entry($bibname,$id)
{
  // get the number of entries
  $xml_content = load_xml_bibfile($bibname);
  $xsl_content = load_file("./xsl/count.xsl");  
  //Nombre d'enregistrement
  $nb_record = xslt_transform($xml_content,$xsl_content);  
  $record = explode(".",$nb_record);
  $nb = $record[0];
  
  $xsl_content = load_file("./xsl/delete.xsl");
  $param = array('id'=>$id);
  $xh = xslt_create();
  xslt_set_encoding($xh,"iso-8859-1");
  $arguments = array('/_xml' => $xml_content, '/_xsl' => $xsl_content);  
  $result = xslt_process($xh,'arg:/_xml','arg:/_xsl',NULL,$arguments,$param);

  if(!$result) {
    if($nb!=1){
      die(sprintf("Impossible de traiter le document XSLT [%d]: %s", 
		  xslt_errno($xh), xslt_error($xh)));
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

/*
delete a bibtex entriy
1) delete from the bibtex file
2) update the bibtex file according to the xml file
3) delete papers from the database
*/
function delete_bibtex_entry($bibname,$id)
{
  $xml_content = load_xml_bibfile($bibname,$id);
  $xsl_content = load_file("./xsl/delete.xsl");
  $param = array('id'=>$id);  
  $newxml = xslt_transform($xml_content,$xsl_content,$param);
  
  // detect all file corresponding to this id.
  $ar = opendir("./bibs/".$bibname."/papers/");
  $tab = array(); 
  while($file = readdir($ar)) {
    $inf = pathinfo($file);
    if(strcmp(substr($inf['basename'],0,strlen($id)+1),$id.".")==0){
      array_push($tab,$file);
    }
  }
  
  foreach($tab as $file){
    unlink("./bibs/".$bibname."/papers/".$file);
  }
 
  // update the xml file.
  $fp = fopen("./bibs/".$bibname."/".$bibname.".xml","w");
  fwrite($fp,$newxml);
  fclose($fp);
  
  //update the bibtex file.
  xml2bibtex($bibname);
  // update the list of groups
  $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

// Test if a bibtex entry with a given ID exists in a bibtex file.
function exists_entry_with_id($id){
  $content = file("./bibs/".$_SESSION['bibname']."/".$_SESSION['bibname'].".bib");
  $found = 1;
  for($i=0;$i<sizeof($content) && $found!=0 ;$i++){
    if(preg_match("/\s*@\s*(\w*)\s*{\s*(.*)\s*,/",$content[$i],$matches)){
      $found = strcmp($matches[2],$id);
    }
  }
  return ($found==0);
}

// Add a bibtex entry to a bibtex file
function add_bibtex_entry($type,$tab,$urlfile,$urlzipfile,$pdffile){
  $filename = "./bibs/".$_SESSION['bibname']."/".$_SESSION['bibname'].".bib";
  $file = fopen($filename,"a+");
  fwrite($file,"\n\n");
  fwrite($file,to_bibtex($type,$tab,$urlfile,$urlzipfile,$pdffile));
  fclose($file);
  update_xml($_SESSION['bibname']);
  // update the list of groups
  $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
}

// convert to BibTeX according to POST data
function to_bibtex($type,$tab,$urlfile,$urlzipfile,$pdffile){
  $bibtex_entries = array("_id", "_address", "_annote", "_author", "_booktitle", "_chapter", "_crossref", "_edition", "_editor", "_howpublished", "_institution", "_journal", "_key", "_month", "_note", "_number", "_organisation", "_pages", "_publisher", "_school", "_series", "_title", "_type", "_volume", "_year","_abstract", "_keywords","_url","_urlzip","_pdf","_group","_website","_longnotes");
    
  if ($urlfile != null){
    $tab['_url'] = $urlfile;
  }
  if ($urlzipfile != null){
    $tab['_urlzip'] = $urlzipfile;
  }
  if ($pdffile != null){
    $tab['_pdf'] = $pdffile;
  }
  $array_key = array_keys($tab);
  $txt = "@".$type."{".$tab['_id'].",\n";
  $first = 1;
  foreach($array_key as $key){
    if($key != "_id" && in_array($key,$bibtex_entries)){
      if($tab[$key] != ""){
        if($first == 0){
          $txt .= ",\n";
        }
        $first = 0;
        $txt .= "\t".substr($key,1)." = {".stripslashes($tab[$key])."}";
      }
    }
  }

  $txt .= "\n}\n";
  
  return $txt;
}

//same thing but html formatted
function to_bibtex_tab($type,$tab,$urlfile,$urlzipfile,$pdffile){
  $bibtex_entries = array("_id", "_address", "_annote", "_author", "_booktitle", "_chapter", "_crossref", "_edition", "_editor", "_howpublished", "_institution", "_journal", "_key", "_month", "_note", "_number", "_organisation", "_pages", "_publisher", "_school", "_series", "_title", "_type", "_volume", "_year","_abstract", "_keywords","_url","_urlzip","_pdf","_group","_website","_longnotes");
    
  if ($urlfile != null){
    $tab['_url'] = $urlfile;
  }
  if ($urlzipfile != null){
    $tab['_urlzip'] = $urlzipfile;
  }
  if ($pdffile != null){
    $tab['_pdf'] = $pdffile;
  }
  $array_key = array_keys($tab);
  $txt = "<table class='bibtex'>";
  $txt .= "<tbody>";
  $txt .= "<tr>";
  $txt .= "<td>"."@".$type."{".$tab['_id'].",</td>";
  $txt .= "</tr>";

  foreach($array_key as $key){
    if($key != "_id" && in_array($key,$bibtex_entries)){
      if($tab[$key] != ""){
	$txt .= "<tr>";
        $txt .= "<td>".substr($key,1)."</td><td> = {".stripslashes($tab[$key])."},</td>";
	$txt .= "</tr>";
      }
    }
  }
  
  $txt .= "</tbody></table>}<br/>";
  
  return $txt;
}

/*
Use to change the base name of a file, keeping its extension
returns the new name
*/
function get_new_name($old,$bibid)
{
  $elem = explode(".",$old);
  
  $newname = $bibid;
  
  for($i=1;$i<sizeof($elem);$i++){
    $newname .= ".".$elem[$i];
  }
  
  return $newname;
}

?>