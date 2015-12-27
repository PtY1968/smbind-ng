<div class=clear><div class=main>
<p><strong>Welcome, {$user}.</strong>
{if $admin == "yes"} (You have administrator privileges){/if}</p>
{if $status == 0}DNS Service is <b>running</b>.{/if}
{if $status == 1}DNS Service is <b><span class=attention>stopped</span></b>.{/if}
<p>You maintain <strong>{$zones}</strong> master zone{if $zones > 1}s{/if} and <strong>{$slave_zones}</strong> slave zone{if $slave_zones > 1}s{/if}.</p>
{if $bad || $bad_slaves}<h4>Zones need to be check or modify</h4><p>{if $bad}Master: {section name=i loop=$bad}<a class=attention href="{$src}record.php?i={$bad[i].id}"><b>{$bad[i].name}</b></a>&nbsp;&nbsp;&nbsp;{/section}<br />{/if}
{if $bad_slaves}Slave: {section name=i loop=$bad_slaves}<a class=attention href="{$src}slave_record.php?i={$bad_slaves[i].id}"><b>{$bad_slaves[i].name}</b></a>&nbsp;&nbsp;&nbsp;{/section}{/if}</p>{/if}
{if $comm || $comm_slaves}<h4>Zones need to be commit</h4><p>{if $comm}Master: {section name=i loop=$comm}<a class=attention href="{$src}record.php?i={$comm[i].id}"><b>{$comm[i].name}</b></a>&nbsp;&nbsp;&nbsp;{/section}<br />{/if}
{if $comm_slaves}Slave: {section name=i loop=$comm_slaves}<a class=attention href="{$src}slave_record.php?i={$comm_slaves[i].id}"><b>{$comm_slaves[i].name}</b></a>&nbsp;&nbsp;&nbsp;{/section}{/if}</p>{/if}
{if $del || $del_slaves}<h4>Zones mark as deleted</h4><p>{if $del}Master: {section name=i loop=$del}<span class=deleted><span class=attention><b>{$del[i].name}</b></span></span>&nbsp;&nbsp;&nbsp;{/section}<br />{/if}
{if $del_slaves}Slave: {section name=i loop=$del_slaves}<span class=deleted><span class=attention><b>{$del_slaves[i].name}</b></span></span>&nbsp;&nbsp;&nbsp;{/section}{/if}</p>{/if}
</div></div>
