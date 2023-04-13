<?php

	// VARs
	$token = $_GET['token']??''; // php7 以後的語法 , 縮減長判斷式
    $host = $_GET['host']??'';
	$type = $_GET['type']??'';
	$port = '22';
	$user = 'USER';
	$pass = 'PASSWD';
	$token_num = 'CUSTOM_TOKEN_420';

	if ( $token !== $token_num )
	{
         echo "Wrong Token.\n";
         exit;
    }
	if (!$host || !$type)
	{
		echo "Host = ESXI_IP.\ntype =  SN / vlan / Mode / Rack / SNMP / IPMI / Vendor / Model / Uptime / Hostname.\n";
        exit;
	}
		
	// SSH process
	$ssh2_conn = ssh2_connect($host, $port, [], ['connect_timeout' => 10]);
	ssh2_auth_password($ssh2_conn, $user, $pass);
	
	// Type options
	switch($type)
	{
		case "SN":
		$command = "esxcli hardware platform get |grep 'Serial Number' |grep -v 'Enclosure' |awk -F \: '{print $2}' |sed 's/[[:space:]]//g'";
		break;

		case "vlan":
		$command = "esxcli network vswitch standard portgroup list |awk -F'vSwitch' '{print $2}' |awk '{print $3}' |sort -n |uniq |sed '/^$/d'";
		break;

		case "Mode":
	        $command = "esxcli system maintenanceMode get";
        	break;

		case "Rack":
	        $command = "vim-cmd hostsvc/net/query_networkhint --pnic-name=vmnic6 |grep 'devId =' |awk -F \= '{print $2}' |awk -F \. '{print $1}' |sed 's/\"//g' |awk -F \- '{print $1}' |sed 's/[[:space:]]//g'";
		break;

		case "SNMP":
        	$command = "esxcli system snmp get |egrep 'Communities|Enable' |awk -F \: '{print $2}'";
	        break;

		case "IPMI":
    		shell_exec("sshpass -p $pass scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null /var/www/html/package/ipmitool root@'$host':/tmp "); // 傳送ipmitool
		$command = "/tmp/ipmitool lan print |grep 'IP Address' |grep -v 'Source' |awk -F \: '{print $2}' |sed 's/[[:space:]]//g'";
	        break;

		case "Vendor";
		$command = "esxcli hardware platform get |grep Vendor |sed 's/Vendor//g' |sed 's/Name//g' |sed 's/\://g' |sed 's/^     //g' |sed 's/Inc\.//g'";
		break;

		case "Model";
        	$command = "esxcli hardware platform get |grep 'Product Name' |awk -F \: '{print $2}' |sed 's/^\ //g'";
	        break;

		case "Uptime";
    		$command = "/bin/uptime |awk -F \, '{print $1}' |awk -F'up\' '{print $2}' |sed 's/^\ //g'";     
		break;

		case "Hostname";
	        $command = "esxcli system hostname get |grep 'Host Name' |awk -F \: '{print $2}' |sed 's/^\ //'";
        	break;

		default:
		echo "Host = ESXI_IP.\ntype =  SN / vlan / Mode / Rack / SNMP / IPMI / Vendor / Model / Uptime / Hostname.\n";
	}
	
	// stream settings
	$stream = ssh2_exec($ssh2_conn, "$command");
	stream_set_blocking($stream, true);
    stream_set_timeout($stream, 1);
	$stream_out=ssh2_fetch_stream($stream, SSH2_STREAM_STDIO); //大小寫有差
	
	// Output
	echo trim(stream_get_contents($stream_out));
?>
