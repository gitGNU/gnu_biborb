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

File: index.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

The starter page.

**/
?>

<?
include("functions.php");

$XHTML11_compliance = '<a class="clean" href="http://validator.w3.org/check/referer"><img src="http://www.w3.org/Icons/valid-xhtml11" alt="Valid XHTML 1.1!" height="31" width="88" class="clean"/></a>';  
$CSS2_compliance = '<a class="clean" href="http://jigsaw.w3.org/css-validator/"><img src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" height="31" width="88" class="clean"/></a>';

$mode = 'website';
//$mode = 'use';

$html_content  = html_header("BibORB","style.css","website");
$html_content .= "<h1>BibTeX On-Line References Browser (BibORB)</h1>";
if($mode == 'website'){
  $html_content .= "<h3>Description</h3>";
  $html_content .= "<p>Web based tool to &quot; browse &quot; bibliographies
 recorded in BibTeX format.</p>";
}

$html_content .= "<h3>Currently Available Bibliographies</h3>";
$html_content .= recorded_bibs2htmlarray();

if($mode == 'website'){
  $html_content .= "<h3>Based on:</h3>";
  $html_content .= "<ul>";
  $html_content .= "<li><abbr>BibTex</abbr>: input bibliographic format</li>";
  $html_content .= "<li><abbr>XML</abbr>: intermediate format for transformation purpose</li>";
  $html_content .= "<li><abbr>XSLT</abbr>: transformation to produce HTML or whatever needed output</li>";
  $html_content .= "<li><abbr>CSS2</abbr>: HTML style definition</li>";
  $html_content .= "<li><abbr>PHP</abbr>: to link previous tools together :)</li>";
  $html_content .= "</ul>";
  $html_content .= "<hr />";
  $html_content .= "<div class='down'>";
  $html_content .= $XHTML11_compliance;
  $html_content .= $CSS2_compliance;  
  $html_content .= "<a href='mailto:ggardey@club-internet.fr'>Guillaume Gardey</a>, 2003";
  $html_content .= "</div>";
}

$html_content .= html_close();

echo $html_content;

function get_recorded_bibs()
{
  $ar = opendir("./bibs");
  $tab = array();    
  while($file = readdir($ar)) {
    if(is_dir("./bibs/".$file) && $file != "." && $file != ".."){     
      array_push($tab,$file);
    }
  }
  return $tab;
}

function get_recorded_bibs_description()
{
  $ar = opendir("./bibs");
  $tab = array();    
  while($file = readdir($ar)) {
    if(is_dir("./bibs/".$file) && $file != "." && $file != ".."){
      $desc = load_file("./bibs/".$file."/description.txt");
      array_push($tab,$desc);
    }
  }
  return $tab;    
}

function recorded_bibs2htmlarray()
{
  $bibs = get_recorded_bibs();
  $bibs_desc = get_recorded_bibs_description(); 
  $html .= "<table>";
  $html .= "<thead><tr><th>Name</th><th>Description</th></tr></thead><tbody>";
  for($i=0;$i<count($bibs);$i++){
    $html .= "<tr><td>".$bibs[$i]."</td><td>".$bibs_desc[$i]."</td>";
    $html .= "<td><form action='bibindex.php' method='get'><div><input type='submit' value='Go' />";
    $html .= "<input type='hidden' name='bibname' value='$bibs[$i]' /><input type='hidden' name='mode' value='welcome' /></div></form></td></tr>";
  }
  $html .= "</tbody></table>";  
  return $html; 
}

?>