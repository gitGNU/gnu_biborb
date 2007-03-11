<?php
/**
 *
 * This file is part of BibORB
 *
 * Copyright (C) 2007  Guillaume Gardey
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
 * File: Reference.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
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


}






?>
