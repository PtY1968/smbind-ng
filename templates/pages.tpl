{if $pages}
<div class=pages>
<table>
<tr>
<td class=pages>Paging:</td>
{foreach name=pages from=$pages item=page}
{if $smarty.foreach.pages.first}
{if $current_page == $page}
<td class=firstno>&nbsp;</td>
<td class=prevno>&nbsp;</td>
{else}
<td class=first><a class=first id=first href="{$page_root}page={$page}" alt="First page" title="First page">&nbsp;</a></td>
<td class=prev><a class=prev id=prev href="{$page_root}page={math equation="$current_page - 1"}" alt="Previous page" title="Previous page">&nbsp;</a></td>
{/if}
{/if}
{if $current_page == $page}
<td class="pages actualpage">{$page}</td>
{else}
<td class=pages><a href="{$page_root}page={$page}" alt="Go to {$page} page" title="Go to {$page}. page">{$page}</a></td>
{/if}
{if $smarty.foreach.pages.last}
{if $current_page == $page}
<td class=nextno>&nbsp;</td>
<td class=lastno>&nbsp;</td>
{else}
<td class=next><a class=next id=next href="{$page_root}page={math equation="$current_page + 1"}" alt="Next page" title="Next page">&nbsp;</a></td>
<td class=last><a class=last id=last href="{$page_root}page={$page}" alt="Last page" title="Last page">&nbsp;</a></td>
{/if}
{/if}
{/foreach}
</tr>
</table>
</div>
{/if}
