<?php

date_default_timezone_set('Asia/Taipei'); //設定時區

// 設定機器人的 token 和 chat_id
$token = 'YourToken'; //PooPoo_bot
$chat_id = 'YourID'; //機器人小天堂

// 設定日誌路徑
$log = '/tmp/test123.json';

// 設定 API 的 URL
$url = "https://api.telegram.org/bot${token}/getUpdates";

// 設定更新的 offset
$update_id = 0;

// 無限迴圈，用於長時間監聽訊息
while (true) 
{
    // 設定 Long Polling 的 timeout 為 60 秒
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'offset' => $update_id + 1,
        'timeout' => 60
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    // 解析回傳的 JSON 資料
    $data = json_decode($result, true);

    // 如果有訊息，則進行處理
    if (isset($data['result']) && is_array($data['result'])) 
    {
        foreach ($data['result'] as $update) 
        {
            // 取得更新的訊息
            $update_id = $update['update_id'];
            $user = $update['message']['from']['first_name'];
            $text = $update['message']['text'];

            // 記錄到 log 檔案中
            $time = date("Y-m-d H:i:s");
            $data = "$time | $user | $text\n";
            file_put_contents("$log", $data, FILE_APPEND);

            // ===================  針對使用者發送的內做出不同回答 =================== //

            // 如果收到的訊息是 /talk 開頭的指令，開始問好
            if (preg_match('/^\/talk(.*)$/i', $text))
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

            // ==================================================================== //

            // 如果收到的訊息是 /function 開頭的指令，回答功能性問題
            elseif (preg_match('/^\/function(.*)$/i', $text))
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
                            
                        default:
                        $message = "${user}可以試試以下指令\n/function date -> 今天日期";
                    }
                }
                
                else
                {
                    $message = "我不知道${user}想做什麼@@\n";
                }
            }

            // ==================================================================== //

            // 如果收到的訊息是 /price 開頭的指令，回答幣價
            elseif (preg_match('/^\/price(.*)$/i', $text))  ### 問幣價
            {
                preg_match('/\/price(.*)/i', $text, $matches);
                $exec = trim($matches[1]);
                if($exec)
                {
                            $key = 'YourKey'; //API_key
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
                            switch($exec)
                            {
                                case "big":
                                $message = "BTC -> $BTC\nETH -> $ETH\nETC -> $ETC\n";
                                break;      
                        
                                case "chain":
                                $message = "BNB -> $BNB\nSOL -> $SOL\nFTM -> $FTM\nNEAR -> $NEAR";
                                break;
    
                                default:
                                $message = "/price指令\nbig -> BTC/ETH/ETC\nchain -> BNB/SOL/NEAR/FTM\n";
                            }
                }
                else
                {       
                    $message = "${user}可以試試以下指令\n/price big -> 主流幣價格\n/price chain -> 獨立鏈價格\n";
                }
            }

            // ==================================================================== //

            // 如果收到的訊息是 /weather 開頭的指令，回答天氣
            elseif (preg_match('/^\/weather(.*)$/i', $text))
            {
                preg_match('/\/weather(.*)/i', $text, $matches);
                $exec = trim($matches[1]);
                if($exec)
                {
                    $key_weather = 'YourKey'; //Weather_API_key
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

            // 輸出回應到 Telegram
            if ($message) 
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot${token}/sendMessage");
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'chat_id' => $chat_id,
                    'text' => $message
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch);
                curl_close($ch);
                $message = ''; //清空變數
            }
        }
    }
}
