{* debug *} 
{* main template for placement mask *}

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

<h2>
	<a href="#" onclick="closePlacement(); return false;">{$ICON.close}</a> {$lang.title} 
	<img src="res/move-spinner.gif" alt="" id="progress" />
</h2>
<div style="padding-top: 5px;" />
<hr style="margin-top: 5px; margin-bottom: 5px;" />
<div style="padding-top: 5px;" />

<form action="" method="post" id="placementform">



{* article info *}

	<table class="sections" id="articleinfo">
	  <tr>
	    <td style="vertical-align:top;">
			<table width="300" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <th scope="row">{$lang.kicker}:</th>
			    <td>{$article->getAttribute('kicker')}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.headline}:</th>
			    <td>{$article->getAttribute('title')}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.author}:</th>
			    <td>{$article->getAttribute('author')}</td>
			  </tr>
			</table>
		</td>
	    <td style="vertical-align:top;">
			<table width="300" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <th scope="row">{$lang.article_uid}:</th>
			    <td>{$article->getUid()} <a href="#" onclick="return false;" id="preview">{$ICON.preview}</a> </td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.editedby}:</th>
			    <td>{$backenduser.username}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.online}:</th>
			    <td>{if $article->getAttribute('hidden')}{$lang.no}{else}{$lang.yes}{/if}</td>
		      </tr>
			  <tr>
			    <th scope="row">{$lang.article_activerole}:</th>
			    <td>{$article_workflow_status_title}</td>
		      </tr>
			</table>
		</td>
	  </tr>
	</table>



{* sections *}

	<input type="hidden" value="{$article->getAttribute("uid")}" name="tx_newspaper_mod7[placearticleuid]" id="placearticleuid" />
	<input type="hidden" value="{$article->getAttribute('kicker')|escape:html}: {$article->getAttribute('title')|escape:html}" name="tx_newspaper_mod7[placearticletitle]" id="placearticletitle" />
	<table id="section_selects" class="sections">
	  <tr>
	    <th scope="col" colspan="3">{$lang.section}</th>
	  </tr>
	  <tr>
		<td>
			<label for="filter">{$lang.filtersections}:</label>
			<input type="text" name="tx_newspaper_mod7[filter]" id="filter" value="" />
			<br />
			<select name="tx_newspaper_mod7[sections_available][]" title="sections_selected" id="sections_available" multiple="multiple" size="7" class="multiple-select ressort-select addresort">
				{html_options options=$sections}
			</select>
		</td>
	    <td>
			<select name="tx_newspaper_mod7[sections_selected][]" id="sections_selected" multiple="multiple" size="9" class="multiple-select ressort-select">
				{html_options options=$sections_active}
			</select>
		</td>
		<td valign="top" width="16">
			<a href="#" class="movetotop" rel="sections_selected">
				{$ICON.group_totop}
			</a>
			<br />				
			<a href="#" class="moveup" rel="sections_selected">
				{$ICON.up}
			</a> 
			<br />
			<a href="#" class="movedown" rel="sections_selected">
				{$ICON.down}
			</a>
			<br />
			<a href="#" class="movetobottom" rel="sections_selected">
				{$ICON.group_tobottom}
			</a>
			<br />
			<a href="#" class="delete" rel="sections_selected">
				{$ICON.group_clear}
			</a>
		</td>
	  </tr>
	  <tr>
	  	<td></td>
	  	<td colspan="2">
{* \todo: what is updated here???	  	
			<input type="button" value="{$lang.checkforupdates}" name="tx_newspaper_mod7[checkrefresh]" id="checkrefresh" title="" />
*}
{* (see #1141) not working: looses some sections, switches first and last section (sometimes) 
<input class="input_image" type="image" src="{$ICON.save}" value="{$lang.save}" name="tx_newspaper_mod7[submit]" id="savesections" title="sections_selected" />
*}
			<input type="button" value="{$lang.save}" name="tx_newspaper_mod7[submit]" id="savesections" title="sections_selected" />
	  	</td>
	  </tr>
	</table>



{* placement *}
<div id="placement"><img src="{$SPINNER}"></div>



{* article list save all/cancel buttons *}
{* See #1141; Move cancel button to top, remove "save all article lists" button
<div id="bottons_al">
	<input type="button" value="{$lang.cancel}" class="cancel" />
	<input type="button" value="{$lang.saveall}" class="saveall" id="saveall" />
</div>
*}


{* workflow comments *}
<div id="wf">
<div id="wf_title">{$lang.label_message}</div>
<textarea name="tx_newspaper_mod7[workflow_comment]" id="workflow_comment"></textarea>
{$workflowlog}
</div>


{* workflow buttons *}

{* \todo: use consts: NP_ACTIVE_ROLE_EDITORIAL_STAFF, NP_ACTIVE_ROLE_DUTY_EDITOR, NP_ACTIVE_ROLE_NONE *}
<div id="buttons">
<input type="hidden" name="tx_newspaper_mod7[workflow_status_ORG]" value="{$article->getAttribute('workflow_status')}" />
<table>

	<tr>
		<td>
			{if $workflow_permissions.place}
				<input type="button" value="{$lang.place}" class="place" />
			{/if}
		</td>
		<td>	
			{if $workflow_permissions.place}
				{if $workflow_permissions.hide}
					<input type="button" value="{$lang.placehide}" class="placehide" />
				{elseif $workflow_permissions.publish}
					<input type="button" value="{$lang.placepublish}" class="placepublish greenframe" />
				{/if}
			{/if}
		</td>
	</tr>
	
	<tr>
		<td>
			{if $workflow_permissions.revise}
				<input type="button" value="{$lang.toeditor}" class="sendtoeditor" />
			{elseif $workflow_permissions.check}
				<input type="button" value="{$lang.todutyeditor}" class="sendtodutyeditor" />
			{/if}
		</td>
		<td>
			{if $workflow_permissions.revise}
				{if $workflow_permissions.hide}
					<input type="button" value="{$lang.toeditorhide}" class="sendtoeditorhide" />
				{elseif $workflow_permissions.publish}
					<input type="button" value="{$lang.toeditorpublish}" class="sendtoeditorpublish" />
				{/if}
			{elseif $workflow_permissions.check}
				{if $workflow_permissions.hide}
					<input type="button" value="{$lang.todutyeditorhide}" class="sendtodutyeditorhide" />
				{elseif $workflow_permissions.publish}
					<input type="button" value="{$lang.todutyeditorpublish}" class="sendtodutyeditorpublish" />
				{/if}
			{/if}
		</td>
	</tr>

	<tr>
		<td></td>
		<td>
			{if $workflow_permissions.hide}
				<input type="button" value="{$lang.putoffline}" class="putoffline" /></td>
			{elseif $workflow_permissions.publish}
				<input type="button" value="{$lang.putonline}" class="putonline" /></td>
			{/if}
	</tr>

</table>


</form>