<?php
/**
 *
 * This file is part of BibORB
 * 
 * Copyright (C) 2003  Guillaume Gardey
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
 * File: action_proxy.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 * 
 * Description:
 * 
 *      Performs actions on the bibliography
 * 
 * 
 */
 
require_once("config.php");
require_once("functions.php");
require_once("basket.php");

session_name($session_id);
session_start();


if(array_key_exists('action',$_GET)){
    // action defined
    // select what do to
    switch($_GET['action']){
        /**
         * Action on a given entry
         */
        case 'delete': // delete an entry from database
            $bibtex = get_bibtex($_SESSION['bibname'],$_GET['id']);
            delete_bibtex_entry($_SESSION['bibname'],$_GET['id']);
            $_SESSION['message'] = "The follwoing entry was deleted: <pre>".$bibtex."</pre>";
            echo header("Location: bibindex.php?mode=operationresult&bibname=".$_SESSION['bibname']."&".session_name()."=".session_id());
            break;
            
        case 'edit': // edit a given entry
            echo header("Location: bibindex.php?mode=update&id=".$_GET['id']."&bibname=".$_SESSION['bibname']."&".session_name()."=".session_id());
            break;
            
        case 'add_to_basket': // add the entry to basket
            add_to_basket($_GET['id']);
            // use javascript to redirect to the previous page
            echo "<head><script language='javascript'>history.back()</script></head>";
            break;
        
        case 'delete_from_basket': //remove the entry from the basket
            delete_from_basket($_GET['id']);
            echo header("Location: bibindex.php?mode=displaybasket&".session_name()."=".session_id());
            break;
        
        case 'bibtex':
            header("Content-Type: text/plain");
            echo get_bibtex($_GET['bibname'],$_GET['id']);
            break;
            
        /**
         * Action on all entries of the basket
         */
        case 'resetbasket': // delete all entries of the basket
            reset_basket();
            echo header("Location: bibindex.php?mode=displaybasket&".session_name()."=".session_id());
            break;
        
        case 'resetgroup': // reset the group of all entries of the basket
            basket_reset_group();
            echo header("Location: bibindex.php?mode=displaybasket&".session_name()."=".session_id());
            break;
        
        case 'addgroup': // append a group to all entries of the basket
            basket_add_group($_GET['groupvalue']);
            echo header("Location: bibindex.php?mode=displaybasket&".session_name()."=".session_id());
            break;
            
        case 'exportbaskettobibtex': // export the basket to bibtex
            header("Content-Type: text/plain");
            echo basket_to_bibtex();
            break;
        
        case 'exportbaskettohtml': // export the basket to HTML
            $html = html_header(null,$GLOBALS['CSS_FILE'],null);
            $html .= basket_to_simple_html();
            $html .= html_close();
            echo $html;
            break;
        
        /**
         * Database manipulation
         */
        case 'create':
            // check if the database already exists
            $databases_names = get_databases_names();
            print_r($databases_names);
            if($_GET['database_name'] != null){
                if(!in_array($_GET['database_name'],$databases_names)){
                    echo 'creating...';
                    $res = mkdir("./bibs/".$_GET['database_name'],0755);
                    if($res){
                        $_SESSION['message'] = "The database was successfully created.";
                    }
                    else{
                        $_SESSION['message'] = "Unabled to create the database.";
                    }
                    mkdir("./bibs/".$_GET['database_name']."/papers",0777);
                    copy("./data/template/template.bib","./bibs/".$_GET['database_name']."/".$_GET['database_name'].".bib");
                    copy("./data/template/template.xml","./bibs/".$_GET['database_name']."/".$_GET['database_name'].".xml");
                    copy("./data/template/description.txt","./bibs/".$_GET['database_name']."/description.txt");
                    chmod("./bibs/".$_GET['database_name']."/".$_GET['database_name'].".xml",0666);
                    chmod("./bibs/".$_GET['database_name']."/".$_GET['database_name'].".bib",0666);
                    chmod("./bibs/".$_GET['database_name']."/description.txt",0766);
                    $fp = fopen("./bibs/".$_GET['database_name']."/description.txt","w");
                    fwrite($fp,$_GET['description']);
                    fclose($fp);
                    $xml = load_file("./bibs/".$_GET['database_name']."/".$_GET['database_name'].".xml");
                    $xml = str_replace("template",$_GET['database_name'],$xml);
                    $fp = fopen("./bibs/".$_GET['database_name']."/".$_GET['database_name'].".xml","w");
                    fwrite($fp,$xml);
                    fclose($fp);
                }
                else{
                    $_SESSION['error'] = "Database already exists!";
                }
            }
            else {
                $_SESSION['error'] = "Empty database name!!";
            }
            echo header("Location: index.php?mode=result&".session_name()."=".session_id());
            break;
            
        case 'remove':
            deldir("./bibs/".$_GET['database_name']);
            $_SESSION['message'] = "Database ".$_SESSION['message']." deleted.";
            echo header("Location: index.php?mode=result&".session_name()."=".session_id());
            break;
        /**
         * Default => error
         */
        default:
            break;
    }
}

