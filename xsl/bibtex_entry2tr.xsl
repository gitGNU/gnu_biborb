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

                    <a class="bibtex_action" href="./action_proxy.php?{$getval}&amp;action=edit">
                        <div class="images">
                            <img src="./data/images/stock_edit-16.png" alt="edit" title="Edit" border="0" align="center"/>
                        </div>
                        <div class="text">
                            edit
                        </div>
                    </a>
                    <a class="bibtex_action" href="./action_proxy.php?{$getval}&amp;action=delete">
                        <div class="images">
                            <img src="./data/images/stock_delete-16.png" alt="delete" title="Delete" border="0" align="center"/>
                        </div>
                        <div class="text">
                            delete
                        </div>
                    </a>
                </xsl:if>
                <xsl:if test="$basket = '' and $basket != 'no'">
                    <a class="basket_action" href="./action_proxy.php?{$getval}&amp;action=add_to_basket">
                        <div class="images">
                            <img src="./data/images/cvs-add-16.png" alt="add to basket" title="Add to basket" border="0" align="center"/>
                        </div>
                        <div class="text">
                            +
                        </div>
                    </a>
                </xsl:if>
                <xsl:if test="$basket != '' and $basket != 'no'">
                    <a class="basket_action" href="./action_proxy.php?{$getval}&amp;action=delete_from_basket">
                        <div class="images">
                            <img src="./data/images/cvs-remove-16.png" alt="remove from basket" title="Remove from basket" border="0" align="center"/>
                        </div>
                        <div class="text">
                            -
                        </div>
                    </a>
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
        
        <tr>
            <td class="last"><br/></td>
        </tr>
    </xsl:template>
    
    <xsl:template match="bibtex:pdf">
        <a href="./bibs/{$bibname}/papers/{node()}">
            <div class="images">
                <img src="data/images/pdf-document.png" alt="pdf" title="View PDF file" border="0" align="center"/>
            </div>
            <div class="text">
                pdf
            </div>
        </a>
    </xsl:template>
    
    <xsl:template match="bibtex:url">
        <a href="./bibs/{$bibname}/papers/{node()}">
            <div class="images">
                <img src="data/images/stock_book-16.png" alt="ps" title="View PS file" border="0" align="center"/>
            </div>
            <div class="text">
                ps
            </div>
        </a>
    </xsl:template>
    
    <xsl:template match="bibtex:urlzip">
        <a href="./bibs/{$bibname}/papers/{node()}">
            <div class="images">
                <img src="data/images/tar.png" alt="ps" title="Download compressed file" border="0" align="center"/>
            </div>
            <div class="text">
                ps.gz
            </div>
        </a>
    </xsl:template>
    
    <xsl:template match="bibtex:website">
        <a href="http://{node()}">
            <div class="images">
                <img src="data/images/link-url-16.png" alt="website" title="Go to a related website" border="0" align="center"/>
            </div>
            <div class="text">
                website
            </div>
        </a>
    </xsl:template>
    
    <xsl:template match="bibtex:link">
        <a href="{node()}">
            <div class="images">
                <img src="data/images/stock_jump-to-16.png" alt="link" title="Go to a related link" border="0" align="center"/>
            </div>
            <div class="text">
                link
            </div>
        </a>
    </xsl:template>
    
    <!--
        Some javascript here to display the abstract. If javascript is not supported, another page is generated to display the abstract of the given entry.
    -->
    <xsl:template name="abstract">
        <xsl:param name="id"/>
        <script type="text/javascript">&lt;!--
        document.write(&quot;&lt;a href=\&quot;javascript:toggle_abstract(\'<xsl:value-of select="$id"/>\')\&quot;&gt;&lt;div class=\&quot;images\&quot;&gt;&lt;img src=\&quot;data/images/stock_about-16.png\&quot; alt=\&quot;Abstract\&quot; title=\&quot;Display abstract\&quot;  border=\&quot;0\&quot;  align=\&quot;center\&quot; /&gt; &lt;/div&gt;&lt;div class=\&quot;text\&quot;&gt;abstract&lt;/div&gt; &lt;/a&gt;&quot;);
        //--&gt;</script>
            <noscript>
                <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">
                    <div class="images">
                        <img src="data/images/stock_about-16.png" alt="abstract" title="Display abstract" border="0" align="center"/>
                    </div>
                    <div class="text">
                        abstract
                    </div>
                </a>
            </noscript>

    </xsl:template>
    
    <xsl:template name="link2bibtex">
        <xsl:param name="id"/>
        <a href ="./action_proxy.php?action=bibtex&amp;bibname={$bibname}&amp;id={$id}">
            <div class="images">
                <img src="data/images/stock_convert-16.png" alt="BibTeX source" title="BibTeX source" border="0" align="center"/>
            </div>
            <div class="text">
                bibtex
            </div>

        </a>
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