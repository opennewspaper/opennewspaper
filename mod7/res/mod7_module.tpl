{* debug *}
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
	{$lang.title} 
	<img src="res/move-spinner.gif" alt="" id="progress" />
</h2>
<div style="padding-top: 5px;" />
<hr style="margin-top: 5px; margin-bottom: 5px;" />
<div style="padding-top: 5px;" />

<table border="0" cellspacing="0" cellpadding="0" class="sections" id="articleinfo">
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
		    <td>{$article->getUid()}</td>
		  </tr>
		  <tr>
		    <th scope="row">{$lang.editedby}:</th>
		    <td>{$backenduser.username}</td>
		    <td rowspan="3" valign="top">
		    	<input type="button" id="preview" value="{$lang.preview}" />
			</td>
		  </tr>
		  <tr>
		    <th scope="row">{$lang.online}:</th>
		    <td>{if $article->getAttribute('hidden')}{$lang.no}{else}{$lang.yes}{/if}</td>
	      </tr>
		  <tr>
		    <th scope="row">{$lang.placed}:</th>
		    <td>{* {if $article->getAttribute('is_placed')}{$lang.yes}{else}{$lang.no}{/if} *}</td>
	      </tr>
		</table>
	</td>
  </tr>
</table>
{$workflowlog}

<br />

<form action="" method="post" id="placementform">
	<input type="hidden" value="{$article->getAttribute("uid")}" name="tx_newspaper_mod7[placearticleuid]" id="placearticleuid" />
	<input type="hidden" value="{$article->getAttribute('kicker')}: {$article->getAttribute('title')}" name="tx_newspaper_mod7[placearticletitle]" id="placearticletitle" />

	<table border="0" cellspacing="0" cellpadding="0" class="sections">
	  <tr>
	    <th scope="col" colspan="3">{$lang.section}</th>
	  </tr>
	  <tr>
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
		<td>
			<label for="filter">{$lang.filtersections}:</label>
			<input type="text" name="tx_newspaper_mod7[filter]" id="filter" value="" />
			<br />
			<select name="tx_newspaper_mod7[sections_available][]" title="sections_selected" id="sections_available" multiple="multiple" size="7" class="multiple-select ressort-select addresort">
				{html_options options=$sections}
			</select>
		</td>
	  </tr>
	</table>
	
	<div align="right" style="width: 674px;">
		<input type="button" value="{$lang.checkforupdates}" name="tx_newspaper_mod7[checkrefresh]" id="checkrefresh" title="" />
		<input type="button" value="{$lang.save}" name="tx_newspaper_mod7[submit]" id="savesections" title="sections_selected" />
	</div>

<br />

<div id="buttons">
	{if $article->getAttribute('workflow_status') == 0}
		{if $input.showworkflowbuttons}
			<input type="button" value="{$lang.tode}" class="sendtode" />
		{else}
			<input type="button" value="{$lang.saveall}" class="saveall" id="saveall" />
		{/if}
		{if $article->getAttribute('hidden')}
			<input type="button" value="{$lang.putonline}" class="putonline" />
		{else}
			<input type="button" value="{$lang.putoffline}" class="putoffline" />
		{/if}
		<input type="button" value="{$lang.cancel}" class="cancel" />
	{/if}
	{if $article->getAttribute('workflow_status') == 1}
		<input type="button" value="{$lang.place}" class="place" />
		{if $input.showworkflowbuttons}
			<input type="button" value="{$lang.toeditor}" class="sendtoeditor" />
		{else}
			<input type="button" value="{$lang.saveall}" class="saveall" id="saveall" />
		{/if}
		{if $article->getAttribute('hidden')}
			<input type="button" value="{$lang.putonline}" class="putonline" />
		{else}
			<input type="button" value="{$lang.putoffline}" class="putoffline" />
		{/if}
		<input type="button" value="{$lang.cancel}" class="cancel" />
	{/if}
</div>

<br />

{* if $article->getAttribute('workflow_status') == 1 *}
	<div id="placement"></div>
{* /if *}

</form>