<?php

include(BAF_APP_WWW . '/includes/amifunc.php');

class MainModule {

	private $_name = 'management_state';
	private $_title = 'State';

	function getName() {
		return($this->_name);
	}

	function getTitle() {
		return($this->_title);
	}

	function execute() {
		if (!isset($_GET['execute'])) {
			return('');
		}

		return('');
	}

	private function _display_sip_registrations ($ami) {

		$sipregs = $ami->SIPshowregistry();

		foreach ($sipregs['Entrys'] as $entry) {
			if (!is_array($entry)) {
				continue;
			}

			$state = trim($entry['State']);
			$state_img = BAF_URL_BASE . '/img/punkt_' . (($state === 'Registered') ? 'gruen' : 'rot') . '.gif';

			$ret .=	"\t<tr>\n" .
				"\t\t<td>" . trim($entry['Host']) . ':' . trim($entry['Port']) . "</td>\n" .		// host
				"\t\t<td>" . trim($entry['Username']) . "</td>\n" .		// user
				"\t\t<td>" . trim($entry['Refresh']) . "</td>\n" .		// refresh
				"\t\t<td>\n" .
				"\t\t\t<img src=\"" . $state_img . "\" />\n" .
				"\t\t\t" . $state . "\n" .
				"\t\t</td>\n" .
				"\t\t<td>" . date('r',$entry['RegistrationTime']) . "</td>\n" .			// regtime
				"\t<tr>\n";
		}

		return($ret);
	}

	private function _display_sip_peers($ami)
	{
		$ba = new beroAri();

		$user = array();
		$query = $ba->select('SELECT e.extension AS extension FROM sip_users AS u, sip_extensions AS e WHERE u.extension = e.id');
		while ($entry = $ba->fetch_array($query)) {
			$user[] = $entry['extension'];
		}
		unset($query);
		unset($entry);

		$sippeers = $ami->SIPpeers();

		foreach ($sippeers['Entrys'] as $entry) {
			if (!is_array($entry) || !in_array($entry['ObjectName'], $user)) {
				continue;
			}

    		$state = trim($entry['Status']);
    		$state_img = BAF_URL_BASE . '/img/punkt_' . ((substr($state, 0, 2) == 'OK') ? 'gruen' : 'rot') . '.gif';

			$ret .= "\t<tr>\n" .
				"\t\t<td>" . trim($entry['IPaddress']) . ':' . trim($entry['IPport']). "</td>\n" .
				"\t\t<td>" . trim($entry['ObjectName']). "</td>\n" .
				"\t\t<td>\n" .
				"\t\t\t<img src=\"" . $state_img . "\" />\n" .
				"\t\t\t" . $state . "\n" .
				"\t\t</td>\n" .
				"\t</tr>\n";
		}

		return($ret);
	}

	private function _display_channels_active ($ami) {

		$entrys = $ami->CoreShowChannels();

		foreach ($entrys['Entrys'] as $entry) {
			if (!is_array($entry)) {
				continue;
			}
			$ret .= "\t<tr>\n";
			$ret .= "\t\t<td>" . trim($entry['Channel']) ."</td>\n";
			$ret .= "\t\t<td>" . trim($entry['Extension']).'@'.trim($entry['Context']).':'.trim($entry['Priority']) ."</td>\n";
			$ret .= "\t\t<td>" . trim($entry['ChannelStateDesc']) ."</td>\n";
			$ret .= "\t\t<td>" . trim($entry['Application']).'('.trim($entry['ApplicationData']).')' ."</td>\n";
			$ret .= "\t\t<td>" . trim($entry['Duration']) ."</td>\n";
			$ret .= "\t</tr>\n";
		}

		return($ret);
	}

	function display () {

		$ami = new AsteriskManager();
		$ami->connect();

		$ret =	"<table class=\"default\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"5\">SIP-Registrations</th>\n" .
			"\t</tr>\n" .
			"\t<tr class=\"sub_head\">\n" .
			"\t\t<td>Host:Port</td>\n" .
			"\t\t<td>Username</td>\n" .
			"\t\t<td>Refresh</td>\n" .
			"\t\t<td>State</td>\n" .
			"\t\t<td>Reg.Time</td>\n" .
			"\t</tr>\n" .
			$this->_display_sip_registrations($ami) .
			"</table>\n" .
			"<br />\n" .
			"<br />\n" .
			"<table class=\"default\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"3\">SIP-Peers</th>\n" .
			"\t</tr>\n" .
			"\t<tr class=\"sub_head\">\n" .
			"\t\t<td>Host:Port</td>\n" .
			"\t\t<td>Username</td>\n" .
			"\t\t<td>State</td>\n" .
			"\t</tr>\n" .
			$this->_display_sip_peers($ami) .
			"</table>\n" .
			"<br />\n" .
			"<br />\n" .
			"<table class=\"default\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"5\">Active Channels</th>\n" .
			"\t</tr>\n" .
			"\t<tr class=\"sub_head\">\n" .
			"\t\t<td>Channel</td>\n" .
			"\t\t<td>Location</td>\n" .
			"\t\t<td>State</td>\n" .
			"\t\t<td>Application(Data)</td>\n" .
			"\t\t<td>Duration</td>\n" .
			"\t</tr>\n" .
			$this->_display_channels_active($ami) .
			"</table>\n";

		$ami->Logout();
		unset($ami);

		return($ret);
	}
}

?>
