{if $lang.$message}
    <p>
        {$lang.$message}
    </p>
{/if}
{if $moduleDelete}
    <select name="tx_newspaper_mod8[tags]">
    {html_options options=$tags}
    </select>

    <input type="submit" value="{$lang.delete}" name="tx_newspaper_mod8[submit]" id="delete" title="{$lang.deleteSelected}" />
{/if}
{if $moduleMerge}
{/if}
