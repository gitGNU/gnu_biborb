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
        

        <div class="sort">
            <xsl:if test="$display_sort = 'true'">
            Sort by:
                <form method="get" action="bibindex.php">
                    <fieldset>
                        <select name='sort' size='1'>
                            <xsl:element name="option">
                                <xsl:attribute name='value'>ID</xsl:attribute>
                                <xsl:if test="$sort = 'ID'">
                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                </xsl:if>
                                ID
                            </xsl:element>
                            <xsl:element name="option">
                                <xsl:attribute name='value'>title</xsl:attribute>
                                <xsl:if test="$sort = 'title'">
                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                </xsl:if>
                                Title
                            </xsl:element>
                            <xsl:element name="option">
                                <xsl:attribute name='value'>year</xsl:attribute>
                                <xsl:if test="$sort = 'year'">
                                    <xsl:attribute name='selected'>selected</xsl:attribute>
                                </xsl:if>
                                Year
                            </xsl:element>
                        </select>
                        <input type='hidden' name='mode' value='{$bibindex_mode}'/>
                        <xsl:if test="$group">
                            <input type='hidden' name='group' value='{$group}'/>
                        </xsl:if>
                        <input type='submit' value='sort'/>
                    </fieldset>
                </form>
            </xsl:if>
            <xsl:if test="$cpt != 0 and $display_basket_actions != 'no' and $display_add_all != 'no'">
            <div class="addtobasket">
                Add all entries to basket <a href="bibindex.php?mode={$bibindex_mode}&amp;action=add_to_basket&amp;id={$ids}&amp;{$extra_get_param}"><img src="./data/images/add.png" alt="add" /></a>
            </div>
		</xsl:if>
        </div>
		<br/>
		
        <!-- start the table -->
        <table id="bibtex_table">
            <tbody>
                <xsl:choose>
                    <xsl:when test="$sort = 'year'">
                        <!-- sort entries by increasing year -->
                        <xsl:apply-templates select="//bibtex:entry">
                            <xsl:sort select=".//bibtex:year" order="descending" data-type="text"/>
                        </xsl:apply-templates>
                    </xsl:when>
                    <xsl:when test="$sort = 'title'">
                        <!-- sort entries by increasing title -->
                        <xsl:apply-templates select="//bibtex:entry">
                            <xsl:sort select=".//bibtex:title" order="ascending" data-type="text"/>
                        </xsl:apply-templates>
                    </xsl:when>
                    <xsl:when test="$sort = 'ID'">
                        <!-- sort entries by increasing id -->
                        <xsl:apply-templates select="//bibtex:entry">
                            <xsl:sort select="@id" order="ascending" data-type="text"/>
                        </xsl:apply-templates>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:apply-templates select='//bibtex:entry'/>
                    </xsl:otherwise>
                </xsl:choose>
            </tbody>
        </table>
    </xsl:template>
    
    <!-- include XSLT stylesheet -->
    <xsl:include href="xsl/biborb_output_model_for_table.xsl"/>
    
</xsl:stylesheet>