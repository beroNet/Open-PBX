/* functions to control windows */
function popup_open (url) {

	left_pos = ((screen.width - 750) / 2);
	height_pos = ((screen.height - 400) / 2);

	popup_win = window.open(url, "beroPBX", "width=720,height=480,left=" + left_pos + ",top=" + height_pos + ",menubar=no,location=no,resizable=yes,scrollbars=yes,status=no");
	popup_win.focus();
}

function popup_close () {
	this.window.close();
}

function resize(){
	if (this.window.sizeToContent) {
		this.window.sizeToContent();
	}
}

/* function to check if IP is valid */
function verifyIP (IPvalue) {

	var ipPattern = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
	var ipArray = IPvalue.match(ipPattern);

	if (ipArray == null) {
		alert('IP-address: ' + IPvalue + ' is not valid.');
		return false;
	}

	for (var i = 1; i <= 4; i++) {
		if (ipArray[i] > 255) {
			alert('IP-address: ' + IPvalue + ' is not valid.');
			return false;
		}
	}

	return true;
}

function limited (text, max) {
	if (text.value.length>=max) {
		text.value=text.value.substring(0,max);
	}
	document.getElementById("zz").innerHTML = text.value.length + ", Rest " + (max - text.value.length);
}

/* button 'apply settings' */
function paint_apply_button (level, module) {

	if ((level == null) || (level == 0)) {
		return;
	}

	var url = '/userapp/beroPBX/includes/create_files.php?m=' + module;

	switch (level) {
		default:
		case '1':
			var bid = 'apply_button_green';
			var txt = 'Do you want to continue and apply the settings made?';
			break;
		case '2':
			var bid = 'apply_button_red';
			var txt = 'This will finish all calls. Do you want to continue?';
			break;
	}

	var button_div = document.getElementById('apply_button');
	button_div.style.display = 'block';
	button_div.innerHTML += "<input type=\"button\" id=\"" + bid + "\" value=\"Apply Changes\" onclick=\"if(confirm('" + txt + "') == true) {window.location.href='" + url + "';}\" />";
}

function addselect (id) {
	/*
	var cid_name_tr=document.getElementById('cid_name_tr');
	var cid_number_tr=document.getElementById('cid_number_tr');
	var to_ext_tr=document.getElementById('to_ext_tr');
	var to_tr=document.getElementById('to_tr');
	*/
	var goto = document.getElementById('goto');
	var setcid = document.getElementById('setcid');

	if (!id){
		goto.style.display = "none";
		setcid.style.display = "none";
		/*cid_name_tr.style.display="none";
		cid_number_tr.style.display="none";
		to_ext_tr.style.display="none";
		to_tr.style.display="none";*/
	} else {
		show();
	}
}


function show () {

	var goto = document.getElementById('goto');
	var dial = document.getElementById('dial');
	var setcid = document.getElementById('setcid');
	var action = document.getElementById('action');

	/*
	var to=document.getElementById('to');
	var to_tr=document.getElementById('to_tr');
	var to_sip=document.getElementById('to_sip');
	var to_ext_tr=document.getElementById('to_ext_tr');
	var dest_number_tr=document.getElementById('dest_tr');
	var cid_name_tr=document.getElementById('cid_name_tr');
	var cid_number_tr=document.getElementById('cid_number_tr');
	var cut_tr=document.getElementById('cut_tr');
	var replace_tr=document.getElementById('replace_tr');
	var sip_apparat=document.getElementById('sip_apparat');
	var isdn_gruppe=document.getElementById('isdn_gruppe');
	*/

	switch (action.options[action.selectedIndex].value) {
		case 'JUMP_TO':
			goto.style.display = "";
			dial.style.display = "none";
			setcid.style.display = "none";
			/*
			to_tr.style.display="";
			cut_tr.style.display="";
			replace_tr.style.display="";
			to_sip.style.display="none";
			to_ext_tr.style.display="none";
			cid_name_tr.style.display="none";
			cid_number_tr.style.display="none";
			*/
		break;
		case 'SET_CALLERID':
			setcid.style.display = "";
			goto.style.display = "none";
			dial.style.display = "none";
			/*
			dest_number_tr.style.display = "";
			to_tr.style.display = "none";
			to_sip.style.display = "none";
			to_ext_tr.style.display = "none";
			cid_name_tr.style.display = "";
			cid_number_tr.style.display = "";
			cut_tr.style.display = "none";
			replace_tr.style.display = "none";
			*/
		break;
		case 'Dial':
			dial.style.display = "";
			setcid.style.display = "none";
			goto.style.display = "none";
			/*
			dest_number_tr.style.display="";
			to_tr.style.display="none";
			to_ext_tr.style.display="none";
			cid_name_tr.style.display="none";
			cid_number_tr.style.display="none";
			cut_tr.style.display="";
			to_sip.style.display="";
			replace_tr.style.display="";
			*/
		break;
	}
}

