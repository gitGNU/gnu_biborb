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
 * File: model.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 *
 * Description:
 *
 *    Transform bibtex fields into a nice html form for edition
 *
-->
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  
    <xsl:output method="html" encoding="iso-8859-1"/>

    <xsl:param name="typeentry"/>
  
    <xsl:template match="/entrylist">
        <!-- Required BibTeX fields -->
        <b>Required Fields</b>
        <table class="required">
            <tbody>
                <xsl:for-each select="entry[@type=$typeentry]/required/*">
                    <xsl:choose>
                        <!-- an alternative : or -->
                        <xsl:when test="name() = 'alternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select="*">
                                <tr>
                                    <td class="required-entry"><xsl:value-of select="name()"/>:</td>
                                    <td class="required-value"><input name="_{name(.)}"/></td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><div style="text-align:center;">or/and</div></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- an exalternative: xor -->
                        <xsl:when test="name() = 'exalternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select="*">
                                <tr>
                                    <td class="required-entry"><xsl:value-of select="name()"/>:</td>
                                    <td class="required-value"><input name="_{name(.)}"/></td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><div style="text-align:center;">or</div></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- all other fields -->
                        <xsl:otherwise>
                            <tr>
                                <td class="required-entry"><xsl:value-of select="name()"/>:</td>
                                <td class="required-value"><input name="_{name(.)}" /></td>
                            </tr>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </tbody>
        </table>
        
        <br/>
        <!-- Optional fields -->
        <b>Optional Fields</b>
        <table class="optional">
            <tbody>
                <xsl:for-each select="entry[@type=$typeentry]/optional/*">
                    <xsl:choose>
                        <!-- an alternative: or -->
                        <xsl:when test="name() = 'alternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select="*">
                                <tr>
                                    <td class="optional-entry"><xsl:value-of select="name()"/>:</td>
                                    <td class="optional-value"><input name="_{name(.)}"/></td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><div style="text-align:center;">or/and</div></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- an exalternative : xor -->
                        <xsl:when test="name() = 'exalternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select="*">
                                <tr>
                                    <td class="optional-entry"><xsl:value-of select="name()"/>:</td>
                                    <td class="optional-value"><input name="_{name(.)}"/></td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><div style="text-align:center;">or/and</div></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- all other fields -->
                        <xsl:otherwise>
                            <tr>
                                <td class="optional-entry"><xsl:value-of select="name()"/>:</td>
                                <td class="optional-value"><input name="_{name(.)}"/></td>
                            </tr>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </tbody>
        </table>
        
        <br/>
        
        <!-- additional fields -->
        <b>BibORB Additional Fields</b>
        
        <script type="text/javascript">&lt;!--
        document.write(&quot;&lt;a style=\&quot;text-decoration:none;color:navy;\&quot; href=\&quot;javascript:toggle_element(\'additional\')\&quot;&gt; display/hide &lt;/a&gt;&quot;);
        //--&gt;</script>
            <noscript></noscript>
        <div id="additional">
        <table class="additional">
            <tbody>
                <xsl:for-each select="entry[@type=$typeentry]/additional/*">
                    <tr>
                        <td class="additional-entry"><xsl:value-of select="name()"/>:</td>
                        <xsl:choose>
                            <xsl:when test="name() = 'abstract' or name()='longnotes'">
                                <td class="additional-value"><textarea name="_{name(.)}" rows="5" cols="20"><xsl:text> </xsl:text></textarea></td>
                            </xsl:when>
                            <xsl:when test="name() = 'url' or name() = 'urlzip' or name() = 'pdf'">
                                <td>
                                    <input type="file" name="{name(.)}" size="45" />
                                </td>
                            </xsl:when>
                            <xsl:otherwise>
                                <td class="additional-value"><input name="_{name(.)}"/></td>
                            </xsl:otherwise>
                        </xsl:choose>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
        </div>
        <br/>
        <br/>
  </xsl:template>
  
 
</xsl:stylesheet>
