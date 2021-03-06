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
 * 
 * File: add_entries.xsl
 * 
 * Description:
 * 
 *      add en entry to the bibliography.
 * 
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">
  
    <xsl:output method="xml" encoding="UTF-8"/>
    
    <xsl:param name="bibname"/>
    <xsl:param name="biborb_xml_version"/>
    
    <xsl:template match="/">
        <!-- the database content -->
        <xsl:variable name="bibfile" select="document($bibname)"/>
        <!-- new ids to add -->
        <xsl:variable name="list_id" select="//id"/>
        <!-- look for all id in the xml file and output the corresponding bibtex entry -->
        <xsl:element name="bibtex:file">
            <xsl:attribute name="name"><xsl:value-of select="$bibfile/bibtex:file/@name"/></xsl:attribute>
            <xsl:attribute name="version"><xsl:value-of select='$biborb_xml_version'/></xsl:attribute>
            <xsl:copy>
                <!-- copy the xml database -->
                <xsl:apply-templates select="$bibfile//bibtex:entry" />
                <!-- copy the new entry if not already present in the database -->
                <xsl:apply-templates select="//bibtex:entry[not(@id=$bibfile//@id)]"/>
            </xsl:copy>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="@*">
        <xsl:attribute name="{name()}"><xsl:value-of select="."/></xsl:attribute>
    </xsl:template>
    
    <xsl:template match="*">
        <xsl:element name="bibtex:{local-name()}">
            <xsl:apply-templates select=" @* | node()"/>
        </xsl:element>
    </xsl:template>
    
</xsl:stylesheet>