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

File: add_entry.php
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

This PHP script is called to add an entry to the bibliography.


**/
include("config.php");
include("functions.php");

session_name($session_id);
session_start();


if(strcmp($_POST['action'],"cancel") == 0){
  header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
}
else 
{
  
  // check if the entry is already present
  $inbib = exists_entry_with_id($_POST['_id']);

  // some init parameter
  $urlfile = null;
  $urlzipfile = null;
  $pdffile = null;
  
  // if present: error
  if($inbib || $_POST['_id'] == null){
    if($inbib){      
      $_SESSION['error'] = "ID already present, select a different one. <br/>";
    }
    else{
      $_SESSION['error'] = "Null ID not allowed. <br/>";
    }
    
    $_SESSION['error'] .= "<A HREF='javascript:history.back()'>Modify</A> or <a href='bibindex.php?mode=welcome&amp;".session_name()."=".session_id()."'>Cancel</a>";
  }
  else {

    // cope with file upload
    if(file_exists($_FILES['url']['tmp_name'])){
      $infofile = pathinfo($_FILES['url']['name']);
      $extension = $infofile['extension'];
      $urlfile=get_new_name($infofile['basename'],$_POST['_id']);
      $path = "./bibs/".$_SESSION['bibname']."/papers/".$urlfile;
      move_uploaded_file($_FILES["url"]["tmp_name"],$path);
      chmod($path,0644);
    }
    
    if(file_exists($_FILES['urlzip']['tmp_name'])){
      $infofile = pathinfo($_FILES['urlzip']['name']);
      $extension = $infofile['extension'];
      $urlzipfile=get_new_name($infofile['basename'],$_POST['_id']);
      $path = "./bibs/".$_SESSION['bibname']."/papers/".$urlzipfile;
      move_uploaded_file($_FILES['urlzip']['tmp_name'],$path);
      chmod($path,0644);
    }  
    if(file_exists($_FILES['pdf']['tmp_name'])){
      $infofile = pathinfo($_FILES['pdf']['name']);
      $extension = $infofile['extension'];
      $pdffile=get_new_name($infofile['basename'],$_POST['_id']);
      $path = "./bibs/".$_SESSION['bibname']."/papers/".$pdffile;
      move_uploaded_file($_FILES["pdf"]["tmp_name"],$path);
      chmod($path,0644);
    }
    
    add_bibtex_entry($_SESSION['add_type'],$_POST,$urlfile,$urlzipfile,$pdffile);

    $_SESSION['message'] = "The following entry was added:<br/>";
    $_SESSION['message'] .= "<pre>".to_bibtex($_SESSION['add_type'],$_POST,$urlfile,$urlzipfile,$pdffile)."</pre>";
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
    
  }
  
  echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
}




?>