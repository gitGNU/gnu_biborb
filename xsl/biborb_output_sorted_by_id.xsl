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
 * File: biborb_output_sorted_by_id.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *    Sort all entries by id and transform it into an HTML table.
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
  
    <xsl:output method="xml" encoding="iso-8859-1" />
    
	<!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>

    <xsl:template match="/">
		<xsl:variable name="cpt" select="count(//bibtex:entry)"/>
		<!-- add a link to add all entries -->
        <xsl:variable name="ids">
            <xsl:for-each select="//bibtex:entry">
                <xsl:value-of select="@id"/>
                <xsl:if test="position() != last()">*</xsl:if>
            </xsl:for-each>
        </xsl:variable>
		
		<xsl:if test="$cpt != 0 and $display_basket_actions != 'no' and $display_add_all != 'no'">
            <div class="addtobasket">
                Add all entries to basket <a href="bibindex.php?mode={$bibindex_mode}&amp;action=add_to_basket&amp;id={$ids}&amp;{$extra_get_param}"><img src="./data/images/add.png" alt="add" /></a>
            </div>
		</xsl:if>
		
        <!-- start the table -->
        <table id="bibtex_table">
            <tbody>
                <xsl:for-each select="//bibtex:entry">
                    <!-- sort entries by increasing id -->
                    <xsl:sort select="@id" order="ascending" data-type="text"/>
                    <xsl:apply-templates select="."/>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
    
    <!-- include XSLT stylesheet -->
    <xsl:include href="xsl/biborb_output_model_for_table.xsl"/>
    
</xsl:stylesheet>