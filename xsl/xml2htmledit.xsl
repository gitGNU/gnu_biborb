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
File: xml2htmledit.xsl
Author: Guillaume Gardey (ggardey@club-internet.fr)
Year: 2003
Licence: GPL

Description:

     Produce a HTML form output for bibtex entries.

-->
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:bibtex="http://bibtexml.sf.net/"
  version="1.0">
  
  <xsl:output method="html" encoding="iso-8859-1"/>

  <xsl:param name="bibname"/>
  <xsl:param name="id"/>

  <xsl:template match="/entrylist">
    <xsl:variable name="bibfile" select="document($bibname)//bibtex:entry[@id=$id]"></xsl:variable>
    <xsl:variable name="type"><xsl:value-of select="local-name($bibfile/*[position() =1])"/></xsl:variable>
    <input type='hidden' name='add_type' value="{$type}"/>
    <b>Required Fields</b>
    <table class='required'>
      <tbody>
        <xsl:for-each select="entry[@type=$type]/required/*">
          <xsl:choose>
            <xsl:when test="name() = 'alternative'">
              <xsl:variable name="cpt"><xsl:value-of select="count(*)"/></xsl:variable>
              <xsl:for-each select='*'>
                <tr>
                  <td width="required-entry"><xsl:value-of select="name()"/>:</td>
                  <td class='required-value'>
                    <xsl:variable name="nm">
                      <xsl:value-of select="name()"/>
                    </xsl:variable>
                    <xsl:variable name="val">
                      <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input name="_{name()}" value='{$val}' />
                  </td>
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
                  <td class='required-value'>
                    <xsl:variable name="nm">
                      <xsl:value-of select="name()"/>
                    </xsl:variable>
                    <xsl:variable name="val">
                      <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input name="_{name()}" value='{$val}' />
                  </td>
                </tr>
                <xsl:if test="not(position() = $cpt)">
                  <tr><td><center>or</center></td></tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
              <tr>
                <td class='required-entry'><xsl:value-of select="name()"/>:</td>
                <td class='required-value'>
                  <xsl:variable name="nm">
                    <xsl:value-of select="name()"/>
                  </xsl:variable>
                  <xsl:variable name="val">
                    <xsl:choose>
                      <xsl:when test="name()='id'">
                        <xsl:value-of select="$bibfile/@id"/>
                      </xsl:when>
                      <xsl:otherwise>
                        <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                          <xsl:value-of select="current()"/>
                        </xsl:for-each>
                      </xsl:otherwise>
                    </xsl:choose>
                  </xsl:variable>
                  <input name="_{name()}" value='{$val}' />
                </td>
              </tr>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </tbody>
    </table>
    <b>Optional Fields</b>
    <table class='optional'>
      <tbody>
        <xsl:for-each select="entry[@type=$type]/optional/*">
          <xsl:choose>
            <xsl:when test="local-name() = 'alternative'">
              <xsl:variable name="cpt"><xsl:value-of select="count(*)"/></xsl:variable>
              <xsl:for-each select='*'>
                <tr>
                  <td class='optional-entry'><xsl:value-of select="name()"/>:</td>
                  <td class='optional-value'>
                    <xsl:variable name="nm">
                      <xsl:value-of select="name()"/>
                    </xsl:variable>
                    <xsl:variable name="val">
                      <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input name="_{name()}" value='{$val}' />
                  </td>
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
                  <td class='optional-entry'><xsl:value-of select="name()"/>:</td>
                  <td class='optional-value'>
                      <xsl:variable name="nm">
                        <xsl:value-of select="name()"/>
                      </xsl:variable>
                      <xsl:variable name="val">
                        <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input name="_{name()}" value='{$val}' />
                  </td>
                </tr>
                <xsl:if test="not(position() = $cpt)">
                  <tr><td><center>or/and</center></td></tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <tr>
                  <td class='optional-entry'><xsl:value-of select="name()"/>:</td>
                  <td class='optional-value'>
                    <xsl:variable name="nm">
                      <xsl:value-of select="name()"/>
                    </xsl:variable>
                    <xsl:variable name="val">
                      <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input name="_{name()}" value='{$val}' />
                  </td>
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
        <xsl:for-each select="entry[@type=$type]/additional/*">
            <tr>
              <td class='additional-entry'><xsl:value-of select="name()"/>:</td>
              <xsl:choose>
                <xsl:when test="name() = 'abstract'">
                  <td class='additional-value'>
                    <textarea name="_{name()}" rows='5' >
                      <xsl:variable name="nm">
                        <xsl:value-of select="name()"/>
                      </xsl:variable>
                      <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </textarea>
                  </td>
                </xsl:when>
                <xsl:when test="name() = 'url' or name() = 'urlzip' or name() = 'pdf'">
                  <td>
                    <xsl:variable name="nm">
                      <xsl:value-of select="name()"/>
                    </xsl:variable>
                    <xsl:variable name="val">
                      <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                        <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input class='current' name="current_{name()}" value='{$val}' />
                    <input class='newfile' name="{name()}" type='file'/>
                  </td>
                </xsl:when>
                <xsl:otherwise>
                  <td class='additional-value'>
                    <xsl:variable name="nm">
                      <xsl:value-of select="name()"/>
                    </xsl:variable>
                    <xsl:variable name="val">
                    <xsl:for-each select="$bibfile//*[local-name() = $nm]">
                      <xsl:value-of select="current()"/>
                      </xsl:for-each>
                    </xsl:variable>
                    <input name="_{name()}" value='{$val}' />
                  </td>
                </xsl:otherwise>
              </xsl:choose>
            </tr>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:template>
 
</xsl:stylesheet>