if(array_key_exists('action',$_POST)){
    
    switch($_POST['action']){
        case 'cancel':
            echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
            break;
        /**
         * Add a new entry
         */
        case 'add':
            add_entry();
            echo header("Location: bibindex.php?mode=operationresult&".session_name()."=".session_id()); 
            break;
        case 'update':
            update_this_entry();
            echo header("Location: bibindex.php?mode=operationresult&".session_name()."=".session_id());
            break;
            
        case 'login':
            $login = $_POST['login'];
            $mdp = $_POST['mdp'];
            if($login=="" || $mdp==""){
                $_SESSION['error'] = "You must fill both login and password!";
                echo header("Location: ".$_POST['from']."?mode=login&".session_name()."=".session_id());
            }
            else {
                $loggedin = check_login($login,$mdp);
                if($loggedin){
                    $_SESSION['user'] = $login;
                    $_SESSION['usermode'] = "admin";
                    echo header("Location: ".$_POST['from']."?mode=welcome&".session_name()."=".session_id());
                }
                else {
                    $_SESSION['error'] = "Wrong login or password";
                    echo $_POST['from'];
                    echo header("Location: ".$_POST['from']."?mode=login&".session_name()."=".session_id());
                }
            }
            break;
            
        default:
            break;
    }
}

function add_entry(){  
    // check if the entry is already present
    $inbib = exists_entry_with_id($_SESSION['bibname'],$_POST['_id']);

    // some init parameter
    $urlfile = null;
    $urlzipfile = null;
    $pdffile = null;
  
    // If the id paper is already in database or the id value is null => error
    if($inbib || $_POST['_id'] == null){
        // Set the correct error message
        if($inbib){
            $_SESSION['error'] = "ID already present, select a different one. <br/>";
        }
        else{
            $_SESSION['error'] = "Null ID not allowed. <br/>";
        }
        $_SESSION['error'] .= "<A HREF='javascript:history.back()'>Modify</A> or <a href='bibindex.php?mode=welcome&amp;".session_name()."=".session_id()."'>Cancel</a>";
    }
    else {
        // Ok, id not present

        // upload files if they are present
        if(file_exists($_FILES['url']['tmp_name'])){
            $urlfile=upload_file($_SESSION['bibname'],'url',$_POST['_id']);
        }
        if(file_exists($_FILES['urlzip']['tmp_name'])){
            $urlzipfile=upload_file($_SESSION['bibname'],'urlzip',$_POST['_id']);
        }  
        if(file_exists($_FILES['pdf']['tmp_name'])){
            $pdffile=upload_file($_SESSION['bibname'],'pdf',$_POST['_id']);
        }
    
        // add the entry 
        add_new_entry($_SESSION['bibname'],$_SESSION['type'],$_POST,$urlfile,$urlzipfile,$pdffile);
    
        // set message
        $_SESSION['message'] = "The following entry was added:<br/>";
        $_SESSION['message'] .= "<pre>".to_bibtex($_SESSION['type'],$_POST,$urlfile,$urlzipfile,$pdffile)."</pre>";
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
}

function update_this_entry(){
  // check if the id value is null
    $_SESSION['error'] = null;
    if($_POST['_id'] == null){
        $_SESSION['error'] = "Null ID not allowed. <br/>";
        $_SESSION['error'] .= "<A HREF='javascript:history.back()'>Modify</A> or <a href='bibindex.php?mode=welcome&amp;".session_name()."=".session_id()."'>Cancel</a>";
    }
    else{
        $urlfile = null;
        $urlzipfile = null;
        $pdffile = null;
    
        if(file_exists($_FILES['url']['tmp_name'])){
            $urlfile = upload_file($_SESSION['bibname'],'url',$_POST['_id']);
        }
        else if($_POST['current_url'] != null){
            $urlfile = $_POST['current_url'];
        }
    
        if(file_exists($_FILES['urlzip']['tmp_name'])){
            $urlzipfile = upload_file($_SESSION['bibname'],'urlzip',$_POST['_id']);
        }
        else if($_POST['current_urlzip'] != null){
            $urlzipfile = $_POST['current_urlzip'];
        }  
    
        if(file_exists($_FILES['pdf']['tmp_name'])){
            $pdffile = upload_file($_SESSION['bibname'],'pdf',$_POST['_id']);
        }
        else if($_POST['current_pdf'] != null){
            $pdffile= $_POST['current_pdf'];
        }
    
        
        //delete_only_bibtex_entry($_SESSION['bibname'],$_POST['_id']);
        //add_bibtex_entry($_SESSION['bibname'],$_POST['add_type'],$_POST,$urlfile,$urlzipfile,$pdffile);
        update_entry($_SESSION['bibname'],$_POST['add_type'],$_POST,$urlfile,$urlzipfile,$pdffile);
        
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
    }
}

function check_login($thelogin,$thepasswd){
  global $host,$dbuser,$pass,$db,$table;
  
  $connect = @mysql_connect($host,$dbuser,$pass) or die("Impossible de se connecter à mysql");
  $base = @mysql_select_db($db,$connect);
  if(!$base){
    echo "Impossible de se connecter à la base utilisateur.";

    exit();
  }
  else {
    $query = "SELECT login,password FROM $table WHERE login='$thelogin' AND password=md5('$thepasswd')";
    $result = mysql_query($query,$connect) or die("Requete invalide".mysql_error());
    return (mysql_num_rows($result)>0);
  }
}

?>