<?php

ob_implicit_flush(true);
error_reporting(0);

class AGI {

	private $_in;
	private $_out;

	public $request;

	
	public function __construct () {
		
		ob_implicit_flush(true);

		$this->_in = (defined('STDIN') ? STDIN : fopen('php://stdin','r'));
		$this->_out = (defined('STDOUT') ? STDOUT : fopen('php://stdout','w'));

		// read the request
		$str = fgets($this->_in);
		while ($str != "\n") {
			$this->request[substr($str, 0, strpos($str, ':'))] = trim(substr($str, strpos($str, ':') + 1));
			$str = fgets($this->_in);
		}
	}


	public function str_esc ($str) {
		$str = str_replace(
			array('\\'  , ' '  , "\n" , "\r" , "\t" , '"'  ),
			array('\\\\', '\\ ', '\\ ', '\\ ', '\\ ', '\\"'),
			$str);
		return ($str == '' ? '""' : $str);
	}


	private function _do ($cmd) {
		
		$fail    = array('code' => 500 , 'result' => -1  , 'data' => ''  );
		$reponse = array('code' => null, 'result' => null, 'data' => null);

		if (! fwrite($this->_out, trim($cmd)."\n")) {
			return $fail;
		}
		fflush($this->_out);

		$count = 0;
		$str = '';
		do {
			uSleep(1000);
			stream_set_timeout($this->_in, 1);
			$str .= trim(fgets($this->_in, 4096));
		} while ($str == '' && $count++ < 5);

		if ($count >= 5) {
			return $fail;
		}

		$response['code'] = substr($str, 0, 3);
		$str = trim(substr($str, 3));

		$response['result'] = null;
		$response['data'] = '';

		$parse = explode(' ', trim($str));
		$in_token = false;
		foreach ($parse as $token) {
			if ($in_token) {
				$response['data'] .= ' '. trim($token, '() ');
				if(substr($token, strlen($token)-1, 1) === ')') $in_token = false;
			}
			elseif (substr($token,0,1) === '(') {
				if(substr($token, strlen($token)-1, 1) !== ')') $in_token = true;
				$response['data'] .= ' '. trim($token, '() ');
			}
			elseif (strpos($token, '=') !== false) {
				$token = explode('=', $token);
				$response[$token[0]] = $token[1];
			}
			elseif ($token != '') {
				$response['data'] .= ' '. $token;
			}
		}
		return $response;
	}


	private function _response_is_success ($response) {
		return (is_array($response) && array_key_exists('code', $response) && $response['code'] === '200');
	}


	private function _do_bool ($cmd) {
		return $this->_response_is_success($this->_do($cmd));
	}



	public function verbose ($str, $level=1) {
		
		return $this->_do_bool('VERBOSE '. $this->str_esc($str) .' '. (int)$level);
	}


	public function set_variable ($name, $val) {

		if (! preg_match('/^[a-zA-Z0-9_()]+$/', $name)) {
			return false;
		}
		return $this->_do_bool('SET VARIABLE '. $name .' '. $this->str_esc($val));
	}


	public function get_variable ($name) {

		if (! preg_match('/^[a-zA-Z0-9_()]+$/', $name)) {
			return false;
		}
		$response = $this->_do('GET VARIABLE '. $name);
		if ($this->_response_is_success($reponse)) {
			return ($response['result'] == 1 ? $reponse['data'] : null);
		} else {
			return false;
		}
	}

}

?>
