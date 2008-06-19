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
 * File: biborbdb.php
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

class_exists('XSLT_Processor')  || include('./php/xslt_processor.php'); //xslt processor
class_exists('Reference')       || include('./php/reference/Reference.php');
class_exists('XmlConverter')    || include('./php/reference/XmlConverter.php');
class_exists('BibtexConverter') || include('./php/reference/BibtexConverter.php');
class_exists('FileToolKit')     || include('./php/FileToolKit.php');


// list of sort attributes
$sort_values = array('author','title','ID','year','dateAdded','lastDateModified');

//Bibtex Database manager
class BibORB_DataBase
{

    // Should a BibTeX file be generated.
    var $_genBibtex;

    // name of the bibliography
    var $_bibName;

    // the biblio directory
    var $_bibDir;

    // list of BibTeX fields relevant for BibORB
    var $_fields;

    // the changelog file;
    var $_changelog;

    // Sort method used to sort entries
    var $_sortMethod;
    var $_sortMethodValues = array('author','title','ID','year','dateAdded','lastDateModified');

    // Sort order method (ascending/descending)
    var $_sortOrder;
    var $_sortOrderValues = array('ascending','descending');

    // Read status
    var $_readStatus;
    var $_readStatusValues = array('any','notread','readnext','read');

    // Ownership
    var $_ownership;
    var $_ownershipValues = array('any','notown','borrowed','buy','own');


    // number of references in the database
    var $_entryCount;
    var $_papersCount;

    // List of all ids of references in the database
    var $_ids;


    var $_sortUpdated;
    var $_browseCacheNeedUpdate;
    var $_browseCache;
    
    
    /**
     * Constructor.
     * $bibname -> name of the bibliography
     *  $genBibtex -> keep an up-to-date BibTeX file. Save in the $bibname
     *                directory with name $bibname.tex.
     */
    function BibORB_DataBase($iBibname, $iGenBibtex = true)
    {
        $this->_bibName = $iBibname;
        $this->_bibDir = './bibs/'.$iBibname;
        $this->_changelog = './bibs/'.$iBibname.'/changelog.txt';
        $this->_genBibtex = $iGenBibtex;
        $this->_readStatus = 'any';
        $this->_ownership = 'any';
        $this->_sortMethod = 'ID';
        $this->_sortOrder = 'ascending';
        $this->_sortUpdated = TRUE;        
        $this->getEntryCount();
        $this->getAllIds();
        $this->browseNeedUpdate = TRUE;
        

        // check the version of biborb files
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXmlContent = $this->getAllEntries();
        $aXslContent = <<< XSLT_END
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:bibtex="http://bibtexml.sf.net/" version="1.0">
    <xsl:output method="text" encoding="UTF-8"/>
    <xsl:template match="/bibtex:file">
            <xsl:value-of select="@version"/>
    </xsl:template>
</xsl:stylesheet>
XSLT_END;

        $aVersion = trim($aXsltp->transform($aXmlContent, $aXslContent));
        $aXsltp->free();

        if ($aVersion != '1.1')
        {
            // * add the first author lastname in a separate field
            // to sort by author
            // * set date added and last modified to yesterday
            // load all data in an array form
            $aEntries = XmlConverter::import($aXmlContent);
            foreach ($aEntries as $aKey => $aRef)
            {
                // get the name of the first author
                if ($aRef->getData('author'))
                {
                    $aPC = new PARSECREATORS();
                    $creatorArray = $pc->parse($aRef->getData('author'));
                    $aEntries[$aKey]->setData('lastName', $creatorArray[0][2]);
                }
                // set dateAdded and lastDateModified attributes
                $aDate = date("Y-m-d",
                              mktime(0, 0, 0, date("m"), date("d")-1, date("Y")));
                if (!$aRef->getData('dateAdded'))
                {
                    $aEntries[$aKey]->setData('dateAdded', $aDate);
                }
                $aEntries[$aKey]->setData('lastDateModified', $aDate);

                // change own= {yes} to own = {own}
                if ($aRef->getData['own'])
                {
                    $aEntries[$aKey]->setData('own', $aRef->getData['own'] == 'yes' ? 'own' : $aRef->getData('own'));
                }
            }
            // convert to XML and save
            $aFileName = $this->getXmlFileName();
            rename($aFileName, $aFileName.'.save');
            FileToolKit::putContent($aFileName, XmlConverter::export($aEntries));
        }
    }

