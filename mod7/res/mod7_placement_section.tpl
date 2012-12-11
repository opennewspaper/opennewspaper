{*debug*}

{* template for section article lists *}

{*
    $article    article object
    $tree       array
*}


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

{* semiautomatic articles lists *}
<table id="al_folded" style="display:none" border="0" cellspacing="0" cellpadding="0">
    <tr>
        {foreach from=$tree item="level" name="levelloop"}

            <td valign="top" class="level">
                {foreach from=$level item="section" name="sectionsloop"}
                    {if $section.object->getArticleList()|get_class|lower == "tx_newspaper_articlelist_semiautomatic"}

                        <div class="level level{$smarty.foreach.levelloop.iteration}" id="al_folded_{$section.object->getUid()}">
                            {$section.rendered}
                        </div>
                    {/if}
                {/foreach}

            </td>
        {/foreach}

    </tr>
</table>

{* manual lists *}
<table border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;" id="hide-empty">
    <tr>
        {foreach from=$tree item="level" name="levelloop"}

            <td valign="top" class="level">
                {foreach from=$level item="section" name="sectionsloop"}
                    {if $section.object->getArticleList()|get_class|lower != "tx_newspaper_articlelist_semiautomatic"}

                        <div class="level level{$smarty.foreach.levelloop.iteration}" id="al{$section.object->getUid()}">
                            {$section.rendered}
                        </div>
                    {/if}
                {/foreach}

            </td>
        {/foreach}

    </tr>
</table>
