<?xml version="1.0" encoding="iso-8859-1" ?>
<!--
 * This file is part of BibORB
 * 
 * Copyright (C) 2003  Guillaume Gardey
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
 * File: basket2html_table.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 *
 * Description:
 *
 *    Display entries given in the XML input file
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
  
    <xsl:output method="xml" encoding="iso-8859-1"/>
    
    <!-- the name of the bibliography -->
    <xsl:param name="bibname"/>
    <xsl:param name="bibnameurl"/>
    <!-- session name and id -->
    <xsl:param name="session_name"/>
    <xsl:param name="session_id"/>
    <xsl:param name="mode"/>
    <xsl:param name="basket"/>
    <xsl:param name="abstract"/>
    <xsl:param name="display_images"/>
    <xsl:param name="display_text"/>

    <xsl:template match="/entrylist">
        <!-- load the xml file into a variable -->
        <xsl:variable name="bibfile" select="document($bibnameurl)" />
        <!-- get all ids of entries to display -->
        <xsl:variable name="ids" select="//id"/>
        <!-- get entries to display -->
        <xsl:variable name="result" select="$bibfile//bibtex:entry[@id=$ids]"/>
        
        <!-- results -->
        <xsl:variable name="cpt" select="count($result)"/>
        <div class="result">
            <xsl:choose>
                <xsl:when test="$cpt = 0">
                    Basket empty.
                </xsl:when>
                <xsl:when test="$cpt = 1">
                    1 entry in the basket.
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$cpt"/> entries in the basket.
                </xsl:otherwise>
            </xsl:choose>
        </div><br/>
        <!-- display all entries in a table -->
        <table id="bibtex_table">
            <tbody>
                <xsl:apply-templates select="$result"/>
            </tbody>
        </table>
    </xsl:template>
    
    <!-- include XSLT stylesheet -->
    <xsl:include href="xsl/bibtex_entry2tr.xsl"/>
    
</xsl:stylesheet>