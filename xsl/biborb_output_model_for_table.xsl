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
 * File: biborb_output_model_for_table.xsl
 * Author: Guillaume Gardey (ggardey@club-internet.fr)
 * Licence: GPL
 *
 * Description:
 *
 *  This file describe the transformation of a bibtex entry into HTML.
 *  Several parameters are taken into account to create an output:
 *		- 
 *
-->
<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:bibtex="http://bibtexml.sf.net/"
    exclude-result-prefixes="bibtex"
    version="1.0">
        
	<!-- This is the template to apply to a bibtex entry -->
    <xsl:template match="bibtex:entry">
	
		<!-- 
			Look if the id is present in the basket.
			If true, $inbasket = inbasket. For latter use.
		-->
		<xsl:variable name="theidp" select="concat('.',@id,'.')"/>
		<xsl:variable name="inbasket">
			<xsl:if test="contains($basketids,$theidp)">inbasket</xsl:if>
		</xsl:variable>
	
		<!-- 
			The first row contains the bibtex ID and buttons/links to manage
			the entry.
			A different style is applied if the entry is in the basket
		-->
		<tr class="{$inbasket}">			
            <td class="bibtex_start">
				<!-- The bibtex entry -->
                <div class="bibtex_key">
                    <xsl:value-of select="@id"/>
                </div>
				
				<!-- Various links (abstract,url,urlzip,pdf,website,linl,bibtex -->
				<div class="bibtex_misc">
					<!-- 
						If an abstract is present and we do not want to see 
						the abstract by default we display a small button
					-->
					<xsl:if test=".//bibtex:abstract and $abstract != 'true'">
						<xsl:call-template name="abstract">
							<xsl:with-param name="id" select="@id"/>
						</xsl:call-template>
					</xsl:if>
					<!-- url -->
					<xsl:apply-templates select=".//bibtex:url"/>
					<!-- urlzip -->
					<xsl:apply-templates select=".//bibtex:urlzip"/>
					<!-- pdf -->
					<xsl:apply-templates select=".//bibtex:pdf"/>
					<!-- website -->
					<xsl:apply-templates select=".//bibtex:website"/>
					<!-- link -->
					<xsl:apply-templates select=".//bibtex:link"/>
					<!-- bibtex -->
					<xsl:call-template name="link2bibtex">
						<xsl:with-param name="id" select="@id"/>
					</xsl:call-template>
				</div>
				<!-- end of the div 'bibtex_misc' -->
				
				<!-- Various command : edit, delete, add/remove from basket -->
				<div class="command">
					
					<!-- Admin mode  $mode='admin' -->
					<xsl:if test="$mode='admin'">
					
						<!-- Edit action -->
						<!-- display images if necessary: $display_images!=null -->
						<xsl:if test="$display_images">
							<a href="./bibindex.php?mode=update&amp;id={@id}#required_ref">
								<img src="./data/images/{$edit-image}" alt="edit" title="Edit"  />
							</a>
						</xsl:if>
						<!-- display text if necessary: $display_text != null -->
						<xsl:if test="$display_text">
							<a class="bibtex_action" href="./bibindex.php?mode=update&amp;id={@id}#required_ref">
								edit
							</a>
						</xsl:if>
						
						<!-- Delete action -->
						<xsl:if test="$display_images">
							<a href="./bibindex.php?mode={$bibindex_mode}&amp;id={@id}&amp;action=delete&amp;{$extra_get_param}">
								<img src="./data/images/{$delete-image}" alt="delete" title="Delete" />
							</a>
						</xsl:if>
						<xsl:if test="$display_text">                        
							<a class="bibtex_action" href="./bibindex.php?mode={$bibindex_mode}&amp;id={@id}&amp;action=delete&amp;{$extra_get_param}">
								delete
							</a>
						</xsl:if>
					</xsl:if>

				
					<!-- Dispay basket actions if needed ($basket!='no') -->
					<xsl:if test="$display_basket_actions != 'no'">
				
						<!-- if not present in basket, display the add action -->
						<xsl:if test="$display_basket_actions = '' and $inbasket =''">
							<xsl:if test="$display_images">
								<a href="./bibindex.php?mode={$bibindex_mode}&amp;action=add_to_basket&amp;id={@id}&amp;{$extra_get_param}">
									<img src="./data/images/{$add-basket-image}" alt="add to basket" title="Add to basket" />
								</a>
							</xsl:if>
							<xsl:if test="$display_text">
								<a class="basket_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=add_to_basket&amp;id={@id}&amp;{$extra_get_param}">
									+
								</a>
							</xsl:if>
						</xsl:if>

						<!-- if present in basket display the remove action -->
						<xsl:if test="$display_basket_actions != '' or contains($inbasket,'inbasket')">
							<xsl:if test="$display_images">
								<a href="./bibindex.php?mode={$bibindex_mode}&amp;action=delete_from_basket&amp;id={@id}&amp;{$extra_get_param}">
									<img src="./data/images/{$remove-basket-image}" alt="remove from basket" title="Remove from basket" />
								</a>
							</xsl:if>
							<xsl:if test="$display_text">
								<a class="basket_action" href="./bibindex.php?mode={$bibindex_mode}&amp;action=delete_from_basket&amp;id={@id}&amp;{$extra_get_param}">
									-
								</a>
							</xsl:if>
						</xsl:if>
					</xsl:if>
				</div>
				<!-- end of the div "command" -->
            </td>
        </tr>
		<!-- end of the first row -->
		
		
        <!-- 
			The second row contains the title of the article
		-->
        <tr>
            <td class="bibtex_title">
                <xsl:apply-templates select=".//bibtex:title"/>
            </td>
        </tr>
		
        <!-- 
			The third row contains the authors
		-->
        <tr>
            <td class="bibtex_author">
                <xsl:apply-templates select=".//bibtex:author"/>
            </td>
        </tr>
		
        <!-- 
			The fourth row contains the abstract
		-->
        <tr>
            <td class="bibtex_abstract">
                <xsl:call-template name="bibtex:abstract">
                    <xsl:with-param name="id" select="@id"/>
                </xsl:call-template>
            </td>
        </tr>

        <!-- 
			The fifth row contains the keywords
		-->
        <tr>
            <td class="bibtex_keywords">
                <xsl:apply-templates select=".//bibtex:keywords"/>
            </td>
        </tr>
        
		<!-- a little trick to add a space between records -->
		<!-- waiting for the corresponding CSS trick :) -->
        <tr class="last"><td><p/></td></tr>
    </xsl:template>
    <!-- end of the template bibtex:entry -->
	
	<!--
		Template for the pdf field.
		Display a link(text/image) to the recorded pdf.
	-->
    <xsl:template match="bibtex:pdf">
        <xsl:if test="$display_images">
            <a href="./bibs/{$bibname}/papers/{node()}">
                <img src="data/images/{$pdf-image}" alt="pdf" title="View PDF file" />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="./bibs/{$bibname}/papers/{node()}">
                pdf
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the url field.
		Display a link(text/image) to the recorded url (ps file).
	-->
    <xsl:template match="bibtex:url">
        <xsl:if test="$display_images">
            <a href="./bibs/{$bibname}/papers/{node()}">
                <img src="data/images/{$ps-image}" alt="ps" title="View PS file"  />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="./bibs/{$bibname}/papers/{node()}">
                ps
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the urlzip field.
		Display a link(text/image) to the recorded urlzip (ps.gz file).
	-->
    <xsl:template match="bibtex:urlzip">
        <xsl:if test="$display_images">
            <a href="./bibs/{$bibname}/papers/{node()}">
                <img src="data/images/{$ps.gz-image}" alt="ps" title="Download compressed file"  />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="./bibs/{$bibname}/papers/{node()}">
                ps.gz
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the website field.
		Display a link(text/image) to the recorded website (internet).
	-->
    <xsl:template match="bibtex:website">
        <xsl:if test="$display_images">
            <a href="http://{node()}">
                <img src="data/images/{$url-image}" alt="website" title="Go to a related website" />
            </a>
        </xsl:if>
        <xsl:if test="$display_text">
            <a href="http://{node()}">
                website
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the link field.
		Display a link(text/image) to the recorded link (intranet/on the biborb server).
	-->
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
        Some javascript here to display the abstract. 
		If javascript is not supported, another page is generated to display 
		the abstract of the given entry.
		If supported, clicking on the abstract link will (un)hide the abstract.
    -->
    <xsl:template name="abstract">
		<!-- pass the id to know to which entry apply the javascript -->
        <xsl:param name="id"/>
		
		<!-- the icone version -->
        <xsl:if test="$display_images">
			<!-- if javascript is supported -->
            <script type="text/javascript">
                <xsl:comment><![CDATA[
                    document.write("<a href=\"javascript:toggle_abstract(\']]><xsl:value-of select="$id"/><![CDATA[\')\"><img src=\"data/images/stock_about-16.png\" alt=\"abstract\" title=\"Display abstract\" /></a>");]]>
                </xsl:comment>
				<!-- easy to insert javasacript in XSL, isn'it? :-D -->
            </script>
			<!-- if javascript not supported -->
            <noscript>
                <div style="display:inline;">
                    <a href="./bibindex.php?mode=details&amp;abstract=1&amp;menu=0&amp;bibname={$bibname}&amp;id={$id}">
                        <img src="data/images/{$abstract-image}" alt="abstract" title="Display abstract"/>
                    </a>
                </div>
            </noscript>
        </xsl:if>
        
		<!-- the text version -->
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
    
	<!--
		Template to generate a link to the bibtex version of the entry.
	-->
    <xsl:template name="link2bibtex">
		<!-- get the bibtex id -->
        <xsl:param name="id"/>
		<!-- image version -->
        <xsl:if test="$display_images">
            <a href ="./bibindex.php?mode=bibtex&amp;bibname={$bibname}&amp;id={$id}">
                <img src="data/images/{$bibtex-image}" alt="BibTeX source" title="BibTeX source"/>
            </a>
        </xsl:if>
		<!-- text version -->
        <xsl:if test="$display_text">
            <a href ="./bibindex.php?mode=bibtex&amp;bibname={$bibname}&amp;id={$id}">
                bibtex
            </a>
        </xsl:if>
    </xsl:template>
    
	<!--
		Template for the abstract field.
		Display the abstract, preserving empty lines.
	-->
    <xsl:template name="bibtex:abstract">
		<!-- the bibtex id -->
        <xsl:param name="id"/>
        <xsl:choose>
			<!-- display the abstract if abstract should always be present -->
            <xsl:when test="$abstract != ''">
                <span id="{$id}">
					<!-- replacing text empty lines with HTML empty lines -->
                    <xsl:call-template name="string-replace">
                        <xsl:with-param name="string" select="translate(string(.//bibtex:abstract),'&#xD;','@#xA;')"/>
                        <xsl:with-param name="from" select="'&#xA;'" />
                        <xsl:with-param name="to" select="'&lt;BR/>'" />
                    </xsl:call-template>
                </span>
            </xsl:when>
			<!-- create the abstract but hide it  -->
            <xsl:otherwise>
                <span id="{$id}" style="display:none;">
					<!-- replacing text empty lines with HTML empty lines -->
                    <xsl:call-template name="string-replace">
                        <xsl:with-param name="string" select="translate(string(.//bibtex:abstract),'&#xD;','@#xA;')"/>
                        <xsl:with-param name="from" select="'&#xA;'" />
                        <xsl:with-param name="to" select="'&lt;BR/>'" />
                    </xsl:call-template>
                </span>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

	
	<!--
		A string replacement function
	-->
    <xsl:template name="string-replace">
        <xsl:param name="string"/>
        <xsl:param name="from"/>
        <xsl:param name="to"/>
        <xsl:choose>
            <xsl:when test="contains($string,$from)">
                <xsl:value-of select="substring-before($string,$from)"/>
                <xsl:value-of select="$to" disable-output-escaping="yes"/>
                <xsl:call-template name="string-replace">
                    <xsl:with-param name="string" select="substring-after($string,$from)"/>
                    <xsl:with-param name="from" select="$from"/>
                    <xsl:with-param name="to" select="$to"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$string"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    
</xsl:stylesheet>