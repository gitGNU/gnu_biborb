<?xml version="1.0" encoding="iso-8859-1" ?>
<!--
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
-->
<!--
 * File: parameters.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    Contains XSL generic parameters.
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
  

    <!-- The name of the bibliography                 -->
    <xsl:param name="bibname"/>
	
	<!-- The url of the bibliography				  -->
	<xsl:param name="bibnameurl"/>
	
	<!-- The list of bibtex ids present in the basket -->
	<xsl:param name="basketids"/>
	
	<!-- Use images for action links TRUE/FALSE		  -->
	<xsl:param name="display_images"/>

	<!-- Use text for action links TRUE/FALSE		  -->
    <xsl:param name="display_text"/>
	
	<!-- Abstract always visible NO					  -->
	<xsl:param name="abstract"/>
	
	<!-- Display the basket actions					  -->
	<xsl:param name="display_basket_actions"/>
	
	<!-- Display the "add all to basket" button  	  -->
	<xsl:param name="display_add_all"/>
	
	<!-- A group value								  -->
	<xsl:param name="group"/>
	
	<!-- A search value								  -->
	<xsl:param name="search"/>
	
	<!-- An author value							  -->
	<xsl:param name="author"/>
	
	<!-- A title value								  -->
    <xsl:param name="title"/>
	
	<!-- A Keywords value							  -->
    <xsl:param name="keywords"/>
	
	<!-- journal									  -->
	<xsl:param name="journal"/>
	
	<!-- editor										  -->
	<xsl:param name="editor"/>
	
	<!-- year										  -->
	<xsl:param name="year"/>
	
	<!-- A bibtex id								  -->
	<xsl:param name="id"/>
	
	<!-- Some bibtex ids (XML formated)				  -->
	<xsl:param name="ids"/>
	
	<!-- Extra get parameters to add in a link		  -->
	<xsl:param name="extra_get_param"/>
	
    <!-- admin mode -->
    <!-- if mode='admin', display edit and delete button -->
    <xsl:param name="mode"/>

	<!-- Which page to generate (welcome, groups...)  -->
	<xsl:param name="bibindex_mode"/>
    
    <!-- Sort -->
    <xsl:param name="sort"/>
    <xsl:param name="display_sort"/>
    
    <!-- Basket action images -->
    <xsl:variable name="add-basket-image">cvs-add-16.png</xsl:variable>
    <xsl:variable name="remove-basket-image">cvs-remove-16.png</xsl:variable>
	
</xsl:stylesheet>