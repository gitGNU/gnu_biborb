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

File: bibindex.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

PHP scripts to create a nice browser for bibliographies recorded in
BibTeX format.


**/
?>

<?

include("functions.php");

// Gets GET variables
$mode = $HTTP_GET_VARS['mode'];
$group = $HTTP_GET_VARS['group'];
$author = $HTTP_GET_VARS['author'];
$keywords = $HTTP_GET_VARS['keywords'];
$title = $HTTP_GET_VARS['title'];
$bibname = $HTTP_GET_VARS['bibname'];
$search = $HTTP_GET_VARS['search'];
$id = $HTTP_GET_VARS['id'];

// The list of different group is computed once
// The list is then passed by GET method if stay in 'group' browsing
if($HTTP_GET_VARS['group_list'] != null)
{
  $group_list = explode(" ",$HTTP_GET_VARS['group_list']);
}

// Select the page to display
switch($mode)
{
 case 'welcome':
   echo bibindex_welcome();   
   break;
 case 'group':
   echo bibindex_group();
   break;   
 case 'all':
   echo bibindex_all();
   break;
 case 'search':
   echo bibindex_search();
   break;
 case 'abstract':
   echo bibindex_abstract();
   break;
 case 'bibtex':
   echo bibindex_bibtex();
   break;   
 case 'update':
   update_xml($bibname);   
   echo bibindex_welcome();
   break;   
 default:
   echo bibindex_welcome();
   break;   
}


// Welcome Page
// Menu + Stats details
function bibindex_welcome()
{
  $html = bibheader();  
  $html .= menu();  
  $html .= main(get_stat($GLOBALS['bibname']));
  $html .= html_close();    
  return $html;
}

// Menu + Group Menu + Group entries
function bibindex_group()
{
  $html = bibheader();  
  $html .= menu();
  $html .= group_menu();
  if($GLOBALS['group']){
    $html .= main(get_bibentries_of_group($GLOBALS['bibname'],$GLOBALS['group']));
  }  
  $html .= html_close();    
  return $html;   
}


// Menu + Display all bib entries
function bibindex_all()
{
  $html = bibheader();
  $html .= menu();
  $html .= main(get_all_bibentries($GLOBALS['bibname']));
  $html .= html_close();
  return $html;  
}

// Menu + Search Menu + Results
function bibindex_search()
{
  $html = bibheader();
  $html .= menu();
  $html .= search_menu();  
  if($GLOBALS['search'] != null){
    $html .= main(search_bibentries($GLOBALS['bibname'],$GLOBALS['search'],
				    $GLOBALS['author'],$GLOBALS['title'],
				    $GLOBALS['keywords']));    
  }  
  $html .= html_close();
  return $html;  
}

// Full details on a selected bibentry
function bibindex_abstract()
{
  $html = bibheader();
  $html .= get_bibentry($GLOBALS['bibname'],$GLOBALS['id']);  
  $html .= html_close();
  return $html;  
}

// Get the bibtex entry
function bibindex_bibtex()
{
  return get_bibtex($GLOBALS['bibname'],$GLOBALS['id']);  
}

// Create the menu
function menu()
{
  $html  = "<div class='left'>";
  $html .= "<h2>BibORB</h2>";
  $html .= "<div class='bibname'>".$GLOBALS['bibname']."</div>";
  $html .= "<div class='menu'>";
  $html .= "<div class='menutitle'>Menu</div>";
  $html .= "<div class='menubloc'>";
  $html .= "<a href='bibindex.php?bibname=".$GLOBALS['bibname']."&amp;mode=welcome'>Home</a><br/>";
  $html .= "<a href='bibindex.php?bibname=".$GLOBALS['bibname']."&amp;mode=all'>Display All</a><br/>";
  $html .= "<a href='bibindex.php?bibname=".$GLOBALS['bibname']."&amp;mode=group'>Groups</a><br/>";
  $html .= "<a href='bibindex.php?bibname=".$GLOBALS['bibname']."&amp;mode=search'>Search</a><br/>";
  $html .= "<a href='bibindex.php?bibname=".$GLOBALS['bibname']."&amp;mode=update'>Update</a><br/>";
  $html .= "</div>";
  $html .= "</div>";
  $html .= "</div>";
  return $html;  
}

//Create the group menu
function group_menu()
{  
  if($GLOBALS["group_list"] == null){    
    $GLOBALS["group_list"] = get_group_list();    
  }
  $html = "<div class='left2'>";
  $html .= "<div class='menu'>";
  $html .= "<div class='menutitle'>Groups</div>";
  $html .= "<div class='menubloc'>";
  for($i=0;$i<sizeof($GLOBALS["group_list"]);$i++){
    if($i!=0){
      $html .= " - ";
    }    
    $html .= "<a href='bibindex.php?mode=group&amp;group=";
    $html .= $GLOBALS['group_list'][$i]."&amp;bibname=".$GLOBALS['bibname']."&amp;group_list=".implode(' ',$GLOBALS['group_list'])."' class='menuitem'>".$GLOBALS['group_list'][$i]."</a>";
  }
  $html .= "</div>";
  $html .= "</div>";
  $html .= "</div>";  

  return $html;  
}

//Create the main panel
function main($content)
{
  $html = "<div class='main'>".$content."</div>";  
  return $html;  
}

// Create html header
function bibheader()
{
  $html = html_header("BibORB - ".$GLOBALS['bibname'],"style.css");
  return $html;  
}

// Create Search menu
function search_menu()
{
  $html = "<div class='left2'>";  
  $html .= "<div class='menu'>";
  $html .= "<div class='menutitle'>Search</div>";
  $html .= "<form action='bibindex.php?bibname=".$GLOBALS['bibname']."&amp;mode=search' method='get'>";
  $html .= "<div>";  
  $html .= "<input type='hidden' name='bibname' value='".$GLOBALS['bibname']."' />";
  $html .= "<input type='hidden' name='mode' value='search' />";  
  $html .= "<input name='search' size='20' value='".$GLOBALS['search']."' /><br />";
  $html .= "<input type='submit' value='Search' /><br />";
  $html .= "<input type='checkbox' name='author' value='author' ";
  if($GLOBALS['author']!=null){
    $html .= "checked='checked'";
  }
  $html .= " />Author<br />";
  $html .= "<input type='checkbox' name='title' value='title' ";
  if($GLOBALS['title']!=null){
    $html .= "checked='checked'";
  }
  $html .= "/>Title<br />";
  $html .= "<input type='checkbox' name='keywords' value='keywords' ";
  if($GLOBALS['keywords']!=null){
    $html .= "checked='checked'";
  }
  $html .= " />Keywords<br />";
  $html .= "</div>";  
  $html .= "</form>";
  $html .= "</div>";
  $html .= "</div>";
  
  return $html;  
}

?>