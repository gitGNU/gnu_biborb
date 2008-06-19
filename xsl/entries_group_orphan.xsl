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
 * File: entries_group_orhpan.xsl
 *
 * Description:
 *
 *  Get entries that are associated with no group.
 *
 *
-->

<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">
  
    <xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="no"/>
	
	<!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>
	
	<xsl:template match="/">
		<xsl:element name="bibtex:file">
			<xsl:copy>
				<xsl:apply-templates select="//bibtex:entry[not(.//bibtex:group)]"/>
			</xsl:copy>
		</xsl:element>
	</xsl:template>
	
	<!-- Hand copy to be sure to copy namespaces -->
	<xsl:template match="@*">
        <xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute>
    </xsl:template>
    
    <xsl:template match="*">
        <xsl:element name="bibtex:{local-name()}">
            <xsl:apply-templates select=" @* | node()"/>
        </xsl:element>
    </xsl:template>
	
</xsl:stylesheet>