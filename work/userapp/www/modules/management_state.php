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

		$ba = new beroAri();

		$query = $ba->select("SELECT name FROM sip_trunks");
		while ($entry = $ba->fetch_array($query)){
			$user[] = implode(explode('-', $entry['name'], -1));
		}
		unset($query);
		unset($entry);

		foreach (explode("\n", $ami->SipShowRegistry()) as $entry) {

			if (strlen($entry) != 101) {
				continue;
			}

			$uname = trim(substr($entry, 30, 12));
			if (in_array($uname, $user)){
				continue;
			}

			$state = str_replace(' ', '', substr($entry, 55, 20));
			$state_img = BAF_URL_BASE . '/img/punkt_' . (($state == 'Registered') ? 'gruen' : 'rot') . '.gif';

			$ret .=	"\t<tr>\n" .
				"\t\t<td>" . trim(substr($entry, 0, 30)) . "</td>\n" .		// host
				"\t\t<td>" . substr($entry, 30, 12) . "</td>\n" .		// user
				"\t\t<td>" . substr($entry, 42, 13) . "</td>\n" .		// refresh
				"\t\t<td>" . $state . "</td>\n" .				// state
				"\t\t<td>\n" .
				"\t\t\t<img src=\"" . $state_img . "\" />\n" .
				"\t\t</td>\n" .
				"\t\t<td>" . substr($entry, 69) . "</td>\n" .			// regtime
				"\t<tr>\n";
		}

		return($ret);
	}

	private function _display_channels_active ($ami) {

		$entries = explode("\n", $ami->ShowChannels());
		$entry_count = count($entries);

		for ($i = 4; $i < ($entry_count - 5); $i++) {
			$tmp = array_values(array_filter(explode(' ', $entries[$i])));

			$ret .=	"\t<tr>\n" .
				"\t\t<td>" . $tmp[0] . "</td>\n" .					// channel
				"\t\t<td>" . $tmp[1] . "</td>\n" .					// location
				"\t\t<td>" . $tmp[2] . "</td>\n" .					// state
				"\t\t<td>" . $tmp[3] . ' ' . $tmp[4] . ' ' . $tmp[5] . "</td>\n" .	// app
				"\t</tr>\n";
			unset($tmp);
		}

		return($ret);
	}

	function display () {

		$ami = new amifunc();
		$ami->Login();

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
			"\t\t<th colspan=\"4\">Active Channels</th>\n" .
			"\t</tr>\n" .
			"\t<tr class=\"sub_head\">\n" .
			"\t\t<td>Channel</td>\n" .
			"\t\t<td>Location</td>\n" .
			"\t\t<td>State</td>\n" .
			"\t\t<td>Application(Data)</td>\n" .
			"\t</tr>\n" .
			$this->_display_channels_active($ami) .
			"</table>\n";

		$ami->Logout();

		return($ret);
	}
}

?>
