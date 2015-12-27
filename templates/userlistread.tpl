<div class=clear><div class=main>
{if $admin == 'yes'}<p><a class=new id=new href="{$src}newuser.php">Create a new user</a></p>{/if}
<table style="min-width:420;">
<tr>
<th>Username</th>
<th>Full name</th>
<th>Admin</th>
<th></th>
<th colspan=2>Action</th>
</tr>
{section name=i loop=$userlist}
<tr>
<td align=center>{$userlist[i].username}</td>
<td align=center>{$userlist[i].realname}</td>
{if $userlist[i].admin == "yes"}<td class=ok title=Yes alt=Yes>&nbsp;</td>
{elseif $userlist[i].admin == "no"}<td class=no title=No alt=No>&nbsp;</td>
{/if}
<td class=empty></td>
{if $userlist[i].username != "admin"}
<td class=ed><a href="{$src}user.php?i={$userlist[i].id}" title="Edit user" alt="Edit user">&nbsp;</a></td>
<td class=del><a href="{$src}deleteuser.php?i={$userlist[i].id}" title="Delete user" alt="Delete user">&nbsp;</a></td>
{else}
<td class=no title="Cannot edit" alt="Cannot edit">&nbsp;</td>
<td class=no title="Cannot delete" alt="Cannot delete">&nbsp;</td>
{/if}
</tr>
{/section}
</table>
</div></div>
{include file="pages.tpl"}
