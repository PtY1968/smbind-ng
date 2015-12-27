{include file="header.tpl"}<body>
<div class=title>{$TITLE}</div>
<div class=section><p>{$pagetitle}</p></div>{include file="menu.tpl"}{include file=$template}
<div class=hint><p>{$help}</p></div>
{if $loggedinuser}<div class=loggeduser>Logged in as: <strong>{$loggedinuser}</strong></div>{/if}{include file="footer.tpl"}
