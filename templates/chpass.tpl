<div class=clear><div class=main><h4>Edit your password:</h4>
<table width=580>
<tr>
<td width=120 align=right>Old password:</td>
<td>
<input class=form-field type=password name=password_oldx id=pass_o tabindex=1 autofocus></td>
<th width=180>Password quality meter<div class=pwc id=pwc>Cannot be empty!</div></th>
</tr>
<tr>
<td width=120 align=right>New password:</td>
<td><input class=form-field type=password name=pass1 id=pass_n1 tabindex=2 ></td>
<td rowspan=2 id=pwmeter class=nok width=180 title="Cannot be empty!" alt="Cannot be empty!"></tr>
<tr>
<td width=120 align=right>Confirm it:</td>
<td><input class=form-field type=password name=pass2 id=pass_n2 tabindex=3 disabled></td>
</tr>
</table>
<form name=chg id=chg method="post" action="{$src}savepass.php"><input type=hidden id=pwmd5 name=password_old><input type=hidden id=pwmd5n name=password_one>
<div class=submit><input class=submit-button type=submit name=Submit value="Save" id=submit tabindex=4 disabled>&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel"></div>
</form>
<input type=hidden name=bad id=bad value="The password is too short">
<input type=hidden name=nok id=nok value="Cannot be empty!">
<input type=hidden name=notenough id=notenough value="Need to be longer!">
<input type=hidden name=almost id=almost value="Need to be complex and/or longer!">
<input type=hidden name=enough id=enough value="Complexity is fair">
<input type=hidden name=perfect id=perfect value="Complexity is perfect">
</div></div>
