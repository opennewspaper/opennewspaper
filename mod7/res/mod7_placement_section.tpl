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
                        {if $section.listtype|lower != "tx_newspaper_articlelist_semiautomatic"}
                            {* if article list is of type semiautomatic it was configured, that these lists are rendered here too *}

                            <div class="level level{$smarty.foreach.levelloop.iteration}"
                                 id="al{$section.section->getUid()}">

                                {$section.rendered_section}

                            </div>
                        {else}
                            {* hide complete div
                            <style>
                                #al_folded_{$section.section->getUid()} {ldelim}
                                display:none;
                                {rdelim}
                            </style>
                        *}
                        {/if}

                    </div>
                {/foreach}
            </td>
        {/foreach}
    </tr>
</table>

