<form name=form1 method="post" action="{$src}slave_zonelist.php">
<div class=clear><div class=main><h4>Properties of the new slave zone:</h4>
<table>
<tr>
<td align=right><strong>Zone:</strong></td>
<td><input type=text name=name class=form-field autofocus></td>
</tr>
<tr>
<td align=right><strong>Master:</strong></td>
<td><input type=text name=master class=form-field value="{$master}"></td>
</tr>
<tr>
<td colspan=2><hr /></td>
</tr>
<tr>
<td align=right><strong>Owner: </strong></td>
<td><select name=owner class=form-field>
{section name=i loop=$userlist}
<option value="{$userlist[i].id}" {if $current_user == $userlist[i].id}selected{/if}>{$userlist[i].realname}</option>
{/section}
</select>
</td>
</tr>
</table></div></div>
<div class=submit><input class=submit-button type=submit name=Submit value="Add zone">&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel"></div>
</form>
