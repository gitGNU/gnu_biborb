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
 * File: bibtex_entry2tr.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Year: 2003
 * Licence: GPL
 *
 * Description:
 *
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
        
    <xsl:template match="bibtex:entry">
        <!-- first row for this entry -->
        <!-- key,url,urlzip,pdf,abstract,website -->
        <xsl:variable name="theid" select="@id"/>
        <tr>
            <td class="bibtex_start">
                <span class="bibtex_key">
                    <xsl:value-of select="@id"/>
                </span>
                <xsl:if test=".//bibtex:abstract and $abstract != 'true'">
                    <xsl:call-template name="abstract">
                        <xsl:with-param name="id" select="$theid"/>
                    </xsl:call-template>
                </xsl:if>
                <xsl:apply-templates select=".//bibtex:url"/>
                <xsl:apply-templates select=".//bibtex:urlzip"/>
                <xsl:apply-templates select=".//bibtex:pdf"/>
                <xsl:apply-templates select=".//bibtex:website"/>
                <xsl:apply-templates select=".//bibtex:link"/>
                <xsl:call-template name="link2bibtex">
                    <xsl:with-param name="id" select="$theid"/>
                </xsl:call-template>
                <xsl:variable name="getval">
                    <xsl:if test="$session_name != ''"><xsl:value-of select="$session_name"/>=<xsl:value-of select="$session_id"/>&amp;id=<xsl:value-of select="@id"/>&amp;mode=edit</xsl:if>
                </xsl:variable>
                <xsl:if test="$mode='admin'">
                    <a class="bibtex_action" href="./action_proxy.php?{$getval}&amp;action=edit">edit</a>
                    <a class="bibtex_action" href="./action_proxy.php?{$getval}&amp;action=delete">delete</a>
                </xsl:if>
                <xsl:if test="$basket = '' and $basket != 'no'">
                    <a class="basket_action" href="./action_proxy.php?{$getval}&amp;action=add_to_basket">+</a>
                </xsl:if>
                <xsl:if test="$basket != '' and $basket != 'no'">
                    <a class="basket_action" href="./action_proxy.php?{$getval}&amp;action=delete_from_basket">-</a>
                </xsl:if>
                <!--
                <form method="get" action="action_proxy.php" class="bibtex_button">
                    <xsl:if test="$session_name != ''">
                        <input type="hidden" name="{$session_name}" value="{$session_id}" />
                        <input type="hidden" name="id" value="{@id}" />
                    </xsl:if>
                    <input type="hidden" name="mode" value="edit" />
                    <xsl:if test="$mode='admin'">
                        <input type="submit" name="action" value="edit" />
                        <input type="submit" name="action" value="delete" />
                    </xsl:if>
                    <xsl:if test="$basket = '' and $basket != 'no'">
                        <input type="submit" name="action" value="add_to_basket" />
                    </xsl:if>
                    <xsl:if test="$basket != '' and $basket != 'no'">
                        <input type="submit" name="action" value="delete_from_basket" />
                    </xsl:if>
                </form>-->
            </td>
        </tr>
        <!-- second row for this entry -->
        <!-- the title -->
        <tr>
            <td class="bibtex_title">
                <xsl:apply-templates select=".//bibtex:title"/>
            </td>
        </tr>
        <!-- third row for this entry -->
        <!-- Authors -->
        <tr>
            <td class="bibtex_author">
                <xsl:apply-templates select=".//bibtex:author"/>
            </td>
        </tr>
        <!-- fourth row for this entry -->
        <!-- Abstract -->
        <!--<xsl:if test="$abstract != ''"> -->
            <tr>
                <td class="bibtex_abstract">
                    <xsl:call-template name="bibtex:abstract">
                        <xsl:with-param name="id" select="$theid"/>
                    </xsl:call-template>
                </td>
            </tr>
        <!--</xsl:if>-->
        <!-- fifth row for this entry -->
        <!-- keywords -->
        <tr>
            <td class="bibtex_keywords">
                <xsl:apply-templates select=".//bibtex:keywords"/>
            </td>
        </tr>
    </xsl:template>
    
    <xsl:template match="bibtex:pdf | bibtex:url | bibtex:urlzip">
        <span>
            <a href="./bibs/{$bibname}/papers/{node()}"><xsl:value-of select="local-name()"/></a>
        </span>
    </xsl:template>
    
    <xsl:template match="bibtex:website">
        <span>
            <a href="http://{node()}"><xsl:value-of select="local-name()"/></a>
        </span>
    </xsl:template>
    
    <xsl:template match="bibtex:link">
        <span>
            <a href="{node()}">link</a>
        </span>
    </xsl:template>
    
    <!--
        Some javascript here to display the abstract. If javascript is not supported, another page is generated to display the abstract of the given entry.
    -->
    <xsl:template name="abstract">
        <xsl:param name="id"/>
        <span>
        <script type="text/javascript">&lt;!--
        document.write(&quot;&lt;a href=\&quot;javascript:toggle_abstract(\'<xsl:value-of select="$id"/>\')\&quot;&gt;abstract&lt;/a&gt;&quot;);
        //--&gt;</script>
            <noscript><a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">abstract</a></noscript>
        </span>
    </xsl:template>
    
    <xsl:template name="link2bibtex">
        <xsl:param name="id"/>
        <a href ="./action_proxy.php?action=bibtex&amp;bibname={$bibname}&amp;id={$id}">bibtex</a>
    </xsl:template>
    
    <xsl:template name="bibtex:abstract">
        <xsl:param name="id"/>
        <xsl:choose>
            <xsl:when test="$abstract != ''">
                <span id="{$id}">
                    <xsl:value-of select=".//bibtex:abstract"/>
                </span>
            </xsl:when>
            <xsl:otherwise>
                <span id="{$id}" style="display:none;white-space:pre">
                    <xsl:value-of select=".//bibtex:abstract"/>
                </span>
            </xsl:otherwise>
        </xsl:choose>
        
    </xsl:template>
</xsl:stylesheet>