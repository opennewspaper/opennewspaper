{*debug*}

{* template for section article lists *}

{*
    $T3PATH
    $lang
    $article     article object
    $tree
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
<div id="al_folded" style="display:none">
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            {foreach from=$tree item="level" name="levelloop"}
                <td valign="top" class="level">
                    {foreach from=$level item="sections" name="sectionsloop"}

                        {foreach from=$sections item="section" name="sectionloop"}{/foreach}
                        {if $section.section->getArticleList()|get_class|lower == "tx_newspaper_articlelist_semiautomatic"}
                            {* type semiautomatic are rendered here only *}

                            <div class="level level{$smarty.foreach.levelloop.iteration}" id="al_folded_{$section.section->getUid()}">
                                {$section.rendered_section}
                            </div>
                        {/if}
                    {/foreach}

                </td>
            {/foreach}

        </tr>
    </table>
</div>

{* manual lists *}
<table border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;" id="hide-empty">

    <tr>

        {foreach from=$tree item="level" name="levelloop"}

            <td valign="top" class="level">

                {foreach from=$level item="sections" name="sectionsloop"}

                    {foreach from=$sections item="section" name="sectionloop"}{/foreach}

{*                    <div class="level level{$smarty.foreach.levelloop.iteration}" id="al{$section.section->getUid()}"> *}

                        {if $section.section->getArticleList()|get_class|lower != "tx_newspaper_articlelist_semiautomatic"}
                            {* if article list is of type semiautomatic it was configured, that these lists are rendered here too *}

                            <div class="level level{$smarty.foreach.levelloop.iteration}" id="al{$section.section->getUid()}">
                                {$section.rendered_section}
                            </div>
                        {/if}

{*                    </div> *}
                {/foreach}

            </td>
        {/foreach}

    </tr>
</table>

