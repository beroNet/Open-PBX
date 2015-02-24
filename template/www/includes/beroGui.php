<?php

Class beroGui {

	private $_lang;

	function __construct ($lang) {

		$this->_lang = $lang;
	}

	private function _main_menu ($module) {

		$pre = "\t\t\t\t\t";

		$ret ="<div id='myslidemenu' class='jqueryslidemenu'>
	<ul id='navigation'>".
			$pre . "\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=dialplan\" id=\"item_dialplan\"> " . $this->_lang->get('menu_dialplan') . "</a></li>\n" .
			$pre . "\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=sip\" id=\"item_sip\">" . $this->_lang->get('menu_siptrunks') . "</a></li>\n" .
			$pre . "\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=sip_users\" id=\"item_sip_users\"> " . $this->_lang->get('menu_users') . "</a></li>\n" .
			$pre . "\t<li><a href=\"#\" id=\"submenu_devices\"> " . $this->_lang->get('menu_devices') . " +</a>\n" .
			$pre . "\t\t<ul>\n" .
			$pre . "\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=devices_phones\" id=\"item_devices_phones\"> " . $this->_lang->get('menu_devices_phones') . "</a></li>\n" .
			$pre . "\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=devices_templates\" id=\"item_devices_templates\"> " . $this->_lang->get('menu_devices_templates') . "</a></li>\n" .
			$pre . "\t\t</ul>\n" .
			$pre . "\t</li>\n" .
			$pre . "\t<li><a href=\"#\" id=\"submenu_management\"> " . $this->_lang->get('menu_management') . " +</a>\n" .
			$pre . "\t\t<ul>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_state\" id=\"item_management_state\"> " . $this->_lang->get('menu_management_state') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_mail\" id=\"item_management_mail\"> " . $this->_lang->get('menu_management_mail') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_pnpconf\" id=\"item_management_pnpconf\"> " . $this->_lang->get('menu_management_pnp') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_backres\" id=\"item_management_backres\"> " . $this->_lang->get('menu_management_backup') . "</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"" . BAF_URL_BASE . "/index.php?m=management_easycfg\" id=\"item_management_easycfg\"> " . $this->_lang->get('menu_management_easycfg') . "</a></li>\n" .
			$pre . "\t\t</ul>\n" .
			$pre . "\t</li>\n" .
			$pre . "\t<li style=\"white-space: nowrap;\">\n" .
			$pre . "\t\t<a href=\"#\" id=\"submenu_apps\">Apps +</a>\n" .
			$pre . "\t\t<ul>\n";

			if ($handle = opendir('/home/admin/apps/')) {
				while (false !== ($file = readdir($handle))) {
					if (in_array($file, array('.', '..'))) {
						continue;
					}
					$ret .= $pre . "\t\t\t<li><a href=\"/userapp/" . $file . "\" id=\"app_" . $file . "\">" . $file . "</a></li>\n";
				}
				closedir($handle);
			}

		$ret .= $pre . "\t\t</ul>\n" .
			$pre . "\t</li>\n" .
			$pre . "\t<li style=\"white-space: nowrap;\">\n" .
			$pre . "\t\t<a href=\"#\" id=\"submenu_apps\">Management +</a>\n" .
			$pre . "\t\t<ul>\n" .
			$pre . "\t\t\t<li><a href=\"/userapp/\" id=\"app_management\">App Management</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"/app/berogui/index.php?m=market\" id=\"app_market\">App Market</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"/app/berogui/\" id=\"berogui\">beroGui</a></li>\n" .
			$pre . "\t\t\t<li><a href=\"/app/berogui/includes/logout.php\" id=\"logout\">Logout</a></li>\n" .
			$pre . "\t\t</ul>\n" .
			$pre . "\t</li>\n" .
                        $pre . "</ul>\n" .
			$pre . "</div>";

		return($ret);
	}

	function _dependencies ($list) {

		if (empty($list)) {
			return('');
		}

		foreach (explode(',', $list) as $dep) {
			if (!file_exists('/apps/' . $dep)) {
				$ret .= "<script>alert(\"OpenPBX needs package \'" . $dep . "\' to work properly!\");</script>\n";
			}
		}

		return($ret);
	}

	function main_header ($app_name, $mod) {

		$ba = new beroAri();

		# check if there is something to be activated
		$query = $ba->query("SELECT option FROM activate WHERE id = 'activate'");
		$option = $ba->fetch_single($query);
		unset($query);

                $ret.='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta http-equiv="cache-control" content="no-cache" />
		<link href="../../app/berogui/includes/css/screen.css" type="text/css" rel="stylesheet"/>
		<link href="../../app/berogui/includes/css/form.css" type="text/css" rel="stylesheet"/>
		<link href="../../app/berogui/includes/template_css.css" type="text/css" rel="stylesheet"/>
		<link href="../../app/berogui/includes/jqueryslidemenu.css" type="text/css" rel="stylesheet"/>
                <link rel="icon" type="image/x-icon" href="' . BAF_URL_BASE . '/img/favicon.ico" />
		<script src="http://'.$_SERVER['HTTP_HOST'].'/app/berogui/includes/js/jquery-1.7.2.min.js" type="text/javascript"></script>
		<script src="http://'.$_SERVER['HTTP_HOST'].'/app/berogui/includes/js/jqueryslidemenu.js" type="text/javascript"></script>
		<script src="http://'.$_SERVER['HTTP_HOST'].'/app/berogui/includes/js/jquery-validation/jquery.validate.min.js" type="text/javascript"></script>
                <script type="text/javascript" src=" '. BAF_URL_BASE . '/js/beroGui.js"></script>
		<title>'  . $mod->getTitle() . '</title>
	</head>
	<body onload=\'paint_apply_button("' . $option . '","' . $mod->getName() . '","' . $this->_lang->get('menu_activate_button') . '","' .
											$this->_lang->get('menu_activate_green'). '","' . $this->_lang->get('menu_activate_red') . '");\'>
		<div id="page" class="container">
			<div id="header">
				<div id="logo">
                                <img src="http://'.$_SERVER[HTTP_HOST].'/app/berogui/includes/images/beroNet.jpg" alt="beroNet" class="png" title="beroNet logo">
				</div>'.
				$this->_main_menu($mod->getName()).'
			</div>
			<br>
			<div class="clear" id="pageName">
				<div class="part1">
					<h1>'  . $mod->getTitle() . '</h1>
				</div>
			</div>
                        '.$this->_dependencies('asterisk,asterisk_sounds') .'
			<br>'.
                        "\t\t\t\t\t\t<div id=\"apply_button\" class=\"center\"></div><br/>\n";


		return($ret);
	}

	function main_footer () {

		$ret =	"<div>&nbsp;</div>
                        </div>
                        </body>
                        </html>";

		return($ret);
	}

	function popup_header ($mod) {

            $ret='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="../../../app/berogui/includes/css/screen.css" type="text/css" rel="stylesheet"/>
		<link href="../../../app/berogui/includes/template_css.css" type="text/css" rel="stylesheet"/>
		<link href="../../../app/berogui/includes/jqueryslidemenu.css" type="text/css" rel="stylesheet"/>
        	<link rel="stylesheet" type="text/css" href="' . BAF_URL_BASE . '/css/beroGui_forms.css" />
		<script src="http://'.$_SERVER['HTTP_HOST'].'/app/berogui/includes/js/jquery-1.7.2.min.js" type="text/javascript"></script>
		<script src="http://'.$_SERVER['HTTP_HOST'].'/app/berogui/includes/js/jqueryslidemenu.js" type="text/javascript"></script>
		<script src="http://'.$_SERVER['HTTP_HOST'].'/app/berogui/includes/js/jquery-validation/jquery.validate.min.js" type="text/javascript"></script>
                <script type="text/javascript" src=" '. BAF_URL_BASE . '/js/beroGui.js"></script>
		<title>' . $mod->getTitle() . '</title>
	</head>
	<body onload="resize();">
		<div id="page" style="width:auto important!; background:#fff;">
			<div id="header">
				<div id="logo">
                                <img src="' . BAF_URL_BASE . '/img/beroLogo.gif" />
				</div>&nbsp;
			</div>
			<br>
			<div class="clear" id="pageName">
				<div class="part1">
					<h3>'. $mod->getTitle() . '</h3>
				</div>
			</div>
			<br>';


		return($ret);
	}

	function popup_footer () {

		$ret =	"<div>&nbsp;</div>
                        </div>
                        </body>
                        </html>";

		return($ret);
	}
}

?>
