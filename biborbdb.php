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
 * File: biborbdb.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)-
 * Licence: GPL
 * 
 * Description:
 *
 *      BibOrb database class.
 *      Provides a class to access the database recorded in XML
 *      When a request is done, the result is given as an xml string (bibtex).
 *      The interface uses this xml to generate an HTML output.
 *
 */
require_once("xslt_processor.php"); //xslt processor

// Bibtex DB manager

class BibORB_DataBase {
	
	var $biblio_name;
	var $error_message;
	var $biblio_dir;
	
	/**
		Constructeur
	 */
	function BibORB_DataBase($bibname){
		$this->biblio_name = $bibname;
		$this->biblio_dir = "./bibs/$bibname/";
	}

	function xml_file(){
		return $this->biblio_dir.$this->biblio_name.".xml";
	}
	
	function name(){
		return $this->biblio_name;
	}
	
	function papers_dir(){
		return $this->biblio_dir."papers/";
	}
	/**
		Return the current error_message
	 */
	function error_message(){
		return $this->error_message;
	}
	
	/**
		Get all entries in the database
	 */
	function all_entries(){
		return load_file($this->xml_file());
	}
	
	/**
		Get all enties for a group
	 */
	function entries_for_group($groupname){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$xml_content = $this->all_entries();    
		$xsl_content = load_file("./xsl/entries_for_group.xsl");
		$param = array('group'=>$groupname);
		return $xsltp->transform($xml_content,$xsl_content,$param);
	}
	
	/**
		Get a set of entries
	 */
	function entries_with_ids($anArray){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$xsl_content = load_file("./xsl/entries_with_ids.xsl");
		//transform the array into an xml string
		$xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
		$xml_content = "<listofids>";
		foreach($anArray as $item){
			$xml_content .= "<id>$item</id>";
		}
		$xml_content .= "</listofids>";

		$param = array('bibnameurl' => $this->xml_file());
		return $xsltp->transform($xml_content,$xsl_content,$param);
	}
	
	/**
		Get an entry
	 */
	function entry_with_id($anID){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$xsl_content = load_file("./xsl/entries_with_ids.xsl");
		
		//transform the array into an xml string
		$xml_content = <<< XML
<?xml version='1.0' encoding='iso-8859-1'?>
<listofids>
	<id>$anID</id>
</listofids>
XML;
		
		$param = array('bibnameurl' => $this->xml_file());
		$res = $xsltp->transform($xml_content,$xsl_content,$param);
		$xsltp->free();
		return $res;
	}
	
	/**
		Add a new entry to the database
		$dataArray contains bibtex values
	 */
	function add_new_entry($dataArray){
		$res = array('added'=>false,
					 'message'=>"");
		
		$bibid = trim($dataArray['_id']);
		// check if the entry is already present
		$inbib = $this->is_bibtex_key_present($bibid);

		// error, ID already exists or empty value
		if( $inbib|| strlen($bibid) == 0 || $bibid == null){
			if($inbib){
				$res['message'] = "ID already present, select a different one.";
			}
			else{
				$res['message'] = "Null ID not allowed.";
			}
		}
		else{
			// upload files if they are present
			if(array_key_exists('url',$_FILES) && file_exists($_FILES['url']['tmp_name'])){
				$urlfile=upload_file($this->biblio_name,'url',$dataArray['_id']);
				$dataArray['_url'] = $urlfile;
			}
			if(array_key_exists('urlzip',$_FILES) && file_exists($_FILES['urlzip']['tmp_name'])){
				$urlzipfile=upload_file($this->biblio_name,'urlzip',$dataArray['_id']);
				$dataArray['_urlzip'] = $urlzipfile;
			}  
			if(array_key_exists('pdf',$_FILES) && file_exists($_FILES['pdf']['tmp_name'])){
				$pdffile=upload_file($this->biblio_name,'pdf',$dataArray['_id']);
				$dataArray['_pdf'] = $pdffile;
			}
			
			// add the new entry
			$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
			$xml = bibtex_array_to_xml($dataArray);
			$xsl = load_file("./xsl/add_entries.xsl");
			$param = array('bibname' => $this->xml_file());
			$result = $xsltp->transform($xml,$xsl,$param);
			$xsltp->free();
			
			$fp = fopen($this->xml_file(),"w");
			fwrite($fp,$result);
			fclose($fp);
			// update bibtex file
			xml2bibtex($this->biblio_name);
			
			$res['added'] = true;
			$res['message'] = "";
			$res['id'] = $dataArray['_id'];
		}
		return $res;
	}
	
