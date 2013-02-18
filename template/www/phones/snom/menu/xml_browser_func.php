<?php

function XML_escape($raw) {
	return str_replace(
		array('&'    , '<'   , '>'   , '"'     , '\''    , "\n"   ),
		array('&amp;', '&lt;', '&gt;', '&quot;', '&apos;', '<br/>'),
		$raw);
}


class SnomIPPhoneObject {
	
	private $_title;
	private $_softkeys;
	protected $_type;

	public function __construct()
	{
		$this->_title = false;
		$this->_softkeys = false;
		$this->_type = 'SnomIPPhoneText';
	}

	public function setTitle($title)
	{
		$this->_title = $title;
	}

	public function setSoftkeys($softkeys)
	{
		if ($softkeys && is_array($softkeys)) {
			$softkeys = new SnomIPPhoneSoftkeys($softkeys);
		}
		$this->_softkeys = $softkeys;
	}

	public function show()
	{
		header('Content-Type: application/xml; charset=utf-8');
		$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<' . $this->_type . '>' . "\n";
		$xml .= "\t" . '<Title>' . XML_escape($this->_title) .'</Title>' . "\n";
		$xml .= $this->_specificXML();
		if ($this->_softkeys) {
			$xml .= $this->_softkeys->asXML();
		}
		$xml .= '</' . $this->_type . '>' . "\n";

		print($xml);

	}

	protected function _specificXML()
	{
		return "\t" . '<Text>Error: abtract IPPhoneObject.show() was invoked</Text>' . "\n";
	}


}


class SnomIPPhoneSoftkeys
{
	private $_keys;
	
	public function __construct($keys=false)
	{
			$this->_keys = array();
			if ($keys && is_array($keys)) {
				foreach ($keys as $name => $key) {
					$this->_keys[$name] = array(
						'url'    => (isset($key['url']) ? $key['url'] : false),
						'action' => (isset($key['action']) ? $key['action'] : false),
						'label'  => (isset($key['label']) ? $key['label'] : false)
					);
				}
			}

	}

	public function add($name, $url=false, $action=false, $label=false)
	{
		$this->_keys[$name] = array(
			'url'    => $url,
			'action' => $action,
			'label'  => $label
		);
	}

	public function addURL($name, $url=false, $label=false)
	{
		$this->add($name, $url, false, $label);
	}

	public function addAction($name, $action=false, $label=false)
	{
		$this->add($name, false, $action, $label);
	}

	public function del($name)
	{
		unset($this->_keys[$name]);
	}

	public function asXML()
	{
		$tags = '';
		foreach ($this->_keys as $name => $key) {
			$tags .= "\t" . '<SoftKeyItem>' ."\n";
			$tags .= "\t\t" . '<Name>'. XML_escape($name) .'</Name>' ."\n";
			if ($key['url']) {
				$tags .= "\t\t" .'<URL>'. $key['url'] .'</URL>'."\n";
			}
			if ($key['action']) {
				$tags .= "\t\t" .'<SoftKey>'. $key['action'] .'</SoftKey>'."\n";
			}
			if ($key['label']) {
				$tags .= "\t\t" . '<Label>' . XML_escape($key['label']) . '</Label>' ."\n";
			}
			$tags .= "\t" . '</SoftKeyItem>' ."\n";
		}

		return $tags;
	}
}


class SnomIPPhoneText extends SnomIPPhoneObject
{
	private $_text;

	public function __construct()
	{
		parent::__construct();
		$this->_text = '';
		$this->_type = 'SnomIPPhoneText';
	}

	public function setText($text)
	{
		$this->_text = $text;
	}

	public function getText()
	{
		return $this->_text;
	}

	protected function _specificXML()
	{
		return "\t" . '<Text>' . XML_escape($this->_text) . '</Text>' . "\n";
	}
}


class SnomIPPhoneMenu extends SnomIPPhoneObject
{
	private $_items;
	private $_sel;
	
	public function __construct()
	{
		parent::__construct();
		$this->_items = array();
		$this->_sel = false;
		$this->_type = 'SnomIPPhoneMenu';
	}

	public function add($name, $uri)
	{
		$this->_items[$name] = $uri;
	}

	public function del($name)
	{
		unset($this->_items[$name]);
	}

	public function select($name=false)
	{
		$this->_sel = $name;
	}

	protected function _specificXML()
	{
		$xml = '';
		foreach ($this->_items as $name => $uri) {
			$xml .= "\t" . '<MenuItem';
			if ($this->_sel && $this->_sel == $name) {
				$xml .= ' sel="true"';
			}
			$xml .= '>' . "\n";
			$xml .= "\t\t" . '<Name>' . XML_escape($name) . '</Name>' . "\n";
			$xml .= "\t\t" . '<URL>' . XML_escape($uri) . '</URL>' . "\n";
			$xml .= "\t" . '</MenuItem>' . "\n";
		}
		return $xml;
	}
}


class SnomIPPhoneDirectory extends SnomIPPhoneObject
{
	private $_items;
	private $_sel;

	public function __construct()
	{
		parent::__construct();
		$this->_items = array();
		$this->_sel = false;
		$this->_type = 'SnomIPPhoneDirectory';
	}

	public function add($name, $phone)
	{
		$this->_items[$name] = $phone;
	}

	public function del($name)
	{
		unset($this->_items[$name]);
	}

	public function select($name=false)
	{
		$this->_sel = $name;
	}

	protected function _specificXML()
	{
		$xml = '';
		foreach ($this->_items as $name => $phone) {
			$xml .= "\t" . '<DirectoryEntry';
			if ($this->_sel && $this->_sel == $name) {
				$xml .= ' sel="true"';
			}
			$xml .= '>' . "\n";
			$xml .= "\t\t" . '<Name>' . XML_escape($name) . '</Name>' . "\n";
			$xml .= "\t\t" . '<Telephone>' . XML_escape($phone) . '</Telephone>' . "\n";
			$xml .= "\t" . '</DirectoryEntry>' . "\n";
		}
		return $xml;
	}
}


class SnomIPPhoneInput extends SnomIPPhoneObject
{
	private $_url;
	private $_fields;

	public function __construct()
	{
		parent::__construct();
		$this->_url = false;
		$this->_fields = array();
		$this->_type = 'SnomIPPhoneInput';
	}

	public function setURL($url='')
	{
		$this->_url = $url;
	}

	public function add($param, $name, $flags, $default='')
	{
		$this->_fields[] = array('param' => $param, 'name' => $name, 'flags' => $flags, 'default' => $default);
	}

	public function del($param)
	{
		foreach ($this->_fields as $i => $info) {
			if ($info['param'] == $param) {
				unset($this->_fields[$i]);
			}
		}
		
	}

	protected function _specificXML()
	{
		$xml = "\t" . '<URL>' . XML_escape($this->_url) . '</URL>' . "\n";
		foreach ($this->_fields as $item) {
			$xml .= "\t" . '<InputItem>' . "\n";
			$xml .= "\t\t" . '<DisplayName>' . XML_escape($item['name']) . '</DisplayName>' . "\n";
			$xml .= "\t\t" . '<QueryStringParam>' . XML_escape($item['param']) . '</QueryStringParam>' . "\n";
			$xml .= "\t\t" . '<DefaultValue>' . XML_escape($item['default']) . '</DefaultValue>' . "\n";
			$xml .= "\t\t" . '<InputFlags>' . $item['flags'] . '</InputFlags>' . "\n";
			$xml .= "\t" . '</InputItem>' . "\n";
		}
		return $xml;
	}
}

?>
