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
require_once("bibtex.php");


/**
 * Extract bibtex field from an array
 */
function extract_bibtex_data($tab){
    $result = array();
    foreach($tab as $key => $value){
        if(in_array($key,$GLOBALS['bibtex_entries']) && trim($value)!= ''){
            $result[$key] = trim($value);
        }
    }
    return $result;   
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
                "¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖØÙÚÛÜİßàáâãäåæçèéêëìíîïğñòóôõöøùúûüıÿ",
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
    $addalllink = "bibindex.php?mode=$mode&amp;action=add_to_basket&amp;id=";
    foreach($ids as $id){
        $addalllink .= "$id*";
    }
    if($extraparam){
        $addalllink .= "&amp;$extraparam";
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

?>