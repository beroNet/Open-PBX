#!/bin/bash

a=$(/sbin/ifconfig eth0 | grep inet | sed 's/.*addr:\(.*\) Bcast.*/\1/\')
localip=$(echo $a)


function print_sip_peer {
name=$1
cat <<EOF
[$name]
address = $localip:25060
register = 0
user = $name 
secret = $name 
externip = 
config = t38=1,ie_on_sip=0,wait_for_cancel=0,dtmfmode=rfc2833,s2i_cpt=,i2s_cpt=,failover_proxy=,failover_timeout=0,from_sip_src_setting=from_user,sip_from_user_setting=\${account_username},sip_from_display_setting=\${new_source}

EOF
}

function print_if_not_existing {
	name=$1
	if ! grep "\[$name\]" /usr/conf/isgw.sip > /dev/null  ; then 
		print_sip_peer $name
	fi
}

print_if_not_existing openpbx-gateway >> /usr/conf/isgw.sip

/usr/bin/env -i bash -c "/usr/local/www/berogui/misc/ini_to_db.php"  2>/dev/null
