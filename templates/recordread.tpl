<form name=form1 method="post" action="{$src}record.php?i={$zone.id}">
<div class=clear><div class=main>
<h4>Edit zone records</h4>
<table style="min-width:820">
<tr>
<td><strong>New:</strong></td>
<td><input type=text name=newhost class=form-field size="16" autofocus></td>
<td><input type=text name=newttl class=form-field size="1"></td>
<td><select name=newtype class=form-field>
{html_options values=$types output=$types}
</select></td>
<td><input type=text name=newdestination class=form-field size="32"></td>
</tr>
<tr>
<td colspan=5><hr /></td>
</tr>
</table>
<table style="min-width:820">
<tr>
<td align=right><strong>Zone:</strong></td>
<td class=highlight>{$zone.name}</td>
<td align=right><strong>Serial:</strong></td>
<td>{$zone.serial}</td>
</tr>
<tr>
<td align=right><strong>Refresh:</strong></td>
<td><input type=text name=refresh size="28" class=form-field value="{$zone.refresh}"></td>
<td align=right><strong>Retry:</strong></td>
<td><input type=text name=retry size="28" class=form-field value="{$zone.retry}"></td>
</tr>
<tr>
<td align=right><strong>Expire:</strong></td>
<td><input type=text name=expire size="28" class=form-field value="{$zone.expire}"></td>
<td align=right><strong>TTL:</strong></td>
<td><input type=text name=ttl size="28" class=form-field value="{$zone.ttl}"></td>
</tr>
<tr>
<td align=right><strong>NS1:</strong></td>
<td><input type=text name=pri_dns size="28" class=form-field value="{$zone.pri_dns}"></td>
<td align=right><strong>NS2:</strong></td>
<td><input type=text name=sec_dns size="28" class=form-field value="{$zone.sec_dns}"></td>
</tr>
{if $admin == "yes"}
<tr>
<td align=right><strong>Owner:</strong></td>
<td align=left><select name=owner class=form-field>
{section name=i loop=$userlist}
<option value="{$userlist[i].id}"{if $userlist[i].id == $zone.owner} selected{/if}>{$userlist[i].realname}</option>
{/section}
</select>
</td>
<td align=right><strong>Secure:</strong></td>
<td align=left>{if $sec == "yes"}<select name=secured class=form-field>
<option value="yes"{if $zone.secured == "yes"} selected{/if}>yes</option>
<option value="no"{if $zone.secured == "no"} selected{/if}>no</option>
</select>{else}{$zone.secured}{/if}
</td>
</tr>
{/if}
</table>
<input type=hidden name=zone value="{$zone.name}">
<input type=hidden name=zoneid value="{$zone.id}">
{if $rcount > 0}
<table style="min-width:780">
<tr>
<th>Host</th>
<th>TTL</th>
<th>Type</th>
<th colspan=2>Destination</th>
<th>Delete</th>
</tr>
{section name=i loop=$record}
<tr>
<td>
<input type=text name=host[{$smarty.section.i.index}] class=form-field value="{$record[i].host}" size="16">
<input type=hidden name=host_id[{$smarty.section.i.index}] value="{$record[i].id}">
</td>
<td>
<input type=text name=rttl[{$smarty.section.i.index}] class=form-field value="{$record[i].ttl}" size="1">
</td>
<td><select name=type[{$smarty.section.i.index}] class=form-field>
{html_options values=$types selected=$record[i].type output=$types}
</select></td>
{if $record[i].type == "MX"}
<td>
<input type=text name=pri[{$smarty.section.i.index}] class=form-field size="1" maxlength="3" value="{$record[i].pri}">
</td><td>
<input type=text name=destination[{$smarty.section.i.index}] class=form-field size="25" value="{$record[i].destination}">
</td>
{else}
<td colspan=2>
<input type=text name=destination[{$smarty.section.i.index}] class=form-field size="33" value="{$record[i].destination}">
</td>
{/if}
<td align=center><input type=checkbox name=delete[{$smarty.section.i.index}] id=delete[{$smarty.section.i.index}]><label for="delete[{$smarty.section.i.index}]" name=delete[{$smarty.section.i.index}]_lbl>&nbsp;</label></td>
</tr>
{/section}
</table>{else}<p>There are no other records in this zone at the moment.</p>{/if}
<input type=hidden name=total value="{$smarty.section.i.total}">
</div></div>
<div class=submit><input class=submit-button type=submit name=Submit value="Save">&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel"></div>
</form>
{include file="pages.tpl"}
