<?xml version="1.0" encoding="iso-8859-1" ?>
<!--
 * This file is part of BibORB
 * 
 * Copyright (C) 2003-2004  Guillaume Gardey
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
  
    <xsl:output method="xml" encoding="iso-8859-1"/>

    <!-- include generic parameters -->
	<xsl:include href="xsl/parameters.xsl"/>
	
	<xsl:param name="update"/>
	<xsl:param name="modelfile"/>
    
	<xsl:template match="/">
    
		<!-- bibtex models -->
		<xsl:variable name="model" select="document($modelfile)"/>
		<!-- store the entry in a variable -->
		<xsl:variable name="entry" select="//bibtex:entry"/>
		<!-- get the entry's type -->
		<xsl:variable name="type" select="local-name($entry/*[position()=1])"/>
        <!-- set the type -->
        <input type="hidden" name="type" value="{$type}"/>
		
        <!-- Display required fields -->
        <div class="form_menu">
            <a href="#required_ref" class="required_item">BIBORB_OUTPUT_REQUIRED_FIELDS</a>
            <a href="#optional_ref" class="optional_item">BIBORB_OUTPUT_OPTIONAL_FIELDS</a>
            <a href="#additional_ref" class="additional_item">BIBORB_OUTPUT_ADDITIONAL_FIELDS</a>
        </div>
        <div class="form_item" id="required_ref">
        <table class="required">
            <tbody>
				<!-- Process all required entries -->
                <xsl:for-each select="$model//entry[@type=$type]/required/*">
                    <xsl:choose>
                        <!-- An alternative : or -->
                        <xsl:when test="name() = 'alternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select='*'>
                                <xsl:variable name="field" select="name()"/>
                                <tr>
                                    <td class="required-entry"><xsl:value-of select="$field"/>:</td>
                                    <td class="required-value">
										<xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
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
                                    <td class="required-entry"><xsl:value-of select="$field"/>:</td>
                                    <td class='required-value'>
                                        <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>                                            
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
                                <td class='required-entry'><xsl:value-of select="name()"/>:</td>
                                <td class='required-value'>
                                    <xsl:variable name="val">
                                        <xsl:choose>
                                            <!-- the id, not editable -->
                                            <xsl:when test="local-name()='id'">
                                                <xsl:value-of select="$entry//@id"/>
                                            </xsl:when>
                                            <!-- other fields, editable -->
                                            <xsl:otherwise>
                                                <xsl:value-of select="$entry//*[local-name() = $field]"/>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </xsl:variable>
                                    <xsl:choose>
                                        <xsl:when test="local-name() = 'id' and $update">
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
        </div>
        
        <div class="form_item" id="optional_ref">
        <!-- Optional fields -->
        <table class='optional'>
            <tbody>
                <xsl:for-each select="$model//entry[@type=$type]/optional/*">
                    <xsl:choose>
                        <!-- an alternative : or -->
                        <xsl:when test="local-name() = 'alternative'">
                            <xsl:variable name="cpt" select="count(*)"/>
                            <xsl:for-each select='*'>
                                <xsl:variable name="field" select="name()"/>
                                <tr>
                                    <td class='optional-entry'><xsl:value-of select="$field"/>:</td>
                                    <td class='optional-value'>
                                        <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
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
                                    <td class='optional-value'>
                                        <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
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
                                    <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                                    <input name="_{name()}" value='{$val}' />
                                </td>
                            </tr>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:for-each>
            </tbody>
        </table>
        
        </div>
<!--
        <script type="text/javascript">
            <xsl:comment><![CDATA[
                document.write("<a style=\'text-decoration:none;color:navy;\' href=\"javascript:toggle_element(\'additional\')\"> display/hide </a>");]]>
            </xsl:comment>
        </script>-->
    
        <!-- Additional fields -->
        <div class="form_item" id="additional_ref">
        <table  class='additional'>
            <tbody>
                <xsl:for-each select="$model//entry[@type=$type]/additional/*">
                    <xsl:variable name="field" select="name()"/>
                    <tr>
                        <td class="additional-entry">
                            <xsl:value-of select="name()"/>:
                            <xsl:if test="name() = 'website'">
                                <span style='font-size:xx-small;'>http://</span>
                            </xsl:if>
                        </td>
                        <xsl:choose>
                            <!-- abstract or longnotes -->
                            <xsl:when test="$field = 'abstract' or $field='longnotes'">
                                <td class='additional-value'>
                                    <textarea name="_{name()}" rows="5" cols="40" >
                                        <xsl:value-of select="$entry//*[local-name() = $field]"/>
                                        <xsl:text> </xsl:text>
                                    </textarea>
                                </td>
                            </xsl:when>
                            <!-- url, urlzip pdf -->
                            <xsl:when test="$field = 'url' or $field = 'urlzip' or $field = 'pdf'">
                                <td>
                                    <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td>BIBORB_OUTPUT_CURRENT_FILE</td>
                                                <td><input class='current' name="current_{name()}" value='{$val}' /></td>
                                            </tr>
                                            <tr>
                                                <td>BIBORB_OUTPUT_NEW_FILE</td>
                                                <td><input class='newfile' name="{name()}" type='file'/></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </xsl:when>
                            <!-- groups -->
			    <xsl:when test="$field = 'groups'">
                                <td class='additional-value'>
                                    <xsl:variable name="val">
                                        <xsl:for-each select="$entry//*[local-name() = 'group']">
                                            <xsl:value-of select="current()"/>
                                            <xsl:if test="position() != last()">,</xsl:if>
                                        </xsl:for-each>
                                    </xsl:variable>
                                    <input name="_groups" value="{$val}" /><br/>
                                    <span style='color:black;font-weight:normal;'>BIBORB_OUTPUT_ADD_A_GROUP</span>#XHTMLGROUPSLIST
                                </td>
                            </xsl:when>
                            <!-- any other fields -->
                            <xsl:otherwise>
                                <td class='additional-value'>
                                    <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
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
    </xsl:template>
    
</xsl:stylesheet>
