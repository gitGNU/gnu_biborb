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
 * File: xml2htmledit.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 *
 * Description:
 *
 *  Create a form to edit an entry
 *
-->
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:bibtex="http://bibtexml.sf.net/"
  exclude-result-prefixes="bibtex"
  version="1.0">
  
    <xsl:output method="html" encoding="iso-8859-1"/>

    <xsl:param name="bibname"/>
    <xsl:param name="id"/>
    <xsl:param name="add"/>

    <xsl:template match="/entrylist">
    
        <!-- get the entry to edit -->
        <xsl:variable name="bibfile" select="document($bibname)//bibtex:entry[@id=$id]"/>
        <!-- get the entry's type -->
        <xsl:variable name="type" select="local-name($bibfile/*[position()=1])"/>
        <!-- set the type -->
        <input type="hidden" name="add_type" value="{$type}"/>
        
        <!-- Let's display required fields -->
        <b>Required Fields</b>
        <table class="required">
            <tbody>
                <xsl:for-each select="entry[@type=$type]/required/*">
                    <xsl:choose>
                        <!-- An alternative : or -->
                        <xsl:when test="name() = 'alternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select='*'>
                                <xsl:variable name="field" select="name()"/>
                                <tr>
                                    <td width="required-entry"><xsl:value-of select="$field"/>:</td>
                                    <td class='required-value'>
                                        <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>
                                        <input name="_{name()}" value="{$val}" />
                                    </td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><center>or/and</center></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- An exalternative : xor -->
                        <xsl:when test="name() = 'exalternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select='*'>
                                <xsl:variable name="field" select="name()"/>
                                <tr>
                                    <td width="required-entry"><xsl:value-of select="$field"/>:</td>
                                    <td class='required-value'>
                                        <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>                                            
                                        <input name="_{name()}" value="{$val}" />
                                    </td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><center>or</center></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- any other case -->
                        <xsl:otherwise>
                            <xsl:variable name="field" select="name()"/>
                            <tr>
                                <td class='required-entry'><xsl:value-of select="$field"/>:</td>
                                <td class='required-value'>
                                    <xsl:variable name="val">
                                        <xsl:choose>
                                            <!-- the id, not editable -->
                                            <xsl:when test="name()='id'">
                                                <xsl:value-of select="$bibfile/@id"/>
                                            </xsl:when>
                                            <!-- other fields, editable -->
                                            <xsl:otherwise>
                                                <xsl:value-of select="$bibfile//*[local-name() = $field]"/>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </xsl:variable>
                                    <xsl:choose>
                                        <xsl:when test="local-name() = 'id' and $add=0">
                                            <xsl:value-of select="$val"/>
                                            <input name="_{name()}" value="{$val}" type="hidden" />
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <input name="_{name()}" value="{$val}" />
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                            </tr>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </tbody>
        </table>
    
        <!-- Optional fields -->
        <b>Optional Fields</b>
        <table class='optional'>
            <tbody>
                <xsl:for-each select="entry[@type=$type]/optional/*">
                    <xsl:choose>
                        <!-- an alternative : or -->
                        <xsl:when test="local-name() = 'alternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select='*'>
                                <xsl:variable name="field" select="name()"/>
                                <tr>
                                    <td class='optional-entry'><xsl:value-of select="$field"/>:</td>
                                    <td class='optional-value'>
                                        <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>
                                        <input name="_{name()}" value='{$val}' />
                                    </td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><center>or/and</center></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- an exalternative : xor -->
                        <xsl:when test="local-name() = 'exalternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select='*'>
                                <xsl:variable name="field" select="name()"/>
                                <tr>
                                    <td class='optional-entry'><xsl:value-of select="$field"/>:</td>
                                    <td class='required-valu'>
                                        <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>
                                        <input name="_{name()}" value='{$val}' />
                                    </td>
                                </tr>
                                <xsl:if test="not(position() = $cpt)">
                                    <tr><td><center>or/and</center></td></tr>
                                </xsl:if>
                            </xsl:for-each>
                        </xsl:when>
                        <!-- any other field -->
                        <xsl:otherwise>
                            <xsl:variable name="field" select="name()"/>
                            <tr>
                                <td class='optional-entry'><xsl:value-of select="$field"/>:</td>
                                <td class='optional-value'>
                                    <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>
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
        <script type="text/javascript">&lt;!--
        document.write(&quot;&lt;a style=\&quot;text-decoration:none;color:navy;\&quot; href=\&quot;javascript:toggle_element(\'additional\')\&quot;&gt;display/hide &lt;/a&gt;&quot;);
        //--&gt;</script>
            <noscript></noscript>
    
        <!-- Additional fields -->
        
        <div id="additional">
        <table  class='additional'>
            <tbody>
                <xsl:for-each select="entry[@type=$type]/additional/*">
                    <xsl:variable name="field" select="name()"/>
                    <tr>
                        <td class='additional-entry'><xsl:value-of select="name()"/>:</td>
                        <xsl:choose>
                            <!-- abstract or longnotes -->
                            <xsl:when test="$field = 'abstract' or $field='longnotes'">
                                <td class='additional-value'>
                                    <textarea name="_{name()}" rows="5" cols="40" >
                                        <xsl:value-of select="$bibfile//*[local-name() = $field]"/>
                                        <xsl:text> </xsl:text>
                                    </textarea>
                                </td>
                            </xsl:when>
                            <!-- url, urlzip pdf -->
                            <xsl:when test="$field = 'url' or $field = 'urlzip' or $field = 'pdf'">
                                <td>
                                    <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>
                                    current:<input class='current' name="current_{name()}" value='{$val}' /><br/>
                                    new:<input class='newfile' name="{name()}" type='file'/>
                                </td>
                            </xsl:when>
                            <!-- groups -->
                            <xsl:when test="fields = 'groups'">
                                <td class='additional-value'>
                                    <xsl:variable name="val">
                                        <xsl:for-each select="$bibfile//*[local-name() = 'group']">
                                            <xsl:value-of select="current()"/>
                                            <xsl:if test="position() != last()">,</xsl:if>
                                        </xsl:for-each>
                                    </xsl:variable>
                                    <input name="_groups" value="{$val}" />
                                </td>
                            </xsl:when>
                            <!-- any other fields -->
                            <xsl:otherwise>
                                <td class='additional-value'>
                                    <xsl:variable name="val" select="$bibfile//*[local-name() = $field]"/>
                                    <input name="_{name()}" value='{$val}' />
                                </td>
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