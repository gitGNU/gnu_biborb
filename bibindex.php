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

File: bibindex.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

PHP scripts to create a nice browser for bibliographies recorded in
BibTeX format.


**/

include("config.php");
include("functions.php");

session_name($session_id);
session_start();

/**
display an error if the variable 'bibname' is not set
*/
if(!array_key_exists('bibname',$_SESSION) && !array_key_exists('bibname',$_GET))
{
  die("Error: bibname is not set");
}

/**
if the session variable 'bibname' is not set, get it from GET variables
*/
if(array_key_exists('bibname',$_GET))
{
  if(array_key_exists('bibname',$_SESSION))
  {
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
  }
     
  $_SESSION['bibname'] = $_GET['bibname'];
}

/**
Register variables in session.
It is not really needed for most of them but easier to reference them in the code.
*/
// which page to display
$_SESSION['mode'] = get_value('mode',$_GET);
// which group to display
$_SESSION['group'] = get_value('group',$_GET);
// 
$_SESSION['search'] = get_value('search',$_GET);
// author search value
$_SESSION['author'] = get_value('author',$_GET);
// keywords search value
$_SESSION['keywords'] = get_value('keywords',$_GET);
// title search value
$_SESSION['title'] = get_value('title',$_GET);
// the type of entry to add
$_SESSION['add_type'] = get_value('add_type',$_GET);
// the bibtex id of an entry
$_SESSION['id'] = get_value('id',$_GET);
// an action 
$_SESSION['action'] = get_value('action',$_GET);
// display entry with abstract
$_SESSION['abstract'] = get_value('abstract',$_GET);
if($_SESSION['abstract'] == null)
{
  $_SESSION['abstract'] = $display_abstract;
}

/**
If authentication is active, check if the user has logged in.
If authentication is not active or the user is logged, modification of the bibliography
is allowed.
*/
if(!$disable_authentication)
{
  if(array_key_exists('user',$_SESSION)){
      $_SESSION['mod'] = "admin";
  }
  else{
    $_SESSION['mod'] = "user";
  }
}
else
{
  $_SESSION['mod'] = "admin";
}


/**
 Select the page to display according to the mode.
*/
switch($_SESSION["mode"])
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
 case 'details':
   echo bibindex_details();
   break;
 case 'bibtex':
   echo bibindex_bibtex();
   break;   
 case 'update':
   echo bibindex_update();
   break;
 case 'login':
   echo bibindex_login();
   break;
 case 'logout':
   echo bibindex_logout();
   break;
 case 'add':
   echo bibindex_add();
   break;
 case 'edit':
   echo bibindex_edit();
   break;
 default:
   echo bibindex_welcome();
   break;   
}

/**
unset session variables for the next page
*/
$_SESSION['error'] = null;
$_SESSION['message'] = null;

/**
This is the default Welcome page.
*/
function bibindex_welcome()
{
  $html = bibheader();  
  $html .= menu();
  $title = "<H2>BibORB: BibTeX On-line References Browser</H2>";
  $content = "This is the bibliography: <b>".$_SESSION['bibname']."</b>.<br/>";
  if($_SESSION['mod'] != 'admin') {
    if(array_key_exists('user',$_SESSION)){      
      $content .= "You are logged as <em>".$_SESSION['user']."</em>.";
    }
  }
  $content .= get_stat($_SESSION['bibname']);
  $html .= main($title,$content);
  $html .= html_close();    
  return $html;
}

/**
This is the page when the user choose to navigate using groups.
*/
function bibindex_group()
{
  $html = bibheader();  
  $html .= menu();
  $html .= group_menu();
  $title = null;
  $content = get_bibentries_of_group($_SESSION['bibname'],$_SESSION['group'],$_SESSION['mod'],$_SESSION['abstract']);
  
  if($_SESSION['group']){
    $html .= main($title,$content);
  }  
  $html .= html_close();    
  return $html;   
}


/**
Display all entries.
If the user is logged in, features are added to each entries: modify/delete
*/
function bibindex_all()
{
  $title = null;
  $html = bibheader();
  $html .= menu();
  $html .= main($title,get_all_bibentries($_SESSION['bibname'],$_SESSION['mod'],$_SESSION['abstract']));
  $html .= html_close();
  return $html;  
}

/**
Search page
*/
function bibindex_search()
{
  $title = null;
  $html = bibheader();
  $html .= menu();
  $html .= search_menu();
  if($_SESSION['search'] != null){
    $content = search_bibentries($_SESSION['bibname'],$_SESSION['search'],
				    $_SESSION['author'],$_SESSION['title'],
				 $_SESSION['keywords'],$_SESSION['mod'],$_SESSION['abstract']);
    if(strpos($content,"key") == null){
      $content = "No results";
    }

    $html .= main($title,$content);
  }
  $html .= html_close();
  return $html;  
}

/**
 Full details on a selected bibentry
*/
function bibindex_details()
{
  $html = bibheader();
  $content = get_bibentry($_SESSION['bibname'],$_SESSION['id'],$_SESSION['abstract']);
  if(array_key_exists('menu',$_GET))
  {

    if($_GET['menu']){
      $html .= menu();
      $html .= main(null,$content);
    }
    else{
      $html .= $content;
    }
  }
  else{
    $html .= $content;
  }
  
  $html .= html_close();
  return $html;  
}

