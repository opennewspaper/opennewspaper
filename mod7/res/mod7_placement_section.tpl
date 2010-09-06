{* debug *}
{* template for section article lists *}

{if $singlemode}
	<script type="text/javascript" language="javascript">
	var langSavedidnotwork = "{$lang.savedidnotwork}";
	var langReallycancel = "{$lang.reallycancel}";
	var langActiondidnotwork = "{$lang.actiondidnotwork}";
	var langReallyrefresh = "{$lang.reallyrefresh}";
	</script>
	<link rel="stylesheet" type="text/css" href="{$T3PATH}typo3conf/ext/newspaper/mod7/res/mod7.css" />
	{if !$FULLRECORD}
		<script src="{$T3PATH}typo3conf/ext/newspaper/mod7/res/jquery-1.3.2.min.js" type="text/javascript"></script>
		<script src="{$T3PATH}typo3conf/ext/newspaper/mod7/res/jquery.selectboxes.js" type="text/javascript"></script>
		<script src="{$T3PATH}typo3conf/ext/newspaper/mod7/res/mod7.js" type="text/javascript"></script>
		<form action="" method="post" id="placementform">
		{if $article}
			<input type="hidden" value="{$article->getAttribute("uid")}" name="tx_newspaper_mod7[placearticleuid]" id="placearticleuid" />
			<input type="hidden" value="{$article->getAttribute('kicker')}: {$article->getAttribute('title')}" name="tx_newspaper_mod7[placearticletitle]" id="placearticletitle" />
		{/if}
	{/if}
	<div class="tx_newspaper_mod7">
{/if}

{if !$FULLRECORD}

{* semiautomatic articles lists ONLY, if $SEMIAUTO_AL_FOLDED is set *}
{if $SEMIAUTO_AL_FOLDED && !$singlemode}
	{literal}
	<script type="text/javascript">
		function toggle_al_folded() {
			if (document.getElementById('al_folded').style.display == 'none') {
				document.getElementById('al_folded').style.display = 'block';
			} else {
				document.getElementById('al_folded').style.display = 'none';
			}
		}
	</script>
	{/literal}
	<div><a href="#" onclick="toggle_al_folded(); return false;">{$lang.toggle_semiauto_al_folded}</a></div>
	<div id="al_folded" style="display:none">
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				{foreach from=$tree item="level" name="levelloop"}
				<td valign="top" class="level">
					{foreach from=$level item="sections" name="sectionsloop"}
					<div class="level level{$smarty.foreach.levelloop.iteration}" id="al_folded_{foreach from=$sections item="section" name="sectionloop"}{if $smarty.foreach.sectionloop.last}{$section.section->getUid()}{/if}{/foreach}">
						<table border="0" cellspacing="0" cellpadding="0" class="articles">
							<tr>
								<th scope="col" colspan="3">
								{foreach from=$sections item="section" name="sectionloop"}
									{$section.section->getAttribute('section_name')} {if $smarty.foreach.sectionloop.iteration < count($sections)}&gt;{/if}
								{/foreach}
								</th>
							</tr>
	
						{if $section.listtype|lower == "tx_newspaper_articlelist_semiautomatic"}			
							{* type semiautomatic are rendered here only *}
		
							{if $isde && isset($section.articlelist)}
								{* duty editor, article list available, class for article list is known *}
								<tr>
									<td>
										{section name=i start=0 step=1 loop=$AL_HEIGHT}
											<div class="counter">{$smarty.section.i.iteration}</div>
										{/section}
									</td>
									<td>
										<select name="tx_newspaper_mod7[placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}][]" id="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" size="{$AL_HEIGHT}" class="multiple-select ressort-select placement-select {if  $section.listtype|lower == "tx_newspaper_articlelist_manual"} manual-list {/if}">
											{foreach from=$section.articlelist item="list" name="al_loop" key="key"}
												<option value="{$key}" label="{$list}" title="{$list}">{$list}</option>										
											{/foreach}
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
{*	Hotfix: hide "to bottom" in semi auto list 
										<a href="#" class="movetobottom" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
											{$ICON.group_tobottom}
										</a>
*}
										<br />
									</td>
								</tr>
							{else}
								<tr>
									<td>
										{if !$isde}<i class="noaccess">{$lang.message_no_dutyeditor}</i><br />{/if}
										{if !isset($section.articlelist)}<i class="noaccess">{$lang.message_no_articlelist}</i><br />{/if}
										{if ($section.listtype|lower != "tx_newspaper_articlelist_semiautomatic") && ($section.listtype|lower != "tx_newspaper_articlelist_manual")}
											<i class="noaccess">{$lang.message_unknown_articlelisttype}</i><br />
										{/if}
									</td>
								</tr>
							{/if}
						
						{else}
							{* hide complete div *}
	<style>
	#al_folded_{$section.section->getUid()} {ldelim}
	display:none;
	{rdelim}
	</style>
						{/if}
							
						</table>
						
						{if $isde && isset($section.articlelist)}
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
	</div>
{/if}



