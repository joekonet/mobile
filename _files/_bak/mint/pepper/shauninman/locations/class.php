<?php

/******************************************************************************
 Pepper

 Developer		: Till Krüss
 Plug-in Name	: Locations

 More info at: http://pepper.pralinenschachtel.de/

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

 Country lookup portion (c) L. Petersen, Weird Silence,
 http://weirdsilence.net/software/ip2c - Released under the GNU GPL.

 ******************************************************************************/

	if (!defined('MINT')) {
		header('Location: /');
		exit();
	};

	$installPepper = 'TK_Locations';

	class TK_Locations extends Pepper {

		var $version = 227;

		var $info = array(
			'pepperName'	=> 'Locations',
			'pepperUrl'		=> 'http://pepper.pralinenschachtel.de/',
			'pepperDesc'	=> 'This Pepper tracks the geographical locations, based on IP addresses.',
			'developerName'	=> 'Till Kr&uuml;ss',
			'developerUrl'	=> 'http://pralinenschachtel.de/'
		);

		var $panes = array(
			'Locations' => array(
				'Most Common',
				'Most Recent'
			)
		);

		var $data = array(
			'locations' => array(
				'total' => array(),
				'unique' => array()
			)
		);

		var $prefs = array(
			'threshold' => 1,
			'sortby' => 0
		);

		var $manifest = array(
			'visit' => array(
				'country_code' => "varchar(2) NOT NULL default 'XX'"
			)
		);

		function isCompatible() {

			if ($this->Mint->version < 212) {
				return array('isCompatible' => false, 'explanation' => '<p>This Pepper requires Mint 2.12 or higher.</p>');
			} else {
				return array('isCompatible' => true);
			}

		}

		function update() {

			if ($this->Mint->version < 212) {

				$this->Mint->logError('This version of Locations requires Mint 2.12 or higher.', 2);

			} elseif ($this->get_countrycode() == FALSE) {

				$this->Mint->logError('Location\'s database appears to be damaged.', 2);

			} elseif ($this->getInstalledVersion() < 223) {

				foreach ($this->data['locations'] as $key1 => $value1) {
					if ($key1 != 'total' && $key1 != 'unique') {
						unset($this->data['locations'][$key1]);
					} else {
						foreach ($this->data['locations'][$key1] as $key2 => $value2) {
							if (empty($key2) || $value2 == '-') {
								unset($this->data['locations'][$key1][$key2]);
							}
						}
					}
				}

				$this->query('UPDATE '.$this->Mint->db['tblPrefix']."visit SET country_code = 'XX' WHERE country_code = '--' OR country_code = ''");

			} elseif ($this->getInstalledVersion() == 223) {

				unset($this->data['locations']['total'][''], $this->data['locations']['unique']['']);

				$this->query('UPDATE '.$this->Mint->db['tblPrefix']."visit SET country_code = 'XX' WHERE country_code = ''");

			}

		}

		function onRecord() {

			if (!$this->Mint->shouldIgnore()) {

				if ($code = $this->get_countrycode()) {

					$this->data['locations']['total'][$code] = isset($this->data['locations']['total'][$code]) ? $this->data['locations']['total'][$code] + 1 : 1;

					if ($this->Mint->acceptsCookies && !isset($_COOKIE['MintUniqueLocation'])) {

						$this->Mint->bakeCookie('MintUniqueLocation', 1, time() + 315360000);

						$this->data['locations']['unique'][$code] = isset($this->data['locations']['unique'][$code]) ? $this->data['locations']['unique'][$code] + 1 : 1;

					}

					return array('country_code' => $code);

				}

			}

		}

		function onDisplay($pane, $tab, $column = '', $sort = '') {

			switch ($pane) {
				case 'Locations': 
					switch ($tab) {
						case 'Most Common':
							return $this->build_mostcommon();
						break;
						case 'Most Recent':
							return $this->build_mostrecent();
						break;
					}
				break;
			}

		}

		function onDisplayPreferences() {

			$sortby0 = $this->prefs['sortby'] == 0 ? ' selected="selected"' : '';
			$sortby1 = $this->prefs['sortby'] == 1 ? ' selected="selected"' : '';
			$threshold = $this->prefs['threshold'];

			$preferences['Display'] = <<<HERE
<table class="snug">
	<tr>
		<th scope="row">Order countries by</th>
		<td><span><select name="locations_sortby">
					<option value="0"{$sortby0}>Total hits</option>
					<option value="1"{$sortby1}>Unique visits</option>
				</select></span></td>
	</tr>
</table>
<table class="snug">
	<tr>
		<td>Fade countries smaller than</td>
		<td><span class="inline"><input type="text" name="locations_threshold" value="{$threshold}" class="cinch" /></span></td>
		<td>percent</td>
	</tr>
</table>
HERE;

			return $preferences;

		}

		function onSavePreferences() {

			if (isset($_POST['locations_sortby']) && is_numeric($_POST['locations_sortby'])) {
				$this->prefs['sortby'] = $_POST['locations_sortby'];
			}

			if (isset($_POST['locations_threshold']) && is_numeric($_POST['locations_threshold'])) {
				$this->prefs['threshold'] = $_POST['locations_threshold'];
			}

		}

		function build_mostcommon() {

			$sortby = $this->prefs['sortby'] ? 'unique' : 'total';
			$slight = $this->prefs['sortby'] ? 'total' : 'unique';
			$locations = $this->data['locations'];

			arsort($locations[$sortby]);

			$countries = array(); $total = 0; $i = 0;

			foreach ($locations[$sortby] as $code => $hits) {
			
				$countries[$this->get_countryname($code)][$sortby] = $hits;
				$total += $hits;
			}

			foreach ($locations[$slight] as $code => $hits) {
				$countries[$this->get_countryname($code)][$slight] = $hits;
				
			}

			$table_data['thead'] = array(array('value' => '% of Total', 'class' => 'sort'), array('value' => 'Country', 'class' => 'focus'), array('value' => 'Total', 'class' => 'sort'), array('value' => 'Unique', 'class' => 'sort'));

			foreach ($countries as $name => $hits) {

				if ($i == $this->Mint->cfg['preferences']['rows']) {
					break;
				}

				$percent = $hits[$sortby] / $total * 100; $i++;

				$row = array($this->Mint->formatPercents($percent), $name, $hits['total'], (isset($hits['unique']) ? $hits['unique'] : '-'));

				if (round($percent, 5) < $this->prefs['threshold'] || $name == 'Unknown') {
					$row['class'] = 'insig';
				}

				$table_data['tbody'][] = $row;

			}

			return $this->Mint->generateTable($table_data);

		}

		function build_mostrecent() {

			$table_data['thead'] = array(array('value' => 'Country', 'class' => 'focus'), array('value' => 'When', 'class' => 'sort'));

			if ($result = $this->query('SELECT country_code, dt FROM '.$this->Mint->db['tblPrefix'].'visit ORDER BY dt DESC LIMIT 0, '.$this->Mint->cfg['preferences']['rows'])) {

				while ($row = mysql_fetch_assoc($result)) {
					$class = $row['country_code'] == 'XX' ? 'insig' : '';
					$table_data['tbody'][] = array($this->get_countryname($row['country_code']), $this->Mint->formatDateTimeRelative($row['dt']), 'class' => $class);
				}

			}

			return $this->Mint->generateTable($table_data);

		}

		function get_countryname($code) {

			if (!isset($this->countries)) {
				$this->countries = $this->countries();
			}

			return $this->countries[$code];

		}

		function get_countrycode() {

			$ip = FALSE;

			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}

			if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
			}

			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

				$ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);

				if ($ip) {
					array_unshift($ips, $ip);
					$ip = FALSE;
				}

				for ($i = 0; $i < count($ips); $i++) {
					if (!preg_match('/^(?:10|172\.(?:1[6-9]|2\d|3[01])|192\.168)\./', $ips[$i])) {
						if (version_compare(phpversion(), '5.0.0', '>=')) {
							if (ip2long($ips[$i]) != FALSE) {
								$ip = $ips[$i];
								break;
							}
						} else {
							if (ip2long($ips[$i]) != -1) {
								$ip = $ips[$i];
								break;
							}
						}
					}
				}
	
			}

			$ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];

			$fp = fopen(dirname(__FILE__).'/database.dat', 'rb');

			if (fread($fp, 4) != 'TKdb') {
				return FALSE;
			}

			fseek($fp, $this->read_long($fp));
			$records = sprintf('%u', $this->read_long($fp));
			$min = sprintf('%u', $this->read_long($fp));
			$max =  sprintf('%u', $this->read_long($fp));
			$recsize = $this->read_byte($fp);
			$countries = $this->read_short($fp);

			$tmp = fread($fp, $countries * 2);

			for ($i = 0; $i < $countries; $i++) {
				$countryname[] = substr($tmp, $i * 2, 2);
			}

			$minip = $this->read_byte($fp);
			$maxip = $this->read_byte($fp);

			for ($i = 0; $i < 256; $i++) {
				$topidx[$i] = -1;
			}

			for ($i = $minip; $i <= $maxip; $i++) {
				$topidx[$i] = $this->read_long($fp);
			}

			$list = split('\.', $ip);
			$aclass = $list[0];
			$ip = sprintf('%u', ip2long($ip));

			if ($ip < $min || $ip > $max || $topidx[$aclass] < 0) {
				$index = -1;
			}

			if ($aclass == $maxip) {
				$top = $records;
				$bottom = abs($topidx[$aclass]) - 1;
			} else {
				$bottom = abs($topidx[$aclass]) - 1;
				$i = 1;
				while ($topidx[$aclass + $i] < 0) {
					$i++;
				}
				$top = $topidx[$aclass + $i];
			}

			if ($aclass == $minip) {
				$bottom = 0;
			}

			$oldtop = -1;
			$oldbot = -1;
			$nextrecord = floor(($top + $bottom) / 2);

			if ($ip == $min) {
				fseek($fp, 16);
				$index = $this->read_short($fp);
			} elseif ($ip == $max) {
				fseek($fp, $records * $recsize - $recsize + 16);
				$index = $this->read_short($fp);
			}

			$cnt = 0;
			while (!FALSE) {

				$cnt++;
				fseek($fp, $nextrecord * $recsize + 8);
				$start = sprintf( '%u', $this->read_long($fp));

				if ($ip < $start) {
					$top = $nextrecord;
				} else {
					$end = sprintf('%u', $this->read_long($fp));
					if ($ip > $end) {
						$bottom = $nextrecord;
					} else {
						$index = $this->read_short($fp);
						break;
					}
				}

				$nextrecord = floor(($top + $bottom) / 2);
				if ($top == $oldtop && $bottom == $oldbot) {
					$index = -1;
					break;
				}

				$oldtop = $top;
				$oldbot = $bottom;

			}

			fclose($fp);

			if ($index >= 0 && $index < $countries) {
				return $countryname[$index];
			}

		}

		function read_long($fp) {

			$tmp = fread($fp, 4);
			return ord($tmp[3]) << 24 | ord($tmp[2]) << 16 | ord($tmp[1]) << 8 | ord($tmp[0]);

		}

		function read_short($fp) {

			$tmp = fread($fp, 2);
			return ord($tmp[1]) << 8 | ord($tmp[0]);

		}

		function read_byte($fp) {

			$tmp = fread($fp, 1);
			return ord($tmp[0]);

		}

		function countries() {

			return array(
				'AD' => 'Andorra',
				'AE' => 'United Arab Emirates',
				'AF' => 'Afghanistan',
				'AG' => 'Antigua and Barbuda',
				'AI' => 'Anguilla',
				'AL' => 'Albania',
				'AM' => 'Armenia',
				'AN' => 'Netherlands Antilles',
				'AO' => 'Angola',
				'AQ' => 'Antarctica',
				'AR' => 'Argentina',
				'AS' => 'American Samoa',
				'AT' => 'Austria',
				'AU' => 'Australia',
				'AW' => 'Aruba',
				'AX' => '&Aring;land Islands',
				'AZ' => 'Azerbaijan',
				'BA' => 'Bosnia and Herzegovina',
				'BB' => 'Barbados',
				'BD' => 'Bangladesh',
				'BE' => 'Belgium',
				'BF' => 'Burkina Faso',
				'BG' => 'Bulgaria',
				'BH' => 'Bahrain',
				'BI' => 'Burundi',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BN' => 'Brunei Darussalam',
				'BO' => 'Bolivia',
				'BR' => 'Brazil',
				'BS' => 'Bahamas',
				'BT' => 'Bhutan',
				'BW' => 'Botswana',
				'BY' => 'Belarus',
				'BZ' => 'Belize',
				'CA' => 'Canada',
				'CD' => 'The Democratic Republic of the Congo',
				'CF' => 'Central African Republic',
				'CG' => 'Congo',
				'CH' => 'Switzerland',
				'CI' => 'Cote D\'ivoire',
				'CK' => 'Cook Islands',
				'CL' => 'Chile',
				'CM' => 'Cameroon',
				'CN' => 'China',
				'CO' => 'Colombia',
				'CR' => 'Costa Rica',
				'CS' => 'Serbia and Montenegro',
				'CU' => 'Cuba',
				'CV' => 'Cape Verde',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DE' => 'Germany',
				'DJ' => 'Djibouti',
				'DK' => 'Denmark',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'DZ' => 'Algeria',
				'EC' => 'Ecuador',
				'EE' => 'Estonia',
				'EG' => 'Egypt',
				'ER' => 'Eritrea',
				'ES' => 'Spain',
				'ET' => 'Ethiopia',
				'FI' => 'Finland',
				'FJ' => 'Fiji',
				'FK' => 'Falkland Islands (Malvinas)',
				'FM' => 'Federated States of Micronesia',
				'FO' => 'Faroe Islands',
				'FR' => 'France',
				'GA' => 'Gabon',
				'GB' => 'United Kingdom',
				'GD' => 'Grenada',
				'GE' => 'Georgia',
				'GF' => 'French Guiana',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GL' => 'Greenland',
				'GM' => 'Gambia',
				'GN' => 'Guinea',
				'GP' => 'Guadeloupe',
				'GQ' => 'Equatorial Guinea',
				'GR' => 'Greece',
				'GS' => 'South Georgia and the South Sandwich Islands',
				'GT' => 'Guatemala',
				'GU' => 'Guam',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HK' => 'Hong Kong',
				'HN' => 'Honduras',
				'HR' => 'Croatia',
				'HT' => 'Haiti',
				'HU' => 'Hungary',
				'ID' => 'Indonesia',
				'IE' => 'Ireland',
				'IL' => 'Israel',
				'IN' => 'India',
				'IO' => 'British Indian Ocean Territory',
				'IQ' => 'Iraq',
				'IR' => 'Islamic Republic of Iran',
				'IS' => 'Iceland',
				'IT' => 'Italy',
				'JE' => 'Jersey',
				'JM' => 'Jamaica',
				'JO' => 'Jordan',
				'JP' => 'Japan',
				'KE' => 'Kenya',
				'KG' => 'Kyrgyzstan',
				'KH' => 'Cambodia',
				'KI' => 'Kiribati',
				'KM' => 'Comoros',
				'KN' => 'Saint Kitts and Nevis',
				'KR' => 'Republic of Korea',
				'KW' => 'Kuwait',
				'KY' => 'Cayman Islands',
				'KZ' => 'Kazakhstan',
				'LA' => 'Lao People\'s Democratic Republic',
				'LB' => 'Lebanon',
				'LC' => 'Saint Lucia',
				'LI' => 'Liechtenstein',
				'LK' => 'Sri Lanka',
				'LR' => 'Liberia',
				'LS' => 'Lesotho',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'LV' => 'Latvia',
				'LY' => 'Libyan Arab Jamahiriya',
				'MA' => 'Morocco',
				'MC' => 'Monaco',
				'MD' => 'Republic of Moldova',
				'ME' => 'Montenegro',
				'MG' => 'Madagascar',
				'MH' => 'Marshall Islands',
				'MK' => 'The Former Yugoslav Republic of Macedonia',
				'ML' => 'Mali',
				'MM' => 'Myanmar',
				'MN' => 'Mongolia',
				'MO' => 'Macao',
				'MP' => 'Northern Mariana Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MS' => 'Montserrat',
				'MT' => 'Malta',
				'MU' => 'Mauritius',
				'MV' => 'Maldives',
				'MW' => 'Malawi',
				'MX' => 'Mexico',
				'MY' => 'Malaysia',
				'MZ' => 'Mozambique',
				'NA' => 'Namibia',
				'NC' => 'New Caledonia',
				'NE' => 'Niger',
				'NF' => 'Norfolk Island',
				'NG' => 'Nigeria',
				'NI' => 'Nicaragua',
				'NL' => 'Netherlands',
				'NO' => 'Norway',
				'NP' => 'Nepal',
				'NR' => 'Nauru',
				'NU' => 'Niue',
				'NZ' => 'New Zealand',
				'OM' => 'Oman',
				'PA' => 'Panama',
				'PE' => 'Peru',
				'PF' => 'French Polynesia',
				'PG' => 'Papua New Guinea',
				'PH' => 'Philippines',
				'PK' => 'Pakistan',
				'PL' => 'Poland',
				'PR' => 'Puerto Rico',
				'PS' => 'Palestinian territories',
				'PT' => 'Portugal',
				'PW' => 'Palau',
				'PY' => 'Paraguay',
				'QA' => 'Qatar',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RS' => 'Republic of Serbia',
				'RU' => 'Russian Federation',
				'RW' => 'Rwanda',
				'SA' => 'Saudi Arabia',
				'SB' => 'Solomon Islands',
				'SC' => 'Seychelles',
				'SD' => 'Sudan',
				'SE' => 'Sweden',
				'SG' => 'Singapore',
				'SI' => 'Slovenia',
				'SK' => 'Slovakia',
				'SL' => 'Sierra Leone',
				'SM' => 'San Marino',
				'SN' => 'Senegal',
				'SO' => 'Somalia',
				'SR' => 'Suriname',
				'ST' => 'Sao Tome and Principe',
				'SV' => 'El Salvador',
				'SY' => 'Syrian Arab Republic',
				'SZ' => 'Swaziland',
				'TD' => 'Chad',
				'TG' => 'Togo',
				'TH' => 'Thailand',
				'TJ' => 'Tajikistan',
				'TK' => 'Tokelau',
				'TL' => 'Timor-Leste',
				'TM' => 'Turkmenistan',
				'TN' => 'Tunisia',
				'TO' => 'Tonga',
				'TR' => 'Turkey',
				'TT' => 'Trinidad and Tobago',
				'TV' => 'Tuvalu',
				'TW' => 'Taiwan',
				'TZ' => 'United Republic of Tanzania',
				'UA' => 'Ukraine',
				'UG' => 'Uganda',
				'UM' => 'United States Minor Outlying Islands',
				'US' => 'United States',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VA' => 'Holy See (Vatican City State)',
				'VC' => 'Saint Vincent And The Grenadines',
				'VE' => 'Venezuela',
				'VG' => 'Virgin Islands, British',
				'VI' => 'Virgin Islands, U.S.',
				'VN' => 'Viet Nam',
				'VU' => 'Vanuatu',
				'WF' => 'Wallis and Futuna Islands',
				'WS' => 'Samoa',
				'XX' => 'Unknown',
				'YE' => 'Yemen',
				'YT' => 'Mayotte',
				'ZA' => 'South Africa',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe'
			);

		}

	}
