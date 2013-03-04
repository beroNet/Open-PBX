<?php

class lang {

	private $_lang_arr;

	function __construct () {

		$this->_lang_arr = array();

		# menu & headlines
		$this->_lang_arr['menu_dialplan']			= 'Dialplan';
		$this->_lang_arr['menu_siptrunks']			= 'SIP-Trunks';
		$this->_lang_arr['menu_users']				= 'Users & Groups';
		$this->_lang_arr['menu_devices']			= 'Devices';
		$this->_lang_arr['menu_devices_phones']			= 'Phones';
		$this->_lang_arr['menu_devices_templates']		= 'Templates';
		$this->_lang_arr['menu_management']			= 'Management';
		$this->_lang_arr['menu_management_state']		= 'State';
		$this->_lang_arr['menu_management_mail']		= 'Mail Configuration';
		$this->_lang_arr['menu_management_pnp']			= 'PNP Configuration';
		$this->_lang_arr['menu_management_backup']		= 'Backup/Restore';
		$this->_lang_arr['menu_management_userapp']		= 'UserApp Management';
		$this->_lang_arr['menu_management_berogui']		= 'beroGui';

		$this->_lang_arr['menu_activate_button']		= 'Apply Changes';
		$this->_lang_arr['menu_activate_green']			= 'Do you want to continue and apply the settings made?';
		$this->_lang_arr['menu_activate_red']			= 'This will finish all calls. Do you want to continue?';

		$this->_lang_arr['headline_dialplan']			= 'Dialplan';
		$this->_lang_arr['headline_siptrunks']			= 'SIP-Trunks';
		$this->_lang_arr['headline_users']			= 'Users & Groups';
		$this->_lang_arr['headline_devices_phones']		= 'Phones';
		$this->_lang_arr['headline_devices_templates']		= 'Phone Templates';
		$this->_lang_arr['headline_management_state']		= 'State';
		$this->_lang_arr['headline_management_mail']		= 'Mail Configuration';
		$this->_lang_arr['headline_management_pnp']		= 'PNP Configuration';
		$this->_lang_arr['headline_management_backup']		= 'Backup/Restore';

		# common fields
		$this->_lang_arr['Condition']				= 'Condition';
		$this->_lang_arr['Action']				= 'Action';
		$this->_lang_arr['Name']				= 'Name';
		$this->_lang_arr['User']				= 'User';
		$this->_lang_arr['Users']				= 'Users';
		$this->_lang_arr['Group']				= 'Group';
		$this->_lang_arr['Groups']				= 'Groups';
		$this->_lang_arr['Registrar']				= 'Registrar';
		$this->_lang_arr['Proxy']				= 'Proxy';
		$this->_lang_arr['Extension']				= 'Extension';
		$this->_lang_arr['Voicemail']				= 'Voicemail';
		$this->_lang_arr['Mail']				= 'Mail';
		$this->_lang_arr['Mail-Address']			= 'Mail-Address';
		$this->_lang_arr['Description']				= 'Description';
		$this->_lang_arr['IP-Address']				= 'IP-Address';
		$this->_lang_arr['MAC-Address']				= 'MAC-Address';
		$this->_lang_arr['Port']				= 'Port';
		$this->_lang_arr['Status']				= 'Status';
		$this->_lang_arr['State']				= 'State';
		$this->_lang_arr['Username']				= 'Username';
		$this->_lang_arr['Password']				= 'Password';
		$this->_lang_arr['Refresh']				= 'Refresh';
		$this->_lang_arr['Channel']				= 'Channel';
		$this->_lang_arr['Location']				= 'Location';
		$this->_lang_arr['Save']				= 'Save';
		$this->_lang_arr['Close']				= 'Close';
		$this->_lang_arr['All']					= 'All';
		$this->_lang_arr['Add']					= 'Add';
		$this->_lang_arr['Enable']				= 'Enable';
		$this->_lang_arr['Enabled']				= 'Enabled';
		$this->_lang_arr['Disable']				= 'Disable';
		$this->_lang_arr['Disabled']				= 'Disabled';
		$this->_lang_arr['Delete']				= 'Delete';
		$this->_lang_arr['Download']				= 'Download';
		$this->_lang_arr['Upload']				= 'Upload';
		$this->_lang_arr['Duration']				= 'Duration';
		$this->_lang_arr['Dtmfmode']				= 'DTMF-Mode';
		$this->_lang_arr['Codecs']				= 'Codecs';
		$this->_lang_arr['Type']				= 'Type';
		$this->_lang_arr['Template']				= 'Template';
		$this->_lang_arr['Cut']					= 'Cut';
		$this->_lang_arr['Prepend']				= 'Prepend';
		$this->_lang_arr['Prefix']				= 'Prefix';
		$this->_lang_arr['Target']				= 'Target';
		$this->_lang_arr['Source']				= 'Source';
		$this->_lang_arr['Trunk']				= 'Trunk';
		$this->_lang_arr['Devices']				= 'Devices';
		$this->_lang_arr['Other_Settings']			= 'Other Settings';
		$this->_lang_arr['DISA']				= 'DISA';
		$this->_lang_arr['Language']				= 'Language';
		$this->_lang_arr['Off']					= 'Off';
		$this->_lang_arr['Phone_Number']			= 'Phone Number';

		$this->_lang_arr['save']				= 'save';
		$this->_lang_arr['reset']				= 'reset';
		$this->_lang_arr['reboot']				= 'reboot';
		$this->_lang_arr['add']					= 'add';
		$this->_lang_arr['modify']				= 'modify';
		$this->_lang_arr['copy']				= 'copy';
		$this->_lang_arr['cut']					= 'cut';
		$this->_lang_arr['delete']				= 'delete';
		$this->_lang_arr['enabled']				= 'enabled';
		$this->_lang_arr['disabled']				= 'disabled';
		$this->_lang_arr['not_configured']			= 'not configured';
		$this->_lang_arr['phone']				= 'phone';
		$this->_lang_arr['calls']				= 'calls';
		$this->_lang_arr['and']					= 'and';
		$this->_lang_arr['digits']				= 'digits';
		$this->_lang_arr['digit(s)']				= 'digit(s)';
		$this->_lang_arr['long']				= 'long';
		$this->_lang_arr['first']				= 'first';
		$this->_lang_arr['using']				= 'using';

		$this->_lang_arr['no_extensions_defined']		= 'No Extensions defined.';
		$this->_lang_arr['please_enter_a_valid_name']		= 'Please Enter a valid Name!';
		$this->_lang_arr['this_name_already_exists']		= 'This Name already exists.';
		$this->_lang_arr['this_name_already_inuse']		= 'This Name is already in use.';
		$this->_lang_arr['this_extension_already_exists']	= 'This Extension already exists.';
		$this->_lang_arr['this_extension_already_inuse']	= 'This Extension is already in use.';
		$this->_lang_arr['could_not_save']			= 'Could not save';
		$this->_lang_arr['does_not_exist']			= 'The following file does not exist:';
		$this->_lang_arr['minimum_length']			= 'Minimum Length';
		$this->_lang_arr['target_number']			= 'Target Number';
		$this->_lang_arr['originating_extension']		= 'Originating Extension';
		$this->_lang_arr['please_fill_the_form']		= 'Please fill in the form completely!';
		$this->_lang_arr['please_choose_another']		= 'Please choose another.';
		$this->_lang_arr['confirm_delete']			= 'Do you really want to delete';
		$this->_lang_arr['this_rule']				= 'this rule';
		$this->_lang_arr['mac_empty']				= 'The MAC-Address given is empty!';
		$this->_lang_arr['mac_invalid']				= 'The MAC-Address given is invalid!';
		$this->_lang_arr['ip_empty']				= 'The IP-Address given is empty!';
		$this->_lang_arr['ip_invalid']				= 'The IP-Address given is invalid!';
		$this->_lang_arr['no_item_in_source_listbox']		= 'The Source-List is empty!';
		$this->_lang_arr['select_item_to_move']			= 'Please select an item to move.';
		$this->_lang_arr['send_to_extension']			= 'Send to Extension';
		$this->_lang_arr['send_to_voicemail']			= 'Send to VoiceMailbox of Extension';
		$this->_lang_arr['send_to_disa']			= 'Send to DISA';
		$this->_lang_arr['dial']				= 'dial';
		$this->_lang_arr['hangup']				= 'hang up';
		$this->_lang_arr['a_number']				= 'a number';
		$this->_lang_arr['ask_for_password']			= 'ask for password';
		$this->_lang_arr['starting_with']			= 'starting with';
		$this->_lang_arr['at_least']				= 'at least';

		# page 'Dialplan'
		$this->_lang_arr['dialplan_table_inbound_head']		= 'Rules for inbound calls';
		$this->_lang_arr['dialplan_table_inbound_button_add']	= 'Add inbound rule';
		$this->_lang_arr['dialplan_table_outbound_head']	= 'Rules for outbound calls';
		$this->_lang_arr['dialplan_table_outbound_button_add']	= 'Add outbound rule';

		# page SIP-Trunks
		$this->_lang_arr['siptrunks_table_head']		= 'SIP-Trunks';
		$this->_lang_arr['siptrunks_table_button_add']		= 'Add SIP-Trunk';

		# page Users & Groups
		$this->_lang_arr['users_table_users_head']		= 'SIP-Users';
		$this->_lang_arr['users_table_users_button_add']	= 'Add SIP-User';
		$this->_lang_arr['users_table_groups_head']		= 'SIP-Groups';
		$this->_lang_arr['users_table_groups_button_add']	= 'Add SIP-Group';

		# page Devices -> Phones
		$this->_lang_arr['phones_table_users_head']		= 'SIP-Phones';
		$this->_lang_arr['phones_table_users_button_add']	= 'Add SIP-Phone';

		# page Management -> State
		$this->_lang_arr['state_table_sippeers_head']		= 'SIP-Peers';
		$this->_lang_arr['state_table_sippeers_name_username']	= 'Name / Username';
		$this->_lang_arr['state_table_sipregs_head']		= 'SIP-Registrations';
		$this->_lang_arr['state_table_sipregs_hostport']	= 'Host:Port';
		$this->_lang_arr['state_table_sipregs_regtime']		= 'Reg.Time';
		$this->_lang_arr['state_table_chans_head']		= 'Active Channels';
		$this->_lang_arr['state_table_chans_app_data']		= 'Application(Data)';

		# page Management -> Mail Configuration
		$this->_lang_arr['mail_table_head']			= 'Mail-Server Settings';
		$this->_lang_arr['mail_table_host']			= 'SMTP-Host';
		$this->_lang_arr['mail_table_port']			= 'SMTP-Port';
		$this->_lang_arr['mail_table_user']			= 'SMTP-User';
		$this->_lang_arr['mail_table_pass']			= 'SMTP-Password';
		$this->_lang_arr['mail_table_from']			= 'SMTP-From';

		# page Management -> PNP Configuration
		$this->_lang_arr['pnp_daemon_head']			= 'Daemon Configuration';
		$this->_lang_arr['pnp_daemon_text']			= "To provision SNOM-phones with enabled PNP-feature,<br />" .
									  "enable the SNOM-PNP-daemon here.<br /><br />" .
									  "The MAC-Addresses to be served can be entered on the left side.<br /><br />";
		$this->_lang_arr['pnp_mac_head']			= 'MAC-Address based provisioning';
		$this->_lang_arr['pnp_mac_table_head']			= 'Managed MAC-Addresses';

		# page Management -> Backup
		$this->_lang_arr['backup_table_download_head']		= 'Download Configuration';
		$this->_lang_arr['backup_table_restore_head']		= 'Restore Configuration';

		# popup Add/Modify Phone
		$this->_lang_arr['popup_phone_title']			= 'Add/Modify Phone';

		# popup Add/Modify Device Template
		$this->_lang_arr['popup_template_title']		= 'Add/Modify Device Template';
		$this->_lang_arr['popup_template_modify_header']	= 'Modify Template';
		$this->_lang_arr['popup_template_copy_header']		= 'Copy Template';
		$this->_lang_arr['popup_template_new_name']		= 'New Template Name';
		$this->_lang_arr['popup_template_new_description']	= 'New Template Description';

		# popup Dialplan
		$this->_lang_arr['popup_dialplan_add_rule']		= 'Add Rule';
		$this->_lang_arr['popup_dialplan_modify_rule']		= 'Modify Rule';
		$this->_lang_arr['popup_dialplan_use_trunk']		= 'Use Trunk';

		# popup SIP
		$this->_lang_arr['popup_sip_title']			= 'Add/Modify SIP-Trunk';
		$this->_lang_arr['popup_sip_table_title_add']		= 'Add SIP-Trunk';
		$this->_lang_arr['popup_sip_table_title_modify']	= 'Modify SIP-Trunk';

		# popup Users
		$this->_lang_arr['popup_users_table_title_user_add']	= 'Add User';
		$this->_lang_arr['popup_users_table_title_user_modify']	= 'Modify User';
		$this->_lang_arr['popup_users_table_title_group_add']	= 'Add Group';
		$this->_lang_arr['popup_users_table_title_group_modify']= 'Modify Group';
		$this->_lang_arr['popup_users_voicemail_note']		= "To use Voicemail, please configure an SMTP-Server in 'Management -> Mail-Settings'.";

		# Snom XML Buttons
		$this->_lang_arr['snomxml_button_main']			= 'Main';
		$this->_lang_arr['snomxml_button_back']			= 'Back';
		$this->_lang_arr['snomxml_button_exit']			= 'Exit';

		# Snom XML Menu: Main
		$this->_lang_arr['snomxml_main_menu']			= 'OpenPBX Main Menu';
		$this->_lang_arr['snomxml_call_diversion']		= 'Call Diversion';

		# Snom XML Menu: Call Diversion
		$this->_lang_arr['snomxml_all_calls']			= 'All Calls';
		$this->_lang_arr['snomxml_line_busy']			= 'Line Busy';
		$this->_lang_arr['snomxml_not_available']		= 'Not Available';
		$this->_lang_arr['snomxml_diversion_all_calls']		= 'Diversion of All Calls';
		$this->_lang_arr['snomxml_diversion_line_busy']		= 'Diversion if Line Busy';
		$this->_lang_arr['snomxml_diversion_not_available']	= 'Diversion if Not Available';
	}

	public function get ($fieldname) {

		if (!array_key_exists($fieldname, $this->_lang_arr)) {
			return('Text Missing');
		}

		return($this->_lang_arr[$fieldname]);
	}
}
?>