	function add_bibtex_entries($bibtex){
		// add the new entry
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$data = bibtex2xml($bibtex);
		
		$xsl = load_file("./xsl/add_entries.xsl");
		$param = array('bibname' => $this->xml_file());
		$result = $xsltp->transform($data[2],$xsl,$param);
		$xsltp->free();
		
		// save the database
		$fp = fopen($this->xml_file(),"w");
		fwrite($fp,$result);
		fclose($fp);
		// update bibtex file
		xml2bibtex($this->biblio_name);
		return $data[1];
	}
	
	/**
		Delete an entry from the database
	 */
	function delete_entry($bibtex_id){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$xml_content = $this->all_entries();
		$xsl_content = load_file("./xsl/delete_entries.xsl");
		$param = array('id'=>$bibtex_id);  
		$newxml = $xsltp->transform($xml_content,$xsl_content,$param);
		
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
			unlink($this->papers_dir().$file);
		}
		
		// update the xml file.
		$fp = fopen($this->xml_file(),"w");
		fwrite($fp,$newxml);
		fclose($fp);
		
		//update the bibtex file.
		xml2bibtex($this->biblio_name);

	}
	
	/**
		Update an entry.
	 */
	function update_entry($dataArray){
		// check if the id value is null
		$res = array('updated'=>false,
					 'message'=>"");
		if($dataArray['_id'] == null){
			$res['updated'] = false;
			$res['message'] = "Null id for an entry not allowed.";
		}
		else{
			$urlfile = null;
			$urlzipfile = null;
			$pdffile = null;
			
			if(array_key_exists('url',$_FILES) && file_exists($_FILES['url']['tmp_name'])){
				$urlfile = upload_file($this->biblio_name,'url',$dataArray['_id']);
				$dataArray['_url'] = $urlfile;
			}
			else if($dataArray['current_url'] != null){
				$urlfile = $dataArray['current_url'];
				$dataArray['_url'] = $urlfile;
			}
			
			if(array_key_exists('urlzip',$_FILES) && file_exists($_FILES['urlzip']['tmp_name'])){
				$urlzipfile = upload_file($this->biblio_name,'urlzip',$dataArray['_id']);
				$dataArray['_urlzip'] = $urlzipfile;
			}
			else if($_POST['current_urlzip'] != null){
				$urlzipfile = $dataArray['current_urlzip'];
				$dataArray['_urlzip'] = $urlzipfile;
			}  
			
			if(array_key_exists('pdf',$_FILES) && file_exists($_FILES['pdf']['tmp_name'])){
				$pdffile = upload_file($this->biblio_name,'pdf',$dataArray['_id']);
				$dataArray['_pdf'] = $pdffile;
			}
			else if($_POST['current_pdf'] != null){
				$pdffile= $dataArray['current_pdf'];
				$dataArray['_pdf'] = $pdffile;
			}
		
			$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
			$xml = bibtex_array_to_xml($dataArray);
			$xsl = load_file("./xsl/update_xml.xsl");
			$param = array('bibname' => $this->xml_file());
			$result = $xsltp->transform($xml,$xsl,$param);
			$xsltp->free();
			
			$fp = fopen($this->xml_file(),"w");
			fwrite($fp,$result);
			fclose($fp);
			// update bibtex file
			xml2bibtex($this->biblio_name);
			
			$res['updated'] = true;
			$res['message'] = "";
			$res['id'] = $dataArray['_id'];
		}
		return $res;
	}
	
	/**
		Test if a key is already present in the database
	 */
	function is_bibtex_key_present($bibtex_key){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$content = load_file($this->xml_file());
		$xsl = load_file("./xsl/search_entry.xsl");
		$param = array('id' => $bibtex_key);
		$result = $xsltp->transform($content,$xsl,$param);
		return (substr_count($result,"true") > 0);
	}
	
	/**
		Return an array containing groups present in the DB
	 */
	function groups()
	{
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		// Get groups from the xml bibtex file
		$xml_content = load_file("./bibs/".$this->biblio_name."/".$this->biblio_name.".xml");
		$xsl_content = load_file("./xsl/group_list.xsl");  
		$group_list = $xsltp->transform($xml_content,$xsl_content);
		$xsltp->free();
		
		// Remove doublons
		$group_list = split("[,~]",$group_list);
		$list = array();
		$j=0;
		for($i=0;$i<sizeof($group_list);$i++){
			$group_list[$i] = trim($group_list[$i]);
			if($group_list[$i] != ""){
				if(!in_array($group_list[$i],$list)){
					$list[$j] = $group_list[$i];
					$j++;
				}
			}
		}
		
		return $list;    
	}
	
	/**
		Add a set of entries to a group
	 */
	function add_to_group($idArray,$group){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		// create an xml string containing id present in the basket
		$xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
		$xml_content = "<listofids>";
		foreach($idArray as $item){
			$xml_content .= "<id>$item</id>";
		}
		$xml_content .= "</listofids>";
		$xsl_content = load_file("./xsl/addgroup.xsl");
		
		$param = array( 'bibname' => $this->xml_file(),
						'group' => $group);
		// new xml file
		$result = $xsltp->transform($xml_content,$xsl_content,$param); 
		
		// update the xml file
		$xsl_content = load_file("./xsl/update_xml.xsl");
		$result = $xsltp->transform($result,$xsl_content,$param);
		
		$fp = fopen($this->xml_file(),"w");
		fwrite($fp,$result);
		fclose($fp);
	}
	
	/**
		Reset groups of a set of entries
	 */
	function reset_groups($idArray){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		// create an xml string containing id present in the basket
		$xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
		$xml_content = "<listofids>";
		foreach($idArray as $item){
			$xml_content .= "<id>$item</id>";
		}
		$xml_content .= "</listofids>";
		$xsl_content = load_file("./xsl/resetgroup.xsl");
		$param = array( 'bibname' => $this->xml_file());
		$result = $xsltp->transform($xml_content,$xsl_content,$param); 
		
		// update the xml file
		$xsl_content = load_file("./xsl/update_xml.xsl");
		$result = $xsltp->transform($result,$xsl_content,$param);
		$xsltp->free();
		
		$fp = fopen($this->xml_file(),"w");
		fwrite($fp,$result);
		fclose($fp);
	}

	/**
		Search in given fields, a given value
	 */
	function search_entries($value,$fields){
		$xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
		$xml_content =$this->all_entries();
		$xsl_content = load_file("./xsl/search.xsl");
		$param = array( 'bibname' => $this->xml_file());
		foreach($fields as $val){
			$param[$val] = "1"; 
		}
		$param['search'] = $value;
		$result = $xsltp->transform($xml_content,$xsl_content,$param); 
		$xsltp->free();
		return $result;
	}
	
	/**
		Count entries
	 */
	function count_entries(){ 
		$allentries = $this->all_entries();
		return substr_count($allentries,"<bibtex:entry ");
	}
	
	/**
		Count on-line available papers
	 */
	function count_epapers(){
		$allentries = $this->all_entries();
		$pdf = substr_count($allentries,"<bibtex:pdf>");
		$urlzip = substr_count($allentries,"<bibtex:urlzip>");
		$url = substr_count($allentries,"<bibtex:url>");
		
		return $url+$urlzip+$pdf;
	}
	
}



