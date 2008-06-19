<?php
/**
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2008 Guillaume Gardey <glinmac+biborb@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

/**
 * 
 * File: biborbdb.sql.php
 * Author: Bernardo Bueno (bernardobueno@ya.com), Anthony Le Berre (leberre.anthony@free.fr)
 * Developed at ESEO (http://www.eseo.fr), leaded by Jerome Delatour (jerome.delatour@eseo.fr)
 * Licence: GPL
 * PEAR must be installed
 * see http://pear.php.net
 * v 0.1 11/04/05
 */

/**
* parameters for the connexion to the SQL database :
*/ /** */

	//! Database server name
	$host = "localhost";	
	
	//! Database user name
	$user = "biborb-admin";
	
	//! Database user password
	$passwd = "biborbdev";
	
	//! SQL database name
	$dbname = "biborb";
	
	//! SQL database type (MySQL, PostgreSQL, Oracle...) 
	$dbtype =  DB_BACKEND;
	// see http://pear.php.net/manual/en/package.database.db.intro-dsn.php

    
DEFINE ("host",$host);
DEFINE ("user", $user);
DEFINE ("passwd",$passwd);
DEFINE ("dbname",$dbname);
DEFINE ("dbtype",$dbtype);

require_once("DB.php");
require_once("php/xslt_processor.php"); //xslt processor

//! Possible values of sort
$sort_values = array('author','title','ID','year','dateAdded','lastDateModified');

/*! \class BibORB_DataBase biborbdb.sql.php
 *  \brief Class for SQL database management of BibORB.
 *  \author Bernardo Bueno
 *  \author Anthony Le Berre
 *  \version 0.1
 *  \date    11/04/2005
 *  \warning Not well tested
 */


class BibORB_DataBase {
	
    /** 
    	\brief A flag TRUE if a BibTeX should be generated or FALSE if not
    */
    var $generate_bibtex; 
    
    /**
    	\brief Name of the biblio
    	The name of the current biblio
    */
    var $biblio_name;
   
    /** 
        \brief The biblio directory. We change the character space by underscore
    */
    var $biblio_dir;
    
    // list of BibTeX fields relevant for BibORB
    var $biborb_fields;
    
    // the changelog file;
    var $changelog;
    
    /** 
	\brief Sort method used to sort entries
	see \b $sort_values to know different values
    */
    var $sort;
    
    /**
    	\public $sort_values
	\brief Array of posibles $sort values
    */
    var $sort_values = array('author','title','ID','year','dateAdded','lastDateModified');
    
    /** 
    	\brief Sort order method (ascending/descending)
	see \b $sort_order_values to know different values
    */
    var $sort_order;
    
    /**
        \public $sort_order_values
	\brief Array of posibles $sort_order values
    */
    var $sort_order_values = array('ascending','descending');
    
    /** 
    	\brief The ID of the biblio in the SQL database
    */
    var $idbiblio;
    
    /**
    	\public $read_status
	\brief Status of lecture
	see \b $read_status_values to know different values
    */
    var $read_status;

    /** 
        \public $read_status
        \brief Array of possible $read_status values
    */
    
    var $read_status_values = array('any','notread','readnext','read');
    
    /**
    	\public $ownership
	\brief current ownership
	see \b $ownership_values to know different value
    */
    var $ownership;

    /**
        \public $ownership_values
	\brief Array of $ownership posibles values
    */
    var $ownership_values = array('any','notown','borrowed','buy','own');

            
    /**
    	\fn BibORB_DataBase($bibname,$genBibtex = true)
        \brief Constructor.
        \param string \b $bibname Name of the bibliography
        \param boolean \b $genBibtex Should keep an up-to-date BibTeX file.
     */
    function BibORB_DataBase($bibname,$genBibtex = true){
	$nombre= remove_accents($bibname);
	$nombre = str_replace(' ','_',$nombre);
					
        $this->biblio_name = $bibname;
        $this->biblio_dir = "./bibs/$nombre/";
        $this->generate_bibtex = $genBibtex;
	
	// Add by me
	$this->idbiblio = id_entry($bibname);
    }
    
    function set_BibORB_fields($tab){
    	$this->biborb_fields = $tab;
    }
    
    /**
    */
    function fullname(){
    	return $this->biblio_name;
    }
    
   /**
	\fn set_sort($sort)
	\brief Set the sort method.
	\param string \b $sort The field to sort on
     */
    function set_sort($sort){
        if(array_search($sort,$this->sort_values) === FALSE){
            $sort = 'ID';
        }
        $this->sort = $sort;        
    }
    
    /**
    	\fn set_sort_order($sort_order)
	\brief Set the sort order. (ascending/descending)
	\param string \b $sort_order Ascending or descending
     */
    function set_sort_order($sort_order){
        if(array_search($sort_order,$this->sort_order_values) === FALSE){
            $sort_order = 'ascending';
        }
        $this->sort_order = $sort_order;
    }
    
    /**
    	\fn string bibtex_file()
        \brief Generate the path of the bib file.
	\return \c string Path of the .bib file
    */
    function bibtex_file(){
        return $this->biblio_dir.$this->biblio_name.".bib";
    }
    
    /**
    	\fn string name()
	\brief Return the name of the bibliography.
	\return \c string Name of the biblio
    */
    function name(){
        return $this->biblio_name;
    }
	
    /**
    	\fn string papers_dir()
        \brief Return the directory containing uploaded papers/data.
	\return \c string Directory containing uploaded papers/data
    */
    function papers_dir(){
        return $this->biblio_dir."papers/";
    }
    
