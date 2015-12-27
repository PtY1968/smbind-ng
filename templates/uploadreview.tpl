<div class=clear><div class=main>
<h4>Review the imported zone</h4>
<p><strong>Zone:</strong> <span class=highlight>{$zone}</span><br />
<strong>Chosen method:</strong> {$method}</p>
<p><strong>Preview your imported zone:</strong></p>
<textarea readonly autofocus wrap="off" cols="116" rows="30" class=ro>{section name=i loop=$output}
{$output[i]}
{/section}</textarea>
<p>Check and edit your imported zone between your <a href="zonelist.php" class=attention>master zones</a></p>
</div></div>
