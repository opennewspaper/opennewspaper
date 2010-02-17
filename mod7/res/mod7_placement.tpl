{* debug *}
{if $singlemode}
	<script type="text/javascript" language="javascript">
	var langSavedidnotwork = "{$lang.savedidnotwork}";
	var langReallycancel = "{$lang.reallycancel}";
	var langActiondidnotwork = "{$lang.actiondidnotwork}";
	var langReallyrefresh = "{$lang.reallyrefresh}";
	</script>
	<link rel="stylesheet" type="text/css" href="res/mod7.css" />
	<script src="res/jquery-1.3.2.min.js" type="text/javascript"></script>
	<script src="res/jquery.selectboxes.js" type="text/javascript"></script>
	<script src="res/mod7.js" type="text/javascript"></script>
	<form action="" method="post" id="placementform">
	<input type="hidden" value="{$article->getAttribute("uid")}" name="tx_newspaper_mod7[placearticleuid]" id="placearticleuid" />
	<input type="hidden" value="{$article->getAttribute('kicker')}: {$article->getAttribute('title')}" name="tx_newspaper_mod7[placearticletitle]" id="placearticletitle" />
{/if}

	<table width="" border="0" cellspacing="0" cellpadding="0">
		<tr>
		{foreach from=$tree item="level" name="levelloop"}
			<td valign="top" class="level">
			{foreach from=$level item="sections" name="sectionsloop"}
				<div class="level level{$smarty.foreach.levelloop.iteration}">
					<table border="0" cellspacing="0" cellpadding="0" class="articles">
						<tr>
					    	<th scope="col" colspan="3">
								{foreach from=$sections item="section" name="sectionloop"}
									{$section.section->getAttribute('section_name')} {if $smarty.foreach.sectionloop.iteration < count($sections)}&gt;{/if}
								{/foreach}
							</th>
					  	</tr>
						{if isset($section.articlelist) && ($isde || $section.listtype|lower == "tx_newspaper_articlelist_semiautomatic")}
						<tr>
						    <td>
								<select name="tx_newspaper_mod7[placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}][]" id="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" multiple="multiple" size="9" class="multiple-select ressort-select placement-select">
									{html_options options=$section.articlelist}			
								</select>
							</td>
							<td valign="top" width="16">
								<a href="#" class="movetotop" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
									{$ICON.group_totop}
								</a>
								<br />	
								<a href="#" class="moveup" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
									{$ICON.up}
								</a> 
								<br />
								<a href="#" class="movedown" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
									{$ICON.down}
								</a>
								<a href="#" class="movetobottom" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
									{$ICON.group_tobottom}
								</a>
								<br />
								{if $section.listtype|lower == "tx_newspaper_articlelist_manual"}
									{* add insert/remove button for article to be placed, remove button for selected article *}
									<br />
									<a {if ($section.article_placed_already)}style="display:none;" {/if}id="addbutton_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" href="#" class="insertarticle" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
										{$ICON.button_left}{* add button *}
									</a>
									<a {if (!$section.article_placed_already)}style="display:none;" {/if}id="delbutton_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" href="#" class="removearticletobeplaced" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
										{$ICON.button_right}{* delete article to be placed button *}
									</a>
									<br />
									<a href="#" class="delete" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
										{$ICON.group_clear}
									</a>
								{/if}
							</td>
						</tr>
						{else}
						<tr>
							<td>
								<i class="noaccess">Keine Berechtigung für Sammelresorts.</i>
							</td>
						</tr>
						{/if}
					</table>
					{if $isde || $section.listtype|lower == "tx_newspaper_articlelist_semiautomatic"}
						<div align="right">
							<input type="button" name="tx_newspaper_mod7[refresh]" title="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" class="refresh" value="{$lang.refresh}" />
							<input type="button" name="tx_newspaper_mod7[save]" title="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" class="save" value="{$lang.save}" />
						</div>
					{/if}
				</div>
			{/foreach}
			</td> 
		{/foreach}
		</tr>
	</table>

{if $singlemode}
	</form>
{/if}