    /**
    	\fn update_bibtex_file()
	\brief Regenerate the bibtex file so that it exactly contains the data present in the database.
    */
    function update_bibtex_file(){
	// Load all the database and transform it into a bibtex string
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        $xml_content = $this->all_entries();
        $xsl_content = file_get_contents("./xsl/xml2bibtex.xsl");
        $bibtex = $xsltp->transform($xml_content,$xsl_content);
        $xsltp->free();
        
        // write the bibtex file
        $fp = fopen($this->bibtex_file(),"w");
        fwrite($fp,$bibtex);
        fclose($fp);
    }
    
    /**
    	\fn reload_from_bibtex
        \brief Reload the database according the bibtex file.
	\warning Deprecated because we don't use an XML file
     */
    function reload_from_bibtex(){
    // ATTENTION NOT IMPLEMENTED
    // Non sense because it is used to reload xml file from data_base change function or change name
        /*$bt = new BibTeX_Tools();
        $data = $bt->bibtex_file_to_xml($this->bibtex_file());
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$data[2]);
        fclose($fp);*/
    }
    
    /**
    	\fn array all_bibtex_ids()
    	\brief Return an array of all BibTeX ids. Ids are sorted according the default \b $sort method and \b $sort_order.
	\return \c array All BibTeX ids
     */
    function all_bibtex_ids(){

	$connect = sql_connexion(); 	
	$sort=$connect->escapeSimple($this->sql_sort($this->sort));
	$order=$connect->escapeSimple($this->sql_sort_order($this->sort_order));
	$idbiblio = $connect->escapeSimple($this->id_biblio());
	
	$sql="SELECT bibtexID_document FROM biborb_document WHERE id_biblio_document = '$idbiblio' ORDER BY $sort $order;";
	$resu = request($connect,$sql);
	$i=0;
	$res[0] = "";
	while ($resu->fetchInto($row)) {
    		$res[$i++]=$row[0];	
	}
	$resu->free();
        
	if($res[0] == ""){
        	return array();
	}
        else{
        	return $res;
        }
    }
    
    /**
    	\fn array ids_for_group($groupname)
	\brief Return an array of BibTex ids of entries belonging to the group \b $groupname.
        If \b $groupname is null, it returns an array of entries that aren't
        associated with a group.
        Ids are sorted according the default \b $sort method and \b $sort_order.
	\param string \b $groupname a name of a group
	\return \c array ids of entries associated with $groupname
    */
    function ids_for_group($groupname){
	
	$connect = sql_connexion(); 	
	$sort=$connect->escapeSimple($this->sql_sort($this->sort));
	$order=$connect->escapeSimple($this->sql_sort_order($this->sort_order));
	$idbiblio = $connect->escapeSimple($this->id_biblio());
	
	if ($groupname!=NULL){
		// Could be a flaw of security because new groups could be created
		//$idgroup=group_exists($groupname);
		// Could not be optimus too big request
		$sql="SELECT bibtexID_document FROM biborb_document INNER JOIN (biborb_group2document INNER JOIN biborb_group ON biborb_group2document.id_group_group2document = biborb_group.id_group ) ON biborb_document.id_document = biborb_group2document.id_document_group2document WHERE (biborb_document.id_biblio_document = '$idbiblio' AND biborb_group.name_group= '$groupname') ORDER BY $sort $order;";
	} else {
		$sql="SELECT bibtexID_document FROM biborb_document INNER JOIN biborb_group2document ON biborb_document.id_document = biborb_group2document.id_document_group2document WHERE (biborb_document.id_biblio_document = '$idbiblio' AND biborb_group2document.id_group_group2document = '0') ORDER BY $sort $order;";
	}
	
	$resu = request($connect,$sql);
	$i=0;
	$res[0] = "";
	while ($resu->fetchInto($row)) {
    		$res[$i++]=$row[0];	
	}
	$resu->free();
        
	if($res[0] == ""){
        	return array();
	}
        else{
        	return $res;
        }
    }
    
    
    /**
    	\fn string all_entries()
        \brief Return an XML representation of all entries of the database.
	\return \c string XML representattion of all entries of the database
    */
    function all_entries(){
       	$anArray= $this->all_bibtex_ids();
	$res=$this->entries_with_ids($anArray);
        return $res;
    }
    
    /**
    	\fn string entries_with_ids($anArray)
	\brief Get the XML representation of a given set of references.
        \param array \b $anArray Contains the list of BibTeX ids. No sort is applied and references should be order in the same way as $anArray.
	\return \c string XML representation of the references stocked in \b $anArray
    */
    function entries_with_ids($anArray){
    
    	$xml = '<?xml version="1.0" encoding="UTF-8"?><bibtex:file xmlns:bibtex="http://bibtexml.sf.net/">';
	foreach($anArray as $item){
		$connect = sql_connexion(); 	
		$item = $connect->escapeSimple($item);
		$sql="SELECT * FROM biborb_document WHERE (bibtexID_document='$item');";
		$res = request($connect,$sql);
		$row =& $res->fetchRow(DB_FETCHMODE_ASSOC);
		if($row){
        		$xml .= entry_array_to_xml_from_sql($row);
		}
        }
	
	$xml .="</bibtex:file>";
	return $xml;
    }
    
    /**
        \fn string entry_with_id($anID)
        \brief Get the XML representation of a given set of references.
        \param array \b $anArray Contains the list of BibTeX ids. No sort is applied and references should be order in the same way as $anArray.
        \return \c string XML representation of \b $anID				
    */
    function entry_with_id($anID){
    	return $this->entries_with_ids(array($anID));
    }
    
