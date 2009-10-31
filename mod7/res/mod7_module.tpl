<link rel="stylesheet" type="text/css" href="res/mod7.css" />
<script src="res/jquery-1.3.2.min.js" type="text/javascript"></script>
<script src="res/jquery.selectboxes.js" type="text/javascript"></script>
<script src="res/mod7.js" type="text/javascript"></script>

<div class="tx-newspaper-mod7">

	<h2>
		{$lang.title}  
		<img src="res/move-spinner.gif" alt="" id="progress" />
	</h2>
	<div style="padding-top: 5px;" />
	<hr style="margin-top: 5px; margin-bottom: 5px;" />
	<div style="padding-top: 5px;" />
	
	<table border="0" cellspacing="0" cellpadding="0" class="sections">
	  <tr>
	    <td align="left">
			<table width="300" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <th scope="row">{$lang.kicker}:</th>
			    <td>{$article->getAttribute("kicker")}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.headline}:</th>
			    <td>{$article->getAttribute("title")}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.author}:</th>
			    <td>{$article->getAttribute("author")}</td>
			  </tr>
			</table>
		</td>
	    <td>
			<table width="300" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <th scope="row">{$lang.editedby}:</th>
			    <td>{$article->getAttribute("modification_user")}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.online}:</th>
			    <td>{if $article->getAttribute("hidden")}ja{else}nein{/if}</td>
			  </tr>
			  <tr>
			    <th scope="row">{$lang.placed}:</th>
			    <td>{* {if $article->getAttribute("is_placed")}ja{else}nein{/if} *}</td>
			  </tr>
			</table>
		</td>
	  </tr>
	</table>
	
	<br />
	
	<form action="" method="post" id="placementform">
		<input type="hidden" name="tx_newspaper_mod7[articleid]" value="{$article->getAttribute("uid")}" />
		<input type="hidden" value="{$article->getAttribute("uid")}" name="tx_newspaper_mod7[placearticleuid]" id="placearticleuid" />
		<input type="hidden" value="{$article->getAttribute("title")}" name="tx_newspaper_mod7[placearticletitle]" id="placearticletitle" />

		<table border="0" cellspacing="0" cellpadding="0" class="sections">
		  <tr>
		    <th scope="col" colspan="3">Ressort</th>
		  </tr>
		  <tr>
		    <td>
				<select name="tx_newspaper_mod7[sections_selected][]" id="sections_selected" multiple="multiple" size="7" class="multiple-select ressort-select">
				</select>
			</td>
			<td valign="top" width="16">
				<a href="#" class="moveup" rel="sections_selected">
					<img src="/typo3/sysext/t3skin/icons/gfx/up.gif" />
				</a> 
				<br />
				<a href="#" class="movedown" rel="sections_selected">
					<img src="/typo3/sysext/t3skin/icons/gfx/down.gif" />
				</a>
				<br />
				<a href="#" class="delete" rel="sections_selected">
					<img src="/typo3/sysext/t3skin/icons/gfx/group_clear.gif" />
				</a>
			</td>
			<td>
				<select name="tx_newspaper_mod7[sections_available][]" title="sections_selected" id="sections_available" multiple="multiple" size="7" class="multiple-select ressort-select addresort">
					{html_options options=$sections}
				</select>
			</td>
		  </tr>
		</table>
		
		<div align="right" style="width: 674px;">
			<input type="button" value="Aktualisierungen &uuml;berpr&uuml;fen" name="tx_newspaper_mod7[checkrefresh]" id="checkrefresh" title="" />
			<input type="button" value="Vorschau" name="tx_newspaper_mod7[submit]" id="preview" title="sections_selected" />
		</div>
	
	<br />
	
	<div id="buttons">
		{if $article->getAttribute('workflow_status') == 0}
			<input type="button" value="Zum CVD" class="sendtocod" />
			<input type="button" value="Online stellen" class="putonline" />
			<input type="button" value="Offline stellen" class="putoffline" />
			<input type="button" value="Abbrechen" class="cancel" />
		{/if}
		{if $article->getAttribute('workflow_status') == 1}
			<input type="button" value="Platzieren" class="place" />
			<input type="button" value="Zurück zum Redakteur" class="sendtoeditor" />
			<input type="button" value="Online stellen" class="putonline" />
			<input type="button" value="Offline stellen" class="putoffline" />
			<input type="button" value="Abbrechen" class="cancel" />
		{/if}
	</div>
	
	<br />
	
	{* {if $article->getAttribute('workflow_status') == 1} *}
		<div id="placement"></div>
	{* {/if} *}
	
	</form>

<div>