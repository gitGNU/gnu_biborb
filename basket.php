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
 * File: basket.php
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 * 
 * Description:
 *
 *      Basket functions 
 * 
 */

/********************************************************************************/
/*                                                                              */
/* Functions concerning the BibTeX basket.                                      */
/* The basket is recorded in a SESSION variable: 'basket'                       */
/*                                                                              */
/********************************************************************************/

class Basket {
	var $items;
	
	function Basket() {
		$this->items = array();
	}
	
	function count_items(){
		return count($this->items);
	}
	
	function add_item($item) {
		if(!in_array($item,$this->items)){
			array_push($this->items,$item);
		}
	}
	
	function add_items($array){
		foreach($array as $item){
			$this->add_item($item);
		}
	}
	
	function remove_item($item) {
		$key = array_search($item,$this->items);
		if(!($key === FALSE)){
			for($i=$key;$i<count($this->items)-1;$i++){
				$this->items[$i] = $this->items[$i+1];
			}
		}
		array_pop($this->items);
	}
	
	function reset(){
		$length = count($this->items);
		for($i=0;$i<$length;$i++){
			array_pop($this->items);
		}
	}
	
	function items_to_string(){
		$res = ".";
		foreach($this->items as $item){
			$res .= $item.".";
		}
		return $res;
	}
}

?>