<?php

Class beroGui {

	private $_lang;

	function __construct ($lang) {

		$this->_lang = $lang;
	}

	private function _main_menu ($module) {

		$pre = "\t\t\t\t\t";

		$ret =
			$pre . "<ul>\n" .
			$pre . "\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=dialplan\" id=\"item_dialplan\"><span>&#8226;</span> " . $this->_lang->get('menu_dialplan') . "</a></li>\n" .
			$pre . "\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=sip\" id=\"item_sip\"><span>&#8226;</span> " . $this->_lang->get('menu_siptrunks') . "</a></li>\n" .
			$pre . "\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=sip_users\" id=\"item_sip_users\"><span>&#8226;</span> " . $this->_lang->get('menu_users') . "</a></li>\n" .
			$pre . "\t<li><a href=\"#\" id=\"submenu_devices\"><span class=\"submenu\">&#9660</span> " . $this->_lang->get('menu_devices') . "</a>\n" .
			$pre . "\t\t<ul>\n" .
			$pre . "\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=devices_phones\" id=\"item_devices_phones\"><span>&#8226;</span> " . $this->_lang->get('menu_devices_phones') . "</a></li>\n" .
			$pre . "\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=devices_templates\" id=\"item_devices_templates\"><span>&#8226;</span> " . $this->_lang->get('menu_devices_templates') . "</a></li>\n" .
			$pre . "\t\t</ul>\n" .
			$pre . "\t</li>\n" .
			$pre . "\t<li><a href=\"#\" id=\"submenu_management\"><span class=\"submenu\">&#9660</span> " . $this->_lang->get('menu_management') . "</a>\n" .
			$pre . "\t\t<ul>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_state\" id=\"item_management_state\"><span>&#8226;</span> " . $this->_lang->get('menu_management_state') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_mail\" id=\"item_management_mail\"><span>&#8226;</span> " . $this->_lang->get('menu_management_mail') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_pnpconf\" id=\"item_management_pnpconf\"><span>&#8226;</span> " . $this->_lang->get('menu_management_pnp') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_backres\" id=\"item_management_backres\"><span>&#8226;</span> " . $this->_lang->get('menu_management_backup') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_easycfg\" id=\"item_management_easycfg\"><span>&#8226;</span> " . $this->_lang->get('menu_management_easycfg') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"/userapp/\"><span>&#8226;</span> " . $this->_lang->get('menu_management_userapp') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"/\"><span>&#8226;</span> " . $this->_lang->get('menu_management_berogui') . "</a></li>\n" .
			$pre . "\t\t</ul>\n" .
			$pre . "\t</li>\n" .
			$pre . "</ul>\n" .
			$pre . "<script>document.getElementById(\"item_" . $module . "\").id='aktiv_men';</script>\n";

		// also mark submenu if an item is in it
		switch ($module) {
		case 'devices_phones':
		case 'devices_templates':
			$ret .=	$pre . "<script>document.getElementById(\"submenu_devices\").id='aktiv_men';</script>\n";
			break;
		case 'management_state':
		case 'management_mail':
		case 'management_pnpconf':
		case 'management_backres':
			$ret .=	$pre . "<script>document.getElementById(\"submenu_management\").id='aktiv_men';</script>\n";
			break;
		}

		return($ret);
	}

	function main_header ($app_name, $mod) {

		$ba = new beroAri();

		# check if there is something to be activated
		$query = $ba->query("SELECT option FROM activate WHERE id = 'activate'");
		$option = $ba->fetch_single($query);
		unset($query);

		$ret =	"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n" .
			"\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n" .
			"<html xmlns=\"http://www.w3.org/1999/xhtml\">\n" .
			"\t<head>\n" .
			"\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
			"\t\t<meta http-equiv=\"cache-control\" content=\"no-cache\" />\n" .
			"\t\t<title>" . $app_name . " (" . $mod->getTitle() . ")</title>\n" .
			"\t\t<link rel=\"icon\" type=\"image/x-icon\" href=\"" . BAF_URL_BASE . "/img/favicon.ico\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_index.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_menu.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_tables.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_links.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_forms.css\" />\n" .
			"\t\t<script type=\"text/javascript\" src=\"" . BAF_URL_BASE . "/js/beroGui.js\"></script>\n" .
			"\t</head>\n" .
			"\t<body onload=\"paint_apply_button('" . $option . "','" . $mod->getName() . "','" . $this->_lang->get('menu_activate_button') . "','" .
											$this->_lang->get('menu_activate_green'). "','" . $this->_lang->get('menu_activate_red') . "');\">\n" .
			"\t\t<div id=\"body_frame\">\n" .
			"\t\t\t<div id=\"body_frame_top\">\n" .
			"\t\t\t\t<div id=\"body_frame_top_corner\"></div>\n" .
			"\t\t\t</div>\n" .
			"\t\t\t<div id=\"body_frame_middle\">\n" .
			"\t\t\t\t<div id=\"body_frame_middle_right\">\n" .
			"\t\t\t\t\t<div id=\"body_frame_head\">" . strtoupper($mod->getTitle()) . "</div>\n" .
			"\t\t\t\t\t<div id=\"body_frame_menu\">\n" .
			$this->_main_menu($mod->getName()) .
			"\t\t\t\t\t\t<div id=\"apply_button\"></div>\n" .
			"\t\t\t\t\t</div>\n" .
			"\t\t\t\t\t<div id=\"body_frame_content\">\n" .
			"\n<!-- INDEX HEADER END -->\n\n";

		return($ret);
	}

	function main_footer () {

		$ret =	"\n<!-- INDEX FOOTER START -->\n\n" .
			"\t\t\t\t\t</div>\n" .
			"\t\t\t\t</div>\n" .
			"\t\t\t\t<div id=\"body_frame_logo\">\n" .
			"\t\t\t\t\t<br />\n" .
			"\t\t\t\t\t<a href=\"http://www.beronet.com/\" target=\"_blank\" onfocus=\"if (this.blur()) { this.blur(); }\">\n" .
			"\t\t\t\t\t\t<img src=\"" . BAF_URL_BASE . "/img/beroLogo.gif\" />\n" .
			"\t\t\t\t\t</a>\n" .
			"\t\t\t\t</div>\n" .
			"\t\t\t</div>\n" .
			"\t\t\t<div id=\"body_frame_bottom\">\n" .
			"\t\t\t\t<div id=\"body_frame_bottom_corner\"></div>\n" .
			"\t\t\t</div>\n" .
			"\t\t</div>\n" .
			"\t</body>\n" .
			"</html>\n";

		return($ret);
	}

	function popup_header ($mod) {

		$ret =	"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n" .
			"\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n" .
			"<html xmlns=\"http://www.w3.org/1999/xhtml\">\n" .
			"\t<head>\n" .
			"\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n" .
			"\t\t<meta http-equiv=\"chache-control\" content=\"no-cache\" />\n" .
			"\t\t<title>" . $mod->getTitle() . "</title>\n" .
			"\t\t<link rel=\"icon\" type=\"image/x-icon\" href=\"" . BAF_URL_BASE . "/img/favicon.ico\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_popup.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_tables.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_links.css\" />\n" .
			"\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . BAF_URL_BASE . "/css/beroGui_forms.css\" />\n" .
			"\t\t<script type=\"text/javascript\" src=\"" . BAF_URL_BASE . "/js/beroGui.js\"></script>\n" .
			"\t</head>\n" .
			"\t<body onload=\"resize();\">\n" .
			"\t\t<script>window.opener.location='" . BAF_URL_BASE . "/index.php?m=" . $mod->getName() ."'</script>\n" .
			"\t\t<div id=\"popup_frame\">\n" .
			"\t\t\t<div id=\"popup_frame_top\">\n" .
			"\t\t\t\t<div id=\"popup_frame_top_corner\"></div>\n" .
			"\t\t\t</div>\n" .
			"\t\t\t<div id=\"popup_frame_middle\">\n" .
			"\t\t\t\t<div id=\"popup_frame_middle_right\">\n" .
			"\t\t\t\t\t<div id=\"popup_frame_head\">" . strtoupper($mod->getTitle()) . "</div>\n" .
			"\t\t\t\t\t<div id=\"popup_frame_content\">\n" .
			"\n<!-- POPUP HEADER END -->\n\n";

		return($ret);
	}

	function popup_footer () {

		$ret =	"\n<!-- POPUP FOOTER START -->\n\n" .
			"\t\t\t\t\t</div>\n" .
			"\t\t\t\t</div>\n" .
			"\t\t\t\t<div id=\"popup_frame_logo\">\n" .
			"\t\t\t\t\t<br />\n" .
			"\t\t\t\t\t<a href=\"http://www.beronet.com/\" target=\"_blank\" onfocus=\"if (this.blur()) { this.blur(); }\">\n" .
			"\t\t\t\t\t\t<img src=\"" . BAF_URL_BASE . "/img/beroLogo.gif\" />\n" .
			"\t\t\t\t\t</a>\n" .
			"\t\t\t\t</div>\n" .
			"\t\t\t</div>\n" .
			"\t\t\t<div id=\"popup_frame_bottom\">\n" .
			"\t\t\t\t<div id=\"popup_frame_bottom_corner\"></div>\n" .
			"\t\t\t</div>\n" .
			"\t\t</div>\n" .
			"\t</body>\n" .
			"</html>\n";

		return($ret);
	}
}

?>
