<?php
/**
 * This file is part of BibORB
 *
 * Copyright (C) 2005-2007  Guillaume Gardey (ggardey@club-internet.fr)
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

/*
    File: htmltoolkit.php
    Author: Guillaume Gardey (ggardey@club-internet.fr)
    Licence: GPL
 */



/**
 * Tool kit class for files management
 *
 */
class FileToolKit
{
	/**
	 * Return the content of a file in a string. An error is thrown if an error
	 * occured while loading the file.
	 *
	 * @param $iFilePath A file to load.
	 */
	function getContent($iFilePath)
	{
		$aRes = file_get_contents($iFilePath);
		if ($aRes === FALSE)
		{
			trigger_error(msg("ERROR_GET_FILE"), E_USER_ERROR);
		}

		return $aRes;
	}

    /**
     * Return all extensions of a file.
     * getAllExtension('qsdf.ps.gz') will return ps.gz
     */
    function getAllExt($iString)
    {
        return substr($iString,strpos($iString,'.'));
    }
    
    
}

?>
