{if $menu_button}<div class=menu>
<p class=tag>Menu</p>
{section name=i loop=$menu_button}
{if $menu_button[i].title == "Commit changes"}
{if $donotcommit == "yes"}<span class=disabled>{$menu_button[i].title}<br /></span>
{else}<a href="{$menu_button[i].link}">{$menu_button[i].title}</a><br />
{/if}
{elseif $menu_button[i].link != $menu_current}<a href="{$menu_button[i].link}">{$menu_button[i].title}</a><br />
{else}<span class=currmenu>{$menu_button[i].title}</span><br />
{/if}
{/section}
</div>
{/if}