<?php
/******************************************************************************
 Mint
  
 Copyright 2004-2007 Shaun Inman. This code cannot be redistributed without
 permission from http://www.shauninman.com/
 
 More info at: http://www.haveamint.com/
 
 ******************************************************************************
 Record
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 

if (isset($_GET['js']))
{
	include(MINT_ROOT.'app/paths/record/js.php');
}
else if (isset($_GET['record']))
{
	$Mint->record();
	
	if (isset($_GET['debug']) || $Mint->cfg['debug'])
	{
		echo $Mint->observe($_GET);
		echo $Mint->getFormattedBenchmark();
		echo '<hr />';
		echo $Mint->observe($Mint);
	}
	if (isset($_GET['serve_img']))
	{
		header("Content-type: image/gif");
		include(MINT_ROOT.'app/images/loaded.gif');
	}
}
mysql_close();
?>