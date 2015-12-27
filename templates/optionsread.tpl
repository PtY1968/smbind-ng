<form name=form1 method="post" action="{$src}options.php">
<div class=clear><div class=main>
<h4>Record Types</h4>
<table style="min-width:600">
{section name=x loop=$records}
<tr>
{section name=y loop=$records[x]}
<td>
<input type=checkbox id={$records[x][y].prefkey} name={$records[x][y].prefkey} {if $records[x][y].prefval == "on"}checked{/if} />
<label for="{$records[x][y].prefkey}" name={$records[x][y].prefkey}_lbl>{$records[x][y].prefkey}</label>
</td>
{/section}
</tr>
{/section}
</table>
<table>
<tr><td colspan=2><hr /></td></tr>
{section name=prefkey loop=$options}
<tr>
<td align=right>
{if $options[prefkey].prefkey == "hostmaster"}Site Hostmaster Address:{/if}
{if $options[prefkey].prefkey == "range"}Items Per Page:{/if}
{if $options[prefkey].prefkey == "prins"}Default Primary NS:{/if}
{if $options[prefkey].prefkey == "secns"}Default Secondary NS:{/if}
{if $options[prefkey].prefkey == "master"}Default master for slaves:{/if}
</td>
<td><input class=form-field type=text size="35" name={$options[prefkey].prefkey} value="{$options[prefkey].prefval}" {if $options[prefkey].prefkey == "hostmaster"}autofocus{/if}></td>
</tr>
{/section}
</table>
</div></div>
<div class=submit><input class=submit-button type=submit name=Submit value="Save">&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel"></div>
</form>
