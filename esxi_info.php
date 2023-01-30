<?php

	// VARs
	$token = isset($_GET['token']) ? $_GET['token'] : '';
    $host = isset($_GET['host']) ? $_GET['host'] : '';
	$type = isset($_GET['type']) ? $_GET['type'] : '';
	$port = '22';
	$user = 'root';
	$pass = 'ESXI_PASSWD';
	$token_num = 'USER_DEFINE_TOKEN'; ### 防止陌生人亂call

	if ( $token == $token_num )
	{
		if (!$host or !$type)
		{
			echo "Host = ESXI_IP.\ntype =  SN / vlan / Mode / Rack / SNMP / IPMI / Vendor / Model / Uptime / Hostname.\n";
		}
		
		else
		{
			// SSH process
			$conn = ssh2_connect($host, $port);
			ssh2_auth_password($conn, 'root', $pass);
	
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
				#$command = "vim-cmd hostsvc/net/query_networkhint --pnic-name=vmnic5 |grep 'systemName =' |awk -F \= '{print $2}' |awk -F \. '{print $1}' |sed 's/\"//g' |sed 's/r/R/g' |sed 's/[[:space:]]//g'";
                $command = "vim-cmd hostsvc/net/query_networkhint --pnic-name=vmnic5 |grep 'devId =' |awk -F \= '{print $2}' |awk -F \. '{print $1}' |sed 's/\"//g' |sed 's/[[:space:]]//g' |sed 's/r/R/g'";
				break;

				case "SNMP":
                $command = "esxcli system snmp get |egrep 'Communities|Enable' |awk -F \: '{print $2}'";
                break;

				case "IPMI":
            	shell_exec("sshpass -p $pass scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null /usr/local/ESXI-update/ipmitool root@'$host':/tmp "); // 傳送ipmitool
				$command = "/tmp/ipmitool lan print |grep 'IP Address' |grep -v 'Source' |awk -F \: '{print $2}' |sed 's/[[:space:]]//g'";
                break;

				case "Vendor";
				$command = "esxcli hardware platform get |grep Vendor |sed 's/Vendor//g' |sed 's/Name//g' |sed 's/\://g' |sed 's/^     //g' |sed 's/Inc\.//g'";
				break;

				case "Model";
                $command = "esxcli hardware platform get |grep 'Product Name' |awk -F \: '{print $2}' |sed s'/PowerEdge//'g |sed s'/ProLiant//'g |sed s'/DL360//'g |sed s'/System//'g |sed s'/x3550//'g |sed s'/\://'g |sed s'/Think//'g |sed s'/IBM//'g |sed 's/\-\[7X02CTO1WW\]\-//g' |sed 's/\-\[5463IFE\]\-//g' |sed 's/[[:space:]]//g'";
                break;

				case "Uptime";
          		$command = "/bin/uptime |awk -F \, '{print $1}' |awk -F'up\' '{print $2}'";     
				break;

				case "Hostname";
                $command = "esxcli system hostname get |grep 'Host Name' |awk -F \: '{print $2}' |sed 's/^\ //'";
                break;

				case "BIOS";
                $command = "smbiosDump  |grep -A3 'BIOS Info' |grep Version |awk -F\: '{print $2}' |sed 's/[[:space:]]//g'";
                break;

				default:
				echo "Host = ESXI_IP.\ntype =  SN / vlan / Mode / Rack / SNMP / IPMI / Vendor / Model / Uptime / Hostname.\n";
			}
			
			// stream settings
			$stream = ssh2_exec($conn, "$command");
			stream_set_blocking($stream, true);
			$stream_out=ssh2_fetch_stream($stream, SSH2_STREAM_STDIO); //大小寫有差
	
			// Output
			echo trim(stream_get_contents($stream_out));
		}
	}

	else
	{
		echo "Wrong Token.\n";
	}
?>