function create_database($name,$description){
    
    $resArray = array('message' => null,
                      'error' => null);
    
    $databases_names = get_databases_names();
    
    if($name != null){
        if(!in_array($name,$databases_names)){
            $res = mkdir("./bibs/$name",0775);
            if($res){
                $resArray['message'] = "The database was successfully created.";
            }
            else{
                $resArray['message'] = "Unabled to create the database.";
            }
            
            mkdir("./bibs/$name/papers",0775);
            copy("./data/template/template.bib","./bibs/$name/$name.bib");
            copy("./data/template/template.xml","./bibs/$name/$name.xml");
            chmod("./bibs/$name/$name.bib",0666);
            chmod("./bibs/$name/$name.xml",0666);
            
            $fp = fopen("./bibs/$name/description.txt","w");
            fwrite($fp,$description);
            fclose($fp);
            $xml = load_file("./bibs/$name/$name.xml");
            $xml = str_replace("template",$name,$xml);
            $fp = fopen("./bibs/$name/$name.xml","w");
            fwrite($fp,$xml);
            fclose($fp);
        }
        else{
            $resArray['error'] = "Database already exists!";
        }
    }
    else {
        $resArray['error'] = "Empty database name!!";
    }
    return $resArray;
}

function delete_database($name){
    // create .trash folder if it does not exit
    if(!file_exists("./bibs/.trash")){
        mkdir("./bibs/.trash",0775);
    }
    // save the bibto .trash folder
    rename("./bibs/$name","./bibs/.trash/$name");
    $res = "Database $name moved to trash.<br/>";
    $res .= "Remove <code>./bibs/.trash/$name</code> to definitively delete it.";
    return $res;
}


?>