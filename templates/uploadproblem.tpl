<form name=rew target="{$src}--recordwrite.php" method="POST">
<div class=clear><div class=main>
<h4>Committed zone has errors</h4>
<p><strong>Zone:</strong> <span class=highlight>{$zone}</span><br />
<strong>Chosen method:</strong> {$method}</p><p>The zonefile is OK, but the import procedure has errors.<br />Please check it, fix it, and try again.</p>
<p><strong>Output:</strong></p>
<pre>{section name=i loop=$problem}
{$problem[i]}
{/section}</pre>
</div></div>
</form>
