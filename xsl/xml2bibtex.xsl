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
 * File: xml2bibtex.xsl
 *
 * Description:
 *
 *   Transform the XML bibentry in a true bib entry.
 *
-->
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"> 

    <xsl:output method="text" encoding="UTF-8" indent="yes"/>
  
    <!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>
    <xsl:preserve-space elements="bibtex:*"/>

    <xsl:template match="/bibtex:file">
        <xsl:choose>
            <xsl:when test="$id != ''">
                <xsl:apply-templates select="//bibtex:entry[@id=$id]/bibtex:*"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select=".//bibtex:entry/bibtex:*"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="bibtex:*">@<xsl:value-of select="local-name()"/>{<xsl:value-of select="../@id"/>,
<xsl:for-each select="*">
            <xsl:choose><xsl:when test="local-name() = 'groups'">groups = {<xsl:for-each select="bibtex:group"><xsl:value-of select="."/><xsl:if test="position() != last()">,</xsl:if>
                    </xsl:for-each>}</xsl:when><xsl:otherwise><xsl:value-of select="local-name()"/> = {<xsl:value-of select="node()"/>}</xsl:otherwise></xsl:choose><xsl:if test="position() != last()">,
</xsl:if>
        </xsl:for-each>
}
    </xsl:template>
</xsl:stylesheet>