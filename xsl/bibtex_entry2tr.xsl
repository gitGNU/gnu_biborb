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

                <!-- display when admin mode -->
                <xsl:if test="$mode='admin'">
                    <xsl:if test="$display_images">
                        <a href="./action_proxy.php?{$getval}&amp;action=edit">
                            <img src="./data/images/stock_edit-16.png" alt="edit" title="Edit"  />
                        </a>
                    </xsl:if>
                    <xsl:if test="$display_text">
                        <a class="bibtex_action" href="./action_proxy.php?{$getval}&amp;action=edit">
                            edit
                        </a>
                    </xsl:if>
                    <xsl:if test="$display_images">
                        <a href="./action_proxy.php?{$getval}&amp;action=delete">
                            <img src="./data/images/stock_delete-16.png" alt="delete" title="Delete" />
                        </a>
                    </xsl:if>
                    <xsl:if test="$display_text">                        
                        <a class="bibtex_action" href="./action_proxy.php?{$getval}&amp;action=delete">
                            delete
                        </a>
                    </xsl:if>
                </xsl:if>
                
                <!-- display if basket -->
                <xsl:if test="$basket = '' and $basket != 'no'">
                    <xsl:if test="$display_images">
                        <a href="./action_proxy.php?{$getval}&amp;action=add_to_basket">
                            <img src="./data/images/cvs-add-16.png" alt="add to basket" title="Add to basket" />
                        </a>
                    </xsl:if>
                    <xsl:if test="$display_text">
                        <a class="basket_action" href="./action_proxy.php?{$getval}&amp;action=add_to_basket">
                            +
                        </a>
                    </xsl:if>
                </xsl:if>
                
                <xsl:if test="$basket != '' and $basket != 'no'">
                    <xsl:if test="$display_images">
                        <a href="./action_proxy.php?{$getval}&amp;action=delete_from_basket">
                            <img src="./data/images/cvs-remove-16.png" alt="remove from basket" title="Remove from basket" />
                        </a>
                    </xsl:if>
                    <xsl:if test="$display_text">
                        <a class="basket_action" href="./action_proxy.php?{$getval}&amp;action=delete_from_basket">
                            -
                        </a>
                    </xsl:if>
                </xsl:if>
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
        <tr>
            <td class="bibtex_abstract">
                <xsl:call-template name="bibtex:abstract">
                    <xsl:with-param name="id" select="$theid"/>
                </xsl:call-template>
            </td>
        </tr>

        <!-- fifth row for this entry -->
        <!-- keywords -->
        <tr>
            <td class="bibtex_keywords">
                <xsl:apply-templates select=".//bibtex:keywords"/>
            </td>
        </tr>
        
        <tr class="last"><td><p/></td></tr>
    </xsl:template>
    
    <xsl:template match="bibtex:pdf">
        <xsl:if test="$display_images">
            <a href="./bibs/{$bibname}/papers/{node()}">
                <img src="data/images/pdf-document.png" alt="pdf" title="View PDF file" />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="./bibs/{$bibname}/papers/{node()}">
                pdf
            </a>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="bibtex:url">
        <xsl:if test="$display_text">
            <a href="./bibs/{$bibname}/papers/{node()}">
                <img src="data/images/stock_book-16.png" alt="ps" title="View PS file"  />
            </a>
        </xsl:if>
        <xsl:if test="$display_images">
            <a href="./bibs/{$bibname}/papers/{node()}">
                ps
            </a>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="bibtex:urlzip">
        <xsl:if test="$display_images">
            <a href="./bibs/{$bibname}/papers/{node()}">
                <img src="data/images/tar.png" alt="ps" title="Download compressed file"  />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="./bibs/{$bibname}/papers/{node()}">
                ps.gz
            </a>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="bibtex:website">
        <xsl:if test="$display_images">
            <a href="http://{node()}">
                <img src="data/images/link-url-16.png" alt="website" title="Go to a related website" />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="http://{node()}">
                website
            </a>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="bibtex:link">
        <xsl:if test="$display_images">
            <a href="{node()}">
                <img src="data/images/stock_jump-to-16.png" alt="link" title="Go to a related link" />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="{node()}">
                link
            </a>
        </xsl:if>
    </xsl:template>
    
    <!--
        Some javascript here to display the abstract. If javascript is not supported, another page is generated to display the abstract of the given entry.
    -->
    <xsl:template name="abstract">
        <xsl:param name="id"/>
        <xsl:if test="$display_images">
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_abstract(\']]><xsl:value-of select="$id"/><![CDATA[\')\"><img src=\"data/images/stock_about-16.png\" alt=\"abstract\" title=\"Display abstract\" /></a>");]]>
                </xsl:comment>
            </script>
            <noscript>
                <div>
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">
                        <img src="data/images/stock_about-16.png" alt="abstract" title="Display abstract"/>
                    </a>
                </div>
            </noscript>
        </xsl:if>
        
         <xsl:if test="$display_text">
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_abstract(\']]><xsl:value-of select="$id"/><![CDATA[\')\">abstract</a>");]]>
                </xsl:comment>
            </script>
            <noscript>
                <div>
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">
                        abstract
                    </a>
                </div>
            </noscript>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="link2bibtex">
        <xsl:param name="id"/>
        <xsl:if test="$display_images">
            <a href ="./action_proxy.php?action=bibtex&amp;bibname={$bibname}&amp;id={$id}">
                <img src="data/images/stock_convert-16.png" alt="BibTeX source" title="BibTeX source"/>
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href ="./action_proxy.php?action=bibtex&amp;bibname={$bibname}&amp;id={$id}">
                bibtex
            </a>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="bibtex:abstract">
        <xsl:param name="id"/>
        <xsl:choose>
            <xsl:when test="$abstract != ''">
                <span id="{$id}">
                    <xsl:call-template name="substitute">
                        <xsl:with-param name="string" select=".//bibtex:abstract"/>
                    </xsl:call-template>
                </span>
            </xsl:when>
            <xsl:otherwise>
                <span id="{$id}" style="display:none;">
                    <xsl:call-template name="substitute">
                        <xsl:with-param name="string" select=".//bibtex:abstract"/>
                    </xsl:call-template>
                </span>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
    <xsl:template name="substitute">
        <xsl:param name="string" />
<!--        <xsl:param name="from" select="'&#xA;'" />-->
        <xsl:param name="from" select="'&#xA;&#xA;'"/>
        <xsl:param name="to">
            <br /><br />
        </xsl:param>
        <xsl:choose>
            <xsl:when test="contains($string, $from)">
                <xsl:value-of select="substring-before($string, $from)" />
                <xsl:copy-of select="$to" />
                <xsl:call-template name="substitute">
                    <xsl:with-param name="string" select="substring-after($string, $from)" />
                    <xsl:with-param name="from" select="$from" />
                    <xsl:with-param name="to" select="$to" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$string" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>