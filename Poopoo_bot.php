<?php
date_default_timezone_set('Asia/Taipei'); //設定時區
while(true) //無限迴圈,重複觸發
{
	$token = '****483605:AAFw8Xu-200iDQLI58C54tdYhj6XZ8*****'; //PooPoo_bot
	$token2 = '****83451462cf20fefb5a006a72****';
	$chat_id = '-62***9261'; //機器人小天堂
	$log = '/tmp/test123.json';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot${token}/getUpdates");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($ch);
    curl_close($ch);
	$tmp = json_decode($result,true);

	// 取得最新的一筆update_id的資料
	$num = count($tmp["result"]);
	$num = $num-1;
	$update_id = ($tmp["result"][$num]["update_id"]);
	$user_id = ($tmp["result"][$num]["message"]["from"]["first_name"]);
	$text = ($tmp["result"][$num]["message"]["text"]);

	$update_id = $update_id+1 ;
	while(true) //無限迴圈,持續監聽下一個update_id
	{
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot${token}/getUpdates");
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,"timeout=50&offset=$update_id");
		$result = curl_exec($ch);
		$tmp = json_decode($result,true);
	    curl_close($ch);
		if ($tmp["result"][0]["message"]["text"])
		{
			$text = $tmp["result"][0]["message"]["text"];
			$user = $tmp["result"][0]["message"]["from"]["first_name"];
			$time = date("Y-m-d H:i:s");
			$data = "$time | $user | $text\n";
			file_put_contents("$log", $data, FILE_APPEND); //寫入Log
			if (preg_match('/^\/talk(.*)$/i', $text))	### 跟機器人聊天
            {
		    	preg_match('/\/talk(.*)/i', $text, $matches);
            	$line = trim($matches[1]);
		        if($line)
            	{
		        	switch($line)
            	    {
		                case "安安":
            	        $message = "${user}安安\n";
		                break;

            	        case "HI":
		                $message = "早上好${user}\n";
            	        break;

						case "hi":
						$message = "早上好${user}\n";
						break;

		                default:
            	        $message = "/talk 想說的話\n";
		            }
		        }
            	else
		        {
	   	        	$message = "我不知道${user}在說啥QAQ\n";
		        }
		    }			

			elseif (preg_match('/^\/function(.*)$/i', $text))	### 機器人小功能
            {
		       preg_match('/\/function(.*)/i', $text, $matches);
		       $exec = trim($matches[1]);
               if($exec)
		       {
               		switch($exec)
		       		{
		            	case "date":
						$time = date("Y/n/j, D, G:i:s");
            		    $message = "${user}, 現在是 $time\n";
		                break;
						// ============= //
							
            		    default:
		                $message = "${user}可以試試以下指令\n/function date -> 今天日期";
		            }
		        }
            	
				else
		        {
            		$message = "我不知道${user}想做什麼@@\n";
		        }
		    }

			elseif (preg_match('/^\/price(.*)$/i', $text))	### 問幣價
            {
		        preg_match('/\/price(.*)/i', $text, $matches);
                $exec = trim($matches[1]);
		        if($exec)
                {
				    		$key = '***f8e94-c133-****-8fee-dc5a0cf*****'; //API_key
		                    $url_cmc = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest'; // CoinmarketCap的url
                		    //$currency = 'BTC,ETH,BNB,SOL,NEAR,FTM,GMT,GST';

		                    // -H hearder設定
                		    $headers = array(
							'X-CMC_PRO_API_KEY:'."$key",
		                    'ACCept:'.'application/json');

                		    // -d 參數設定
		                    $parameters = [
               			    	'symbol' => 'BTC,ETH,ETC,BNB,SOL,NEAR,FTM,GMT,GST',
		                        'convert' => 'USD'];

                		    // curl設定
		                    $qs = http_build_query($parameters); // query string encode the parameters
                		    $request = "{$url_cmc}?{$qs}"; // create the request URL
		                    $curl_cmc = curl_init();
                		    curl_setopt_array($curl_cmc, array(
		                        CURLOPT_URL => $request,            // set the request URL
                		        CURLOPT_HTTPHEADER => $headers,     // set the headers
		                        CURLOPT_RETURNTRANSFER => 1));

                		    // 呼叫API
		                    $response = curl_exec($curl_cmc); // Send the request, save the response
                		    $result = json_decode($response, true);

		    		    	// 輸出變數
		                    $BTC = round($result["data"]["BTC"]["quote"]["USD"]["price"], 3);
                		    $ETH = round($result["data"]["ETH"]["quote"]["USD"]["price"], 3);
		    		    	$ETC = round($result["data"]["ETC"]["quote"]["USD"]["price"], 3);
		    		    	$BNB = round($result["data"]["BNB"]["quote"]["USD"]["price"], 3);
		                    $SOL = round($result["data"]["SOL"]["quote"]["USD"]["price"], 3);
		  		    		$NEAR = round($result["data"]["NEAR"]["quote"]["USD"]["price"], 3);
				    		$FTM = round($result["data"]["FTM"]["quote"]["USD"]["price"], 3);
 		                    $GMT = round($result["data"]["GMT"]["quote"]["USD"]["price"], 3);
                 		    $GST = round($result["data"]["GST"]["quote"]["USD"]["price"], 3);
		    		    	switch($exec)
                    		{
				    			case "stepn":
								$message = "GST -> $GST\nGMT -> $GMT\n";
								break;

								case "big":
		                        $message = "BTC -> $BTC\nETH -> $ETH\nETC -> $ETC\n";
                		        break;		
						
								case "chain":
								$message = "BNB -> $BNB\nSOL -> $SOL\nFTM -> $FTM\nNEAR -> $NEAR";
								break;
	
								default:
                        		$message = "/price指令\nstepn -> GMT/GST\nbig -> BTC/ETH/ETC\nchain -> BNB/SOL/NEAR/FTM\n";
				    		}
				}
				else
                {		
		            $message = "${user}可以試試以下指令\n/price big -> 主流幣價格\n/price chain -> 獨立鏈價格\n/price stepn -> STEPN價格";
                }
			}

			elseif (preg_match('/^\/weather(.*)$/i', $text))
			{
				preg_match('/\/weather(.*)/i', $text, $matches);
 		        $exec = trim($matches[1]);
               	if($exec)
				{
					$key_weather = 'rdec-key-123-45678-01112****'; //Weather_API_key
					$url_weather = 'https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-C0032-001'; // opendata的天氣url
					// -d 參數設定
					$parameters2 = [
						'Authorization' => "$key_weather"];	
					// curl設定
					$qs2 = http_build_query($parameters2); // query string encode the parameters
					$request2 = "{$url_weather}?{$qs2}"; // create the request URL
					$curl_weather = curl_init();
					curl_setopt_array($curl_weather, array(
						CURLOPT_URL => $request2,            // set the request URL
						CURLOPT_RETURNTRANSFER => 1));
					
					// 呼叫API
					$response2 = curl_exec($curl_weather); // Send the request, save the response
					$result2 = json_decode($response2, true);
					
					// 地點
					switch($exec)
					{
						case "基隆":
						$location = '18';
						break;

						case "新北":
						$location = '1';
						break;

						case "台北":
						$location = '5';
						break;

						case "桃園":
						$location = '13';
						break;

						case "新竹":
						$location = '4';
						break;

						case "苗栗":
						$location = '8';
						break;

						case "台中":
						$location = '11';
						break;

						case "南投":
						$location = '14';
						break;

						case "彰化":
						$location = '20';
						break;

						case "雲林":
						$location = '9';
						break;

						case "嘉義":
						$location = '2';
						break;

						case "台南":
						$location = '6';
						break;

						case "高雄":
						$location = '15';
						break;

						default:
						$message = "/weather 地名";
					}
					
					if ($location)
					{
						// 地名
						$place_name = $result2['records']['location'][$location]['locationName'];

						// 時間
						$start_time = $result2['records']['location'][$location]['weatherElement'][0]['time'][0]['startTime'];
						$endtine = $result2['records']['location'][$location]['weatherElement'][0]['time'][0]['endTime'];
						$start_time2 = $result2['records']['location'][$location]['weatherElement'][0]['time'][1]['startTime'];
						$endtine2 = $result2['records']['location'][$location]['weatherElement'][0]['time'][1]['endTime'];
						$start_time3 = $result2['records']['location'][$location]['weatherElement'][0]['time'][2]['startTime'];
						$endtine3 = $result2['records']['location'][$location]['weatherElement'][0]['time'][2]['endTime'];

						// 天氣狀況
						$weather = $result2['records']['location'][$location]['weatherElement'][0]['time'][0]['parameter']['parameterName'];
						$weather2 = $result2['records']['location'][$location]['weatherElement'][0]['time'][1]['parameter']['parameterName'];
						$weather3 = $result2['records']['location'][$location]['weatherElement'][0]['time'][2]['parameter']['parameterName'];

						// 降雨機率
						$probability = $result2['records']['location'][$location]['weatherElement'][1]['time'][0]['parameter']['parameterName'];
						$probability2 = $result2['records']['location'][$location]['weatherElement'][1]['time'][1]['parameter']['parameterName'];
						$probability3 = $result2['records']['location'][$location]['weatherElement'][1]['time'][2]['parameter']['parameterName'];
					
						// 溫度
						$temp = $result2['records']['location'][$location]['weatherElement'][2]['time'][0]['parameter']['parameterName'];
						$temp2 = $result2['records']['location'][$location]['weatherElement'][2]['time'][1]['parameter']['parameterName'];
						$temp3 = $result2['records']['location'][$location]['weatherElement'][2]['time'][2]['parameter']['parameterName'];

						$message = "地名: $place_name\n\n時間: $start_time ~ $endtine\n天氣: $weather-$probability %\n最低溫度: $temp\n\n時間:$start_time2 ~ $endtine2\n天氣: $weather2-$probability2 %\n最低溫度: $temp2\n\n時間:$start_time3 ~ $endtine3\n天氣: $weather3-$probability3 %\n最低溫度: $temp3";
						//$message = "編號: $location";
					}
				}
				else
                {
	                $message = "我不知道${user}想查什麼哪裡的天氣@@\n";
                }
			}

			// 輸出至telegram
			if ($message)
			{
	   			$ch4 = curl_init();
		    	curl_setopt($ch4, CURLOPT_URL, "https://api.telegram.org/bot${token}/sendMessage");
		   	 	curl_setopt($ch4,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch4,CURLOPT_POSTFIELDS,"text=$message&chat_id=$chat_id");
	   	 		curl_exec($ch4);
				$message = ''; //清空變數
			}
			break;
		}
	}
}
?>


