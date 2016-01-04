{include file="header.tpl"}<body>
<div class=title>{$TITLE}</div>
<div class=section><p>{$pagetitle}</p></div>{include file="menu.tpl"}{include file=$template}
<div class=hint><p>{$help}</p></div>
{if $loggedinuser}<div class=loggeduser>Logged&nbsp;in&nbsp;as:&nbsp;<strong>{$loggedinuser}</strong>{if $admin}&nbsp;|&nbsp;App&nbsp;engine:&nbsp;<strong>{$version}</strong>{/if}</div>{/if}{include file="footer.tpl"}
