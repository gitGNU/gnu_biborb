<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2003-2004  Guillaume Gardey (ggardey@club-internet.fr)
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
    File: auth.mysql.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL

    Description:

        The following definition of Auth uses a mysql database to store the 
    authorizations.

    The database is organized as follows:

        biborb_users(id,login,password,name,firstname,admin)
            password is stored using the md5 function
            admin: Y if the user is an admin, N otherwise
    
        biborb_auth(user_id,db_name,access)
            user_id: a valid biborb_users id
            db_name: the bibliography's name or '*' to set authorizations for
                     all databases
            access: a 3 characters field (add|modify|delete)
                       111 == add modify and delete, 
                       100 == add no modify no delete ...
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


/**
    Class Auth: a genreic class to check authorizations.
    This implementation of Auth uses a MySQL database.
*/
class Auth
{
    var $host;          // database host
    var $dbuser;        // a valid user for the database
    var $pass;          // its password
    var $dbname;        // name of the database containing BibORB tables
    var $users_table;   // name of the table containing users data.
    var $users_auth;    // name of the table containing authorizations data.
    
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
        // connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            // Get ($user,$pass) record
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
        //connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            // get $admin value for $user
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
    //connection to the users database
        $connect = @mysql_connect($this->host,$this->dbuser,$this->pass) or die("Unable to connect to mysql!");
        $base = @mysql_select_db($this->dbname,$connect);
        if(!$base){
            die("Unable to connect to the users database!");
        }
        else{
            // get records where $id = $user
            $query = "SELECT id FROM ".$this->users_table." WHERE login='$user'";
            $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
            
            if(mysql_num_rows($result) != 0){
                $row = mysql_fetch_assoc($result);
                $id = $row['id'];

                // look for *
                $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='*'";
                $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                if(mysql_num_rows($result) != 0){
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[2] == '1';
                }
                else{
                    $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='$database_name'";
                    $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[2] == '1';
                }
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
                
                $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='*'";
                $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());

                if(mysql_num_rows($result) != 0){
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[0] == '1';
                }
                else{
                    $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='$database_name'";
                    $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[0] == '1';
                }
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
                
                $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='*'";
                $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());

                if(mysql_num_rows($result) != 0){
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[1] == '1';
                }
                else{
                    $query = "SELECT access FROM ".$this->users_auth." WHERE user_id='$id' AND db_name='$database_name'";
                    $result = mysql_query($query,$connect) or die("Invalid request".mysql_error());
                    $row = mysql_fetch_assoc($result);
                    $access = $row['access'];
                    return $access[1] == '1';
                }
            }
            else{
                return FALSE;
            }
        }
    }
}

?>
