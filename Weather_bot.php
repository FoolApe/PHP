<?php
$text = '/weather 澎湖';	### test
preg_match('/\/weather(.*)/i', $text, $matches);
$exec = trim($matches[1]);
if($exec)
{
	$key_weather = 'rdec-key-123-45678-01*****14'; //Weather_API_key
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

		case "台南":
		$location = '15';
		break;

		case "澎湖":
		$location = '19';
                break;

		default:
		$message = "/weather 地名";
	}
	
	/// test
	echo "編號: $location\n";

	if ($location)
	{
	    // 地名
	    $place_name = $result2['records']['location']["$location"]['locationName'];

   	    // 時間
	    $start_time = $result2['records']['location']["$location"]['weatherElement'][0]['time'][0]['startTime'];
 	    $endtine = $result2['records']['location']["$location"]['weatherElement'][0]['time'][0]['endTime'];
	    $start_time2 = $result2['records']['location']["$location"]['weatherElement'][0]['time'][1]['startTime'];
 	    $endtine2 = $result2['records']['location']["$location"]['weatherElement'][0]['time'][1]['endTime'];
	    $start_time3 = $result2['records']['location']["$location"]['weatherElement'][0]['time'][2]['startTime'];
	    $endtine3 = $result2['records']['location']["$location"]['weatherElement'][0]['time'][2]['endTime'];

	    // 天氣狀況
 	    $weather = $result2['records']['location']["$location"]['weatherElement'][0]['time'][0]['parameter']['parameterName'];
	    $weather2 = $result2['records']['location']["$location"]['weatherElement'][0]['time'][1]['parameter']['parameterName'];
	    $weather3 = $result2['records']['location']["$location"]['weatherElement'][0]['time'][2]['parameter']['parameterName'];

	    // 降雨機率
	    $probability = $result2['records']['location']["$location"]['weatherElement'][1]['time'][0]['parameter']['parameterName'];
	    $probability2 = $result2['records']['location']["$location"]['weatherElement'][1]['time'][1]['parameter']['parameterName'];
	    $probability3 = $result2['records']['location']["$location"]['weatherElement'][1]['time'][2]['parameter']['parameterName'];
	
	    // 溫度
	    $temp = $result2['records']['location']["$location"]['weatherElement'][2]['time'][0]['parameter']['parameterName'];
	    $temp2 = $result2['records']['location']["$location"]['weatherElement'][2]['time'][1]['parameter']['parameterName'];
	    $temp3 = $result2['records']['location']["$location"]['weatherElement'][2]['time'][2]['parameter']['parameterName'];

	    $message = "地名: $place_name\n\n時間: $start_time ~ $endtine\n天氣: $weather-$probability %\n最低溫度: $temp\n\n時間:$start_time2 ~ $endtine2\n天氣: $weather2-$probability2 %\n最低溫度: $temp2\n\n時間:$start_time3 ~ $endtine3\n天氣: $weather3-$probability3 %\n最低溫度: $temp3";
	    //echo $message . "\n";
	      echo "降雨機率: $probability\n";
	    //print_r($result2);
	}
}

else
{
    $message = "我不知道${user}想查什麼哪裡的天氣@@\n";
}

?>
