<?php

/**
* Asterisk Manager class
*/
class AsteriskManager
{
	private $_socket;
	public $error;

	public function __construct()
	{
		$this->_socket = false;
		$this->error = NULL;
	}

	public function __destruct()
	{
		if (is_resource($this->_socket)) {
			fclose($this->_socket);
		}
		$this->_socket = false;
	}

	private function send_request($action, $parameters=array())
	{
		$req = 'Action: '. $action ."\r\n";
		foreach ($parameters as $var => $val) {
			$req .= $var .': '. $val ."\r\n";
		}
		$req .= "\r\n";
		fwrite($this->_socket, $req, strlen($req));
		fflush($this->_socket);
		return $this->wait_response();
	}

	private function wait_response()
	{
		$parameters = array();

		if (feof($this->_socket)) {
			return false;
		}

		$buffer = fgets($this->_socket, 4096);
		while (trim($buffer) !== '') {
			list($var, $val) = explode(':', $buffer);
			if ($parameters['Response'] === 'Follows' && isset($parameters['Privilege'])) {
				if (substr($buffer, 0, 6) != '--END ') {
					$parameters['data'] .= $buffer;
				}
			} else {
				$parameters[trim($var)] = trim($val);
			}
			usleep(1000);
			$buffer = fgets($this->_socket, 4096);
		}
		
		return $parameters;
	}

	public function connect($server='127.0.0.1', $port=5038, $username='YmVyb1BCWF91c2VybmFtZV8yMDEy', $secret='YmVyb1BCWF9wYXNzd29yZF8yMDEy')
	{
		// connect to _socket
		$errno = NULL;
		$errstr = NULL;
		$this->_socket = fsockopen($server, $port, $errno, $errstr, 1);
		if (!$this->_socket) {
			$this->error = 'Could not connect - '. $errno .': '. $errstr;
			return false;
		}

		stream_set_timeout($this->_socket, 1);

		// read the header
		$str = fgets($this->_socket);
		if ($str == false) {
			$this->error = 'Asterisk manager header not received.';
			return false;
		}

		// Login
		$res = $this->send_request('Login', array('Username'=>$username,'Secret'=>$secret,'Events'=>'off'));
		if ($res['Response'] !== 'Success') {
			$this->error = 'Could not login - Authentication failed';
			$this->disconnect();
			return false;
		}

		return true;
	}

	public function disconnect()
	{
		$this->__destruct();
	}

	public function Command($command=false)
	{
		return $this->send_request('Command', array('Command'=>$command));
	}

	public function CoreShowChannels()
	{
		$rets = $this->send_request('CoreShowChannels');
		if ($rets['Response'] == 'Success' && $rets['EventList'] == 'start') {
			$rets['Entrys'] = array();
			$event = $this->wait_response();
			while ($event['EventList'] != 'Complete') {
				$rets['Entrys'][] = $event;
				$event = $this->wait_response();
			}
		}
		return $rets;
	}

	public function DBDel($family, $key)
	{
		return $this->send_request('DBDel', array('Family'=>$family, 'Key'=>$key));
	}

	public function DBDelTree($family)
	{
		return $this->send_request('DBDelTree', array('Family'=>$family));
	}
	
	public function DBGet($family, $key)
	{
		$rets = $this->send_request('DBGet', array('Family'=>$family, 'Key'=>$key));
		if ($rets['Response'] == 'Success') {
				usleep(1000);
				$tmp = $this->wait_response();
				if ($tmp['Event'] == 'DBGetResponse') {
					if ($tmp['Family']) $rets['Family'] = $tmp['Family'];
					if ($tmp['Key']) $rets['Key'] = $tmp['Key'];
					if (isset($tmp['Val'])) $rets['Val'] = $tmp['Val'];
					usleep(100);
					$this->wait_response();
				}
		}
		return $rets;
	}

	public function DBPut($family, $key, $val)
	{
		return $this->send_request('DBPut', array('Family'=>$family, 'Key'=>$key, 'Val'=>$val));
	}

	public function Logout()
	{
		$rets = $this->send_request('Logoff');
		$this->disconnect();
	}

	public function Reload($module=false)
	{
		$parameters = array();
		if ($module) {
			$parameters['Module'] = $module;
		}

		return $this->send_request('Reload', $parameters);
	}

	public function SIPpeers()
	{
		$rets = $this->send_request('SIPpeers');
		if ($rets['Response'] === 'Success' && $rets['EventList'] === 'start') {
			$rets['Entrys'] = array();
			$event = $this->wait_response();
			while ($event['EventList'] != 'Complete') {
				$rets['Entrys'][] = $event;
				$event = $this->wait_response();
			}
		}
		return $rets;
	}

	public function SIPshowregistry()
	{
		$rets = $this->send_request('SIPshowregistry');
		if ($rets['Response'] === 'Success' && $rets['EventList'] === 'start') {
			$rets['Entrys'] = array();
			$event = $this->wait_response();
			while ($event['EventList'] !== 'Complete') {
				$rets['Entrys'][] = $event;
				$event = $this->wait_response();
			}
		}
		return $rets;
	}

}

?>
