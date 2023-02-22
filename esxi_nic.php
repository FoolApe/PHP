<?php

	// VARs
	$token = $_GET['token']??''; // php7 以後的語法 , 縮減長判斷式
    $host = $_GET['host']??'';
	$type = $_GET['type']??'';
    $vmnic = $_GET['vmnic']??'';
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
	$ssh2_conn = ssh2_connect($host, $port, [], ['connection_timeout => 3']);
    if (!$ssh2_conn)
    {
        throw new Exception("Unable to establish SSH connection");
    }
	ssh2_auth_password($ssh2_conn, $user, $pass);
    
	// Type options
	switch($type)
	{
        case "port"; // 對接vswitch_port
        $command = "vim-cmd hostsvc/net/query_networkhint --pnic-name='$vmnic' |grep 'portId' |awk -F \= '{print $2}' |sed 's/\,//g' |sed 's/\"//g' |sed 's/[[:space:]]//g'";
        break;

        case "switch"; // 對接實體switch名稱(櫃位)
        $command = "vim-cmd hostsvc/net/query_networkhint --pnic-name='$vmnic' |grep 'devId' |awk -F \= '{print $2}' |sed 's/\,//g' |sed 's/\"//g' |sed 's/[[:space:]]//g'";
        break;

        case "switch_vlan"; // 對接實體switch開放的vlan
        $command = "vim-cmd hostsvc/net/query_networkhint --pnic-name='$vmnic' |grep vlanId |awk -F \= '{print $2}' |sort -n |xargs |sed 's/[[:space:]]//g' |sed 's/,$//'";
        break;

        case "speed"; 
        $command = "esxcli network nic list |grep '$vmnic' |awk '{print $6}'";
        break;

		default:
		echo "Host = ESXI_IP.\ntype =  SN / vlan / Mode / Rack / SNMP / IPMI / Vendor / Model / Uptime / Hostname.\n";
        exit;
	}
	
	// stream settings
	$stream = ssh2_exec($ssh2_conn, "$command");
	stream_set_blocking($stream, true);
    stream_set_timeout($stream, 1);
	$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO); //大小寫有差
	
	// Output
	echo trim(stream_get_contents($stream_out));
?>
