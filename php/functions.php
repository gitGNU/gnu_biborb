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
  if($title != null){$html .= "<h2 id='main_title'>$title</h2>";}
  if($error){$html .= "<div id='error'>$error</div>";}
  if($message){$html .= "<div id='message'>$message</div>";}
  if($content != null) {$html .= "<div id='content'>$content</div>";}
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
    $html .= msg("Add all entries to the basket.");
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
function sort_div($selected_sort,$selected_order,$mode,$misc){
    // ensure the localization is set up
    load_i18n_config($_SESSION['language']);

    $html = "<div class='sort'>";
    $html .= msg("Sort by:");
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
        $html .= "<option value='title' selected='selected'>".msg("Title")."</option>";
    }
    else{
        $html .= "<option value='title'>".msg("Title")."</option>";
    }
    
    if($selected_sort == 'year'){
        $html .= "<option value='year' selected='selected'>".msg("Year")."</option>";
    }
    else{
        $html .= "<option value='year'>".msg("Year")."</option>";
    }
    $html .= "</select>";
    $html .= "<input type='hidden' name='mode' value='$mode'/>";
    if($misc){
        foreach($misc as $key=>$val){
            $html .= "<input type='hidden' name='$key' value='$val'/>";
        }
    }
    $html .= "<select name='sort_order'>";
    if($selected_order=='ascending'){
        $html .= "<option value='ascending' selected='selected'>".msg("ascending")."</option>";
    }
    else{
        $html .= "<option value='ascending'>".msg("ascending")."</option>";
    }
    if($selected_order=='descending'){
        $html .= "<option value='descending' selected='selected'>".msg("descending")."</option>";
    }
    else{
        $html .= "<option value='descending'>".msg("descending")."</option>";
    }
    $html .= "</select>";
    $html .= "<input type='submit' value='".msg("Sort")."'/>";
    $html .= "</fieldset>";
    $html .= "</form>";
    $html .= "</div>";
    
    return $html;
}

/**
    Analyze a .dot aux file and return an array of bibtex ids
 */
function bibtex_keys_from_aux($auxfile){
    $lines = load_file($auxfile);
    preg_match_all("/citation{(.*)}/i",$lines,$res);
    return $res[1];
}

/**
    Create the nav bar
*/
function create_nav_bar($current_page,$max_page,$mode,$extraparam=null){
    $html = "";
    if($max_page>1){
        $html .= "<div id='nav_bar'>";
        if($extraparam != null){
            $extraparam = "&amp;".$extraparam;
        }
        if($current_page != 0){
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=0'><img src='data/images/stock_first-16.png' alt='First' title='First'/></a>";
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=".($current_page-1)."'><img src='data/images/stock_left-16.png' alt='Previous' title='Previous'/></a>";
        }
        for($i=0;$i<$max_page;$i++){
            if($current_page==$i){
                $html .= "<a id='current_page' href='bibindex.php?mode=$mode$extraparam&amp;page=$i'>".($i+1)."</a>";
            }
            else{
                $html .= "<a class='num_page' href='bibindex.php?mode=$mode$extraparam&amp;page=$i'>".($i+1)."</a>";
            }
        }
        if($current_page != $max_page-1){
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=".($current_page+1)."'><img src='data/images/stock_right-16.png' alt='Next' title='Next'/></a>";
            $html .= "<a href='bibindex.php?mode=$mode$extraparam&amp;page=".($max_page-1)."'><img src='data/images/stock_last-16.png' alt='Last' title='Last'/></a>";
        }
        $html .= "</div>";
    }
    return $html;
}

?>