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

File: modify.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

This PHP script is called to modify an entry to the bibliography.


**/

include("config.php");
include("functions.php");

session_name($session_id);
session_start();

if(strcmp($_POST['action'],"cancel") == 0){
  echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
}
else 
{
  $urlfile = null;
  $urlzipfile = null;
  $pdffile = null;
  
  // ok, so how i update?
  // delete and create :)
  
  if(file_exists($_FILES['url']['tmp_name'])){
    $infofile = pathinfo($_FILES['url']['name']);
    $urlfile = get_new_name($infofile['basename'],$_POST['id']);
    $path = "./bibs/".$_SESSION['bibname']."/papers/".$urlfile;
    if(file_exists($path)){
      unlink($path);
    }
    move_uploaded_file($_FILES['url']['tmp_name'],$path);
    chmod($path,0644);
  }
  else {
    if($_POST['current_url'] != null){
      $urlfile = $_POST['current_url'];
    }
  }
  
  if(file_exists($_FILES['urlzip']['tmp_name'])){
    $infofile = pathinfo($_FILES['urlzip']['name']);
    $urlzipfile = get_new_name($infofile['basename'],$_POST['id']);
    $path = "./bibs/".$_SESSION['bibname']."/papers/".$urlzipfile;
    if(file_exists($path)){
      unlink($path);
    }
    move_uploaded_file($_FILES['urlzip']['tmp_name'],$path);
    chmod($path,0644);
  }
  else {
    if($_POST['current_urlzip'] != null){
      $urlzipfile = $_POST['current_urlzip'];
    }
  }  

  if(file_exists($_FILES['pdf']['tmp_name'])){
    $infofile = pathinfo($_FILES['pdf']['name']);
    $pdffile = get_new_name($infofile['basename'],$_POST['id']);
    $path = "./bibs/".$_SESSION['bibname']."/papers/".$pdffile;
    if(file_exists($path)){
      unlink($path);
    }
    move_uploaded_file($_FILES['pdf']['tmp_name'],$path);
    chmod($path,0644);
  }
  else {
    if($_POST['current_pdf'] != null){
      $pdffile= $_POST['current_pdf'];
    }
  }

  delete_only_bibtex_entry($_SESSION['bibname'],$_POST['_id']);
  add_bibtex_entry($_POST['add_type'],$_POST,$urlfile,$urlzipfile,$pdffile);

  $_SESSION['message'] = "The following entry was updated:<br/>";
  $_SESSION['message'] .= to_bibtex_tab($_POST['add_type'],$_POST,$urlfile,$urlzipfile,$pdffile);

  if($urlfile != null || $urlzipfile != null || $pdffile != null){
    if($urlfile){
      $_SESSION['message'] .= $urlfile." was added to the database<br/>";
    }
    if($urlzipfile){
      $_SESSION['message'] .= $urlzipfile." was added to the database<br/>";
    }
    if($pdffile){
      $_SESSION['message'] .= $pdffile." was added to the database<br/>";
    }
  }
  else {
    $_SESSION['message'] .= "No electronic paper was added to the database<br/>";
  }
  echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());  
}
  



?>