{* all article lists or manual lists only, if $SEMIAUTO_AL_FOLDED is set *}
	<table border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;" id="hide-empty">
		<tr>
			{foreach from=$tree item="level" name="levelloop"}
			<td valign="top" class="level">
				{foreach from=$level item="sections" name="sectionsloop"}
				<div class="level level{$smarty.foreach.levelloop.iteration}" id="al{foreach from=$sections item="section" name="sectionloop"}{if $smarty.foreach.sectionloop.last}{$section.section->getUid()}{/if}{/foreach}">
					<table border="0" cellspacing="0" cellpadding="0" class="articles">
						<tr>
							<th scope="col" colspan="3">
							{foreach from=$sections item="section" name="sectionloop"}
								{$section.section->getAttribute('section_name')} {if $smarty.foreach.sectionloop.iteration < count($sections)}&gt;{/if}
							{/foreach}
							</th>
						</tr>

					{if (!$SEMIAUTO_AL_FOLDED || $singlemode) || $section.listtype|lower != "tx_newspaper_articlelist_semiautomatic"}				
						{* if article list is of type semiautomatic it was configured, that these lists are rendered here too *}
	
	
						{if $isde && isset($section.articlelist) && ($section.listtype|lower == "tx_newspaper_articlelist_semiautomatic" || $section.listtype|lower == "tx_newspaper_articlelist_manual")}
							{* duty editor, article list available, class for article list is known *}
							<tr>
								<td>
									{section name=i start=0 step=1 loop=$AL_HEIGHT}
										<div class="counter">{$smarty.section.i.iteration}</div>
									{/section}
								</td>
								<td>
									<select name="tx_newspaper_mod7[placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}][]" id="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" size="{$AL_HEIGHT}" class="multiple-select ressort-select placement-select {if  $section.listtype|lower == "tx_newspaper_articlelist_manual"} manual-list {/if}">
										{foreach from=$section.articlelist item="list" name="al_loop" key="key"}
											<option value="{$key}" label="{$list}" title="{$list}">{$list}</option>										
										{/foreach}
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
{*	Hotfix: hide "to bottom" in semi auto list *}
{if $section.listtype|lower == "tx_newspaper_articlelist_manual"}
									<a href="#" class="movetobottom" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
										{$ICON.group_tobottom}
									</a>
{/if}
									<br />
									{if $section.listtype|lower == "tx_newspaper_articlelist_manual"}
										{if $article}
											{* add insert/remove button for article to be placed, remove button for selected article *}
											<br />
											<a {if ($section.article_placed_already)}style="display:none;" {/if}id="addbutton_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" href="#" class="insertarticle" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
												{$ICON.button_left}{* add button *}
											</a>
											<a {if (!$section.article_placed_already)}style="display:none;" {/if}id="delbutton_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" href="#" class="removearticletobeplaced" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
												{$ICON.button_right}{* delete article to be placed button *}
											</a>
										{else}
											<br />
											{* add article browser *}											
											<a href="#" onclick="setFormValueOpenBrowser_AL('placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}', '{$section.section->getAttribute('section_name')|escape:html}'); return false;" >{$ICON.articlebrowser}</a>
										{/if}
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
									{if !$isde}<i class="noaccess">{$lang.message_no_dutyeditor}</i><br />{/if}
									{if !isset($section.articlelist)}<i class="noaccess">{$lang.message_no_articlelist}</i><br />{/if}
									{if ($section.listtype|lower != "tx_newspaper_articlelist_semiautomatic") && ($section.listtype|lower != "tx_newspaper_articlelist_manual")}
										<i class="noaccess">{$lang.message_unknown_articlelisttype}</i><br />
									{/if}
								</td>
							</tr>
						{/if}
						
					{/if}
						
					</table>
					
					{if !$SEMIAUTO_AL_FOLDED || $section.listtype|lower != "tx_newspaper_articlelist_semiautomatic"}
						{if $isde && isset($section.articlelist) && ($section.listtype|lower == "tx_newspaper_articlelist_semiautomatic" || $section.listtype|lower == "tx_newspaper_articlelist_manual")}
							{if !$article} {* don't show buttons in placement mask, see #1221 *}
								<div align="right">
									<input type="button" name="tx_newspaper_mod7[refresh]" title="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" class="refresh" value="{$lang.refresh}" />
									<input type="button" name="tx_newspaper_mod7[save]" title="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" class="save" value="{$lang.save}" />
								</div>
							{/if}
						{/if}
					{else}
						{* old version, display a message where to find the article list: $lang.semiauto_list_is_folded *}
						{* hide complete div *}
{if !$singlemode}
<style>
#al{$section.section->getUid()} {ldelim}
display:none;
{rdelim}
</style>
{/if}
					{/if}
				</div>
				{/foreach}
			</td>
			{/foreach}
		</tr>
	</table>
{/if}

{if $singlemode}
	{if !$FULLRECORD}
		</form>        
        <a href="{$smarty.server.PHP_SELF}?{$smarty.server.QUERY_STRING}&tx_newspaper_mod7[fullrecord]=1&tx_newspaper_mod9[fullrecord]=1">{$lang.label_articlelist_fullrecord}</a>
	{else}
		{$AL_BACKEND}
	{/if}
        </div>
{/if}                                                    