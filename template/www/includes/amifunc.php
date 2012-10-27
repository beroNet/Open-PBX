<?php

class amifunc {

	var $mysocket;
	var $myerror;

	function amifunc() {
		$this->mysocket = false;
		$this->myerror = '';
	 }

	function Login ($host='localhost', $port='5038', $username='YmVyb1BCWF91c2VybmFtZV8yMDEy', $password='YmVyb1BCWF9wYXNzd29yZF8yMDEy') {

		$this->mysocket = fsockopen($host, $port, $errno, $errstr, 1);

		if (!$this->mysocket) {
			$this->myerror =  "Could not connect - $errstr ($errno)";
			return(false);
		}

		stream_set_timeout($this->mysocket, 1);

		$sQuery =	"Action: Login\r\n" .
				"UserName: " . $username . "\r\n" .
				"Secret: " . $password . "\r\n\r\n";

		$wrets = $this->Query($sQuery);

		if (strpos($wrets, "Message: Authentication accepted") == FALSE) {
			fclose($this->mysocket);
			$this->mysocket = false;
			$this->error = "Could not login - Authentication failed";
			return(false);
		}

		return(true);
	}

	function Originate ($sChannel, $sNumber, $CallerID) {

		$sQuery =	"Action: Originate\r\n" .
				"Channel: ". $sChannel . $sNumber . "\r\n" .
				"Callerid: " . $CallerID . "\r\n" .
				"Application: Playback\r\n" .
				"Data: tt-monkeys\r\n\r\n";

		return($this->Query($sQuery));
	}

	function DBPut ($family, $key, $value) {

		$sQuery =	"Action: DBPut\r\n" .
				"Family: ". $family . "\r\n" .
				"Key: " . $key . "\r\n" .
				"Val: " . $value . "\r\n\r\n";

		return($this->Query($sQuery));
	}

	function DBGET ($family, $key) {

		$sQuery =	"Action: DBGET\r\n" .
				"Family: ". $family . "\r\n" .
				"Key: " . $key . "\r\n\r\n";

		return($this->Query($sQuery));
	}

	function deltree ($family) {

		$sQuery =	"Action: command\r\n" .
				"command: database deltree ". $family . "\r\n\r\n";

		return($this->Query($sQuery));
	}

	function del ($family, $key) {

		$sQuery =	"Action: command\r\n" .
				"command: database del ". $family . " " . $key . "\r\n\r\n";

		return($this->Query($sQuery));
	}

	function SipShowRegistry() {

		$sQuery =	"Action: Command\r\n" .
				"command: Sip Show Registry\r\n\r\n";

		return($this->Query($sQuery));
	}

	function ShowChannels(){

		$sQuery =	"Action: Command\r\n" .
				"command: Core Show Channels\r\n\r\n";

		return($this->Query($sQuery));
	}

	function DatabaseShow(){

		$sQuery =	"Action: Command\r\n" .
				"command: Database Show\r\n\r\n";

		return($this->Query($sQuery));
	}
/*
	function GsmShowStatus(){

		$wrets=$this->Query("Action: Command\r\ncommand: gsm show status\r\n\r\n");
		return $wrets;
	}

	function MisdnShowStacks(){

		$wrets=$this->Query("Action: Command\r\ncommand: misdn show stacks\r\n\r\n");
		return $wrets;
	}
 */
	function SnomReboot($name){

		$sQuery =	"Action: Command\r\n" .
				"command: sip notify reboot-snom " . $name . "\r\n\r\n";

		return($this->Query($sQuery));
	}

	function Logout() {

		if ($this->mysocket == false) {
			return;
		}

		fputs($this->mysocket, "Action: Logoff\r\n\r\n");
		while (!feof($this->mysocket)) {
			$wrets .= fread($this->mysocket, 8192);
		}

		fclose($this->mysocket);
		$this->mysocket = false;
	}
/*
	function SendSMS($port,$number,$text){
		$wrets=$this->Query("Action: GsmSendSms\r\nport: $port\r\nnumber: $number\r\ntext: $text\r\n\r\n");
		return $wrets;
	}
 */
	function ExtReload(){

		$sQuery = 	"Action: Command\r\n" .
				"command: Extensions Reload\r\n\r\n";

		return($this->Query($sQuery));
	}

	function Reload () {

		$sQuery =	"Action: Command\r\n" .
				"command: core reload\r\n\r\n";

		return($this->Query($sQuery));
	}

	function Restart() {

		$sQuery =	"Action: Command\r\n" .
				"command: restart now\r\n\r\n";

		return($this->Query($sQuery));
	}

	function Query ($query) {

		$wrets = "";
		$linecount = 0;

		if ($this->mysocket == false) {
			return (false);
		}

		fputs($this->mysocket, $query);

		do {
			$line = fgets($this->mysocket, 4096);

//			echo $line . "<br />\n";

			$wrets .= $line;
			$info = stream_get_meta_data($this->mysocket);

			if ($line == "\r\n") {
				$linecount++;
			}

			if ($linecount == 2) {
				break;
			}
//		} while ($line != "\r\n" && $info['timed_out'] == false );
		} while ($info['timed_out'] == false);

		return($wrets);
	}
}

?>
