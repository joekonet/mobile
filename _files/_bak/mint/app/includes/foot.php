<div id="donotremove">
	<?php if (isset($Mint)) { echo $Mint->getFormattedVersion(); } ?> &copy; 2004-<?php echo date("Y"); ?> Shaun Inman. All rights reserved.
	Available at haveamint.com. <?php if (isset($Mint) && $Mint->cfg['mode'] == 'client') { echo '<span>(Client Mode Enabled)</span>'; } ?>
</div>
<?php
if (isset($_GET['benchmark']))
{
	echo $Mint->getFormattedBenchmark();
}
if (isset($_GET['observe']))
{
	echo '<div class="observe">'.$Mint->observe($Mint).'</div>'; 
}
?>
<script type="text/javascript" language="javascript">
// <![CDATA[
SI.onbeforeload();
// ]]>
</script>
