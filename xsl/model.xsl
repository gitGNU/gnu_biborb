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
File: model.xsl
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

     Transform the model.xml into a HTML form for edition of a bibtex entry

-->
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  
  <xsl:output method="html"/>

  <xsl:param name="typeentry"/>
  
  <xsl:template match="/entrylist">
    <b>Required Fields</b>
    <table class='required'>
      <tbody>
        <xsl:for-each select="entry[@type=$typeentry]/required/*">
          <xsl:choose>
            <xsl:when test="name() = 'alternative'">
              <xsl:variable name="cpt"><xsl:value-of select="count(*)"/></xsl:variable>
              <xsl:for-each select='*'>
                <tr>
                  <td class='required-entry'><xsl:value-of select="name()"/>:</td>
                  <td class='required-value'><input name="_{name(.)}"/></td>
                </tr>
                <xsl:if test="not(position() = $cpt)">
                  <tr><td><center>or/and</center></td></tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:when test="name() = 'exalternative'">
              <xsl:variable name="cpt"><xsl:value-of select="count(*)"/></xsl:variable>
              <xsl:for-each select='*'>
                <tr>
                  <td class='required-entry'><xsl:value-of select="name()"/>:</td>
                  <td class='required-value'><input name="_{name(.)}"/></td>
                </tr>
                <xsl:if test="not(position() = $cpt)">
                  <tr><td><center>or</center></td></tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
              <tr>
                <td class='required-entry'><xsl:value-of select="name()"/>:</td>
                <td class='required-value'><input name="_{name(.)}" /></td>
              </tr>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </tbody>
    </table>
    <br/>
    <b>Optional Fields</b>
    <table class='optional'>
      <tbody>
        <xsl:for-each select="entry[@type=$typeentry]/optional/*">
          <xsl:choose>
            <xsl:when test="local-name() = 'alternative'">
              <xsl:variable name="cpt"><xsl:value-of select="count(*)"/></xsl:variable>
              <xsl:for-each select='*'>
                <tr>
                  <td><xsl:value-of select="name()"/>:</td>
                  <td><input name="_{name(.)}"/></td>
                </tr>
                <xsl:if test="not(position() = $cpt)">
                  <tr><td><center>or/and</center></td></tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:when test="local-name() = 'exalternative'">
              <xsl:variable name="cpt"><xsl:value-of select="count(*)"/></xsl:variable>
              <xsl:for-each select='*'>
                <tr>
                  <td><xsl:value-of select="name()"/>:</td>
                  <td><input name="_{name(.)}"/></td>
                </tr>
                <xsl:if test="not(position() = $cpt)">
                  <tr><td><center>or/and</center></td></tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <tr>
                  <td class='optional-entry'><xsl:value-of select="name()"/>:</td>
                  <td class='optional-value'><input name="_{name(.)}"/></td>
                </tr>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </tbody>
    </table>
    <br/>
    <b>BibORB Additional Fields</b>
    <table class='additional'>
      <tbody>
        <xsl:for-each select="entry[@type=$typeentry]/additional/*">
            <tr>
              <td class='additional-entry'><xsl:value-of select="name()"/>:</td>
              <xsl:choose>
                <xsl:when test="name() = 'abstract' or name()='longnotes'">
                  <td class='additional-value'><textarea name="_{name(.)}" rows="5"/></td>
                </xsl:when>
                <xsl:when test="name() = 'url' or name() = 'urlzip' or name() = 'pdf'">
                  <td>
                    <input type='file' name='{name(.)}' size='45'/>
                  </td>
                </xsl:when>
                <xsl:otherwise>
                  <td class='additional-value'><input name="_{name(.)}"/></td>
                </xsl:otherwise>
              </xsl:choose>
            </tr>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:template>
  
 
</xsl:stylesheet>
