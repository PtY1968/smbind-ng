<div class=clear><div class=main><h4>Edit user's credentials:</h4>
<form name=form1 method="post" action="{$src}user.php?i={$user.id}" id=new>
<table width=580>
<tr>
<td align=right width=120><strong>Username:</strong></td>
<td colspan=2 class=highlight>{$user.username}</td>
<th width=180>Password quality meter<div class=pwc id=pwc>Won't modified!</div></th>
</tr>
<tr>
<td align=right width=120><strong>Full name:</strong></td>
<td colspan=2><input type=text name=realname class=form-field value="{$user.realname}" autofocus tabindex=1</td>
<td rowspan=2 id=pwmeter class=nok width=180 title="Won't modified!" alt="Won't modified!"></td>
</tr>
<tr>
<td align=right width=120><strong>Admin rights:</strong></td>
<td colspan=2 height=32 valign=bottom>
<input type=radio name=admin id=adminy value="yes"{if $user.admin} checked="checked" tabindex=2{/if} /><label for="adminy">yes</label>&nbsp;&nbsp;
<input type=radio name=admin id=adminn value="no"{if !$user.admin} checked="checked" tabindex=2{/if} /><label for="adminn">no</label><input type=hidden name=password id=pwmd5n>
</td>
</tr>
</table>
<div class=submit><input class=submit-button type=submit name=Submit value="Save" id=submit tabindex=5>&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel" tabindex=6></div>
</form>
<table width=580>
<tr>
<td align=right width=120><strong>New password:</strong></td>
<td><input type=password name=password_one class=form-field id=pass1 autofocus tabindex=3></td>
<td width=180></td>
</tr>
<tr>
<td align=right width=120><strong>Confirm it:</strong></td>
<td><input type=password name=confirm_password class=form-field disabled id=pass2 tabindex=4></td>
<td width=180></td>
</tr>
{if $success}<tr><td colspan=3><hr /></td></tr><tr><td colspan=3 align=center><span class=attention>{$success}</span></td></tr>{/if}
</table>
{if $zonelist}<table width=580><tr>
<td colspan=3><hr /></td>
</tr>
<tr>
<th colspan=3><p>Master zones owned by this user:</p></th>
</tr>
<tr>
<th>Name</th>
<th>Serial</th>
<th>Action</th>
</tr>
{section name=i loop=$zonelist}
<tr>
<td align=center>{$zonelist[i].name}</td>
<td align=center>{$zonelist[i].serial}</td>
<td class=ed><a href="{$src}record.php?i={$zonelist[i].id}">&nbsp;</a></td>
</tr>{/section}</table>{/if}
{if $szonelist}<table width=580><tr>
<td colspan=3><hr /></td>
</tr>
<tr>
<th colspan=3><p>Slave zones owned by this user:</p></th>
</tr>
<tr>
<th>Name</th>
<th>Master</th>
<th>Action</th>
</tr>
{section name=i loop=$szonelist}
<tr>
<td align=center>{$szonelist[i].name}</td>
<td align=center>{$szonelist[i].master}</td>
<td class=ed><a href="{$src}slave_record.php?i={$szonelist[i].id}">&nbsp;</a></td>
</tr>{/section}</table>{/if}
<input type=hidden name=bad id=bad value="The password is too short">
<input type=hidden name=nok id=nok value="Won't modified!">
<input type=hidden name=notenough id=notenough value="Need to be longer!">
<input type=hidden name=almost id=almost value="Need to be complex and/or longer!">
<input type=hidden name=enough id=enough value="Complexity is fair">
<input type=hidden name=perfect id=perfect value="Complexity is perfect">
<input type=hidden name=notempty id=notempty value=0>
</div></div>