    /**
     *
     */
    function set_BibORB_fields($tab)
    {
        $this->_fields = $tab;
    }

    /**
     *
     */
    function getFullName()
    {
        return FileToolKit::getContent($this->_bibDir.'/'.'fullname.txt');
    }


    /**
     * Set the sort method.
     */
    function setSortMethod($aSort)
    {
        $this->_sortMethod = array_search($aSort, $this->_sortMethodValues) === FALSE ? 'ID' : $aSort;
        $this->_sortUpdated = TRUE;
    }

    function getSortMethod()
    {
        return $this->_sortMethod;
    }
    

    /**
     * Set the sort order. (ascending/descending)
     */
    function setSortOrder($aSortOrder)
    {
        $this->_sortOrder = array_search($aSortOrder,$this->_sortOrderValues) === FALSE ? 'ascending' : $aSortOrder;
        $this->_sortUpdated = TRUE;

    }

    function getSortOrder()
    {
        return $this->_sortOrder;
    }
    
    function getSortMethodValues()
    {
        return $this->_sortMethodValues;
    }

    function getSortOrderValues()
    {
        return $this->_sortOrderValues;
    }

    function getReadStatusValues()
    {
        return $this->_readStatusValues;
    }

    function getOwnershipValues()
    {
        return $this->_ownershipValues;
    }

    /**
     * Set the read status.
     * When querying the database, only references of the given $read_status
     * will be output.
     */
    function setReadStatus($aStatus)
    {
        $this->_readStatus = array_search($aStatus, $this->_readStatusValues) === FALSE ? 'notread' : $aStatus;
    }

    /**
     * Set the ownership
     * When querying the database, only references of the given $ownership
     * will be output.
     */
    function setOwnership($aOwnership)
    {
        $this->_ownerShip = array_search($aOwnership,$this->_ownershipValues) === FALSE ? 'notown' : $aOwnership;
    }

    /**
     * Generate the path of the xml file.
     */
    function getXmlFileName()
    {
        return $this->_bibDir.'/'.$this->_bibName.'.xml';
    }

    /**
     * Generate the path of the bib file.
     */
    function getBibtexFileName()
    {
        return $this->_bibDir.'/'.$this->_bibName.'.bib';
    }

    /**
     * Return the name of the bibliography.
     */
    function getName()
    {
        return $this->_bibName;
    }

    /**
     *   Return the directory containing uploaded papers/data.
     */
    function getPapersDir()
    {
        return $this->_bibDir.'/papers/';
    }

    /**
     * Update the .bib file wrt the .xml file.
     * Only used in this class.
     */
    function updateBibtexFile()
    {
        // Load all the database and transform it into a bibtex string
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/xml2bibtex.xsl');
        $aBibtex = $aXsltp->transform($aXml, $aXsl);
        $aXsltp->free();

        // write the bibtex file
        FileToolKit::putContent($this->getBibtexFileName(), $aBibtex);
    }

    /**
     * Reload the database according the bibtex file.
     */
    function reloadFromBibtex()
    {
        // load the bibtex file and transform it to XML
        XmlConverter::export(BibtexConverter::import($this->getBibtexFileName()));
    }

    /**
     * Return an array of all BibTeX ids.
     * The entries are sorted using $this->_sort and $this->_sortOrder
     */
    function getAllIds()
    {
        if (!isset($this->_ids) || $this->_sortUpdated)
        {
            $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
            $aXml = $this->getAllEntries();
            
            $aXsl = FileToolKit::getContent('./xsl/extract_ids.xsl');
            $aXsl = str_replace('XPATH_QUERY','//bibtex:entry',$aXsl);                        
            $aParam = array('sort' => $this->_sortMethod,
                            'sort_order' => $this->_sortOrder);
            $aRes = $aXsltp->transform($aXml, $aXsl, $aParam);
            $aXsltp->free();
            $this->_ids = remove_null_values(explode('|',$aRes));
            $this->_sortUpdated = FALSE;
        }
        return $this->_ids;
    }

