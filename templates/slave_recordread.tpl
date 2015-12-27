<form name=form1 method="post" action="{$src}slave_zonelist.php?i={$zone.id}">
<div class=clear><div class=main><h4>Edit properties of the slave zone:</h4>
<table>
<tr>
<td align=right><strong>Zone:</strong></td>
<td class=highlight>{$zone.name}</td>
</tr>
<tr>
<td align=right><strong>Master:</strong></td>
<td><input type=text name=master size="25" class=form-field value="{$zone.master}"></td>
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
</tr>
{/if}
</table>
<input type=hidden name=name value="{$zone.name}">
</div></div>
<div class=submit><input class=submit-button type=submit name=Submit value="Save">&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel"></div>
</form>
