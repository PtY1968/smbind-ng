<form name=upl enctype=multipart/form-data action="{$src}convert.php" method="post">
<div class=clear><div class=main>
<h4 id=choose>Import a zone file</h4>
<p{if $count == 0} id=nofile{/if}>Choose an import method, a file/zone (don't forget to fill the domain part).</p>
<table>
<tr>
<td><input type=radio name=method id=list value="list"><label for="list">Orphan files</label></td>
<td class=empty>
<td><select class=form-field id=sel name=sel>
{if $count == 0}<option> NOT FOUND </option>{else}<option>- select file -</option>{/if}
{section name=i loop=$files}
<option>{$files[i]}</option>
{/section}
</select>&nbsp;<strong>Domain:&nbsp;</strong><input class=form-field id=sel_domain value="" name=sel_domain></td>
</tr>
<tr>
<td><input type=radio name=method id=file value="file"><label for="file">Browse</label></td>
<td class=empty>
<td><strong>Domain:&nbsp;</strong><input class=form-field id=fil_domain value="" name=fil_domain>&nbsp;<input type=file class=form-field id=fil name=fil></td>
</tr>
<tr>
<td style="vertical-align:top;padding-top:7px;"><input type=radio name=method id=text value="text"><label for="text">Edit</label></td>
<td class=empty>
<td><strong>Domain:&nbsp;</strong><input class=form-field id=txt_domain value="" name=txt_domain><br /><textarea id=txt name=txt cols="100" rows="18"></textarea></td>
</tr>
</table>
</div></div>
<div class=submit><input class=submit-button type=submit name=Submit value="Upload">&nbsp;<input type=button class=submit-button id=cancel value="Cancel" name ="cancel"></div>
</form>