    /**
    	\fn array add_new_entry($dataArray)
        \brief Add a new reference to the database
         It is in charge of moving uploaded files to the right place and ensure
         that the id selected is not already defined.
        \param array \b $dataArray contains bibtex values.
        \return \c array An array that resumes the operation.
	the array contain :
		message => a message with operation result
		id => if created, the new entry bibtex id's
		added => TRUE or FALSE
    */
    function add_new_entry($dataArray){   
	//Array for messages and results
	$resArray = array('message'=>"", 
			'added' => FALSE);
	
	$bibname = $this->name();
	$idbiblio = $this->id_biblio();
	
	$bibtexid = trim($dataArray['id']);
        // check if the entry is already present
        $inbib = $this->is_bibtex_key_present($bibtexid);
	
        // error, ID already exists or empty value
	if( $inbib|| strlen($bibtexid) == 0 || $bibtexid == null ){
            if($inbib){
                $resArray['message'] = msg("BibTeX ID already present, select a different one.");
                $resArray['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
            else{
                $resArray['message'] = msg("Null BibTeX ID for an entry not allowed.");
                $resArray['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
        }
        else{
	        $nombre= remove_accents($this->biblio_name);
		$nombre = str_replace(' ','_',$nombre);
				
		// upload files if they are present
		if(array_key_exists('up_url',$_FILES) && file_exists($_FILES['up_url']['tmp_name'])){
			$fileInfo = pathinfo($_FILES['up_url']['name']);
			if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
				$dataArray['url'] = upload_file($nombre,'up_url',$dataArray['id']);
			}
			else{
				$resArray['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_url']['name']);
				$resArray['message'] .= "<br/>";
			}
		}
		else if(array_key_exists('ad_url',$dataArray)){
			$dataArray['url'] = $dataArray['ad_url'];
		}
		if(array_key_exists('up_urlzip',$_FILES) && file_exists($_FILES['up_urlzip']['tmp_name'])){
			$fileInfo = pathinfo($_FILES['up_urlzip']['name']);
			if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
				$dataArray['urlzip'] = upload_file($nombre,'up_urlzip',$dataArray['id']);
			}
			else{
				$resArray['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_urlzip']['name']);
				$resArray['message'] .= "<br/>";
			}
		}
		else if(array_key_exists('ad_urlzip',$dataArray)){
			$dataArray['urlzip'] = $dataArray['ad_urlzip'];
		}
		if(array_key_exists('up_pdf',$_FILES) && file_exists($_FILES['up_pdf']['tmp_name'])){
			$fileInfo = pathinfo($_FILES['up_pdf']['name']);
			if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
				$dataArray['pdf'] = upload_file($nombre,'up_pdf',$dataArray['id']);		}
			else{	
				$resArray['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_pdf']['name']);
				$resArray['message'] .= "<br/>";
			}
		}
		else if(array_key_exists('ad_url',$dataArray)){
			$dataArray['pdf'] = $dataArray['ad_pdf'];
		}

		/////////for entity///////////
		//if(array_key_exists('author', $dataArray) && array_key_exists('authorids', $dataArray)){
		//	$dataArray['author'] =  $dataArray['authorids'];
		//}
		/////////for entity///////////
		
		// add the new entry
		$connect = sql_connexion(); 	
		$sql = "INSERT INTO biborb_document (";
		$bibtex_val = array();
	    	foreach($dataArray as $key => $value){
        		if(in_array($key,$GLOBALS['bibtex_entries']) && trim($value)!= ''){
				if($key!="id" && $key!="groups"){
            				$bibtex_val[$key] = trim($value);
					$sql .= "$key"."_document,";
				}
				else if($key == "id"){
					$bibtex_val[$key] = trim($value);
					$sql .= "bibtexID_document,";
				}
        		}
    		}
		$sql .= "id_biblio_document,type_document) VALUES (";
		foreach($bibtex_val as $key => $value){
			$sql .= "'".$connect->escapeSimple($bibtex_val[$key])."'".",";
    		}
		$typedoc = $dataArray['___type'];
		$sql.= "'$idbiblio',"."'$typedoc'".");";

      		request($connect, $sql);
		
		// This group exists?
		if(!empty($dataArray['groups'])){
			$groupvalues = split(',',$dataArray['groups']);
                	foreach($groupvalues as $gr){
                    		$this->add_entry_to_group($bibtex_val['id'],$gr);
                	}
		} else {
			$this->add_entry_to_group($bibtex_val['id'],NULL);
		}
		
            	$resArray['message'] .= "";
		$resArray['added'] = TRUE;
		$resArray['id'] = $bibtex_val['id'];
		
