<form name=form1 method="post" action="{$src}zonelist.php">
<div class=clear><div class=main><h4>Properties of the new master zone:</h4>
<table>
<tr>
<td align=right><strong>Zone:</strong></td>
<td><input type=text name=name class=form-field autofocus></td>
</tr>
<tr>
<td align=right><strong>Refresh:</strong></td>
<td><input type=text name=refresh class=form-field value="86400"></td>
</tr>
<tr>
<td align=right><strong>Retry:</strong></td>
<td><input type=text name=retry class=form-field value="7200"></td>
</tr>
<tr>
<td align=right><strong>Expire:</strong></td>
<td><input type=text name=expire class=form-field value="3600000"></td>
</tr>
<tr>
<td align=right><strong>Time To Live:</strong></td>
<td><input type=text name=ttl class=form-field value="300"></td>
</tr>
<tr>
<td align=right><strong>Primary NS:</strong></td>
<td><input type=text name=pri_dns class=form-field value="{$pri_dns}"></td>
</tr>
<tr>
<td align=right><strong>Secondary NS:</strong></td>
<td><input type=text name=sec_dns class=form-field value="{$sec_dns}"></td>
</tr>
<tr>
<td align=right><strong>Web Server:</strong></td>
<td><input type=text name=www class=form-field value=""></td>
</tr>
<tr>
<td align=right><strong>Mail Server:</strong></td>
<td><input type=text name=mail class=form-field value=""></td>
</tr>
<tr>
<td align=right><strong>FTP Server:</strong></td>
<td><input type=text name=ftp class=form-field value=""></td>
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
