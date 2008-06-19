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
 * File: search_entry.xsl
 *
 * Description:
 *
 *    Return true if the entry id is present in the bibliography
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">
  
    <xsl:output method="txt"/>
    
    <!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>

    <xsl:template match="bibtex:file">
		<xsl:choose>
			<xsl:when test="//bibtex:entry[@id=$id]">true</xsl:when>
			<xsl:otherwise>false</xsl:otherwise>
		</xsl:choose>
    </xsl:template>
    
</xsl:stylesheet>