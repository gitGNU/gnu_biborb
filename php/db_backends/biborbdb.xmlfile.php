<?php
/**
*
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
 * 
 * File: biborbdb.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *
 *      BibOrb database class.
 *      Provides a class to access the database recorded in XML
 *      When a request is done, the result is given as an xml string (bibtex).
 *      The interface uses this xml to generate an HTML output.
 *
 *
 */

require_once("php/xslt_processor.php"); //xslt processor

// Bibtex Database manager
class BibORB_DataBase {
	
    // Should a BibTeX file be generated.
    var $generate_bibtex;
    
    // name of the bibliography
    var $biblio_name;
    // the biblio directory
    var $biblio_dir;
    
    // Sort method used to sort entries
    var $sort;
    // Sort order method (ascending/descending)
    var $sort_order;
    
    
    /**
        Constructor.
        $bibname -> name of the bibliography
        $genBibtex -> keep an up-to-date BibTeX file.
     */
    function BibORB_DataBase($bibname,$genBibtex = true){
        $this->biblio_name = $bibname;
        $this->biblio_dir = "./bibs/$bibname/";
        $this->generate_bibtex = $genBibtex;
    }
    
    /**
        Set the sort method.
     */
    function set_sort($sort){
        $this->sort = $sort;
    }
    
    /**
        Set the sort order. (ascending/descending)
     */
    function set_sort_order($sort_order){
        $this->sort_order = $sort_order;
    }
    
    /**
        Generate the path of the xml file.
    */
    function xml_file(){
        return $this->biblio_dir.$this->biblio_name.".xml";
    }
    
    /**
        Generate the path of the bib file.
    */
    function bibtex_file(){
        return $this->biblio_dir.$this->biblio_name.".bib";
    }
    
    /**
        Return the name of the bibliography.
    */
    function name(){
        return $this->biblio_name;
    }
	
    /**
        Return the directory containing uploaded papers/data.
    */
    function papers_dir(){
        return $this->biblio_dir."papers/";
    }
    
    /**
        Update the .bib file wrt the .xml file.
        Only used in this class. 
    */
    function update_bibtex_file(){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/xml2bibtex.xsl");
        $bibtex = $xsltp->transform($xml_content,$xsl_content);
        $xsltp->free();
        // write the bibtex file
        $fp = fopen($this->bibtex_file(),"w");
        fwrite($fp,$bibtex);
        fclose($fp);
    }
    
