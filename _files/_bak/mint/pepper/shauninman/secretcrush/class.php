<?php
/******************************************************************************
 Pepper
 
 Developer		: Shaun Inman
 Plug-in Name	: Secret Crush
 
 http://www.shauninman.com/

 ******************************************************************************/

$installPepper = "SI_SecretCrush";

class SI_SecretCrush extends Pepper
{
	var $version	= 200; 
	var $info		= array
	(
		'pepperName'	=> 'Secret Crush',
		'pepperUrl'		=> '',
		'pepperDesc'	=> 'Take a peak inside the cookie jar and divine the names of secret crushes tirelessly nibbling away at your bandwidth.',
		'developerName'	=> 'Shaun Inman',
		'developerUrl'	=> ''
	);
	var $panes		= array
	(
		'Crushes'	=> array
		(
			'Newest Unique',
			'Most Recent',
			'Repeat'
		)
	);
	var $prefs		= array
	(
		'onlyNamed'			=> 0,
		'sessionTimeout'	=> 7
	);
	var $manifest	= array
	(
		'visit'	=> array
		(
			'ip_long' 			=> "INT(10) NOT NULL",
			'session_checksum'	=> "INT(10) NOT NULL",
			'visitor_name'		=> "VARCHAR(255) NOT NULL"
		)
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version < 200)
		{
			$compatible = array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper requires Mint 2, a paid upgrade, now available at haveamint.com.</p>'
			);
		}
		else
		{
			$compatible = array
			(
				'isCompatible'	=> true,
			);
		}
		return $compatible;
	}
	
	/**************************************************************************
	 update()
	 **************************************************************************/
	function update()
	{
		if (!isset($this->prefs['sessionTimeout']))
		{	
			$this->prefs['sessionTimeout'] = 7;
		}
		if ($this->Mint->cfg['manifest']['visit']['ip_long'] == 0)
		{
			$this->Mint->cfg['manifest']['visit']['ip_long'] = $this->pepperId;
		}
	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord() 
	{
		if (isset($_COOKIE['MintCrush']))
		{
			$session_checksum = $_COOKIE['MintCrush'];
		}
		else
		{
			$session_checksum = crc32(time().$this->Mint->getIP().mt_rand());
		}
		
		$this->Mint->bakeCookie('MintCrush', $session_checksum, time() + round($this->prefs['sessionTimeout'] * 60));
		
		$ip_long		= $this->Mint->getIPLong();
 		$visitor_name	= '';
 		
 		if (is_array($_COOKIE))
 		{
			foreach ($_COOKIE as $cookie => $name)
			{
				// Match known identifiers
				if (preg_match("/(mtcmtauth|txp_name|comment_author_|comment_name|sFullName|RememberAuthorName|username|login|author)/i", $cookie))
				{ 
					// Normalize spaces
					$visitor_name = $this->Mint->escapeSQL(preg_replace("/(%20|\+)/", '', $name)); 
				}
			}
		}
		return array
		(
 			'ip_long'			=> $ip_long,
			'session_checksum'	=> $session_checksum,
			'visitor_name' 		=> $visitor_name
		);
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) 
		{
			/* Visitors *******************************************************/
			case 'Crushes': 
				switch($tab)
				{
					/* Newest Unique ******************************************/
					case 'Newest Unique':
						$html .= $this->getHTML_VisitorsUnique();
					break;
					/* Most Recent ********************************************/
					case 'Most Recent':
						$html .= $this->getHTML_VisitorsRecent();
					break;
					/* Repeat *************************************************/
					case 'Repeat':
						$html .= $this->getHTML_VisitorsRepeat();
					break;
					/* Paths *************************************************/
					case 'Paths':
						$html .= $this->getHTML_VisitorsPaths();
					break;
				}
			break;
		}
		return $html;
	}
	
	/**************************************************************************
	 onCustom()
	 **************************************************************************/
	function onCustom()
	{
		/* VISITOR PAGES -----------------------------------------------------*/
		if
		(
			isset($_POST['action']) 		&& 
			$_POST['action']=='getVisitorsPages'	&& 
			isset($_POST['session_checksum'])
		)
		{
			$session_checksum = $this->escapeSQL($_POST['session_checksum']);
			echo $this->getHTML_VisitorsPages($session_checksum);
		}
	}
	
	/**************************************************************************
	 onRss()
	 **************************************************************************/
	function onRss()
	{
		$rssData = array();	
		$rssData['title'] = 'Crushes';
		$named = ($this->prefs['onlyNamed'])?"WHERE `visitor_name`!='' ":'';
		$query = "SELECT `id`, `ip_long`, `visitor_name`, `resource`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					$named
					GROUP BY `ip_long` 
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rssRows']}";
		
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$visitor		= $this->identifyVisitor($r['visitor_name'], long2ip($r['ip_long']));
				$visitor_clean	= str_replace('&', '&amp;', $visitor);
				$resource_cleaned	= str_replace('&', '&amp;', $r['resource']);
				$res_title			= (!empty($r['resource_title'])) ? stripslashes($r['resource_title']) : $resource_cleaned;
				
				$body = <<<HERE

				<table>
					<tr>
						<th scope="row" align="right">Who</th>
						<td>$visitor_clean</td>
					</tr>
					<tr>
						<th scope="row" align="right">Where</th>
						<td><a href="{$resource_cleaned}">$res_title</a></td>
					</tr>
				</table>

HERE;
				
				$rssData['items'][] = array
				(
					'title' => $visitor,
					'body'	=> $body,
					'link'	=> $r['resource'],
					'date'	=> $r['dt']
				);
			}
		}
		
		return $rssData;
	}
	
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
	function onDisplayPreferences() 
	{
		/* Global *************************************************************/
		$checked = ($this->prefs['onlyNamed'])?' checked="checked"':'';
		$preferences['Crushes'] = <<<HERE
<table class="snug">
	<tr>
		<td>A new session starts after </td>
		<td><span class="inline"><input type="text" id="sessionTimeout" name="sessionTimeout" maxlength="4" value="{$this->prefs['sessionTimeout']}" class="cinch" /></span></td>
		<td>minutes of inactivity</td>
	</tr>
</table>
<table>
	<tr>
		<td><label><input type="checkbox" name="onlyNamed" value="1"$checked /> Only show "Named" visitors (improves Crushes performance but not recommended for those who don't use cookies with a login or comment form to remember visitor names)</label></td>
	</tr>
</table>
HERE;
		return $preferences;
	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['onlyNamed']		= (isset($_POST['onlyNamed']))?$_POST['onlyNamed']:0;
		$this->prefs['sessionTimeout']	= (int) $_POST['sessionTimeout'];
	}
	
	/**************************************************************************
	 getHTML_VisitorsRecent()
	 ************************************************************************** /
	function getHTML_VisitorsRecent()
	{
		$html = '';
		
		$named = ($this->prefs['onlyNamed'])?"WHERE `visitor_name`!='' ":'';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'From','class'=>'focus'),
			array('value'=>'When','class'=>'sort')
		);
		
		// Referrers Pane
		$query = "SELECT `ip_long`, `visitor_name`, `resource`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					$named
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$dt 		= $this->Mint->formatDateTimeRelative($r['dt']);
				$visitor 	= $this->identifyVisitor($r['visitor_name'], long2ip($r['ip_long']));
				$res_title 	= (!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource'];
				$tableData['tbody'][] = array
				(
					"$visitor".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>On <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$dt
				);
			}
		}
			
		$html = $this->Mint->generateTable($tableData);
		return $html;
		}


	/**************************************************************************
	 getHTML_VisitorsUnique()
	 **************************************************************************/
	function getHTML_VisitorsUnique()
	{
		$html = '';
		
		$named = ($this->prefs['onlyNamed'])?"WHERE `visitor_name`!='' ":'';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Who','class'=>'focus'),
			array('value'=>'When','class'=>'sort')
		);
		
		$query = "SELECT `ip_long`, `visitor_name`, `resource`, `resource_title`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					$named
					GROUP BY `ip_long` 
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$dt 		= $this->Mint->formatDateTimeRelative($r['dt']);
				$visitor 	= $this->identifyVisitor($r['visitor_name'], long2ip($r['ip_long']));
				$res_title 	= (!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource'];
				$tableData['tbody'][] = array
				(
					"$visitor".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>On <a href=\"{$r['resource']}\">$res_title</a></span>":''),
					$dt
				);
			}
		}
		
		$html .= $this->Mint->generateTable($tableData);
		$html .= $this->Mint->generateRSSLink($this->pepperId, 'Newest Unique Crushes');
		return $html;
		}

	/**************************************************************************
	 getHTML_VisitorsRepeat()
	 **************************************************************************/
	function getHTML_VisitorsRepeat()
	{
		$html = '';
		
		$filters = array
		(
			'Show all'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Repeat', $filters);
		
		$where = array();
		if ($this->filter)
		{
			$where[] = "dt > ".(time() - ($this->filter * 60 * 60));
		}
		if ($this->prefs['onlyNamed'])
		{
			$where[] = "`visitor_name`!=''";
		}
		$whereQuery = (!empty($where)) ? 'WHERE '.join(' AND ', $where) : '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Hits','class'=>'sort'),
			array('value'=>'Who','class'=>'focus')
		);
		
		$query = "SELECT `ip_long`, `visitor_name`, `resource`, `resource_title`, COUNT(`ip_long`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					$whereQuery
					GROUP BY `ip_long` 
					ORDER BY `total` DESC, `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$visitor 	= $this->identifyVisitor($r['visitor_name'], long2ip($r['ip_long']));
				$res_title	= (!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource'];
				$tableData['tbody'][] = array
				(
					$r['total'],
					"$visitor".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>On <a href=\"{$r['resource']}\">$res_title</a></span>":'')
				);
			}
		}
			
		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_VisitorsPaths()
	 **************************************************************************/
	function getHTML_VisitorsRecent()
	{
		$html = '';
		
		$tableData['hasFolders'] = true;
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Pages','class'=>'sort'),
			array('value'=>'Who/Where','class'=>'focus'),
			array('value'=>'When/Duration','class'=>'sort')
		);
		
		$named = ($this->prefs['onlyNamed'])?" AND `visitor_name`!='' ":'';
		$query = "SELECT `ip_long`, `visitor_name`, COUNT(`resource_checksum`) as `pages`, `session_checksum`, `dt` 
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `session_checksum` != 0 $named
					GROUP BY `session_checksum` 
					ORDER BY `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				
				$dt 		= $this->Mint->formatDateTimeRelative($r['dt']);
				$visitor 	= $this->identifyVisitor($r['visitor_name'], long2ip($r['ip_long']));
				
				$tableData['tbody'][] = array
				(
					$r['pages'],
					"$visitor",
					$this->Mint->formatDateTimeRelative($r['dt']),

					'folderargs' => array
					(
						'action'			=> 'getVisitorsPages',
						'session_checksum'	=> $r['session_checksum']
					)
				);
			}
		}
		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_VisitorsPages()
	 
	 Given the name and ip will return a non-empty name value or failing that
	 try to look up a host name and last result just return the ip
	 **************************************************************************/
	function getHTML_VisitorsPages($session_checksum)
	{
		$html = '';
		
		/**/
		$query = "SELECT `referer`, `resource`, `resource_title`,`dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `session_checksum` = '{$session_checksum}'
					ORDER BY `dt` DESC ";
					//LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$v = array();
		$tableData['classes'] = array
		(
			'sort',
			'focus',
			'sort'
		);
		
		$first = true;
		$next_dt = time();
		$last = array();
		$wasReferred = false;
		if ($result = $this->query($query))
		{
			
			while ($r = mysql_fetch_assoc($result))
			{
				$wasReferred = (!empty($r['referer']));
				$ref_title = $this->Mint->abbr($r['referer']);
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				
				if ($first)
				{
					$first = false;
					$duration = (time() - $r['dt'] > $this->prefs['sessionTimeout'] * 60) ? 'Timed out' : 'Viewing';
				}
				else
				{
					$duration = $this->Mint->formatDateTimeSpan($r['dt'], $next_dt);
				}
				
				$next_dt = $r['dt'];
				
				$tableData['tbody'][] = array
				(
					'&nbsp;',
					"<span>On <a href=\"{$r['resource']}\">$res_title</a></span>",
					$duration
				);
				
				$last = array
				(
					'&nbsp;',
					"<span>From <a href=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a></span>",
					'&nbsp;'
				);
			}
		}
		
		if ($wasReferred)
		{
			$tableData['tbody'][] = $last;
		}
		
		$html .= $this->Mint->generateTableRows($tableData);
		/**/
		return $html;
	}
	
	/**************************************************************************
	 identifyVisitor()
	 
	 Given the name and ip will return a non-empty name value or failing that
	 try to look up a host name and last result just return the ip
	 **************************************************************************/
	function identifyVisitor($name, $ip)
	{
		$visitor = $name;
		if (empty($visitor))
		{
			$visitor = preg_replace("/^(-?[a-z]*)*(-?[0-9]{1,3}){4}[a-z]*\./i", '', gethostbyaddr($ip));
		}
		return $visitor;
	}
}
