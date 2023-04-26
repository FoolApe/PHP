<?php

date_default_timezone_set('Asia/Taipei'); //設定時區
error_reporting(E_ALL | E_STRICT);

// 設定機器人的 token 和 chat_id / 設定日誌路徑
require_once 'poopoo_spec.php';

// 載入按鈕設定
require_once 'poopoo_button.php';

// 載入副程式
require_once 'poopoo_function.php';

// 設定 TG 的 URL
$url = "https://api.telegram.org/bot${bot_token}/getUpdates";

// 設定更新的 offset
$update_id = 0;



// 無限迴圈，用於長時間監聽訊息
while (true) 
{
    // 先清空舊資訊
    $message = '';
    $message_special = '';
    $city = '';

    // 設定 Long Polling 的 timeout 為 60 秒
    $ch_listen = curl_init();
    curl_setopt($ch_listen, CURLOPT_URL, $url);
    curl_setopt($ch_listen, CURLOPT_POSTFIELDS, [
        'offset' => $update_id + 1,
        'timeout' => 60
    ]);
    curl_setopt($ch_listen, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch_listen);
    curl_close($ch_listen);

    // 解析回傳的 JSON 資料
    $data = json_decode($result, true);

    // 檢查回傳值
    var_dump($result);
    var_dump($data);

    // 如果有訊息，則進行處理
    if (isset($data['result']) && is_array($data['result'])) 
    {
        foreach ($data['result'] as $update) 
        {
            // 取得更新的訊息
            $update_id = $update['update_id'];

            // 判斷是按鈕還是呼叫menu
            if (isset($update['callback_query']) && is_array($update['callback_query']))
            {
                $callback_query = $update['callback_query'];
                $user = $callback_query['from']['first_name']; // 顯示暱稱
                $text = $callback_query['data'];
                $user_name = $callback_query['from']['username']; // 使用者名稱
                $user_id = $callback_query['from']['id']; // 使用者id
            }
            else
            {
                // 取得更新的訊息
                $user = $update['message']['from']['first_name'];
                $text = $update['message']['text'];
            }

            // 記錄到 log 檔案中
            $time = date("Y-m-d H:i:s");
            $data = "$time | $user | $text\n";
            file_put_contents("$log", $data, FILE_APPEND);

            // ===================  針對使用者發送的內做出不同回答 =================== //

            // 如果收到 /command ，拋出menu按鈕
            if (preg_match('/^\/command$/i', $text))
            {
                $message = '*請選擇類別*';
                $reply_markup = json_encode(['inline_keyboard' => $menu_keyboard]);
                $ch_command = curl_init();
                curl_setopt($ch_command, CURLOPT_URL, "https://api.telegram.org/bot${bot_token}/sendMessage");
                curl_setopt($ch_command, CURLOPT_POSTFIELDS, [
                    'chat_id' => $chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $reply_markup,
                ]);
                curl_setopt($ch_command, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch_command);
                curl_close($ch_command);
            }

            // 如果收到按鈕callback , 拋出下一層的回覆
            elseif (isset($callback_query)) 
            {
                // 判斷誰按了哪個按鈕 , 以及message_id
                $callback_data = $text;

                // 依照按鈕決定回覆的訊息 , 並標記按按鈕的人
                switch ($callback_data) 
                {
                    // menu選單
                    case 'menu_button1':
                        $message = "*想跟我聊什麼?*";
                        $message_special = "聊天有益身心健康";
                        $reply_markup = json_encode(['inline_keyboard' => $talk_keyboard]);
                        break;

                    case 'menu_button2':
                        $message = "*想問什麼時間?*";
                        $message_special = "雖然沒啥屁用";
                        $reply_markup = json_encode(['inline_keyboard' => $date_keyboard]);
                        break;

                    case 'menu_button3':
                        $message = "*想問哪些token?*";
                        $message_special = "HODL就是王道";
                        $reply_markup = json_encode(['inline_keyboard' => $coin_keyboard]);
                        break;

                    case 'menu_button4':
                        $message = "*想用什麼功能?*";
                        $message_special = "功能還怪怪的XD";
                        $reply_markup = json_encode(['inline_keyboard' => $function_keyboard]);
                        break;  

                    // 功能選單
                    case 'function_button1':
                        $message = "*想查哪裡天氣?*";
                        $message_special = "有你在,雨天也開心❤️";
                        $reply_markup = json_encode(['inline_keyboard' => $weather_keyboard]);
                        break;

                    case 'function_button2':
                        $message = "*我還沒想到😄*";
                        $message_special = "Comming soon!!";
                        $reply_markup = '';
                        break;

                    // 聊天選單
                    case 'talk_button1':
                        // 呼叫幹話語錄
                        $message = talk_shit() . "😎";
                        $message_special = "聽君一席話,如聽一席話";
                        $reply_markup = '';
                        break;

                    case 'talk_button2':
                        $message = "*安安 ${user}😘*";
                        $message_special = "打打招呼,嘻嘻哈哈";
                        $reply_markup = '';
                        break;

                    // 日期選單
                    case 'date_button1':
                        $time = date("Y/n/j, D, G:i:s");
                        $message = "*$user , 現在是 ${time}⏰*";
                        $message_special = "其實機器人時間也沒比較準";
                        $reply_markup = '';
                        break;

                    // 問價選單
                    case 'coin_button1':  
                        // 呼叫function抓幣價 , 將function回傳的array輸出
                        $crypto_price = get_crypto_price($key_cmc, $url_cmc);
                        $BTC_price = $crypto_price['BTC'];
                        $ETH_price = $crypto_price['ETH'];

                        $message = "*BTC -> $BTC_price\nETH -> $ETH_price\n*";
                        $message_special = "BTC to the mooooon🚀🚀🚀";
                        $reply_markup = '';
                        break;

                    case 'coin_button2':
                        // 呼叫function抓幣價 , 將function回傳的array輸出
                        $crypto_price = get_crypto_price($key_cmc, $url_cmc);
                        $BNB_price = $crypto_price['BNB'];
                        $SOL_price = $crypto_price['SOL'];
                        $NEAR_price = $crypto_price['NEAR'];
                        $FTM_price = $crypto_price['FTM'];
                        $MATIC_price = $crypto_price['MATIC'];

                        $message = "*BNB -> $BNB_price\nSOL -> $SOL_price\nNEAR -> $NEAR_price\nFTM -> $FTM_price\nMATIC -> $MATIC_price*";
                        $message_special = "SOL to the mooooon🚀🚀🚀";
                        $reply_markup = '';
                        break;

                    // 天氣選單
                    case 'weather_button1':
                        $city = 'Taichung';
                        $message = get_weather($key_weather, $url_weather , $city);
                        $message_special = "慶記之都🔫";
                        $reply_markup = '';

                    case 'weather_button2':
                        $city = 'Changhua';
                        $message = get_weather($key_weather, $url_weather , $city);
                        $message_special = "爌肉飯讚🍖";
                        $reply_markup = '';

                    case 'weather_button3':
                        $city = 'Nantou';
                        $message = get_weather($key_weather, $url_weather , $city);
                        $message_special = "好山好水⛰";
                        $reply_markup = '';
                    
                    case 'weather_button4':
                        $city = 'Yunlin';
                        $message = get_weather($key_weather, $url_weather , $city);
                        $message_special = "路邊有牛🐮";
                        $reply_markup = '';

                    case 'weather_button5':
                        $city = 'Chiayi';
                        $message = get_weather($key_weather, $url_weather , $city);
                        $message_special = "噴水雞肉🦃";
                        $reply_markup = '';

                    case 'weather_button6':
                        $city = 'Tainan';
                        $message = get_weather($key_weather, $url_weather , $city);
                        $message_special = "美食之都🍽";
                        $reply_markup = '';
                }

                // 刪除keyboard
                $chat_id = $callback_query['message']['chat']['id'];
                $message_id = $callback_query['message']['message_id'];
                remove_inline_keyboard($bot_token, $chat_id, $message_id);

                // 特殊回覆
                $callback_query_id = $callback_query['id'];
                $ch_inline = curl_init();
                curl_setopt($ch_inline, CURLOPT_URL, "https://api.telegram.org/bot${bot_token}/answerCallbackQuery");
                curl_setopt($ch_inline, CURLOPT_POSTFIELDS, [
                    'callback_query_id' => $callback_query_id,
                    'text' => $message_special 
                    //'show_alert' => true // 設定為true，讓機器人發送一個短暫的警告通知
                ]);
                curl_setopt($ch_inline, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch_inline);
                curl_close($ch_inline);
                    
                //-------------------------------//

                // 一般回覆
                $ch_button = curl_init();
                curl_setopt($ch_button, CURLOPT_URL, "https://api.telegram.org/bot${bot_token}/sendMessage");

                // 檢查是否需要顯示按鈕
                if (isset($reply_markup))
                {
                    curl_setopt($ch_button, CURLOPT_POSTFIELDS, [
                        'chat_id' => $chat_id,
                        'text' => $message,
                        'parse_mode' => 'Markdown',
                        'reply_markup' => $reply_markup,
                    ]);
                }
                else
                {
                    curl_setopt($ch_button, CURLOPT_POSTFIELDS, [
                        'chat_id' => $chat_id,
                        'text' => $message,
                        'parse_mode' => 'Markdown',
                    ]);
                }
                curl_setopt($ch_button, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch_button);
                curl_close($ch_button);
            }

            else 
            {
                // 不是指令也不是按鈕, 不回應
                $message = "";
            }
        }
    }
}

?>