    /**
        Reload the database according the bibtex file.
     */
    function reload_from_bibtex(){
        $bt = new BibTeX_Tools();
        $data = $bt->bibtex_file_to_xml($this->bibtex_file());
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$data[2]);
        fclose($fp);
    }
    
    /**
        Return a sorted array of all BibTeX ids.
     */
    function all_bibtex_ids(){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();    
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }
    
    /**
        Return a sorted array of BibTex ids of entries belonging to the group $groupname.
        If $groupname is null, it returns a sorted array of entries that aren't
        associated with a group.
    */
    function ids_for_group($groupname){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();    
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        if($groupname){
            $xpath = '//bibtex:entry[(.//bibtex:group)=$group]';
        }
        else{
            $xpath = '//bibtex:entry[not(.//bibtex:group)]';
        }
        $xsl_content = str_replace("XPATH_QUERY",$xpath,$xsl_content);
        $param = array('group'=>$groupname,
                       'sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }
    
    
    /**
        Get all entries in the database
    */
    function all_entries(){
        return load_file($this->xml_file());
    }
    
    /**
        Get all enties for a group
     DEPRECATED
    */
   /* function entries_for_group($groupname){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();    
        $xsl_content = load_file("./xsl/entries_for_group.xsl");
        $param = array('group'=>$groupname);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
	
        return $res;
    }*/
    
    
    /**
        Get all entries that are not associated with a group.
     DEPRECATED
     */
   /* function entries_group_orphan(){
        $xsltp = new XSLT_PRocessor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/entries_group_orphan.xsl");
        $res = $xsltp->transform($xml_content,$xsl_content,null);
        $xsltp->free();
        return $res;
    }*/
    
    /**
        Get a set of entries.
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
        $res = $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return $res;
    }
    
    /**
        Get an entry
    */
    function entry_with_id($anID){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xsl_content = load_file("./xsl/entries_with_ids.xsl");
	
        //transform the array into an xml string
        $xml_content = "<?xml version='1.0' encoding='iso-8859-1'?>";
        $xml_content .= "<listofids>";
        $xml_content .= "<id>$anID</id>";
        $xml_content .= "</listofids>";
	
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
        $res = array(   'added'=>false,
                        'message'=>"");
	
        $bibid = trim($dataArray['id']);
        // check if the entry is already present
        $inbib = $this->is_bibtex_key_present($bibid);
	
        // error, ID already exists or empty value
        if( $inbib|| strlen($bibid) == 0 || $bibid == null){
            if($inbib){
                $res['message'] = msg("BibTeX ID already present, select a different one.");
                $res['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
            else{
                $res['message'] = msg("Null BibTeX ID for an entry not allowed.");
                $res['message'] .= "<div style='text-align:center'><a href='javascript:history.back()'>".msg("Back")."</a></div>";
            }
        }
        else{
            // upload files if they are present
            if(array_key_exists('url',$_FILES) && file_exists($_FILES['url']['tmp_name'])){
                $urlfile=upload_file($this->biblio_name,'url',$dataArray['id']);
                $dataArray['url'] = $urlfile;
            }
            if(array_key_exists('urlzip',$_FILES) && file_exists($_FILES['urlzip']['tmp_name'])){
                $urlzipfile=upload_file($this->biblio_name,'urlzip',$dataArray['id']);
                $dataArray['urlzip'] = $urlzipfile;
            }  
            if(array_key_exists('pdf',$_FILES) && file_exists($_FILES['pdf']['tmp_name'])){
                $pdffile=upload_file($this->biblio_name,'pdf',$dataArray['id']);
                $dataArray['pdf'] = $pdffile;
            }
	    
            // add the new entry
            $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
            $bt = new BibTeX_Tools();
            $data = $bt->entries_array_to_xml(array(extract_bibtex_data($dataArray)));
            $xml = $data[2];
            $xsl = load_file("./xsl/add_entries.xsl");
            $param = array('bibname' => $this->xml_file());
            $result = $xsltp->transform($xml,$xsl,$param);
            $xsltp->free();
	    
            $fp = fopen($this->xml_file(),"w");
            fwrite($fp,$result);
            fclose($fp);
            // update bibtex file
            if($this->generate_bibtex){$this->update_bibtex_file();}
	    
            $res['added'] = true;
            $res['message'] = "";
            $res['id'] = $dataArray['id'];
        }
        return $res;
    }
	
    /**
        Add entries. $bibtex is a bibtex string.
    */
    function add_bibtex_entries($bibtex){
        // add the new entry
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $bt = new BibTeX_Tools();
        $data = $bt->bibtex_string_to_xml($bibtex);
        $xsl = load_file("./xsl/add_entries.xsl");
        $param = array('bibname' => $this->xml_file());
        $result = $xsltp->transform($data[2],$xsl,$param);
        $xsltp->free();

        // save the database
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
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
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }
    
    /**
        Delete entries from the database
    */
    function delete_entries($tabids){
        foreach($tabids as $id){
            $this->delete_entry($id);
        }
    }
    
    /**
        Update an entry.
    */
    function update_entry($dataArray){
		// check if the id value is null
        $res = array('updated'=>false,
		     'message'=>"");
        if($dataArray['id'] == null){
            $res['updated'] = false;
            $res['message'] = msg("Null BibTeX ID for an entry not allowed.");
        }
        else{
            $urlfile = null;
            $urlzipfile = null;
            $pdffile = null;
            
            if(array_key_exists('url',$_FILES) && file_exists($_FILES['url']['tmp_name'])){
                $urlfile = upload_file($this->biblio_name,'url',$dataArray['id']);
                $dataArray['url'] = $urlfile;
            }
            else if($dataArray['current_url'] != null){
                $urlfile = $dataArray['current_url'];
                $dataArray['url'] = $urlfile;
            }
            
            if(array_key_exists('urlzip',$_FILES) && file_exists($_FILES['urlzip']['tmp_name'])){
                $urlzipfile = upload_file($this->biblio_name,'urlzip',$dataArray['id']);
                $dataArray['urlzip'] = $urlzipfile;
            }
            else if($_POST['current_urlzip'] != null){
                $urlzipfile = $dataArray['current_urlzip'];
                $dataArray['urlzip'] = $urlzipfile;
            }  
            
            if(array_key_exists('pdf',$_FILES) && file_exists($_FILES['pdf']['tmp_name'])){
                $pdffile = upload_file($this->biblio_name,'pdf',$dataArray['id']);
                $dataArray['pdf'] = $pdffile;
            }
            else if($_POST['current_pdf'] != null){
                $pdffile= $dataArray['current_pdf'];
                $dataArray['pdf'] = $pdffile;
            }
            
            $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
            $bt = new BibTeX_Tools();
            $data = $bt->entries_array_to_xml(array(extract_bibtex_data($dataArray)));
            $xml = $data[2];

            $xsl = load_file("./xsl/update_xml.xsl");
            $param = array('bibname' => $this->xml_file());
            $result = $xsltp->transform($xml,$xsl,$param);
            $xsltp->free();
            
            $fp = fopen($this->xml_file(),"w");
            fwrite($fp,$result);
            fclose($fp);
            // update bibtex file
            if($this->generate_bibtex){$this->update_bibtex_file();}
            
            $res['updated'] = true;
            $res['message'] = "";
            $res['id'] = $dataArray['id'];
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
        Return an array containing groups present in the bibliography.
    */
    function groups(){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        // Get groups from the xml bibtex file
        $xml_content = load_file($this->xml_file());
        $xsl_content = load_file("./xsl/group_list.xsl");  
        $group_list = $xsltp->transform($xml_content,$xsl_content);
        $xsltp->free();
        
        // Remove doublons
        $group_list = split("[,~]",$group_list);
        foreach($group_list as $key=>$value){
            if(trim($value) == ""){
                unset($group_list[$key]);
            }
            else{
                $group_list[$key] = trim($value);
            }
        }
        $list = array_unique($group_list);
        sort($list);
        
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
        $xsltp->free();
        
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
    
    function ids_for_search($value,$fields){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->search_entries($value,$fields);    
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY",'//bibtex:entry',$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }
    
    /**
        Advanced search function
    */
    function advanced_search_entries($searchArray){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content =$this->all_entries();
        $xsl_content = load_file("./xsl/advanced_search.xsl");
        $param = array( 'bibname' => $this->xml_file());
        foreach($searchArray as $key => $val){
            $param[$key] = $val; 
        }
	
        $result = $xsltp->transform($xml_content,$xsl_content,$param); 
        $xsltp->free();
        return $result;
    }
    
    function ids_for_advanced_search($searchArray){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->advanced_search_entries($searchArray);    
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY",'//bibtex:entry',$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }

    /**
        XPath search
     */
    function xpath_search($xpath_query){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();
        $xsl_content = load_file("./xsl/xpath_query.xsl");
        $xsl_content = str_replace("XPATH_QUERY",$xpath_query,$xsl_content);
        $param = array( 'bibname' => $this->xml_file());
        $result = $xsltp->transform($xml_content,$xsl_content,$param); 
        $xsltp->free();
        return $result;
    }
    
    function ids_for_xpath_search($xpath_query){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = $this->all_entries();    
        $xsl_content = load_file("./xsl/extract_ids.xsl");
        $xsl_content = str_replace("XPATH_QUERY","//bibtex:entry[$xpath_query]",$xsl_content);
        $param = array('sort' => $this->sort,
                       'sort_order' => $this->sort_order);
        $res =  $xsltp->transform($xml_content,$xsl_content,$param);
        $xsltp->free();
        return remove_null_values(explode('|',$res));
    }
    
    /**
        Total number of entries
    */
    function count_entries(){ 
        $allentries = $this->all_entries();
        return substr_count($allentries,"<bibtex:entry ");
    }
    
    /**
        Count on-line available papers.
    */
    function count_epapers(){
        $allentries = $this->all_entries();
        $pdf = substr_count($allentries,"<bibtex:pdf>");
        $urlzip = substr_count($allentries,"<bibtex:urlzip>");
        $url = substr_count($allentries,"<bibtex:url>");
	
        return $url+$urlzip+$pdf;
    }
    
    /**
        Return a list of available types of papers
    */
    function entry_types(){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        $xml_content = load_file("./xsl/model.xml");
        $xsl_content = load_file("./xsl/get_all_bibtex_types.xsl");        
        $result = $xsltp->transform($xml_content,$xsl_content); 
        $xsltp->free();
	
        return explode(" ",trim($result));
    }

    /**
     Change the type of a given entry
    */
    function change_type($id,$newtype){	
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        // get the entry
        $xml_content = $this->entry_with_id($id);
        // get its type
        $oldtype = trim($xsltp->transform($xml_content,load_file("./xsl/get_bibtex_type.xsl")));
        // replace it
        $xml_content = str_replace("bibtex:$oldtype","bibtex:$newtype",$xml_content);
        // update the xml
        $xsl = load_file("./xsl/update_xml.xsl");
        $param = array('bibname' => $this->xml_file());
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $xsltp->free();
        // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }

    /**
     Change the bibtex key
    */
    function change_id($id,$newid){
        $xsltp = new XSLT_Processor("file://".getcwd()."/biborb","ISO-8859-1");
        // get the entry
        $xml_content = $this->entry_with_id($id);
        
        $path = $this->papers_dir();
        // change the name of recorded files if necessary
        preg_match("/\<bibtex:url\>(.*)\<\/bibtex:url\>/",$xml_content,$matches);
        if(count($matches)>0){
            $oldname = $matches[1];
            $newname = get_new_name($oldname,$newid);
            rename($path.$oldname,$path.$newname);
            $xml_content = str_replace("<bibtex:url>$oldname</bibtex:url>","<bibtex:url>$newname</bibtex:url>",$xml_content);
        }
        preg_match("/\<bibtex:urlzip\>(.*)\<\/bibtex:urlzip\>/",$xml_content,$matches);
        if(count($matches)>0){
            $oldname = $matches[1];
            $newname = get_new_name($oldname,$newid);
            rename($path.$oldname,$path.$newname);
            $xml_content = str_replace("<bibtex:urlzip>$oldname</bibtex:urlzip>","<bibtex:urlzip>$newname</bibtex:urlzip>",$xml_content);
        }
        preg_match("/\<bibtex:pdf\>(.*)\<\/bibtex:pdf\>/",$xml_content,$matches);
        if(count($matches)>0){
            $oldname = $matches[1];
            $newname = get_new_name($oldname,$newid);
            rename($path.$oldname,$path.$newname);
            $xml_content = str_replace("<bibtex:pdf>$oldname</bibtex:pdf>","<bibtex:pdf>$newname</bibtex:pdf>",$xml_content);
        }    
        // update the xml
        $xsl = load_file("./xsl/update_xml.xsl");
        $param = array('bibname' => $this->xml_file());
        $result = $xsltp->transform($xml_content,$xsl,$param);
        $xsltp->free();
         // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$result);
        fclose($fp);
        
        // replace by the new id in the xml file
        $xml_content = str_replace("id=\"$id\"","id=\"$newid\"",load_file($this->xml_file()));
        // save it
        $fp = fopen($this->xml_file(),"w");
        fwrite($fp,$xml_content);
        fclose($fp);
        
        // update bibtex file
        if($this->generate_bibtex){$this->update_bibtex_file();}
    }
}


/**
 Create a new bibliography.
*/
function create_database($name,$description){
    // array to store messages or errors
    $resArray = array('message' => null,
                      'error' => null);
    
    $databases_names = get_databases_names();
    
    if($name != null){
        if(!in_array($name,$databases_names)){
            $res = mkdir("./bibs/$name",0775);
            if($res){
                $resArray['message'] = msg("BIB_CREATION_SUCCESS");
            }
            else{
                $resArray['message'] = msg("BIB_CREATION_ERROR");
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
            $resArray['error'] = msg("BIB_EXISTS");
        }
    }
    else {
        $resArray['error'] = msg("BIB_EMPTY_NAME");
    }
    return $resArray;
}

/**
    Delete a bibliography
 */
function delete_database($name){
    // create .trash folder if it does not exit
    if(!file_exists("./bibs/.trash")){mkdir("./bibs/.trash",0775);}
    // save the bibto .trash folder
    rename("bibs/$name","bibs/.trash/$name-".date("Ymd")) or die("BibORB Error: Error while moving $name to .trash folder");
    $res = sprintf(msg("Database %s moved to trash."),$name)."<br/>";
    $res .= sprintf(msg("Remove %s to definitively delete it."),"<code>./bibs/.trash/$name-".date("Ymd")."</code>");
    return $res;
}

/**
    Get the name of recorded bibliographies.
 */
function get_databases_names(){
    $dir = opendir("./bibs/");
    $databases_names = array();
    while($file = readdir($dir)){
        if(is_dir("./bibs/".$file) && $file != '.' && $file != '..' && $file[0] != '.'){
            array_push($databases_names,$file);
        }
    }
    return $databases_names;
}

/**
    Extract ids from the xml
*/
function extract_ids_from_xml($xmlstring)
{
    preg_match_all("/id=['|\"](.*)['|\"]/U",$xmlstring,$matches);
    return array_unique($matches[1]);
}
?>