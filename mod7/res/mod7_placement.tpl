<table width="100%" border="0" cellspacing="0" cellpadding="0">
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
					{if isset($section.articlelist) && ($iscod || $section.listtype == "tx_newspaper_ArticleList_Semiautomatic")}
					<tr>
					    <td>
							<select name="tx_newspaper_mod7[placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}][]" id="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" multiple="multiple" size="7" class="multiple-select ressort-select placement-select">
								{html_options options=$section.articlelist}			
							</select>
						</td>
						<td valign="top" width="16">
							<a href="#" class="moveup" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
								<img src="/typo3/sysext/t3skin/icons/gfx/up.gif" />
							</a> 
							<br />
							<a href="#" class="movedown" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
								<img src="/typo3/sysext/t3skin/icons/gfx/down.gif" />
							</a>
							{if $section.listtype != "tx_newspaper_ArticleList_Semiautomatic"}
								<br />
								<a href="#" class="insertarticle" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
									<img src="/typo3/sysext/t3skin/icons/gfx/button_left.gif" width="14" height="14" />
								</a>
							{/if}
							<br />
							<a href="#" class="delete" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
								<img src="/typo3/sysext/t3skin/icons/gfx/group_clear.gif" />
							</a>
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
				{if $iscod || $section.listtype == "tx_newspaper_ArticleList_Semiautomatic"}
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