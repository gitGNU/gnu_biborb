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

login php script


**/

include("config.php");
include("functions.php");

session_name($session_id);
session_start();

$login = $_POST['login'];
$mdp = $_POST['mdp'];

if($login=="" || $mdp==""){
  $_SESSION['error'] = "You must fill both login and password!";
  echo header("Location: bibindex.php?mode=login&".session_name()."=".session_id());
}
else {
  $loggedin = check_login($login,$mdp);
  if($loggedin){
    $_SESSION['user'] = $login;
    echo header("Location: bibindex.php?mode=welcome&".session_name()."=".session_id());
  }
  else {
    $_SESSION['error'] = "Wrong login or paswword";
    echo header("Location: bibindex.php?mode=login&".session_name()."=".session_id());
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