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
        <input type="hidden" name="id" value="{$id}"/>
		
        <!-- Display required fields -->
        <fieldset id="required_ref" class="required">
            <legend>BIBORB_OUTPUT_REQUIRED_FIELDS</legend>
            <!-- Process all required entries -->
            <xsl:for-each select="$model//entry[@type=$type]/required/*">
                <xsl:choose>
                    <!-- An alternative : or -->
                    <xsl:when test="name() = 'alternative'">
                        <xsl:variable name="cpt" select="count(*)"/>
                        <xsl:for-each select='*'>
                            <xsl:variable name="field" select="name()"/>
                            <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                            <label for="{name()}"><xsl:value-of select="$field"/>:</label>
                            <input name="{name()}" value="{$val}" /><br/>
                            <xsl:if test="not(position() = $cpt)">
                                or/and<br/>
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:when>
                    <!-- An exalternative : xor -->
                    <xsl:when test="name() = 'exalternative'">
                        <xsl:variable name="cpt" select="count(*)"/>
                        <xsl:for-each select='*'>
                            <xsl:variable name="field" select="name()"/>
                            <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>                                            
                            <label for="{name()}"><xsl:value-of select="$field"/>:</label>
                            <input name="{name()}" value="{$val}" /><br/>
                            <xsl:if test="not(position() = $cpt)">
                                or<br/>
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:when>
                    <!-- any other case -->
                    <xsl:otherwise>
                        <xsl:variable name="field" select="name()"/>
                        <label for="{name()}"><xsl:value-of select="name()"/>:</label>
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
                                <input name="{name()}" value="{$val}" type="hidden" />
                                <xsl:value-of select="$val"/><br/>
                            </xsl:when>
                            <xsl:otherwise>
                                <input name="{name()}" value="{$val}" /><br/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </fieldset>
        

        <!-- Optional fields -->
        <fieldset id="optional_ref" class="optional">
            <legend>BIBORB_OUTPUT_OPTIONAL_FIELDS</legend>
            <xsl:for-each select="$model//entry[@type=$type]/optional/*">
                <xsl:choose>
                    <!-- an alternative : or -->
                    <xsl:when test="local-name() = 'alternative'">
                        <xsl:variable name="cpt" select="count(*)"/>
                        <xsl:for-each select='*'>
                            <xsl:variable name="field" select="name()"/>
                            <label for="{name()}"><xsl:value-of select="$field"/>:</label>
                            <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                            <input name="{name()}" value='{$val}' /><br/>
                            <xsl:if test="not(position() = $cpt)">
                                or/and<br/>
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:when>
                    <!-- an exalternative : xor -->
                    <xsl:when test="local-name() = 'exalternative'">
                        <xsl:variable name="cpt" select="count(*)"/>
                        <xsl:for-each select='*'>
                            <xsl:variable name="field" select="name()"/>
                            <label for="{name()}"><xsl:value-of select="$field"/>:</label>
                            <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                            <input name="{name()}" value='{$val}' /><br/>
                            <xsl:if test="not(position() = $cpt)">
                                or/and<br/>
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:when>
                    <!-- any other field -->
                    <xsl:otherwise>
                        <xsl:variable name="field" select="name()"/>
                        <label for="{name()}"><xsl:value-of select="$field"/>:</label>
                        <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                        <input name="{name()}" value='{$val}' /><br/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </fieldset>
        
<!--
        <script type="text/javascript">
            <xsl:comment><![CDATA[
                document.write("<a style=\'text-decoration:none;color:navy;\' href=\"javascript:toggle_element(\'additional\')\"> display/hide </a>");]]>
            </xsl:comment>
        </script>-->
    
        <!-- Additional fields -->
        <fieldset id="additional_ref" class="additional">
            <legend>BIBORB_OUTPUT_ADDITIONAL_FIELDS</legend>
            <xsl:for-each select="$model//entry[@type=$type]/additional/*">
                <xsl:variable name="field" select="name()"/>
                <label for="{name()}">
                    <xsl:value-of select="name()"/>:
                    <xsl:if test="name() = 'website'">
                        <span style='font-size:9px;'>(http://)</span>
                    </xsl:if>
                </label>
                <xsl:choose>
                    <!-- abstract or longnotes -->
                    <xsl:when test="$field = 'abstract' or $field='longnotes'">
                        <textarea name="{name()}">
                            <xsl:value-of select="$entry//*[local-name() = $field]"/>
                                <xsl:text> </xsl:text>
                        </textarea>
                    </xsl:when>
                    <!-- url, urlzip pdf -->
                    <xsl:when test="$field = 'url' or $field = 'urlzip' or $field = 'pdf'">
                        <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                        <!--<label for="current_{name()}">BIBORB_OUTPUT_CURRENT_FILE</label>-->
                        <input name="current_{name()}" value='{$val}' /><br/>
                        <!--<label for="{name()}">BIBORB_OUTPUT_NEW_FILE</label>-->
                        <label for="{name()}"><xsl:text> </xsl:text></label>
                        <input name="{name()}" type='file'/>
                    </xsl:when>
                    <!-- groups -->
                    <xsl:when test="$field = 'groups'">
                        <xsl:variable name="val">
                            <xsl:for-each select="$entry//*[local-name() = 'group']">
                                <xsl:value-of select="current()"/>
                                <xsl:if test="position() != last()">,</xsl:if>
                            </xsl:for-each>
                        </xsl:variable>
                        <input name="groups" value="{$val}" /><br/>
                        <label><xsl:text> </xsl:text></label>
                        <span style='color:black;font-weight:normal;font-size:small;'>BIBORB_OUTPUT_ADD_A_GROUP</span>#XHTMLGROUPSLIST
                        <br/>
                    </xsl:when>
                    <!-- any other fields -->
                    <xsl:otherwise>
                        <xsl:variable name="val" select="$entry//*[local-name() = $field]"/>
                        <input name="{name()}" value='{$val}' /><br/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </fieldset>
        <br/>
    </xsl:template>
    
</xsl:stylesheet>
