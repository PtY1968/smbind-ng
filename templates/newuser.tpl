<div class=clear><div class=main><form name=form1 method="post" action="{$src}userlist.php" id=new>
<h4>Properties of the new user:</h4><div class=submit>
<input class=submit-button type=submit name=Submit value="Save" disabled id=submit tabindex=6>&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel" tabindex=8></div>
<table width=580>
<tr>
<td align=right width=120><strong>Username:</strong></td>
<td><input type=text name=username_ class=form-field autofocus tabindex=1></td>
</tr>
<tr>
<td align=right width=120><strong>Full name:</strong></td>
<td><input type=text name=realname class=form-field tabindex=2></td>
</tr>
<tr>
<td align=right width=120><strong>Admin rights:</strong></td>
<td height="30">
<input type=radio name=admin id=adminy value="yes" tabindex=3 /><label for="adminy">yes</label>
<input type=radio name=admin id=adminn value="no" checked="checked" tabindex=3 /><label for="adminn">no</label>
<input type=hidden name=password_ id=pwmd5n>
</td>
</tr>
</table></form>
<table width=580>
<tr>
<td align=right width=120><strong>Password:</strong></td>
<td><input type=password name=password_one class=form-field id=pass1 tabindex=4></td>
<th width=180>Password quality meter<div class=pwc id=pwc>Cannot be empty!</div></th>
</tr>
<tr>
<td align=right width=120><strong>Confirm it:</strong></td>
<td><input type=password name=confirm_password class=form-field id=pass2 disabled tabindex=5></td>
<td rowspan=2 id=pwmeter class=nok width=180 title="Cannot be empty!" alt="Cannot be empty!">
<input type=hidden name=bad id=bad value="The password is too short">
<input type=hidden name=nok id=nok value="Cannot be empty!">
<input type=hidden name=notenough id=notenough value="Need to be longer!">
<input type=hidden name=almost id=almost value="Need to be complex and/or longer!">
<input type=hidden name=enough id=enough value="Complexity is fair">
<input type=hidden name=perfect id=perfect value="Complexity is perfect">
<input type=hidden name=notempty id=notempty value=1>
</td>
</tr>
<tr>
<td colspan=2 height=30></td>
</tr>
</table>
</div></div>