    /**
     * Return a sorted array of BibTex ids of entries belonging to the group $groupname.
     * If $groupname is null, it returns a sorted array of entries that aren't
     * associated with a group.
     */
    function getIdsForGroup($aGroup)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/extract_ids.xsl');
        // generate the apropriate XSLT request
        if (isset($aGroup) ||
            $aGroup != '')
        {
            // return entries associated to a given group
            $aXpath = '//bibtex:entry[(.//bibtex:group)=$group';
            if ($this->_readStatus != 'any')
            {
                $aXpath .= ' and (.//bibtex:read)=\''.$this->_readStatus.'\'';
            }
            if ($this->_ownership != 'any')
            {
                $aXpath .= ' and (.//bibtex:own)=\''.$this->_ownership.'\'';
            }
            $aXpath .= ']';
        }
        else
        {
            // return 'orphan' entries
            $aXpath = '//bibtex:entry[not(.//bibtex:group)';
            if ($this->_readStatus != 'any')
            {
                $aXpath .= ' and (.//bibtex:read)=\''.$this->_readStatus.'\'';
            }
            if ($this->_ownership != 'any')
            {
                $aXpath .= ' and (.//bibtex:own)=\''.$this->_ownership.'\'';
            }
            $aXpath .= ']';
        }
        $aXsl = str_replace('XPATH_QUERY',$aXpath,$aXsl);
        // do the transformation
        $aParam = array('group'=> $aGroup,
                        'sort' => $this->_sortMethod,
                        'sort_order' => $this->_sortOrder,
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        $aRes =  $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        return remove_null_values(explode('|',$aRes));
    }


    /**
     * Get all entries in the database
     */
    function getAllEntries()
    {
        return FileToolKit::getContent($this->getXmlFileName());
    }

