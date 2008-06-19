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
 * File: basket.php
 *
 * Description:
 *      This file defines a Basket class. The basket stores distinct items. 
 */


/**
 * A basket to store distinct items.
 * 
 * @author G. Gardey
 */
class Basket
{
    // an array of distinct items
	var $items;
	
    /**
     * Constructor.
     */
	function Basket() {
		$this->items = array();
	}

    /**
     * Number of items in the basket.
     */
	function count_items(){
		return count($this->items);
	}

    /**
     * Add an item.
     * If the item is already present, it is not added.
     */
	function add_item($item) {
		if(!in_array($item,$this->items) && $item != ''){
			array_push($this->items,$item);
		}
	}

    /**
     * Add a set of items.
     */
	function add_items($array){
		foreach($array as $item){
			$this->add_item($item);
		}
	}

    /**
     * Remove an item.
     */
	function remove_item($item) {
		$key = array_search($item,$this->items);
        if($key !== FALSE){
            unset($this->items[$key]);
            $this->items = array_values($this->items);
        }
	}
	
    /**
     * Remove all items.
     */
	function reset(){
        $this->items = array();
	}
	
    /**
     * Retun a string representing the list of items separated by a dot.
     */
	function items_to_string(){
		$res = ".";
		foreach($this->items as $item){
			$res .= $item.".";
		}
		return $res;
	}
}

?>