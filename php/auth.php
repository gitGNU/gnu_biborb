<?php
/**
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
    File: auth.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL

    Description:
        This file defines the Auth class to provide a basic user management
    system. Auth is designed to be flexible so that you can customize it to 
    your needs.

        4 levels of authorizations are available: add entries, edit/update
    entries, delete entries, admin. Only the 'admin' level is authorized to 
    create or delete bibliographies, it also allows the user to add, edit and
    delete entries. The add/edit/delete authorizations are defined for each
    bibliographies.
        
        Authorizations are accessed by Biborb using the following functions:
            - is_valid_user($user,$pass)
                Check the login.
            - can_add_entry($user,$biblio)
                Check if $user can add entry to the bibliography named
                $biblio.
            - can_delete_entry($user,$biblio)
                Check if $user can delete entries from the bibliography named
                $biblio.
            - can_modify_entry($user,$biblio)
                Check if $user can edit and update entries from the bibliography
                named $biblio
            - is_admin_user($user)
                Check if $user is an admin user.
            
        It is then easy to redefines these methods to match your needs (other 
    databases, ldap, postgres, xml databases....)


        The following definition of Auth uses a mysql databas to store the 
    authorizations.

    The database is organized as follows:
        biborb_users(id,login,password,name,firstname,admin)
            password is stored using the md5 function
            admin: Y if the user is an admin, N otherwise
    
        biborb_auth(user_id,db_name,access)
            user_id: a valid biborb_users id
            db_name: the bibliography 
            access: a 3 characters field (add|modify|delete)
                so 111 == add modify and delete, 100 == add no modify no delete...
*/

/**
    The database configuration
*/
$host = "localhost";
$dbuser = "biborb-admin";
$pass = "biborbdev";
$db = "biborb";
$table = "biborb_users";
$auth_table = "biborb_auth";

class Auth
{
    var $host;
    var $dbuser;
    var $pass;
    var $dbname;
    var $users_table;
    
    /**
        Constructor
     */
    function Auth(){
        $this->host = $GLOBALS['host'];
        $this->dbuser = $GLOBALS['dbuser'];
        $this->pass = $GLOBALS['pass'];
        $this->dbname = $GLOBALS['db'];
        $this->users_table = $GLOBALS['table'];
        $this->users_auth = $GLOBALS['auth_table'];
    }
    
    /**
        Is the login/password valid?
        Returns TRUE/FALSE
     */
    function is_valid_user($user,$pass){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            $query = "SELECT login,password FROM ".$this->users_table." WHERE login='$user' AND password=md5('$pass')";
            $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
            return (mysql_num_rows($result)>0);
        }
    }
    
    /**
        Is the user an administrator?
        Returns TRUE/FALSE
     */
    function is_admin_user($user){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            $query = "SELECT admin FROM ".$this->users_table." WHERE login='$user'";
            $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
            
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                return ($row['admin'] == 'Y');
            }
            else{
                return FALSE;
            }
        } 
    }
    
    /**
        Can the user delete entries?
        Returns TRUE/FALSE
     */
    function can_delete_entry($user, $database_name){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            $query = "SELECT id FROM ".$this->users_table." WHERE login='$user'";
            $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
            
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];
                $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='$database_name'";
                $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                $row = mysql_fetch_assoc($result);
                $access = $row['access'];
                return $access[2] == '1';
            }
            else{
                return FALSE;
            }
        }
    }
    
    /**
        Can the user add entries?
        Return TRUE/FALSE
     */
    function can_add_entry($user, $database_name){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            $query = "SELECT id FROM ".$this->users_table." WHERE login='$user'";
            $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
            
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];
                $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='$database_name'";
                $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                $row = mysql_fetch_assoc($result);
                $access = $row['access'];
                return $access[0] == '1';
            }
            else{
                return FALSE;
            }
        }
    }
    
    /**
        Can the user update entries?
        Return TRUE/FALSE
     */
    function can_modify_entry($user, $database_name){
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            $query = "SELECT id FROM ".$this->users_table." WHERE login='$user'";
            $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
            
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];
                $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='$database_name'";
                $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                $row = mysql_fetch_assoc($result);
                $access = $row['access'];
                return $access[1] == '1';
            }
            else{
                return FALSE;
            }
        }
    }
}

?>