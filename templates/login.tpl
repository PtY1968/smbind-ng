<div class=login><div class=loginform>
<h4>Enter your credentials:</h4>
<form method="post" name=login action="{if $action}{$action}{/if}" id=normal>
<table align=center width=400>
<tr>
<td align=right>Username:</td>
<td align=right><input class=form-field type=text name=username autofocus tabindex=1></td>
</tr>
</table>
<div class=loginend>
<table align=center width=400>
{if $recaptcha != "" }
<tr>
<td colspan=2><label for="recaptcha">Fill the captcha:</label></td>
</tr>
<tr>
<td align=center colspan=2 id=captchaload>{nocache}{$recaptcha}{/nocache}</td>
</tr>
{else}
<tr>
<td colspan=2><span class=whitelist>You're allowed without captcha:</span></td>
</tr>
<tr>
<td colspan=2 class=nocap><span class=disablestamp>don't need</span></td>
</tr>
{/if}
<tr>
<td colspan=2><hr /></td>
</tr>
<tr>
<td colspan=2 align=center><input type=hidden name=password id=pwmd5><input class=submit-button type=submit name=Submit value="Login" id=submit tabindex=3></td>
</tr>
</table></div></form>
<table align=center width=400>
<tr>
<td align=right>Password:</td>
<td align=right><input class=form-field type=password name=password_local id=password tabindex=2></td>
</tr>
</table>
</div>
</div>