    /**
     * Get a set of entries.
     */
    function getEntriesWithIds($anArray)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXsl = FileToolKit::getContent('./xsl/entries_with_ids.xsl');
        //transform the array into an xml string
        $aXml = '<?xml version="1.0" encoding="UTF-8"?>';
        $aXml .= '<listofids>';
        foreach ($anArray as $item)
        {
            $aXml .= '<id>'.$item.'</id>';
        }
        $aXml .= '</listofids>';
        $aParam = array('bibnameurl' => $this->getXmlFileName());
        $aRes = $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        return $aRes;
    }

    /**
     * Get an entry
     */
    function getEntryWithId($anID)
    {
        return $this->getEntriesWithIds(array($anID));
    }

    /**
     * Add a new entry to the database
     *  $dataArray contains bibtex values
     */
    function addNewEntry($dataArray)
    {
        $res = array('added'=>false,
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
            if(array_key_exists('up_url',$_FILES) && file_exists($_FILES['up_url']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_url']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['url'] = upload_file($this->_bibName,'up_url',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_url']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            if(array_key_exists('up_urlzip',$_FILES) && file_exists($_FILES['up_urlzip']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_urlzip']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['urlzip'] = upload_file($this->_bibName,'up_urlzip',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_urlzip']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            if(array_key_exists('up_pdf',$_FILES) && file_exists($_FILES['up_pdf']['tmp_name'])){
                $fileInfo = pathinfo($_FILES['up_pdf']['name']);
                if(in_array($fileInfo['extension'],$GLOBALS['valid_upload_extensions'])){
                    $dataArray['pdf'] = upload_file($this->_bibName,'up_pdf',$dataArray['id']);
                }
                else{
                    $res['message'] .= sprintf(msg("%s not uploaded: invalid file type."),$_FILES['up_pdf']['name']);
                    $res['message'] .= "<br/>";
                }
            }

            // add the new entry
            $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
            $bt = new BibTeX_Tools();

            // extract real bibtex values from the array
            $bibtex_val = $bt->extract_bibtex_data($dataArray,$this->_fields);

            // get first lastname author
            if(array_key_exists('author',$bibtex_val)){
                $pc = new PARSECREATORS();
                $authors = $pc->parse($bibtex_val['author']);
                $bibtex_val['lastName'] = $authors[0][2];
            }
            // set its type
            $bibtex_val['___type'] = $dataArray['add_type'];
            // date added
            $bibtex_val['dateAdded'] = date("Y-m-d");
            // date modified
            $bibtex_val['lastDateModified'] = date("Y-m-d");
            // convert to xml
            $data = $bt->entries_array_to_xml(array($bibtex_val));

            $xml = $data[2];
            $xsl = FileToolKit::getContent("./xsl/add_entries.xsl");
            $param = array('bibname' => $this->getXmlFileName(),
                           'biborb_xml_version' => BIBORB_XML_VERSION);

            $result = $xsltp->transform($xml,$xsl,$param);
            $xsltp->free();

            // save xml
            $fp = fopen($this->getXmlFileName(),"w");
            fwrite($fp,$result);
            fclose($fp);

            // update bibtex file
            if($this->genBibtex){$this->update_bibtex_file();}

            $res['added'] = true;
            $res['message'] .= "";
            $res['id'] = $dataArray['id'];
        }
        return $res;
    }

    /**
        Add entries. $bibtex is a bibtex string.
        Only entries which bibtex key is not present in the database are added.
        It returns an array:
            array( 'added' => array of successfuly added references
                   'notadded' => array of references notadded due to bibtex key conflicts
                )
    */
    function addBibtexEntries($iBibtex)
    {
        // the array to return
        $aRes = array('added' => array(),
                      'notadded' => array());
        //open the database file in append mode
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXsl = FileToolKit::getContent('./xsl/add_entries.xsl');
        $aParam = array('bibname' => $this->getXmlFileName(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);

        // entries to add
        $aEntriesToAdd = BibtexConverter::import($iBibtex);

        // bibtex key present in database
        $aDbIds = $this->getAllIds();

        // iterate and add ref which id is not present in the database
        foreach ($aEntriesToAdd as $aKey=>$aEntry)
        {
            if (array_search($aEntry->getId(),$aDbIds) === FALSE)
            {
                if ($aEntry->getData('author'))
                {
                    $aPC = new PARSECREATORS();
                    $authors = $aPC->parse($aEntry->getData('author'));
                    $aEntriesToAdd[$aKey]->setData('lastName',$authors[0][2]);
                }
                $aEntriesToAdd[$aKey]->setData('dateAdded',date('Y-m-d'));
                $aEntriesToAdd[$aKey]->setData('lastDateModified',date('Y-m-d'));
                $aRes['added'][] = $aEntry->getID();
            }
            else
            {
                $aRes['notadded'][] = $aEntry->getID();
            }
        }
        $aResult = $aXsltp->transform(XmlConverter::export($aEntriesToAdd),$aXsl,$aParam);
        FileToolKit::putContent($this->getXmlFileName(), $aResult);
        $aXsltp->free();


        // update bibtex file
        if($this->_genBibtex)
        {
            $this->updateBibtexFile();
        }

        return $aRes;
    }

    /**
     * Delete an entry from the database
     */
    function deleteEntry($iId)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aRefToDel = XmlConverter::import($this->getEntryWithId($iId));
        $aXml = $this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/delete_entries.xsl');
        $aParam = array('id' => $iId,
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $aXml = $aXsltp->transform($aXml,$aXsl,$aParam);

        // detect all file corresponding to this id.
        $aPapersDir = dir($this->getPapersDir());
        $aPaperTypes = array('url', 'urlzip', 'pdf');
        while (($aFile = $aPapersDir->read()) !== false)
        {
            foreach ($aPaperTypes as $aPaperType)
            {
                if ($aFile == $aRefToDel->getData($aPaperType))
                {
                    unlink($aPapersDir->path.'/'.$aFile);
                    $this->_papersCount--;
                }
            }
        }

        // update the xml file.
        FileToolKit::putContent($this->getXmlFileName(), $aXml);

        //update the bibtex file.
        if ($this->_genBibtex)
        {
            $this->updateBibtexFile();
        }
        $this->_entryCount--;
        $this->_browseCacheNeedUpdate = TRUE;
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
     * Update an entry.
     */
    function updateEntry($dataArray)
    {
        $aRes = array('updated'=>false,
                      'message'=>'');

        // check if the id value is null
        if ($dataArray['id'] == null)
        {
            $aRes['updated'] = false;
            $aRes['message'] = msg('Null BibTeX ID for an entry not allowed.');
        }
        else
        {
            // Load the reference            
            $aRef = new Reference($dataArray['id'], $dataArray['type_ref']);
            $aRef->setData($dataArray, $this->_fields);
            if ($aRef->getData('author'))
            {
                $pc = new PARSECREATORS();
                $authors  = $pc->parse($aRef->getData('author'));
                $aRef->setData('lastName',$authors[0][2]);
            }
            $aRef->setData('lastDateModified', date('Y-m-d'));

            // look for new uploaded papers
            $aUpTypes = array('up_url', 'up_urlzip', 'up_pdf');
            foreach ($aUpTypes as $aUpType)
            {
                $aFileType = substr($aUpType,3);
                if (array_key_exists($aUpType,$_FILES) && file_exists($_FILES[$aUpType]['tmp_name']))
                {
                    $aFileInfo = pathinfo($_FILES[$aUpType]['name']);
                    if (in_array($aFileInfo['extension'],$GLOBALS['valid_upload_extensions']))
                    {
                        $aDataArray[$aFileType] = upload_file($this->_bibName,$aUpType,$aDataArray['id']);
                        if (!$aRef->getValue($aFileType))
                            $this->_papersCount++;
                    }
                    else
                    {
                        $aRes['message'] .= sprintf(msg('%s not uploaded: invalid file type.'),$_FILES[$aUpType]['name']);
                        $aRes['message'] .= '<br/>';
                    }
                }
            }
            // Convert and write it to xml
            $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
            $aXsl = FileToolKit::getContent('./xsl/update_xml.xsl');
            $aParam = array('bibname' => $this->getXmlFileName(),
                            'biborb_xml_version' => BIBORB_XML_VERSION);
            $aResult = $aXsltp->transform(XmlConverter::export($aRef),$aXsl,$aParam);
            $aXsltp->free();

            FileToolKit::putContent($this->getXmlFileName(), $aResult);
            // update bibtex file
            if($this->_genBibtex)
            {
                $this->updateBibtexFile();
            }

            $aRes['updated'] = true;
            $aRes['message'] .= '';
            $aRes['id'] = $aRef->getId();
            $this->_browseCacheNeedUpdate = TRUE;
        }
        return $aRes;
    }

    /**
        Test if a key is already present in the database
    */
    function is_bibtex_key_present($bibtex_key){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        $content = FileToolKit::getContent($this->getXmlFileName());
        $xsl = FileToolKit::getContent("./xsl/search_entry.xsl");
        $param = array('id' => $bibtex_key);
        $result = $xsltp->transform($content,$xsl,$param);
        return (substr_count($result,"true") > 0);
    }

    /**
     * Return an array containing groups present in the bibliography.
     */
    function getGroups()
    {
        $aXsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        // Get groups from the xml bibtex file
        $aXml = FileToolKit::getContent($this->getXmlFileName());
        $aXsl = FileToolKit::getContent("./xsl/group_list.xsl");
        $aGroups = $aXsltp->transform($aXml,$aXsl);
        $aXsltp->free();

        // Remove doublons
        $aGroups = split("[,~]", $aGroups);
        foreach ($aGroups as $aKey=>$aValue)
        {
            $aValue = trim($aValue);
            if ( $aValue == "")
            {
                unset($aGroups[$aKey]);
            }
            else
            {
                $aGroups[$aKey] = $aValue;
            }
        }
        $aGroups = array_unique($aGroups);
        sort($aGroups);
        return $aGroups;
    }

    /**
        Add a set of entries to a group
    */
    function add_to_group($idArray,$group){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        // create an xml string containing id present in the basket
        $xml_content = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml_content .= "<listofids>";
        foreach($idArray as $item){
            $xml_content .= "<id>$item</id>";
        }
        $xml_content .= "</listofids>";
        $xsl_content = FileToolKit::getContent("./xsl/addgroup.xsl");

        $param = array( 'bibname' => $this->getXmlFileName(),
                        'group' => $group,
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        // new xml file
        $result = $xsltp->transform($xml_content,$xsl_content,$param);

        // update the xml file
        $xsl_content = FileToolKit::getContent("./xsl/update_xml.xsl");
        $result = $xsltp->transform($result,$xsl_content,$param);
        $xsltp->free();

        $fp = fopen($this->getXmlFileName(),"w");
        fwrite($fp,$result);
        fclose($fp);
    }

    /**
        Reset groups of a set of entries
    */
    function reset_groups($idArray){
        $xsltp = new XSLT_Processor("file://".BIBORB_PATH,"UTF-8");
        // create an xml string containing id present in the basket
        $xml_content = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml_content .= "<listofids>";
        foreach($idArray as $item){
            $xml_content .= "<id>$item</id>";
        }
        $xml_content .= "</listofids>";
        $xsl_content = FileToolKit::getContent("./xsl/resetgroup.xsl");
        $param = array( 'bibname' => $this->getXmlFileName(),
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        $result = $xsltp->transform($xml_content,$xsl_content,$param);

        // update the xml file
        $xsl_content = FileToolKit::getContent("./xsl/update_xml.xsl");
        $result = $xsltp->transform($result,$xsl_content,$param);
        $xsltp->free();

        $fp = fopen($this->getXmlFileName(),"w");
        fwrite($fp,$result);
        fclose($fp);
    }

    /**
     Search in given fields, a given value
    */
    function searchEntries($iValue,$iFields)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml =$this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/search.xsl');
        $aParam = array( 'bibname' => $this->getXmlFileName());
        foreach($iFields as $aVal)
        {
            $aParam[$aVal] = '1';
        }
        $aParam['search'] = $iValue;
        $aResult = $aXsltp->transform($aXml,$aXsl,$aParam);
        $aXsltp->free();
        return $aResult;
    }

    function getIdsForSearch($iValue, $iFields)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->searchEntries($iValue,$iFields);
        $aXsl = FileToolKit::getContent('./xsl/extract_ids.xsl');
        $aXsl = str_replace('XPATH_QUERY','//bibtex:entry',$aXsl);
        $aParam = array('sort' => $this->_sortMethod,
                        'sort_order' => $this->_sortOrder);
        $aRes =  $aXsltp->transform($aXml,$aXsl,$aParam);
        $aXsltp->free();
        return remove_null_values(explode('|',$aRes));
    }

    /**
        Advanced search function
    */
    function advancedSearchEntries($iSearchArray)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml =$this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/advanced_search.xsl');
        $aParam = array( 'bibname' => $this->getXmlFileName());
        foreach ($iSearchArray as $aKey => $aVal)
        {
            $aParam[$aKey] = $aVal;
        }
        $aResult = $aXsltp->transform($aXml,$aXsl,$aParam);
        $aXsltp->free();
        return $aResult;
    }

    function getIdsForAdvancedSearch($iSearchArray)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->advancedSearchEntries($iSearchArray);
        $aXsl = FileToolKit::getContent("./xsl/extract_ids.xsl");
        $aXsl = str_replace('XPATH_QUERY','//bibtex:entry',$aXsl);
        $aParam = array('sort' => $this->_sortMethod,
                        'sort_order' => $this->_sortOrder);
        $aRes =  $aXsltp->transform($aXml,$aXsl,$aParam);
        $aXsltp->free();
        return remove_null_values(explode('|',$aRes));
    }

    /**
        XPath search
     */
    function xpathSearch($iXPath)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/xpath_query.xsl');
        $aXsl = str_replace('XPATH_QUERY',$iXPath,$aXsl);
        $aParam = array( 'bibname' => $this->getXmlFileName());
        $aResult = $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        return $aResult;
    }

    function getIdsForXpathSearch($iXPath)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->getAllEntries();
        $aXsl = FileToolKit::getContent('./xsl/extract_ids.xsl');
        $aXsl = str_replace('XPATH_QUERY',"//bibtex:entry[{$iXPath}]",$aXsl);
        $aParam = array('sort' => $this->_sortMethod,
                        'sort_order' => $this->_sortOrder);
        $aRes = $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        return remove_null_values(explode('|',$aRes));
    }

    /**
        Total number of entries
    */
    function getEntryCount()
    {
        if (!isset($this->_updated))
        {
            $allentries = $this->getAllEntries();
            $this->_entryCount = substr_count($allentries,"<bibtex:entry ");
        }
        return $this->_entryCount;
    }

    /**
        Count on-line available papers.
    */
    function getPapersCount()
    {
        if (!isset($this->_papersCount))
        {

            $allentries = $this->getAllentries();
            $pdf = substr_count($allentries,"<bibtex:pdf>");
            $urlzip = substr_count($allentries,"<bibtex:urlzip>");
            $url = substr_count($allentries,"<bibtex:url>");

            $this->_papersCount = $url+$urlzip+$pdf;
        }

        return $this->_papersCount;
    }

    /**
     * Change the type of a given entry
     */
    function changeType($iId,$iNewType)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        // get the entry
        $aXml = $this->getEntryWithId($iId);
        $aOldType = $aXsltp->transform($aXml, FileToolKit::getContent('./xsl/get_bibtex_type.xsl'));
        $aXml = str_replace('bibtex:'.$aOldType,'bibtex:'.$iNewType, $aXml);
        // update the xml
        $aXsl = FileToolKit::getContent('./xsl/update_xml.xsl');
        $aParam = array('bibname' => $this->getXmlFileName(),
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        $aResult = $aXsltp->transform($aXml,$aXsl,$aParam);
        $aXsltp->free();
        // save it
        FileToolKit::putContent($this->getXmlFileName(),$aResult);
        if ($this->_genBibtex)
        {
            $this->updateBibtexFile();
        }
    }

    /**
     * Change the bibtex key
     */
    function changeId($iId, $iNewId)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        // get the entry
        $aXml = $this->getEntryWithId($iId);

        $aPath = $this->getPapersDir();
        $aPaperTypes = array('url', 'urlzip', 'pdf');
        foreach ($aPaperTypes as $aPaperType)
        {
            // change the name of recorded files if necessary
            preg_match("/\<bibtex:{$aPaperType}\>(.*)\<\/bibtex:{$aPaperType}\>/",$aXml,$aMatches);
            if (count($aMatches)>0)
            {
                $aOldName = $aMatches[1];
                $aNewName = str_replace($iId, $iNewId, $aOldName);
                if (file_exists($aNewName))
                {
                    rename($aPath.'/'.$aOldName, $aPath.'/'.$aNewName);
                }
                $aXml = str_replace("<bibtex:{$aPaperType}>{$aOldName}</bibtex:{$aPaperType}>",
                                    "<bibtex:{$aPaperType}>{$aNewName}</bibtex:{$aPaperType}>",
                                    $aXml);
            }
        }
        // update the xml
        $aXsl = FileToolKit::getContent('./xsl/update_xml.xsl');
        $aParam = array('bibname' => $this->getXmlFileName(),
                        'biborb_xml_version' => BIBORB_XML_VERSION);
        $aResult = $aXsltp->transform($aXml,$aXsl,$aParam);
        $aXsltp->free();
        // replace by the new id in the xml file
        $aResult = str_replace("id=\"{$iId}\"","id=\"{$iNewId}\"",$aResult);
        // save it
        FileToolKit::putContent($this->getXmlFileName(), $aResult);
        // update bibtex file
        if ($this->_genBibtex)
        {
            $this->updateBibtexFile();
        }
    }


    /**
     * Change the ownership of a given entry
     * Shelf mode
     */
    function changeOwnership($iId, $iNewOwnership)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
                                          // get the entry
        $aXml = $this->getEntryWithId($iId);
        //$oldownership = trim($xsltp->transform($xml_content,FileToolKit::getFileContents("./xsl/get_bibtex_ownership.xsl")));
        // replace it
        if (strpos($aXml, '<bibtex:own>') === false)
        {
            $aXml = str_replace('</bibtex:title>',
                                '</bibtex:title><bibtex:own>'.$iNewOwnership.'</bibtex:own>',
                                $aXml);
        }
        else
        {
            $aXml = preg_replace('/\<bibtex\:own>.*\<\/bibtex\:own\>/',
                                 '<bibtex:own>'.$iNewOwnership.'</bibtex:own>',
                                 $aXml);
        }
        // update the xml
        $aXsl = FileToolKit::getContent('./xsl/update_xml.xsl');
        $aParam = array('bibname' => $this->getXmlFileName(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $aResult = $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        // save it
        FileToolKit::putContent($this->getXmlFileName(), $aResult);
        // update bibtex file
        if ($this->_genBibtex)
        {
            $this->updateBibtexFile();
        }
    }

    /**
     * Change the read status of a given entry
     * Shelf mode
     */
    function changeReadStatus($iId,$iNewReadStatus){
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
                                          // get the entry
        $aXml = $this->getEntryWithId($iId);
        // replace it
        if (strpos($aXml, '<bibtex:read>') === false)
        {
            $aXml = str_replace('</bibtex:title>',
                                '</bibtex:title><bibtex:read>'.$iNewReadStatus.'</bibtex:read>',
                                $aXml);
        }
        else
        {
            $aXml = preg_replace('/\<bibtex\:read>.*\<\/bibtex\:read\>/',
                                 '<bibtex:read>'.$iNewReadStatus.'</bibtex:read>',
                                 $aXml);
        }
        // update the xml
        $aXsl = FileToolKit::getContent('./xsl/update_xml.xsl');
        $aParam = array('bibname' => $this->getXmlFileName(),
                       'biborb_xml_version' => BIBORB_XML_VERSION);
        $aResult = $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        // save it
        FileToolKit::putContent($this->getXmlFileName(), $aResult);
        // update bibtex file
        if ($this->_genBibtex)
        {
            $this->updateBibtexFile();
        }
    }

    /**
     * Get all different values for a specific field in the database
     */
    function getAllValuesFor($iField)
    {
        if ($this->_browseCacheNeedUpdate)
        {
            myUnset($this->_browseCache);
            $this->_browseCacheNeedUpdate = FALSE;
        }
   
        if ($iField == 'author')
        {
            if (!isset($this->_browseCache['author']))
            {
                $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
                $aXml = $this->getAllEntries();
                $aXsl = FileToolKit::getContent('./xsl/extract_field_values.xsl');
                $aParam = array('field' => 'author');
                $aRes = $aXsltp->transform($aXml,$aXsl,$aParam);
                $aAuthors = remove_null_values(explode('|',$aRes));
                $aXsltp->free();
                $aPC = new PARSECREATORS();
                $this->_browseCache['author'] = array();
                foreach ($aAuthors as $aAuthor)
                {
                    $aCreators = $aPC->parse($aAuthor);
                    foreach ($aCreators as $aCreator)
                    {
                        $aC = trim($aCreator[2]);
                        if (!in_array($aC, $this->_browseCache['author']))
                        {
                            $this->_browseCache['author'][] = $aC;
                        }
                    }
                }
                sort($this->_browseCache['author']);
            }
            
            return $this->_browseCache['author'];
        }
        else
        {            
            if (!isset($this->_browseCache[$iField]))
            {
                $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
                $aXml = $this->getAllEntries();
                $aXsl = FileToolKit::getContent('./xsl/extract_field_values.xsl');
                $aParam = array('sort' => $this->_sortMethod,
                                'sort_order' => $this->_sortOrder,
                                'field' => $iField);
                $aRes = $aXsltp->transform($aXml,$aXsl,$aParam);
                $aXsltp->free();
                $this->_browseCache[$iField] = array_values(remove_null_values(explode('|',$aRes)));
                natcasesort($this->_browseCache[$iField]);
            }
            
            return $this->_browseCache[$iField];
        }
    }

    /**
     * Select among ids, entries that match $fied=$value
     */
    function filter($iIds, $iField, $iValue)
    {
        $aXsltp = new XSLT_Processor('file://'.BIBORB_PATH,'UTF-8');
        $aXml = $this->getEntriesWithIds($iIds);
        if ($iField == 'author')
        {
            $aXpath = 'contains(translate(.//bibtex:'.$iField.",\$ucletters,\$lcletters),translate('".$iValue."',\$ucletters,\$lcletters))";
        }
        else
        {
            $aXpath = './/bibtex:'.$iField."='".$iValue."'";
        }
        $aXsl = FileToolKit::getContent('./xsl/extract_ids.xsl');
        $aXsl = str_replace('XPATH_QUERY',"//bibtex:entry[$aXpath]",$aXsl);
        $aParam = array('sort' => $this->_sortMethod,
                       'sort_order' => $this->_sortOrder);
        $aRes = $aXsltp->transform($aXml, $aXsl, $aParam);
        $aXsltp->free();
        return remove_null_values(explode('|',$aRes));
    }

}
 /**
 * Extract ids from the xml
 */
function extract_ids_from_xml($xmlstring) {
    preg_match_all("/id=['|\"](.*)['|\"]/U",$xmlstring,$matches);
    return array_unique($matches[1]);
}
?>
