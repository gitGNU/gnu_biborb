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
 * File: xslt_processor.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *
 *      A simple class to encapsulate XSLT method.
 *    
 */

class XSLT_Processor {
	
	var $output;
	var $xsltproc;
	var $xsl_parameters;
	var $xml_string;
	var $xsl_string;
	
	function XSLT_Processor($base,$encoding){
		$this->xsltproc = xslt_create();
		xslt_set_base($this->xsltproc,$base);
		xslt_set_encoding($this->xsltproc,$encoding);
		$this->xsl_parameters = null;
	}
	
	function free(){
		xslt_free($this->xsltproc);
	}
	
	function set_xslt_encoding($encoding){
		xslt_set_encoding($this->xsltproc,$encoding);
	}
	
	function set_xslt_base($base){
		xslt_set_base($this->xsltproc,$base);
	}
	
	function set_xslt_parameters($param){
		if(is_array($param)){
			$this->parameters = $param;
		}
	}
	
	function transform($xmlstring,$xslstring,$xslparam=array()){
		$arguments = array('/_xml' => $xmlstring,
						   '/_xsl' => $xslstring);
		$result = xslt_process($this->xsltproc,'arg:/_xml','arg:/_xsl',NULL,$arguments,$xslparam);
		if(!$result && xslt_errno($this->xsltproc)>0){
			die(sprintf("Cannot process XSLT document [%d]: %s", xslt_errno($this->xsltproc), xslt_error($this->xsltproc)));
		}
		
		return $result;
	}	
}


?>