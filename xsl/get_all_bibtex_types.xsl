<?xml version="1.0" encoding="UTF-8"?>
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
 * File: get_all_bibtex_types.xsl
 *
 * Description:
 *
 *    Get the bibtex type
 *
-->
<xsl:stylesheet 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    version="1.0">

    <xsl:output method="text" encoding="UTF-8"/>
    
    <xsl:template match="/">
        <xsl:for-each select="//entry">
            <xsl:value-of select="@type"/>
            <xsl:text> </xsl:text>
        </xsl:for-each>
    </xsl:template>
    
</xsl:stylesheet>