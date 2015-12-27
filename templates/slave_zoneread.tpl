<div class=clear><div class=main>
{if $admin == 'yes'}<p><a class=new id=new href="{$src}slave_newzone.php">Create a new slave zone</a></p>{else}<h4>List of your slave zones</h4>{/if}
{if $zonelist}<table style="min-width:500">
<tr>
<th>Name</th>
<th>Master</th>
<th>Changed</th>
<th>&nbsp;</th>
<th colspan=2>Valid</th>
<th>&nbsp;</th>
<th colspan=2>Action</th>
</tr>
{section name=i loop=$zonelist}
<tr>
<td>{$zonelist[i].name}</td>
<td>{$zonelist[i].master}</td>

{if $zonelist[i].updated == "yes"}<td class=ok title=Yes alt=Yes>&nbsp;</td>
{elseif $zonelist[i].updated == "no"}<td class=no title=No alt=No>&nbsp;</td>
{else}<td class=some title=Unknown alt=Unknown></td>
{/if}
<td class=empty>&nbsp;</td>
{if $zonelist[i].valid == "yes"}{if $zonelist[i].updated == "no"}<td class=ok title=Yes alt=Yes>&nbsp;</td><td class=view><a alt="View slave zone" title="View slave zone" href="{$src}slave_zoneview.php?i={$zonelist[i].id}">&nbsp;</a></td>{else}<td class=ok title=Yes alt=Yes>&nbsp;</td><td class=preview><a alt="preview zone" title="Preview zone" href="{$src}slave_zoneview.php?i={$zonelist[i].id}&pre=y">&nbsp;</a></td>{/if}
{elseif $zonelist[i].valid == "no"}<td class=nok title=No alt=No></td><td class=empty></td>
{else}<td class=some title=Maybe alt=Maybe></td><td class=check><a id=_doitlink_{$zonelist[i].id} alt="Check zone" title="Check zone" href="{$src}slave_zonelist.php?i={$zonelist[i].id}&check=y{if $pages}&page={$current_page}{/if}">&nbsp;</a></td>
{/if}
<td class=empty>&nbsp;</td>
<td class=ed><a title="Edit zone" alt="Edit zone" href="{$src}slave_record.php?i={$zonelist[i].id}">&nbsp;</a></td>
<td class=del><a alt="Remove zone" title="Remove zone" href="{$src}slave_deletezone.php?i={$zonelist[i].id}">&nbsp;</a></td>
</tr>
{/section}
</table>{else}<p>You don't have slave zones.</p>{/if}
</div></div>
{include file="pages.tpl"}