/**
 Get the bibtex entry
*/
function bibindex_bibtex()
{
  return "<pre>".get_bibtex($_SESSION['bibname'],$_SESSION['id'])."</pre>";  
}


/**
Update the bibliography
*/
function bibindex_update()
{
  $html = bibheader();
  $html .= menu();

  if($_SESSION['mod'] == "admin"){
    update_xml($_SESSION['bibname']);   
    $_SESSION['message'] = "XML file succesfully updated.";
  }
  $html .= main(null,null);
  $html .= html_close();
  
  return $html;
}

/**
Display the login page
*/
function bibindex_login(){
  $html = bibheader();
  $html .= menu();
  $title = "<H2>BibORB Manager</H2>";
  $content = "<center>";
  $content .= "<table>";
  $content .= "<tr>";
  $content .= "<td>";
  $content .= "<form action='login.php?".session_name()."=".session_id()."' method='post'>";
  $content .= "<input type='text' name='login' size='15' maxlength='20' value='login'/><br/>";
  $content .= "<input type='password' name='mdp' size='15' maxlength='20' value='mdp'/>";
  $content .= "</td></tr>";
  $content .= "<tr><td><center><input type='submit' value='se connecter'/></center>";
  $content .= "</td></tr>";
  $content .= "</tr>";
  $content .= "</table>";
  $content .= "</center>";
  $html .= main($title,$content);
  $html .= html_close();
  return $html;
}

/*
logout page
*/
function bibindex_logout()
{
  $_SESSION['user'] = null;
  echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
}

/*
Edit bibtex entries
*/
function bibindex_edit()
{
  switch($_SESSION['action']){
  case 'selectentry':
    $html = bibheader();
    $html .= menu();
    $title = null;
    $content = select_entry();
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
    break;

  case 'add':
    $html = bibheader();
    $html .= menu();
    $title = null;
    $content = "<form method='post' action='add_entry.php' enctype='multipart/form-data'>";
    $content .= "<input name='".session_name()."' value='".session_id()."' type='hidden'/>";
    $content .= "<input name='add_type' value='".$_SESSION['add_type']."' type='hidden'/>";
    $content .= "<input name='mode' value='edit' type='hidden'/>";
    $content .= get_entry_fields($_SESSION['add_type']);
    $content .= "<p/><center><input type='submit' name='action' value='cancel'/>";
    $content .= "<input type='submit' name='action' value='add'/>";
    $content .= "</center></form>";
    $html .= main($title,$content);
    $html .= html_close();
    return $html;
    break;

  case 'delete':
    $bibtex = get_bibtex($_SESSION['bibname'],$_SESSION['id']);
    delete_bibtex_entry($_SESSION['bibname'],$_SESSION['id']);
    $_SESSION['message'] = "The follwoing entry was deleted: <pre>".$bibtex."</pre>";
    echo bibindex_welcome();
    break;

  case 'modify':
    $html = bibheader();
    $html .= menu();
    $title = null;
    $content = "<form method='post' action='modify.php' enctype='multipart/form-data'>";
    $content .= get_bibentry_for_edition($_SESSION['bibname'],$_SESSION['id']);
    $content .= "<center>";
    $content .= "<input type='submit' name='action' value='cancel'/>";
    $content .= "<input type='submit' name='action' value='ok' />";
    $content .= "</center>";
    $content .= "</form>";
    $html .= main($title,$content);
    $html .= html_close();
    echo $html;
    break;

  default:
    $_SESSION['error'] = "Unknown action:".$_SESSION['action'];
    echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
    break;
  }
}


/**
 Create the menu
*/
function menu()
{
  $html  = "<div class='left'>";
  $html .= "<span class='left_title'><h2>BibORB</h2></span>";
  $html .= "<div class='bibname'>".$_SESSION['bibname']."</div>";
  $html .= "<div class='menu'>";
  $html .= "<div class='menutitle'>Menu</div>";
  $html .= "<div class='menubloc'>";
  $html .= "<a class='menuitem' href='bibindex.php?mode=welcome&amp;".session_name()."=".session_id()."'>Home</a>";
  $html .= "<a class='menuitem' href='bibindex.php?mode=all&amp;".session_name()."=".session_id()."'>Display All</a>";
  $html .= "<a class='menuitem' href='bibindex.php?mode=group&amp;".session_name()."=".session_id()."'>Groups</a>";
  $html .= "<a class='menuitem' href='bibindex.php?mode=search&amp;".session_name()."=".session_id()."'>Search</a>";
  if($_SESSION['mod'] == "admin"){
    $html .= "<a class='menuitem' href='bibindex.php?mode=update&amp;".session_name()."=".session_id()."'>Update</a>";
    $html .= "<a class='menuitem' href='bibindex.php?mode=edit&amp;".session_name()."=".session_id()."&amp;action=selectentry'>Add an entry</a>";
    $html .= "<a class='menuitem' href='bibindex.php?mode=logout&amp;".session_name()."=".session_id()."'>Logout</a>";
  }
  else {
    $html .= "<a class='menuitem' href='bibindex.php?mode=login&amp;".session_name()."=".session_id()."'>Login</a>";
  }
  $html .= "</div>";
  $html .= "</div>";
  $html .= "</div>";
  return $html;  
}

