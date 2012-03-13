<?php
/*************************************************************
 Pepper

 Developer		: John Barker
 Plug-in Name	: MySpace Tracker
 Website:		: http://www.inspiredmind.net/

 Track the visitors on your MySpace account.

 *************************************************************/

if (!defined('MINT')) { header('Location:/'); } // Don't allow direct viewing.

$installPepper = 'MySpace_Tracker';

class MySpace_Tracker extends Pepper {

	var $version	= 100;
	var $info		= array
	(
		'pepperName'	=> 'MySpace Tracker',
		'pepperUrl'		=> 'http://www.inspiredmind.net/mint/peppers/myspace',
		'pepperDesc'	=> 'Track who\'s visiting your MySpace account.',
		'developerName'	=> 'John Barker',
		'developerUrl'	=> 'http://www.inspiredmind.net'
	);
	var $panes		= array
	(
		'MySpace' => array
		(
			'Your MySpace Visitors'
		)
	);
	var $prefs		= array
	(
		'Void' =>	'1'
	);
	var $manifest	= array
	(
		'myspace_hits'		=> array
		(
			'id'			=> 'INT(11) NOT NULL AUTO_INCREMENT',
			'ip'			=> 'VARCHAR(64) NOT NULL',
			'host'			=> 'VARCHAR(64) NOT NULL',
			'hits'			=> 'INT(11) NOT NULL',
			'last_visit'	=> 'VARCHAR(64) NOT NULL',
			'location'		=> 'VARCHAR(64) NOT NULL'
		),
		'myspace_track'		=> array
		(
			'ip'			=> 'VARCHAR(64) NOT NULL',
			'host'			=> 'VARCHAR(64) NOT NULL',
			'time'			=> 'INT(11) NOT NULL',
			'location'		=> 'VARCHAR(64) NOT NULL'
		),
		'myspace_user'		=> array
		(
			'id'			=> 'INT(11) NOT NULL AUTO_INCREMENT',
			'ip'			=> 'VARCHAR(64) NOT NULL'
		)
	);

	function isCompatible() 
	{
		return array
		(
			'isCompatible'	=> true
		);
	}

	function onDisplay($pane, $tab, $column='', $sort='')
	{
		$html = '';
		
		switch ($pane)
		{
			case 'MySpace':
				switch ($tab)
				{
					case 'Your MySpace Visitors':
						$html .= $this->getHTML_Unique();
						break;
				}
				break;
		}

		return $html;
	}

	function onCustom() 
	{
		if
		(
			isset($_POST['action'])			&&
			isset($_POST['action'])			&&
			isset($_POST['mslocation'])
		)
		{
			$location = urldecode($_POST['mslocation']);
			$ip = $_POST['ip'];

			switch($_POST['action'])
			{
				case 'location':
					echo $this->getHTML_UserMore($location, $ip);
				break;
			}
		}
	}

	function getHTML_UserMore($location, $ip)
	{
		$html = '';

		$sql = mysql_query("SELECT * FROM `{$this->Mint->db['tblPrefix']}myspace_track` WHERE IP='$ip' ORDER BY `time` DESC");
		$tableData['tbody'][] = array("<strong>Visitor From</strong>: <a href=\"http://www.hostip.info/correct.html?spip={$ip}\">{$location}</a>", '', '');

		if ($sql) {
			while ($row = mysql_fetch_array($sql)) {
				$tableData['tbody'][] = array($this->Mint->formatDateTimeRelative($row['time']), '', '');
			}
		}

		$html = $this->Mint->generateTableRows($tableData);

		return $html;
	}

	function getHTML_Unique()
	{
		$html = '';
		$tableData['hasFolders'] = true;
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			array('value'=>'Hostname','class'=>'focus'),
			array('value'=>'Last Visit','class'=>'sort'),
			array('value'=>'Hits','class'=>'sort')
			);

		$query = "SELECT * FROM `{$this->Mint->db['tblPrefix']}myspace_hits` ORDER BY `last_visit` DESC LIMIT 0,20";

		if ($result = mysql_query($query)) {
			while ($r = mysql_fetch_array($result)) {
				$tableData['tbody'][] = array($this->Mint->abbr($r['host'], 30), 
											  $this->Mint->formatDateTimeRelative($r['last_visit']), 
											  $r['hits'],
											  'folderargs'=>array('action'=>'location','mslocation'=>urlencode($r['location']),'ip'=>$r['ip']));
			}
		}
		else {
			$tableData['tbody'][] = array('Sorry, you have no visitors.', '');
		}
		
		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}

}

?>