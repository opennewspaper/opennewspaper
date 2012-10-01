{*debug*}
{* template for section article lists *}

{*
    $T3PATH
    $lang
    $article     article object
    $tree
*}

{* semiautomatic articles lists *}

{include file="mod7_automatic_al.tmpl"}


{* all article lists or manual lists *}
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

          					{if $section.listtype|lower != "tx_newspaper_articlelist_semiautomatic"}
		            			{* if article list is of type semiautomatic it was configured, that these lists are rendered here too *}


					            {if $isde || $smarty.foreach.levelloop.index >= $allowed_placement_level
                                    && isset($section.articlelist) && ($section.listtype|lower == "tx_newspaper_articlelist_semiautomatic" || $section.listtype|lower == "tx_newspaper_articlelist_manual")}
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
                                                    <option value="{$key}" label="{$list|escape:"html"}" title="{$list|escape:"html"}">{$list}</option>
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
                                            <a href="#" class="movetobottom" rel="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}">
                                                {$ICON.group_tobottom}
                                            </a>
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
                                            {if !$isde && $smarty.foreach.levelloop.index >= $allowed_placement_level}<i class="noaccess">{$lang.message_no_dutyeditor}</i><br />{/if}
                                            {if $smarty.foreach.levelloop.index < $allowed_placement_level}<i class="noaccess">{$lang.message_no_access_to_level}</i>{/if}
                                            {if !isset($section.articlelist)}<i class="noaccess">{$lang.message_no_articlelist}</i><br />{/if}
                                            {if ($section.listtype|lower != "tx_newspaper_articlelist_semiautomatic") && ($section.listtype|lower != "tx_newspaper_articlelist_manual")}
                                                <i class="noaccess">{$lang.message_unknown_articlelisttype}</i><br />
                                            {/if}
                                        </td>
                                    </tr>
           						{/if}

				            {/if}

				        </table>

                        {if $section.listtype|lower != "tx_newspaper_articlelist_semiautomatic"}
                            {if $isde || $smarty.foreach.levelloop.index >= $allowed_placement_level
                                && isset($section.articlelist) && ($section.listtype|lower == "tx_newspaper_articlelist_semiautomatic" || $section.listtype|lower == "tx_newspaper_articlelist_manual")}
                                <div align="right" style="display:none;">
                                    <input type="button" name="tx_newspaper_mod7[refresh]" title="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" class="refresh" value="{$lang.refresh}" />
                                    <input type="button" name="tx_newspaper_mod7[save]" title="placer_{foreach from=$sections item="section" name="sectionloop"}{$section.section->getAttribute('uid')}{if $smarty.foreach.sectionloop.iteration < count($sections)}_{/if}{/foreach}" class="save" value="{$lang.save}" />
                                </div>
                            {/if}
                        {else}
                            {* old version, display a message where to find the article list: $lang.semiauto_list_is_folded *}
                            {* hide complete div *}
                            <style>
                                #al{$section.section->getUid()} {ldelim}
                                display:none;
                                {rdelim}
                            </style>
      					{/if}
	        		</div>
			    {/foreach}
		    </td>
		{/foreach}
	</tr>
</table>