/**
Create the group menu
*/
function group_menu()
{  
  if($_SESSION["group_list"] == null){    
    $_SESSION["group_list"] = get_group_list($_SESSION['bibname']);
  }
  $html = "<div class='left2'>";
  $html .= "<div class='menu'>";
  $html .= "<div class='menutitle'>Groups</div>";
  $html .= "<div class='menubloc'>";
  for($i=0;$i<sizeof($_SESSION["group_list"]);$i++){
    $html .= "<a href='bibindex.php?mode=group&amp;group=";
    $html .= $_SESSION['group_list'][$i]."&amp;".session_name()."=".session_id()."'class='menuitem'>".$_SESSION['group_list'][$i]."</a>";
  }
  $html .= "</div>";
  $html .= "</div>";
  $html .= "</div>";  

  return $html;  
}

/**
Create the main panel
*/
function main($title,$content)
{
  $html = "<div class='main'>";
  if($title != null){
    $html .= "<div class='main_title'>";
    $html .= "<h2>".$title."</h2>";
    $html .= "</div>";
  }
  if(array_key_exists('error',$_SESSION)){
    if($_SESSION['error'] != null){
      $html .= "<h3>Error:</h3>";
      $html .= "<div class='error'>";
      $html .= $_SESSION['error'];
      $html .= "</div>";
    }
  }
  if(array_key_exists('message',$_SESSION)){
    if($_SESSION['message'] != null){
      $html .= "<h3>Message:</h3>";
      $html .= "<div class='message'>";
      $html .= $_SESSION['message'];
      $html .= "</div>";
    }
  }
  
  if($content != null) {
    $html .= "<div class='content'>";
    $html .= $content;
    $html .= "</div>";
  }
  
  return $html;  
}

/**
 Create html header
*/
function bibheader()
{
  $html = html_header("BibORB - ".$_SESSION['bibname'],"style.css");
  return $html;  
}

/**
 Create Search menu
*/
function search_menu()
{
  $html = "<div class='left2'>";  
  $html .= "<div class='menu'>";
  $html .= "<div class='menutitle'>Search</div>";
  $html .= "<form action='bibindex.php?mode=search&amp;".session_name()."=".session_id()."' method='get'>";
  $html .= "<div>";  
  $html .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />";
  $html .= "<input type='hidden' name='mode' value='search' />";  
  $html .= "<input name='search' size='20' value='".$_SESSION['search']."' /><br />";
  $html .= "<input type='submit' value='Search' /><br />";
  $html .= "<input type='checkbox' name='author' value='author' ";
  if($_SESSION['author']=="author"){
    $html .= "checked='checked'";
  }
  $html .= " />Author<br />";
  $html .= "<input type='checkbox' name='title' value='title' ";
  if($_SESSION['title']=="title"){
    $html .= "checked='checked'";
  }
  $html .= "/>Title<br />";
  $html .= "<input type='checkbox' name='keywords' value='keywords' ";
  if($_SESSION['keywords']=="keywords"){
    $html .= "checked='checked'";
  }
  $html .= " />Keywords<br />";
  $html .= "</div>";  
  $html .= "</form>";
  $html .= "</div>";
  $html .= "</div>";
  
  return $html;  
}

/**
Create the form to select which type of entry to add.
*/
function select_entry(){
  $html =  "<form method='GET' action='bibindex.php'>
              <input name='mode' value='edit' type='hidden'/>
<input name='".session_name()."' value='".session_id()."' type='hidden'/>
              Select an entry type:
              <select name='add_type' size='1'>
                <option value='article'>article</option>
                <option value='book'>book</option>
                <option value='booklet'>booklet</option>
                <option value='conference'>conference</option>
                <option value='inbook'>inbook</option>
                <option value='incollection'>incollection</option>
                <option value='inproceedings'>inproceedings</option>
                <option value='manual'>manual</option>
                <option value='mastersthesis'>mastersthesis</option>
                <option value='misc'>misc</option>
                <option value='phdthesis'>phdthesis</option>
                <option value='proceedings'>proceedings</option>
                <option value='techreport'>techreport</option>
                <option value='unpublished'>unpublished</option>
              </select><br/>
<center>
              <input type='submit' name='action' value='cancel'/>
              <input type='submit' name='action' value='add'/>

</center>
          </form>";
  return $html;
}

/**
Return the input fields corresponding to a given type
*/
function get_entry_fields($type)
{
  $xml_content = load_file("./xsl/model.xml");
  $xsl_content = load_file("./xsl/model.xsl");
  $param = array("typeentry"=>$type);
  return xslt_transform($xml_content,$xsl_content,$param);
}

/**
get values in an array, null if key does not exists
 */
function get_value($key,$tab)
{
  if(array_key_exists($key,$tab)){
    return $tab[$key];
  }
  else{
    return null;
  }
  
}

?>
