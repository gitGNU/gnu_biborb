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
 * File: all_sorted_by_id2html_table.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
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
  
    <xsl:output method="xml" encoding="iso-8859-1" omit-xml-declaration="yes"/>
    
    <!-- the name of the bibliography -->
    <xsl:param name="bibname"/>
    <!-- session name and id -->
    <xsl:param name="session_name"/>
    <xsl:param name="session_id"/>
    <!-- admin mode -->
    <!-- if mode='admin', display edit and delete button -->
    <xsl:param name="mode"/>
    <xsl:param name="basket"/>
    <xsl:param name="abstract"/>

    <xsl:template match="/">
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
    <xsl:include href="./xsl/bibtex_entry2tr.xsl"/>
    
</xsl:stylesheet>