		// update bibtex file
            	if($this->generate_bibtex){$this->update_bibtex_file();}
	}
	return $resArray;
    }
	
    /**
	\fn array add_bibtex_entries($bibtex)
        \brief Add entries by importing a BibTeX string.
	\param array \b $bibtex An array containing BibTex strings
	\return \c array An array containing the result
	the array contain :
		it's a two dimensional array :
		[added][] : ids which were correctly added
		[notadded][] : ids which were not correctly added
    */
    function add_bibtex_entries($bibtex){
	// the array to return
        $res = array('added' => array(),
                     'notadded' => array()); 
        
	$bt = new BibTeX_Tools();
        // entries to add
        $entries_to_add = $bt->get_array_from_string($bibtex); 
        // bibtex key present in database
        $dbids = $this->all_bibtex_ids();

        // iterate and add ref which id is not present in the database
        foreach($entries_to_add as $entry){
            if(array_search($entry['id'],$dbids) === FALSE){
                // Add an entry
		// To be revised It works but I'm not sure about key index and bibTeX Import-Export
		$entry['bibtexid']=$entry['id'];
		$entry['id_biblio']=$this->id_biblio();
		$resArray=$this->add_new_entry($entry);
		if ($resArray['added'] == TRUE){
			$res['added'][] = $resArray['id'];
		} else {
			$res['notadded'][] = $entry['id'];
		}
            }
            else{
                $res['notadded'][] = $entry['id'];
            }
        }
        
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
        
        return $res;
    }
    
    /**
    	\fn delete_entry($bibtex_id)
        \brief Delete an entry from the database.
	\param string \b $bibtex_id a BibTeX id
    */
    function delete_entry($bibtex_id){

	$connect = sql_connexion();
	
	// Delete all from groups
	$bibtex_id = $connect->escapeSimple($bibtex_id);
	$iddoc=get_id_document($bibtex_id);
	$sql = "DELETE FROM biborb_group2document WHERE id_document_group2document  ='$iddoc';";
	request($connect, $sql);
	
	$sql = "DELETE FROM biborb_document WHERE bibtexID_document  ='$bibtex_id';";
	request($connect, $sql);
	
        //update the bibtex file.
        if($this->generate_bibtex){$this->update_bibtex_file();}

	// detect all file corresponding to this id.
	$ar = opendir($this->papers_dir());
	$tab = array();
	while($file = readdir($ar)) {
		$inf = pathinfo($file);
		if(strcmp(substr($inf['basename'],0,strlen($bibtex_id)+1),$bibtex_id.".")==0){
			array_push($tab,$file);
		}
	}
	foreach($tab as $file){
		if(file_exists($this->papers_dir().$file)){
			unlink($this->papers_dir().$file);
		}
	}
}
    
    /**
    	\fn delete_entries($tabids)
        \brief Delete entries from the database.
	\param array \b $tabids an array containing ids to delete
    */
    function delete_entries($tabids){
        foreach($tabids as $id){
            $this->delete_entry($id);
        }
    }
    
    /**
    	\fn update_entry($dataArray)
	\brief Update an entry.
        \param array \b $dataArray contains the new values for each BibTeX field.
	\return \c array an Array containing the result
	the array contain :
		message => a message with operation result
		id => bibtex id
		updated => TRUE or FALSE
    */
    function update_entry($dataArray){
    
	$resArray['updated'] = FALSE;
        $resArray['message'] = "";
        $resArray['id'] = $dataArray['id'];
	
    	$bibname = $this->name();
	$idbiblio = $this->id_biblio();
	
	$bibtexid = trim($dataArray['id']);
        // check if the entry is already present
        $inbib = $this->is_bibtex_key_present($bibtexid);
	
        // error, ID already exists or empty value
	if( !$inbib|| strlen($bibtexid) == 0 || $bibtexid == null ){
            if(!$inbib){
                $resArray['message'] = sprintf("BibTeX ID NOT present, select a different one.");
                $resArray['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
            else{
                $resArray['message'] = msg("Null BibTeX ID for an entry not allowed.");
                $resArray['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
        }
        else{
            // upload files if they are present
	    $nombre= remove_accents($this->biblio_name);
            $nombre = str_replace(' ','_',$nombre);
		    
            if(array_key_exists('up_url',$_FILES) && file_exists($_FILES['up_url']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_url']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['url'] = upload_file($nombre,'up_url',$dataArray['id']);
                }
                else{
		    unset($dataArray['url']);
                    $resArray['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_url']['name']);
                    $resArray['message'] .= "<br/>";
                }
            }
            else if(array_key_exists('ad_url',$dataArray)){
                $dataArray['url'] = $dataArray['ad_url'];
            }
            
            if(array_key_exists('up_urlzip',$_FILES) && file_exists($_FILES['up_urlzip']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_urlzip']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['urlzip'] = upload_file($nombre,'up_urlzip',$dataArray['id']);
                }
                else{
		    unset($dataArray['urlzip']);
                    $resArray['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_urlzip']['name']);
                    $resArray['message'] .= "<br/>";
                }
            }
            else if(array_key_exists('ad_urlzip',$dataArray)){
                $dataArray['urlzip'] = $dataArray['ad_urlzip'];
            }
            
            if(array_key_exists('up_pdf',$_FILES) && file_exists($_FILES['up_pdf']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_pdf']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['pdf'] = upload_file($nombre,'up_pdf',$dataArray['id']);
                }
                else{
		    unset($dataArray['pdf']);
                    $resArray['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_pdf']['name']);
                    $resArray['message'] .= "<br/>";
                }
            }
            else if(array_key_exists('ad_url',$dataArray)){
                $dataArray['pdf'] = $dataArray['ad_pdf'];
            }	
	    
		// Update the new entry
		$connect = sql_connexion(); 	
		$sql = "UPDATE biborb_document SET ";

	    	foreach($dataArray as $key => $value){
        		if(in_array($key,$GLOBALS['bibtex_entries']) && trim($value)!= ''){
				if($key!="id" && $key!="groups"){
            				$val = trim($value);
					$sql .= "$key"."_document="."'".$connect->escapeSimple($val)."'".",";
				}
				elseif($key == "id"){
					$val = trim($value);
					$sql .= "bibtexID_document="."'".$connect->escapeSimple($val)."'".",";
				}
        		}
    		}
		$id = $dataArray['id'];
		$sql .= "id_biblio_document='$idbiblio' WHERE (bibtexID_document='$id'); ";
		
      		request($connect, $sql);
		
		// Delete all groups and after it Updates
		$this->reset_groups(array($dataArray['id']));
				
		if($dataArray['groups']!=NULL){
			$groupvalues = split(',',$dataArray['groups']);
                	foreach($groupvalues as $gr){
                    		$this->add_entry_to_group($dataArray['id'],$gr);
                	}
		}

		$resArray['updated'] = TRUE;
		
		// update bibtex file
            	if($this->generate_bibtex){$this->update_bibtex_file();}
	}
	
        return $resArray;
    }
    
    /**
    	\fn boolean is_bibtex_key_present($bibtex_key)
        \brief Test if a bibtex key is already present in the database
	\param string \b $bibtex_key the bibtex id
        \return \c boolean TRUE if $bibtex_key is present in the SQL database
    */
    function is_bibtex_key_present($bibtex_key){
        // Should be a BibTeXId the same in 2 different bibliographies I think NO
		
	$connect = sql_connexion(); 
	$bibtex_key = $connect->escapeSimple($bibtex_key);
	$sql = "SELECT bibtexID_document FROM biborb_document WHERE bibtexID_document='$bibtex_key';";
	$res = request($connect,$sql);
	$row = $res->fetchRow();
	return $row[0];
    
    }
    
    /**
    	\fn array groups()
	\brief Return an array containing groups present in the bibliography.
	\return \c array an Array containing all groups
    */
    function groups(){
	$connect = sql_connexion(); 
	$sql = "SELECT name_group FROM biborb_group;";
	$res = request($connect,$sql);   
	$list = array();	
	$i=0;
	while($row = $res->fetchRow()) {
        	$list[$i++] = $row[0];
	}
		        
        return $list;
    }
	
    /**
    	\fn string add_to_group($idArray,$group)
	\brief Add a set of entries to a group
	\param array \b $idArray an array of entries's ids
	\param string \b $group group in which to add entries
    */
    function add_to_group($idArray,$group){
    	foreach($idArray as $item){
		$this->add_entry_to_group($item,$group);
        }	
    }
    
    /**
    	\fn reset_groups($idArray)
        \brief Reset groups of a set of entries
	\param array \b $idArray an array entries ids
    */
    function reset_groups($idArray){
        foreach($idArray as $item){
		$this->add_entry_to_group($item,NULL);
        }
    }
    
    /**
    	\fn array ids_for_search($value,$fields)
	\brief Search in given fields, a given value.
	\param array \b $fields array containing the name of fields to look at.
	\param string \b $value value to search
     	\return \c array an Array of BibTeX ids.
    */
    function ids_for_search($value,$fields){

	$list=array();
	if(count($fields)>1){
	    	$i=0;
		$connect = sql_connexion(); 
		$sort=$connect->escapeSimple($this->sql_sort($this->sort));
		$order=$connect->escapeSimple($this->sql_sort_order($this->sort_order));
		$idbiblio = $connect->escapeSimple($this->id_biblio());
		$value = $connect->escapeSimple($value);
		
		$sql = "SELECT bibtexID_document FROM biborb_document WHERE id_biblio_document = '$idbiblio' AND (";
	        for( $i=0; $i<count($fields); $i++){
			if($fields[$i]!="sort"){
				$key = $fields[$i]."_document";
				$sql .= "$key LIKE '%$value%'";
				if($i < (count($fields)-2)){
					$sql .= " OR ";
				}
			}
	        }
		$sql .= ") ORDER BY $sort $order;";
		$res = request($connect,$sql);
		while($row = $res->fetchRow()){
			$list[$i++] = $row[0];
		}
	}
        return $list;
    }
    
        
    /**
    	\fn array ids_for_advanced_search($searchArray)
        \brief Advanced search function
	\param array \b $searchArray an array of search parameters
	\return \c array an Array containing results of the search (BibTeX ids)
    */
    function ids_for_advanced_search($searchArray){
		
        $i=0;
	$connect = sql_connexion(); 
	$list=array();
	$sort=$connect->escapeSimple($this->sql_sort($this->sort));
	$order=$connect->escapeSimple($this->sql_sort_order($this->sort_order));
	$idbiblio = $connect->escapeSimple($this->id_biblio());
	
	$sc=$searchArray['search_connector'];
	
	$sql = "SELECT bibtexID_document FROM biborb_document WHERE (";
	$sust=array("search_" =>"");
        foreach($searchArray as $key => $val){
		if($key!='search_connector'){
			$key = strtr($key,$sust)."_document";
			$val = $connect->escapeSimple($val);
			$sql .= " $key LIKE '%$val%' $sc";
		}
	}
	if($sc=="and"){
		$sql.=" id_biblio_document = '$idbiblio') ORDER BY $sort $order;";
	} else {
		$sql.=" 0 AND id_biblio_document = '$idbiblio') ORDER BY $sort $order;";
	}
	$res = request($connect,$sql);
	while($row = $res->fetchRow()){
		$list[$i++] = $row[0];
	}
	
        return $list;	
    }
    

    /**
	\fn string xpath_search($xpath_query)
	\brief Search $xpath_query in the XML file
	\param string \b $xpath_query A xpath query to be searched
	\result \c string The result of the search
	
     */
    function xpath_search($xpath_query){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        $xml_content = $this->all_entries();
        $xsl_content = file_get_contents("./xsl/xpath_query.xsl");
        $xsl_content = str_replace("XPATH_QUERY",$xpath_query,$xsl_content);
        $param = array( 'bibname' => $this->xml_file());
        $result = $xsltp->transform($xml_content,$xsl_content,$param); 
        $xsltp->free();
        return $result;
    }

    /**
    	\fn array ids_for_xpath_search()
	\brief Return the IDs of the entries for the xpath_search
	\param string $xpath_query
	\return \c array IDs for xpath_search

    */
    
    function ids_for_xpath_search($xpath_query){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        $xml_content = $this->all_entries();    
        $xsl_content = file_get_contents("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry[$xpath_query]",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }    
    
    /**
    	\fn int count_entries()
        \brief Total number of references in the database
	\return \c int number of registred references
    */
    function count_entries(){
    	$connect = sql_connexion();
	$idbiblio = $connect->escapeSimple($this->id_biblio());
	$sql="SELECT Count(*) AS Total FROM biborb_document WHERE id_biblio_document = '$idbiblio';";
	$res = request($connect,$sql);
	$row = $res->fetchRow();
	return $row[0];
    }
    
    /**
    	\fn int count_epapers()
        \brief Count on-line available papers.
	\return \c int number of available papers
    */
    function count_epapers(){
	$connect = sql_connexion();
	$idbiblio = $connect->escapeSimple($this->id_biblio());
	$sql="SELECT Count(pdf_document & url_document & urlzip_document) AS Total FROM biborb_document WHERE id_biblio_document = '$idbiblio';";
	$res = request($connect,$sql);
	$row = $res->fetchRow();
	return $row[0];
    }
    
    /**
    	\fn array entry_types()
        \brief Return a list of available types of papers (article, book, ....)
	\return \c array an Array of available types of papers
    */
    function entry_types(){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        $xml_content = file_get_contents("./xsl/model.xml");
        $xsl_content = file_get_contents("./xsl/get_all_bibtex_types.xsl");        
        $result = $xsltp->transform($xml_content,$xsl_content); 
        $xsltp->free();
	
        return explode(" ",trim($result));
    }

    /**
    	\fn change_type($id,$newtype)
	\brief Change the type of a given entry
	\param string \b $id an entry id
	\param string \b $newtype type of entry
    */
    function change_type($id,$newtype){

	$connect = sql_connexion(); 	
	$id = $connect->escapeSimple($id);
	$newtype = $connect->escapeSimple($newtype);
        $sql="UPDATE biborb_document SET type_document='$newtype' WHERE (bibtexID_document='$id');";
	request($connect,$sql);
	if($this->generate_bibtex){$this->update_bibtex_file();}
    }

    /**
    	\fn change_id($id,$newid)
	\brief Change the bibtex key
     	\param string \b $id an entry id
	\param string \b $newid the new id of the entry
    */
    function change_id($id,$newid){
    
	$connect = sql_connexion(); 	
	$id = $connect->escapeSimple($id);
	$newid = $connect->escapeSimple($newid);
        $sql="UPDATE biborb_document SET bibtexID_document='$newid' WHERE (bibtexID_document='$id');";
	request($connect,$sql);
	if($this->generate_bibtex){$this->update_bibtex_file();}

    }
    
    /**
    	\fn change_ownership($id,$newownership)
	\brief Change the ownership status of a given entry
        Shelf mode
	\param string \b $id an entry id
	\param string \b $newownership the new ownership
     */
    function change_ownership($id,$newownership){	
        // update bibtex file
	$connect = sql_connexion(); 	
	$id = $connect->escapeSimple($id);
	$newownership = $connect->escapeSimple($newownership);
	$sql="UPDATE biborb_document SET own_document='$newownership' WHERE (bibtexID_document='$id');";
	request($connect,$sql);
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }
    
    /**
    	\fn change_readstatus($id,$newreadstatus)
	\brief Change the read status of a given entry
        Shelf mode
	\param string \b $id an entry id
	\param string \b $newreadstatus the new read status
     */
    function change_readstatus($id,$newreadstatus){
        // update bibtex file
	$connect = sql_connexion(); 	
	$id = $connect->escapeSimple($id);
	$newreadstatus = $connect->escapeSimple($newreadstatus);
	$sql="UPDATE biborb_document SET read_document='$newreadstatus' WHERE (bibtexID_document='$id');";
	request($connect,$sql);
        if($this->generate_bibtex){$this->update_bibtex_file();}	
    }

    /**
    	\fn NULL xml_file()
        \brief Added in order to don't change bibindex.php
	\return \c NULL
     */
    function xml_file(){
       
	//return $this->biblio_dir.$this->biblio_name.".xml";
	return NULL;
    }
    
    /**
    	\fn add_entry_to_group($item,$group)
        \brief Add an entry \b $item to a group identified by \b $group
	\param string \b $item the entry to add	
	\param string \b $group the group in which to add the entry
     */
    function add_entry_to_group($item,$group){
	$connect = sql_connexion();
    	$item = $connect->escapeSimple($item);
	$iddoc=get_id_document($item);
	if ($group!=NULL){
		$idgroup=group_exists($group);
		$sql = "DELETE FROM biborb_group2document WHERE (id_document_group2document  ='$iddoc' AND id_group_group2document='0');";
		request($connect, $sql);
		$sql = "INSERT IGNORE INTO biborb_group2document (id_document_group2document,id_group_group2document) VALUES (";
		$sql .= "'$iddoc','$idgroup');";
	} else {
		$sql = "DELETE FROM biborb_group2document WHERE id_document_group2document  ='$iddoc';";
		request($connect, $sql);
		$sql = "INSERT IGNORE INTO biborb_group2document (id_document_group2document,id_group_group2document) VALUES (";
		$sql .= "'$iddoc','0');";
	} 	
      	request($connect, $sql);
    }
    
    /**
    	\fn int id_biblio()
        \brief Return the id_biblio in the SQL Database  of the bibliography.
	\return \c string The id_biblio in the SQL Database
    */
    function id_biblio(){
        return $this->idbiblio;
    }
    
   /**
   	\fn string sql_sort($sort)
        \brief Set the sort method for the SQL request
	\param string \b $sort sort method
	\return \c string representing the sort method for the SQL request
     */
    function sql_sort($sort){
	switch($sort){
		case 'ID':
			$sqlsort = "bibtexID_document";
			break;
		case 'author':
			$sqlsort = "author_document";
			break;
		case 'title':
			$sqlsort = "title_document";
			break;
		case 'year':
			$sqlsort = "year_document";
			break;
		case 'dateAdded':
			$sqlsort= "bibtexID_document";
			break;
		case 'lastDateModified':
			$sqlsort="bibtexID_document";
			break;
		default:
			$sqlsort="bibtexID_document";
			break;
	}
	return $sqlsort;
    }
    
    /**
    	\fn sql_sort_order($sort_order)
        \brief Set the sort order (ascending/descending) for the SQL request
	\param string \b $sort_order the sort order
	\return \c string the SQL traduction of sort order
     */
    function sql_sort_order($sort_order){
	switch($sort_order){
		case 'ascending':
			$sort_order="";
			break;
		case 'descending':
			$sort_order="DESC";
			break;
		default:
			$sort_order="";
			break;
	}
	return $sort_order;
    }
    

    
// Functions that should be in the model CLASS but are not used by Browse action
    
   /**
   	\fn array get_values_for($field)
        \brief Get all different values for a specific field in the database
	\param string \b $field the field to search
	\return \c array an Array with different possible values for the \b $field
    */
    function get_values_for($field){
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
            $xml_content = $this->all_entries();
            $xsl_content = file_get_contents("./xsl/extract_field_values.xsl");
            $param = array('sort' => $this->sort,
                           'sort_order' => $this->sort_order,
                           'field' => $field);
            $res = $xsltp->transform($xml_content,$xsl_content,$param);
            $xsltp->free();
            $tab = remove_null_values(explode('|',$res));
            sort($tab);
            return $tab;
    }
    
    /**
    	\fn array filter($ids, $field, $value)
        \brief Select among ids, entries that match $fied=$value
	\param array \b $ids bibtex ids to search in
	\param string \b $field fields to search in
	\param string \b $value value to searhc for
	\return \c array an Array of bibitex id
     */
    function filter($ids, $field, $value){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        $xml_content = $this->entries_with_ids($ids);
        if($field == 'author'){
            $xpath_query = "contains(.//bibtex:$field,'$value')";
        }
        else{
            $xpath_query = ".//bibtex:$field='$value'";
        }
        $xsl_content = file_get_contents("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry[$xpath_query]",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }
    
    /**
        \fn set_read_status($status)
	\brief Set the read status.
        When querying the database, only references of the given \b $read_status
        will be output.
	\param string \b $status the read status
     */
    function set_read_status($status){
        if(array_search($status,$this->read_status_values) === FALSE){
            $status = 'notread';
        }
        $this->read_status = $status;
    }
    
    /**
    	\fn set_ownership($val)
	\brief Set the ownership
        When querying the database, only references of the given \b $ownership
        will be output.
	\param string \b $val the ownership
     */
    function set_ownership($val){
        if(array_search($val,$this->ownership_values) === FALSE){
            $status = 'notown';
        }
        $this->ownership = $val;
    }
    
}

//END of CLASS


/**
	\fn array create_database($name,$description)
	\brief Create a new bibliography.
	\param string \b $name the bibliography name
	\param string \b $description the bibliography description
	\return \c array an Array of results
	the array contain :
		message => message to display
		error => error to display
*/
function create_database($name,$description){
    	// Not implemented description.txt error on line 200 file interface.php
	// array to store messages or errors
	
	
    	$resArray = array('message' => null,
                      'error' => null);
	
	$databases_names = get_databases_names();
	
	// check it is not a pathname
	if(!ereg('^[^./][^/]*$', $name)){
        	$resArray['error'] = msg("Invalid name for bibliography!");
    	}
    	else if($name != null){
		
		$name = remove_accents($name);
            	$name = str_replace(' ','_',$name);

		if(!in_array($name,$databases_names)){
				
			$connect = sql_connexion(); 
			$description = $connect->escapeSimple($description);
			$name = $connect->escapeSimple($name);
			$sql = "INSERT INTO biborb_biblio (description_biblio, name_biblio)";
			$sql .= "VALUES ('$description','$name');";
			request($connect, $sql);
			
            		umask(DMASK);
            		$res = mkdir("./bibs/$name");
            		if($res){
                		$resArray['message'] = msg("BIB_CREATION_SUCCESS");
            		} else {
                		$resArray['message'] = msg("BIB_CREATION_ERROR");
            		}
				
			//File description
			umask(UMASK);
			$fp = fopen("./bibs/$name/description.txt","w");
            		fwrite($fp,htmlentities($description));
            		fclose($fp);
			umask(DMASK);
            		mkdir("./bibs/$name/papers");
		}
		else{
			$resArray['error'] = msg("BIB_EXISTS");
		}
	}
	else {
		$resArray['error'] = msg("BIB_EMPTY_NAME");
	}
		      
    return $resArray;
}

/**
    \fn string delete_database($name)
    \brief Delete a bibliography
    \param string \b $name the name of a bibliography
    \return \c string Message
 */
function delete_database($name){
    	
	//check if $name is a valid bibliography name
    	if(!in_array($name,get_databases_names())){
	        trigger_error("Wrong database name: $name");
    	}
	
	$connect = sql_connexion();
	$id_biblio=id_entry($name);
	$sql = "DELETE FROM biborb_document WHERE id_biblio_document ='$id_biblio';";
	$result = request($connect, $sql);
	
	$sql = "DELETE FROM biborb_biblio WHERE name_biblio ='$name';";
	$result = request($connect, $sql);
	
	$name = remove_accents($name);
        $name = str_replace(' ','_',$name);
	// create .trash folder if it does not exit
    	if(!file_exists("./bibs/.trash")){
	       	$oldmask = umask();
        	umask(DMASK);
        	mkdir("./bibs/.trash");
        	umask($oldmask);
    	}
    	// save the bibto .trash folder
    	rename("./bibs/$name","./bibs/.trash/$name-".date("Ymd")) or trigger_error("Error while moving $name to .trash folder");
    	$res = sprintf(msg("Database %s moved to trash."),$name)."<br/>";
    	$res .= sprintf(msg("Remove %s to definitively delete it."),"<code>./bibs/.trash/$name-".date("Ymd")."</code>");    	
	
	$res = sprintf("Record '%s' succesfully deleted from the database.",$name);
	return $res;
}

/**
    \fn array get_databases_names()
    \brief Get names of recorded bibliographies.
    \return \c array Array of bibliographies names
 */
function get_databases_names(){
	$connect = sql_connexion(); 
	$sql = "SELECT name_biblio FROM biborb_biblio ORDER BY name_biblio ;";
	$result = request($connect,$sql);
	$databases_names = array();	
	while($row = $result->fetchRow()) {
        	$databases_names[$row[0]] = $row[0];
	}
	
	return $databases_names;
}

/**
	\fn array get_databases_desc()
	\brief Get the description of recorded bibliographies.
	\return \c array Array of bibliographies descriptions
*/
function get_databases_desc(){
	$connect = sql_connexion();
	$sql = "SELECT description_biblio FROM biborb_biblio ORDER BY name_biblio";
	$result = request($connect,$sql);
	$descriptions = array();
	$i=0;
        while($row = $result->fetchRow()) {
		$descriptions[$i++] = $row[0];
	}

	return $descriptions;
}

/**
	\fn object sql_connexion()
	\brief Connexion to the SQL database.
	\return \c object New DB object
*/	
function sql_connexion(){ 
	$path = ini_get('include_path');

	if ($path)
		ini_set('include_path','/usr/share/php/:'.$path);
	else
		ini_set('include_path','.:/usr/share/php/');

	$dsn = dbtype."://".user.":".passwd."@".host."/".dbname;
	$db = DB::connect($dsn);
     	
	if (DB::isError($db)) 
		die($db->getMessage());
	else 

	return $db;
}
    
/**
	\fn mixed request ($db,$sql_request)
	\brief Execute a query based on the SQL database.
	\param object \b $db a DB object
	\param string \b $sql_request SQL expression
	\return \c mixed Request result
*/
function request ($db,$sql_request){
	$result = $db->query($sql_request);
	
	if (DB::isError($result)) {
		trigger_error($result->getMessage());
	}
	else
	
	return $result;
}
    
/**
    \fn string entry_array_to_xml_from_sql($tab)
    \brief Convert an array wich is the result of a SQL request in a XML representation for use with XSLT transformation for the display_entry
    \param array \b $tab Array containing the result of a SQL request and the field's name as array's key
    \return \c string XML representation of $tab
*/
    function entry_array_to_xml_from_sql($tab){
        $xml = "<bibtex:entry id='".$tab['bibtexID_document']."'>";
        $xml .= "<bibtex:".$tab['type_document'].">";
        foreach($tab as $key => $value){
            if($key != 'id_group_document' && $key!= 'type_document' && $key!= 'id_biblio_document' && $key!= 'id_document' && $key!= 'bibtexID_document'){
	    	if ($value != NULL){
		$sust=array("_document" =>"");
                $xml .= "<bibtex:".strtr($key,$sust).">";
                $xml .= trim(myhtmlentities($value));
                $xml .= "</bibtex:".strtr($key,$sust).">";
		}
            }
	}
	//Not optimus can we do it better
	$iddoc=get_id_document($tab['bibtexID_document']);
	$connect = sql_connexion();
	$sql = "SELECT id_group_group2document FROM biborb_group2document WHERE id_document_group2document = '$iddoc';";
	$result = request($connect,$sql);
	$xml .= "<bibtex:groups>";
	while ($result->fetchInto($row)) {
    		$xml .= "<bibtex:group>";
               	$xml .= trim(myhtmlentities(name_group($row[0])));
            	$xml .= "</bibtex:group>";
	}
	$xml .= "</bibtex:groups>";
        $xml .= "</bibtex:".$tab['type_document'].">";
        $xml .= "</bibtex:entry>";
        return $xml;
    }

    
/**
    \fn string id_entry($bibname)
    \brief Return id_biblio from a bibliography name 
    \param string \b $bibname a bibliography name
    \return \c string The id associated to the bibliography name
*/
function id_entry($bibname){
	
	$connect = sql_connexion(); 
	$sql = "SELECT id_biblio FROM biborb_biblio WHERE name_biblio = '$bibname';";
	$bibid = request($connect,$sql);
	$row = $bibid->fetchRow();
	
	return $row[0];
}

/**
    \fn int group_exists($group)
    \brief Function to verify if a group exists if not it creates it
    \param string \b $group a group name
    \return \c int ID_group in the SQL database
*/		
function group_exists($group){		
	if ($group!=NULL){
		$connect = sql_connexion();
		$group = $connect->escapeSimple($group);
		$sql = "SELECT id_group FROM biborb_group WHERE name_group = '$group';";
		$res = request($connect,$sql);
		$idgroup = $res->fetchRow();
		if($idgroup[0]==NULL){
			// Not optimus how it could be in 1 request?
			$sql = "INSERT INTO biborb_group (name_group)";
			$sql .= "VALUES ('$group');";
			request($connect, $sql);
			
			$sql = "SELECT id_group FROM biborb_group WHERE name_group = '$group';";
			$res = request($connect,$sql);
			$idgroup = $res->fetchRow();
		}
	} else {
		$idgroup[0]=0;
	}
	return $idgroup[0];
}

/**
    \fn name_group($idgroup)
    \brief Return name_group from the id in the SQL Database
    \param string \b $idgroup a group id
    \return \c string The group name associated to this id 
*/		
function name_group($idgroup){
    
	$connect = sql_connexion();
	$idgroup = $connect->escapeSimple($idgroup);
	$sql = "SELECT name_group FROM biborb_group WHERE id_group = '$idgroup';";
	$res = request($connect,$sql);
	$namegroup = $res->fetchRow();
	
	return $namegroup[0];
}
	
/**
    \fn int get_id_document($bibTeXID)
    \brief Return id_document from the biborb_document SQL table with $bibTeXID as BibTeXID
    \param string \b $bibTeXID a bibtex id
    \return \c int The document id asociated to thiis BibTexID
*/		
function get_id_document($bibTeXID){	
	$connect = sql_connexion();
	$bibTeXID = $connect->escapeSimple($bibTeXID);
	$sql = "SELECT  id_document  FROM biborb_document WHERE bibtexID_document  = '$bibTeXID';";
	$res = request($connect,$sql);
	$iddoc = $res->fetchRow();
	return $iddoc[0];
}
	
?>
