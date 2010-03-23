{* debug *}
{if $singlemode}
	<script type="text/javascript" language="javascript">
	var langSavedidnotwork = "{$lang.savedidnotwork}";
	var langReallycancel = "{$lang.reallycancel}";
	var langActiondidnotwork = "{$lang.actiondidnotwork}";
	var langReallyrefresh = "{$lang.reallyrefresh}";
	</script>
	<link rel="stylesheet" type="text/css" href="{$T3PATH}typo3conf/ext/newspaper/mod7/res/mod7.css" />
	<script src="{$T3PATH}typo3conf/ext/newspaper/mod7/res/jquery-1.3.2.min.js" type="text/javascript"></script>
	<script src="{$T3PATH}typo3conf/ext/newspaper/mod7/res/jquery.selectboxes.js" type="text/javascript"></script>
	<script src="{$T3PATH}typo3conf/ext/newspaper/mod7/res/mod7.js" type="text/javascript"></script>
	<form action="" method="post" id="placementform">
	<div class="tx_newspaper_mod7">
{/if}

<h2>{$lang.title_articlelist_list}</h2>

<table width="" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top" class="level">
			<div class="level level{$smarty.foreach.levelloop.iteration}">
				<table border="0" cellspacing="0" cellpadding="0" class="articles">
					{if $articlelist && ($articlelist_type == "tx_newspaper_articlelist_semiautomatic" || $articlelist_type == "tx_newspaper_articlelist_manual")}
					{* article list availaable, class for article list is known *}
					<tr><th scope="col" colspan="3">{$articlelist->getAttribute('notes')}</th></tr>
					<tr>
					    <td>
							<select name="tx_newspaper_mod7[al_{$articlelist->getAbstractUid()}][]" id="al_{$articlelist->getAbstractUid()}" multiple="multiple" size="9" class="multiple-select ressort-select placement-select">
								{html_options options=$articles}			
							</select>
						</td>
						<td valign="top" width="16">
							<a href="#" class="movetotop" rel="al_{$articlelist->getAbstractUid()}">
								{$ICON.group_totop}
							</a>
							<br />	
							<a href="#" class="moveup" rel="al_{$articlelist->getAbstractUid()}">
								{$ICON.up}
							</a> 
							<br />
							<a href="#" class="movedown" rel="al_{$articlelist->getAbstractUid()}">
								{$ICON.down}
							</a>
							<a href="#" class="movetobottom" rel="al_{$articlelist->getAbstractUid()}">
								{$ICON.group_tobottom}
							</a>
							<br />
							{if $section.listtype|lower == "tx_newspaper_articlelist_manual"}
								<br />
								<a onclick="alert('Article browser still missing ...'); return false;" href="#">
									{$ICON.articlebrowser}{* add article using the article browser *}
								</a>
								<br />
								<a href="#" class="delete" rel="al_{$articlelist->getAbstractUid()}">
									{$ICON.group_clear}
								</a>
							{/if}
						</td>
					</tr>
					{else}
					<tr>
						<td>
							{if !$articlelist}
								<i class="noaccess">{$lang.message_no_articlelist}</i><br />
							{/if}
							{if ($articlelist_type != "tx_newspaper_articlelist_semiautomatic") && ($articlelist_type != "tx_newspaper_articlelist_manual")}
								<i class="noaccess">{$lang.message_unknown_articlelisttype}</i><br />
							{/if}
						</td>
					</tr>
					{/if}
				</table>
				{if $articlelist && ($articlelist_type == "tx_newspaper_articlelist_semiautomatic" || $articlelist_type == "tx_newspaper_articlelist_manual")}
					<div align="right">
						<input type="button" name="tx_newspaper_mod7[refresh]" title="al_{$articlelist->getAbstractUid()}" class="refresh" value="{$lang.refresh}" />
						<input type="button" name="tx_newspaper_mod7[save]" title="al_{$articlelist->getAbstractUid()}" class="save" value="{$lang.save}" />
					</div>
				{/if}
			</div>
		</td> 
	</tr>
</table>

{if $singlemode}
	</form>
	</div>
{/if}