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
    File: auth.model.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL

    Description:
        Implementation of Auth using files.

            bib_users.txt contains information about registered users
                ex:
                    userA:passwordA,1
                    userB:passwordB,0

            => userA is an administrator, userB not.

            bib_access.txt contains authorizations
                ex:
                    bib1:userA*adm,userB*a

            userA can add (a), delete (d) or modify (m) references
            userB can only add new references.
*/

/**
    Class Auth: a genreic class to check authorizations.
*/
class Auth
{
    var $f_users;
    var $f_access;
    /**
        Constructor
     */
    function Auth(){
        $this->f_users = "./php/auth_backends/bib_users.txt";
        $this->f_access = "./php/auth_backends/bib_access.txt";
    }
    
    /**
        Is the login/password valid?
        Returns TRUE/FALSE
     */
    function is_valid_user($user,$pass){
        $content = file($this->f_users);
        foreach($content as $line){
            if(preg_match("/\s*(\w*)\s*:\s*(\w*)\s*/",$line,$match)){
                if($match[1] == $user){
                    return (crypt($pass,$match[2]) == $match[2]);
                }
            }
        }
        return FALSE;
    }
    
    /**
        Is the user an administrator?
        Returns TRUE/FALSE
     */
    function is_admin_user($user){
        $content = file($this->f_users);
        foreach($content as $line){
            if(preg_match("/\s*(\w*)\s*:\s*\w*\s*,\s*(\w)\s*/",$line,$match)){
                if($match[1] == $user){
                    return ($match[2] == '1');
                }
            }
        }
        return false;
    }
    
    /**
        Can the user delete entries?
        Returns TRUE/FALSE
     */
    function can_delete_entry($user, $database_name){
        $users = $this->registered_users_for_bibliography($database_name);
        if(array_key_exists($user,$users)){
            return strstr($users[$user],'d');
        }
    }
    
    /**
        Can the user add entries?
        Return TRUE/FALSE
     */
    function can_add_entry($user, $database_name){
        $users = $this->registered_users_for_bibliography($database_name);
        if(array_key_exists($user,$users)){
            return strstr($users[$user],'a');
        }
    }
    
    /**
        Can the user update entries?
        Return TRUE/FALSE
     */
    function can_modify_entry($user, $database_name){
        $users = $this->registered_users_for_bibliography($database_name);
        if(array_key_exists($user,$users)){
            return strstr($users[$user],'m');
        }
    }

    function registered_users_for_bibliography($bibname){
        $content = file($this->f_access);
        $users = array();
        foreach($content as $line){
            if(preg_match("/\s*(.*)\s*:(.*)/",$line,$match)){
                if($match[1] == $bibname){
                    $data = explode(',',$match[2]);
                    foreach($data as $user){
                        $tab = explode('*',$user);
                        $users[$tab[0]] = $tab[1];
                    }
                }
            }
        }
        return $users;
    }
}

?>
