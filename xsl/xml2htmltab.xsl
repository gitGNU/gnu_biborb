<?xml version="1.0" encoding="iso-8859-1" ?>
<!--
This file is part of BibORB

Copyright (C) 2003  Guillaume Gardey

BibORB is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

BibORB is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
-->
<!--
File: xml2htmltab.xsl
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

     Produce a table-like HTML output for bibtex entries.

-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"> 
   
  <xsl:output method="html"/> 

  <xsl:param name="groupval"/>
  
  <xsl:param name="author"/>
  <xsl:param name="title"/>
  <xsl:param name="keywords"/>
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
  
  <xsl:param name="id"/>
  <xsl:param name="mode"/>

  <xsl:template match="/file">
    <xsl:variable name='bibname' select='@name'/>

    <table width="100%">
      <tbody>
        <xsl:choose>
          <!-- Affichage par groupe -->
          <xsl:when test="string-length($groupval)!=0">
            <xsl:for-each select="bibentry[contains(group,$groupval)]">
              <xsl:sort select="@id" order="ascending" data-type="text"/>
              <xsl:call-template name='bibdisplay'>
                <xsl:with-param name='bibname' select='$bibname'/>
                <xsl:with-param name='mode' select='$mode'/>
              </xsl:call-template>
            </xsl:for-each>
          </xsl:when>
          <!-- Recherche (auteurs,titre,mots-clé) -->
          <xsl:when test="string-length($author)!=0 or string-length($title)!=0 or string-length($keywords)!=0">            
            <xsl:for-each select="bibentry">
              <xsl:sort select="@id" order="ascending" data-type="text"/>
              <xsl:variable name="authornames">
                <xsl:value-of select="translate(author,$ucletters,$lcletters)"/>
              </xsl:variable>
              <xsl:variable name="titlenames">
                <xsl:value-of select="translate(title,$ucletters,$lcletters)"/>
              </xsl:variable>
              <xsl:variable name="keywordsnames">
                <xsl:value-of select="translate(keywords,$ucletters,$lcletters)"/>
              </xsl:variable>
              <xsl:if test="(contains($authornames,$authorsearch) and (string-length($authorsearch) != 0)) or (contains($titlenames,$titlesearch) and (string-length($titlesearch) != 0)) or (contains($keywordsnames,$keywordssearch) and (string-length($keywordssearch)!=0))">
                <xsl:call-template name='bibdisplay'>
                  <xsl:with-param name='bibname' select='$bibname'/>
                  <xsl:with-param name='mode' select='$mode'/>
                </xsl:call-template>
              </xsl:if>
            </xsl:for-each>
          </xsl:when>
          <!-- Afficahge complet d'une entrée -->
          <xsl:when test="string-length($id)!=0">
            <xsl:for-each select="bibentry[@id=$id]">
              <xsl:call-template name='bibdisplay'>
                <xsl:with-param name='bibname' select='$bibname'/>
                <xsl:with-param name='mode' select='$mode'/>
              </xsl:call-template>              
            </xsl:for-each>
          </xsl:when>
          <!-- Tout afficher -->
          <xsl:otherwise>
            <xsl:for-each select="bibentry">
              <xsl:sort select="@id" order="ascending" data-type="text"/>
              <xsl:call-template name='bibdisplay'>
                <xsl:with-param name='bibname' select='$bibname'/>
                <xsl:with-param name='mode' select='$mode'/>
              </xsl:call-template>
            </xsl:for-each>
          </xsl:otherwise>
        </xsl:choose>
      </tbody>
    </table>
  </xsl:template>

  <xsl:template name="bibdisplay">
    <xsl:param name='bibname'/>
    <xsl:param name='mode'/>
      <tr>
        <td class='startbib'>
          <span class='key'><xsl:value-of select="@id"/></span>
          <xsl:if test="./url">
            <a href="./bibs/{$bibname}/papers/{url}" class='button'>ps</a>
          </xsl:if>
          <xsl:if test="./urlzip">
            <a href="./bibs/{$bibname}/papers/{urlzip}" class='button'>ps.gz</a>
          </xsl:if>
          <xsl:if test="./pdf">
            <a href="./bibs/{$bibname}/papers/{pdf}" class='button'>pdf</a>
          </xsl:if>
          <xsl:if test="./abstract">
            <a class='button' href="./bibindex.php?mode=abstract&amp;id={@id}&amp;bibname={$bibname}">abstract</a>
          </xsl:if>
          <a class='button' href="./bibindex.php?mode=bibtex&amp;id={@id}&amp;bibname={$bibname}">bibtex</a>          
        </td>
      </tr>
      <tr>
        <td>
          <span class="title">
            <xsl:choose>
              <xsl:when test="./txttitle">
                <xsl:value-of select="./txttitle"/>                  
              </xsl:when>
              <xsl:when test="./title">
                <xsl:value-of select="./title"/>                  
              </xsl:when>
            </xsl:choose>
          </span>
        </td>
      </tr>
      <tr>
        <td>
          <span class="author">
            <xsl:choose>
              <xsl:when test="./txtauthor">
                <xsl:value-of select="txtauthor"/>
              </xsl:when>
              <xsl:when test="./author">
                <xsl:value-of select="author"/>
              </xsl:when>
            </xsl:choose>
          </span>
        </td>
      </tr>
      <xsl:if test="$mode='abstract'">
        <tr>
          <td><xsl:value-of select="abstract"/></td>
        </tr>
      </xsl:if>
      <tr><td><span class="keywords"><xsl:value-of select="keywords"/></span></td></tr>
  </xsl:template>
  
</xsl:stylesheet>