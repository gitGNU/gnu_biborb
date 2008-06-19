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
 * File: Reference.php
 *
 * Description:
 *
 */

/**
 *
 */
class Reference
{
    // an array containing data
    var $_data;
    //
    var $_type;
    var $_id;

    /**
     *
     */
    function Reference($iId, $iType, $iData = null)
    {
        $this->_id = $iId;
        $this->_type = $iType;
        $this->_data = isset($iData) ? $iData : array();
    }

    function getData($iKey)
    {
        if (isset($this->_data[$iKey]))
            return $this->_data[$iKey];
        else
            return null;
    }

    /**
     *
     *
     */
    function setData($iMixed, $iValue)
    {
        /*
            If $iMixed is an array, all data are purged and replaces by
        all values which keys is in $iValue.
        */
        if (is_array($iMixed))
        {
            myUnset($this->_data);
            foreach ($iMixed as $aKey => $aValue)
            {
                $aVal = trim($aValue);
                if (in_array($aKey,$iValue) && $aVal != '')
                {
                    $this->_data[$aKey] = $aValue;
                }
            }
        }
        else
        {
            $this->_data[$iMixed] = $iValue;
        }
    }

    function setType($iType)
    {
        $this->_type = $iType;
    }

    function getType()
    {
        return $this->_type;
    }


    function getId()
    {
        return $this->_id;
    }


    function setId($iId)
    {
        $this->_id = $iId;
    }

    function getDataKeys()
    {
        return array_keys($this->_data);
    }


    /* static */ function getFieldsForType($iType)
    {
        $fieldsArray = array('required'   => array(),
                             'optional'   => array(),
                             'additional' =>array());
        
        $fields = str_replace("\n",'',file_get_contents('./xsl/model.xml'));

        if (preg_match("/<entry type=\"$iType\">(.*)<\/entry>/U",$fields,$matches))
        {
            foreach ($fieldsArray as $type => $value)
            {                
                if (preg_match("/<$type>(.*)<\/$type>/U",$matches[1],$required))
                {
                    if (preg_match_all("/<(.*)\/>/U",$required[1],$tags))
                    {                    
                        foreach($tags[1] as $field)
                        {
                            $fieldsArray[$type][] = $field;
                        }
                    }
                }
            }
        }
        return $fieldsArray;        
    }
    
}






?>
