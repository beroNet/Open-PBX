<?php

class lang {

	private $_lang_arr;

	function __construct () {

		$this->_lang_arr = array();

		# menu & headlines
		$this->_lang_arr['menu_dialplan']			= 'Wählplan';
		$this->_lang_arr['menu_siptrunks']			= 'SIP-Amtsleitung';
		$this->_lang_arr['menu_users']				= 'Benutzer';
		$this->_lang_arr['menu_devices']			= 'Geräte';
		$this->_lang_arr['menu_devices_phones']			= 'Telefone';
		$this->_lang_arr['menu_devices_templates']		= 'Vorlagen';
		$this->_lang_arr['menu_management']			= 'Verwaltung';
		$this->_lang_arr['menu_management_state']		= 'Status';
		$this->_lang_arr['menu_management_mail']		= 'E-Mail Einstellungen';
		$this->_lang_arr['menu_management_pnp']			= 'PNP Einstellungen';
		$this->_lang_arr['menu_management_backup']		= 'Sicherung';
		$this->_lang_arr['menu_management_userapp']		= 'UserApp Verwaltung';
		$this->_lang_arr['menu_management_berogui']		= 'beroGui';

		$this->_lang_arr['menu_activate_button']		= 'Änderungen anwenden';
		$this->_lang_arr['menu_activate_green']			= 'Möchten Sie die geänderten Einstellungen anwenden?';
		$this->_lang_arr['menu_activate_red']			= 'Dies wird alle laufenden Gespräche beenden! Wollen Sie fortfahren?';

		$this->_lang_arr['headline_dialplan']			= 'Wählplan';
		$this->_lang_arr['headline_siptrunks']			= 'SIP-Amtsleitung';
		$this->_lang_arr['headline_users']			= 'Benutzer & Gruppen';
		$this->_lang_arr['headline_devices_phones']		= 'Telefone';
		$this->_lang_arr['headline_devices_templates']		= 'Vorlagen';
		$this->_lang_arr['headline_management_state']		= 'Status';
		$this->_lang_arr['headline_management_mail']		= 'E-Mail Einstellungen';
		$this->_lang_arr['headline_management_pnp']		= 'PNP Einstellungen';
		$this->_lang_arr['headline_management_backup']		= 'Sicherung';

		# common fields
		$this->_lang_arr['Condition']				= 'Bedingung';
		$this->_lang_arr['Action']				= 'Aktion';
		$this->_lang_arr['Name']				= 'Name';
		$this->_lang_arr['User']				= 'Benutzer';
		$this->_lang_arr['Users']				= 'Benutzer';
		$this->_lang_arr['Group']                               = 'Gruppe';
		$this->_lang_arr['Groups']				= 'Gruppen';
		$this->_lang_arr['Registrar']				= 'Registrar';
		$this->_lang_arr['Proxy']				= 'Proxy';
		$this->_lang_arr['Extension']				= 'Durchwahl';
		$this->_lang_arr['Voicemail']				= 'Anrufbeantworter';
		$this->_lang_arr['Mail']				= 'E-Mail';
		$this->_lang_arr['Mail-Address']			= 'E-Mail-Adresse';
		$this->_lang_arr['Description']				= 'Beschreibung';
		$this->_lang_arr['IP-Address']				= 'IP-Adresse';
		$this->_lang_arr['MAC-Address']				= 'MAC-Adresse';
		$this->_lang_arr['Port']				= 'Port';
		$this->_lang_arr['Status']				= 'Status';
		$this->_lang_arr['State']				= 'Status';
		$this->_lang_arr['Username']				= 'Benutzername';
		$this->_lang_arr['Password']				= 'Passwort';
		$this->_lang_arr['Refresh']				= 'Erneuerung';
		$this->_lang_arr['Channel']				= 'Kanal';
		$this->_lang_arr['Location']				= 'Standort';
		$this->_lang_arr['Save']				= 'Speichern';
		$this->_lang_arr['Close']				= 'Schließen';
		$this->_lang_arr['All']					= 'Alle';
		$this->_lang_arr['Add']					= 'Hinzufügen';
		$this->_lang_arr['Enable']				= 'Aktivieren';
		$this->_lang_arr['Enabled']				= 'Aktiviert';
		$this->_lang_arr['Disable']				= 'Deaktivieren';
		$this->_lang_arr['Disabled']				= 'Deaktiviert';
		$this->_lang_arr['Delete']				= 'Löschen';
		$this->_lang_arr['Download']				= 'Herunterladen';
		$this->_lang_arr['Upload']				= 'Hochladen';
		$this->_lang_arr['Duration']				= 'Dauer';
		$this->_lang_arr['Dtmfmode']				= 'DTMF-Modus';
		$this->_lang_arr['Codecs']				= 'Codecs';
		$this->_lang_arr['Type']				= 'Typ';
		$this->_lang_arr['Template']				= 'Vorlage';
		$this->_lang_arr['Cut']					= 'Abschneiden';
		$this->_lang_arr['Prepend']				= 'Voranstellen';
		$this->_lang_arr['Prefix']				= 'Präfix';
		$this->_lang_arr['Target']				= 'Ziel';
		$this->_lang_arr['Source']				= 'Quelle';
		$this->_lang_arr['Trunk']				= 'Amtsleitung';
		$this->_lang_arr['Trunks']				= 'Amtsleitungen';
		$this->_lang_arr['Device']				= 'Gerät';
		$this->_lang_arr['Devices']				= 'Geräte';
		$this->_lang_arr['Other_Settings']			= 'Andere Einstellungen';
		$this->_lang_arr['DISA']				= 'DISA';
		$this->_lang_arr['Language']				= 'Sprache';
		$this->_lang_arr['Off']					= 'Aus';
		$this->_lang_arr['Phone_Number']			= 'Telefonnummer';
		$this->_lang_arr['Settings']				= 'Einstellungen';

		$this->_lang_arr['save']				= 'speichern';
		$this->_lang_arr['reset']				= 'zurücksetzen';
		$this->_lang_arr['reboot']				= 'neu starten';
		$this->_lang_arr['add']					= 'hinzufügen';
		$this->_lang_arr['modify']				= 'ändern';
		$this->_lang_arr['copy']				= 'kopieren';
		$this->_lang_arr['cut']					= 'ausschneiden';
		$this->_lang_arr['delete']				= 'löschen';
		$this->_lang_arr['enabled']				= 'aktiviert';
		$this->_lang_arr['disabled']				= 'deaktiviert';
		$this->_lang_arr['not_configured']			= 'nicht konfiguriert';
		$this->_lang_arr['phone']				= 'telefon';
		$this->_lang_arr['calls']                               = 'wählt';
		$this->_lang_arr['and']					= 'und';
		$this->_lang_arr['digits']				= 'Ziffern';
		$this->_lang_arr['digit(s)']                            = 'Ziffer(n)';
		$this->_lang_arr['long']				= 'lang';
		$this->_lang_arr['first']				= 'ersten';
		$this->_lang_arr['using']				= 'mittels';

		$this->_lang_arr['no_extensions_defined']		= 'Keine Durchwahlen definiert.';
		$this->_lang_arr['please_enter_a_valid_name']		= 'Bitte geben Sie einen validen Namen ein!';
		$this->_lang_arr['this_name_already_exists']		= 'Dieser Name existiert bereits.';
		$this->_lang_arr['this_name_already_inuse']		= 'Dieser Name ist in Benutzung.';
		$this->_lang_arr['this_extension_already_exists']	= 'Diese Durchwahl existiert bereits.';
		$this->_lang_arr['this_extension_already_inuse']	= 'Diese Durchwahl ist in Benutzung.';
		$this->_lang_arr['could_not_save']			= 'Konnte nicht abspeichern';
		$this->_lang_arr['does_not_exist']			= 'Die folgende Datei existiert nicht:';
		$this->_lang_arr['minimum_length']			= 'Minimale Länge';
		$this->_lang_arr['target_number']			= 'Ziel-Rufnummer';
		$this->_lang_arr['originating_extension']		= 'Quell-Durchwahl';
		$this->_lang_arr['please_fill_the_form']		= 'Bitte füllen Sie das Formular vollständig aus!';
		$this->_lang_arr['please_choose_another']		= 'Bitte ändern Sie Ihre Eingabe.';
		$this->_lang_arr['confirm_delete']			= 'Soll der Löschvorgang durchgeführt werden für';
		$this->_lang_arr['this_rule']				= 'diese Regel';
		$this->_lang_arr['mac_empty']				= 'Die angegebene MAC-Adresse ist leer!';
		$this->_lang_arr['mac_invalid']				= 'Die angegebene MAC-Adresse ist ungültig!';
		$this->_lang_arr['ip_empty']				= 'Die angegebene IP-Adresse ist leer!';
		$this->_lang_arr['ip_invalid']				= 'Die angegebene IP-Adresse ist ungültig!';
		$this->_lang_arr['no_item_in_source_listbox']		= 'Die Quell-Liste ist leer!';
		$this->_lang_arr['select_item_to_move']			= 'Bitte wählen sie ein Element aus, um es zu verschieben.';
		$this->_lang_arr['send_to_extension']                   = 'Weiterleiten an Durchwahl';
		$this->_lang_arr['send_to_voicemail']                   = 'Weiterleiten an die Mailbox der Durchwahl';
		$this->_lang_arr['send_to_disa']                        = 'Weiterleiten an DISA';
		$this->_lang_arr['dial']				= 'anrufen';
		$this->_lang_arr['hangup']				= 'auflegen';
		$this->_lang_arr['a_number']				= 'eine Nummer';
		$this->_lang_arr['ask_for_password']			= 'nach Passwort fragen';
		$this->_lang_arr['starting_with']			= 'beginnend mit';
		$this->_lang_arr['at_least']				= 'mindestens';


		# page 'Dialplan'
		$this->_lang_arr['dialplan_table_inbound_head']		= 'Regeln für eingehende Telefonate';
		$this->_lang_arr['dialplan_table_inbound_button_add']	= 'Regel hinzufügen (eingehend)';
		$this->_lang_arr['dialplan_table_outbound_head']	= 'Regeln für ausgehende Telefonate';
		$this->_lang_arr['dialplan_table_outbound_button_add']	= 'Regel hinzufügen (ausgehend)';

		# page SIP-Trunks
		$this->_lang_arr['siptrunks_table_head']		= 'SIP-Amtsleitungen';
		$this->_lang_arr['siptrunks_table_button_add']		= 'SIP-Amtsleitung hinzufügen';

		# page Users & Groups
		$this->_lang_arr['users_table_users_head']		= 'SIP-Benutzer';
		$this->_lang_arr['users_table_users_button_add']	= 'SIP-Benutzer hinzufügen';
		$this->_lang_arr['users_table_groups_head']		= 'SIP-Gruppen';
		$this->_lang_arr['users_table_groups_button_add']	= 'SIP-Gruppe hinzufügen';
		$this->_lang_arr['send_from_user']			= 'Sende \'fromuser\'';

		# page Devices -> Phones
		$this->_lang_arr['phones_table_users_head']		= 'SIP-Telefone';
		$this->_lang_arr['phones_table_users_button_add']	= 'SIP-Telefon hinzufügen';

		# page Management -> State
		$this->_lang_arr['state_table_sippeers_head']		= 'Angemeldete Geräte';
		$this->_lang_arr['state_table_sippeers_name_username']	= 'Name / Benutzername';
		$this->_lang_arr['state_table_sipregs_head']		= 'SIP-Registrierungen';
		$this->_lang_arr['state_table_sipregs_hostport']	= 'Host:Port';
		$this->_lang_arr['state_table_sipregs_regtime']		= 'Reg.Zeit';
		$this->_lang_arr['state_table_chans_head']		= 'Aktive Kanäle';
		$this->_lang_arr['state_table_chans_app_data']		= 'Anwendung(Daten)';

		# page Management -> Mail Configuration
		$this->_lang_arr['mail_table_head']			= 'E-Mail-Server Einstellungen';
		$this->_lang_arr['mail_table_host']			= 'SMTP-Host';
		$this->_lang_arr['mail_table_port']			= 'SMTP-Port';
		$this->_lang_arr['mail_table_user']			= 'SMTP-Benutzer';
		$this->_lang_arr['mail_table_pass']			= 'SMTP-Passwort';
		$this->_lang_arr['mail_table_from']			= 'SMTP-From';

		# page Management -> PNP Configuration
		$this->_lang_arr['pnp_daemon_head']			= 'Daemon Konfiguration';
		$this->_lang_arr['pnp_daemon_text']			= "Um SNOM-Telefone mit aktiviertem PNP-feature zu provisionieren,<br />" .
									  "aktivieren Sie den SNOM-PHP-Daemon hier.<br /><br />" .
									  "Die MAC-Adressen, die bedient werden sollen, können auf der linken Seite angegeben werden.<br /><br />";
		$this->_lang_arr['pnp_mac_head']			= 'MAC-Adressen basierte Provisionierung';
		$this->_lang_arr['pnp_mac_table_head']			= 'Verwaltete MAC-Adressen';

		# page Management -> Backup
		$this->_lang_arr['backup_table_download_head']		= 'Konfiguration herunterladen';
		$this->_lang_arr['backup_table_restore_head']		= 'Konfiguration wiederherstellen';

		# popup Add/Modify Phone
		$this->_lang_arr['popup_phone_title']			= 'Telefon Hinzufügen/Ändern';

		# popup Add/Modify Device Template
		$this->_lang_arr['popup_template_title']		= 'Vorlage Hinzufügen/Ändern';
		$this->_lang_arr['popup_template_modify_header']	= 'Vorlage Ändern';
		$this->_lang_arr['popup_template_copy_header']		= 'Vorlage Kopieren';
		$this->_lang_arr['popup_template_new_name']		= 'Name der neuen Vorlage';
		$this->_lang_arr['popup_template_new_description']	= 'Beschreibung der neuen Vorlage';

		# popup Dialplan
		$this->_lang_arr['popup_dialplan_add_rule']		= 'Regel hinzufügen';
		$this->_lang_arr['popup_dialplan_modify_rule']		= 'Regel ändern';
		$this->_lang_arr['popup_dialplan_use_trunk']		= 'Benutze Amtsleitung';

		# popup SIP
		$this->_lang_arr['popup_sip_title']			= 'SIP-Amtsleitung Hinzufügen/Ändern';
		$this->_lang_arr['popup_sip_table_title_add']		= 'SIP-Amtsleitung Hinzufügen';
		$this->_lang_arr['popup_sip_table_title_modify']	= 'SIP-Amtsleitung Ändern';

		# popup Users
		$this->_lang_arr['popup_users_table_title_user_add']	= 'Benutzer Hinzufügen';
		$this->_lang_arr['popup_users_table_title_user_modify']	= 'Benutzer Ändern';
		$this->_lang_arr['popup_users_table_title_group_add']	= 'Gruppe Hinzufügen';
		$this->_lang_arr['popup_users_table_title_group_modify']= 'Gruppe Ändern';
		$this->_lang_arr['popup_users_voicemail_note']		= "Um den Anrufbeantworter zu nutzen, konfigurieren Sie bitte einen SMTP-Server unter 'Verwaltung -> E-Mail Einstellungen'.";

		# Snom XML Buttons
		$this->_lang_arr['snomxml_button_main']			= 'Haupt';
		$this->_lang_arr['snomxml_button_back']			= '<<';
		$this->_lang_arr['snomxml_button_exit']			= 'Ende';

		# Snom XML Menu: Main
		$this->_lang_arr['snomxml_main_menu']			= 'OpenPBX Hauptmenü';
		$this->_lang_arr['snomxml_call_diversion']		= 'Anrufumleitung';

		# Snom XML Menu: Call Diversion
		$this->_lang_arr['snomxml_all_calls']			= 'Alle Anrufe';
		$this->_lang_arr['snomxml_line_busy']			= 'Besetzt';
		$this->_lang_arr['snomxml_not_available']		= 'Nicht Verfügbar';
		$this->_lang_arr['snomxml_diversion_all_calls']		= 'Umleitung bei Alle Anrufe';
		$this->_lang_arr['snomxml_diversion_line_busy']		= 'Umleitung wenn Besetzt';
		$this->_lang_arr['snomxml_diversion_not_available']	= 'Umleitung wenn Nicht Verfügbar';
	}

	public function get ($fieldname) {

		if (!array_key_exists($fieldname, $this->_lang_arr)) {
			return('Text Fehlt');
		}

		return($this->_lang_arr[$fieldname]);
	}
}
?>
