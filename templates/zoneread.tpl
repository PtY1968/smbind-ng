<div class=clear><div class=main>
{if $admin  == 'yes'}<p><a class=new id=new href="{$src}newzone.php">Create a new master zone</a></p>{else}<h4>List of your master zones</h4>{/if}
{if $zonelist}<table style="min-width:600">
<tr>
<th>Name</th>
<th>Serial</th>
<th>Changed</th>
<th>&nbsp;</th>
<th colspan=2>Secure</th>
<th>&nbsp;</th>
<th colspan=2>Valid</th>
<th>&nbsp;</th>
<th colspan=2>Action</th>
</tr>
{section name=i loop=$zonelist}
<tr>
<td>{$zonelist[i].name}</td>
<td>{$zonelist[i].serial}</td>

{if $zonelist[i].updated == "yes"}<td class=ok title=Yes alt=Yes>&nbsp;</td>
{elseif $zonelist[i].updated == "no"}<td class=no title=No alt=No>&nbsp;</td>
{else}<td class=some title=Unknown alt=Unknown></td>
{/if}
<td class=empty>&nbsp;</td>
{if $zonelist[i].secured == "yes"}<td class=ok title=Yes alt=Yes>&nbsp;</td>
{if $zonelist[i].valid == "yes" and $zonelist[i].updated == "no"}<td class=view><a alt="View zone as secured" title="View zone as secured" href="{$src}zoneview.php?i={$zonelist[i].id}&s=1">&nbsp;</a></td>
{else}<td class=empty></td>
{/if}
{elseif $zonelist[i].secured == "no"}<td colspan=2 class=no title=No alt=No></td>
{/if}
<td class=empty>&nbsp;</td>
{if $zonelist[i].valid == "yes"}<td class=ok title=Yes alt=Yes>&nbsp;</td>{if $zonelist[i].updated == "no"}<td class=view><a alt="View zone as plain" title="View zone as plain" href="{$src}zoneview.php?i={$zonelist[i].id}&s=0">&nbsp;</a></td>{else}<td class=preview><a alt="Preview zone" title="Preview zone" href="{$src}zonepview.php?i={$zonelist[i].id}&s=0">&nbsp;</a></td>{/if}
{elseif $zonelist[i].valid == "no"}<td class=nok title=Problem alt=Problem></td><td class=empty></td>
{else}<td class=some title=Maybe alt=Maybe></td><td class=check><a alt="Check zone" title="Check zone" href="{$src}zonelist.php?i={$zonelist[i].id}&check=y{if $pages}&page={$current_page}{/if}">&nbsp;</a></td>
{/if}
<td class=empty>&nbsp;</td>
<td class=ed><a title="Edit zone" alt="Edit zone" href="{$src}record.php?i={$zonelist[i].id}">&nbsp;</a></td>
<td class=del><a alt="Remove zone" title="Remove zone" href="{$src}deletezone.php?i={$zonelist[i].id}{if $pages}&page={$current_page}{/if}">&nbsp;</a></td>
</tr>
{/section}
</table>{else}<p>You don't have master zones.</p>{/if}
</div></div>
{include file="pages.tpl"}
