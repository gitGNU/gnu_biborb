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
 * File: search2html_table.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 *
 * Description:
 *
 *    search and display result
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0"> 
   
    <xsl:output method="html" encoding="iso-8859-1"/>
    
    <!-- Possible input parameters -->
    <xsl:param name="bibname"/>
    <xsl:param name="group"/>
    <xsl:param name="author"/>
    <xsl:param name="title"/>
    <xsl:param name="keywords"/>
    <xsl:param name="mode"/>
    <xsl:param name="basket"/>
    <xsl:param name="abstract"/>
    <xsl:param name="session_id"/>
    <xsl:param name="session_name"/>

    <!-- every parameter values to lower case -->
    <xsl:variable name="lcletters">abcdefghijklmnopqrstuvwxyz</xsl:variable>
    <xsl:variable name="ucletters">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>
    <xsl:param name="authorsearch">
        <xsl:value-of select="translate($author,$ucletters,$lcletters)"/>
    </xsl:param>
    <xsl:param name="titlesearch">
        <xsl:value-of select="translate($title,$ucletters,$lcletters)"/>
    </xsl:param>
    <xsl:param name="keywordssearch">
        <xsl:value-of select="translate($keywords,$ucletters,$lcletters)"/>
    </xsl:param>
  
    <xsl:template match="/">
        <xsl:variable name='bibname' select='@name'/>
        <!-- begining of the table -->
        <table id="bibtex_table">
            <tbody>
                <xsl:for-each select="//bibtex:entry">
                    <xsl:sort select="@id" order="ascending" data-type="text"/>
                    <xsl:variable name="authornames">
                        <xsl:value-of select="translate(.//bibtex:author,$ucletters,$lcletters)"/>
                    </xsl:variable>
                    <xsl:variable name="titlenames">
                        <xsl:value-of select="translate(.//bibtex:title,$ucletters,$lcletters)"/>
                    </xsl:variable>
                    <xsl:variable name="keywordsnames">
                        <xsl:value-of select="translate(.//bibtex:keywords,$ucletters,$lcletters)"/>
                    </xsl:variable>
                    <xsl:if test="(contains($authornames,$authorsearch) and (string-length($authorsearch) != 0)) or (contains($titlenames,$titlesearch) and (string-length($titlesearch) != 0)) or (contains($keywordsnames,$keywordssearch) and (string-length($keywordssearch)!=0))">
                        <xsl:apply-templates select="."/>
                    </xsl:if>
                </xsl:for-each>
        </tbody>
        </table>
    </xsl:template>
  
    <!-- include XSLT stylesheet -->
    <xsl:include href="file://./xsl/bibtex_entry2tr.xsl"/>
  
</xsl:stylesheet>