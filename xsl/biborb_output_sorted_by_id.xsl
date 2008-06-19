<?xml version="1.0" encoding="UTF-8" ?>
<!--
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
-->
<!--
 * File: biborb_output_sorted_by_id.xsl
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
  
    <xsl:output method="xml" encoding="UTF-8" />
    
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
        <br/>

        <!-- start the table -->
        <table id="bibtex_table">
            <tbody>
                <xsl:apply-templates select='//bibtex:entry'/>
            </tbody>
        </table>
    </xsl:template>
    
    <!-- include XSLT stylesheet -->
    <xsl:include href="xsl/biborb_output_model_for_table.xsl"/>
    
</xsl:stylesheet>