function is_submit (form) {
     if (form.is_submit) {
	     return false;
     }

     form.is_submit = true;
     return true;
}

// functions for switch-lists
function move (Orig, Dest) {

	var varFromBox = document.getElementById(Orig);
	var varToBox = document.getElementById(Dest);

	if ((varFromBox == null) || (varToBox == null)) {
		return false;
	}

	if(varFromBox.length < 1) {
		alert('There are no items in the source ListBox');
		return false;
	}

	if(varFromBox.options.selectedIndex == -1) {
		alert('Please select an Item to move');
		return false;
	}

	while (varFromBox.options.selectedIndex >= 0) {
		var newOption = new Option();

		newOption.text = varFromBox.options[varFromBox.options.selectedIndex].text;
		newOption.value = varFromBox.options[varFromBox.options.selectedIndex].value;
		varToBox.options[varToBox.length] = newOption;

		varFromBox.remove(varFromBox.options.selectedIndex);

	}

	return true;
}

function selectall(name) {

	if ((name == null) || (name == '')) {
		name = 'sel';
	}

	selectBox = document.getElementById(name);

	for (var i = 0; i < selectBox.options.length; i++) {
		selectBox.options[i].selected = true;
	}
}

function up (name) {

	if ((name == null) || (name == '')) {
		name = 'sel';
	}

	obj = document.getElementById(name);
	index = obj.selectedIndex;

	if (index > 0) {
		change(obj, index, (index - 1));
	}
}

function down (name) {

	if ((name == null) || (name == '')) {
		name = 'sel';
	}

	obj = document.getElementById(name);
	index = obj.selectedIndex;

	if ((index != -1) && (index < (obj.length - 1))) {
		change(obj, index, (index + 1));
	}
}

function change(obj,num1,num2) {
	proVal = obj.options[num1].value;
	proTex = obj.options[num1].text;

	obj.options[num1].value = obj.options[num2].value;
	obj.options[num1].text = obj.options[num2].text;

	obj.options[num2].value = proVal;
	obj.options[num2].text = proTex;

	obj.selectedIndex = num2;
}

function display_hidden_rule(obj, dispMode) {

	for (i=0; i<obj.length; i++) {
		nodisp = document.getElementById('rule_' + obj.options[i].text);
		if (nodisp != null) {
			nodisp.style.display = 'none';
		}
	}

	dispObj = document.getElementById('rule_' + obj.options[obj.selectedIndex].text);
	dispObj.style.display = dispMode;
}

function display_hidden_init (name, dispMode) {

	dispObj = document.getElementById(name);
	dispObj.style.display = dispMode;
}

function confirm_delete (name, url) {

	if (confirm('Do you really want to delete \'' + name + '\'?') == false) {
		return(false);
	}

	if (url != null) {
		this.window.location.href = url;
	}

	return(true);
}
