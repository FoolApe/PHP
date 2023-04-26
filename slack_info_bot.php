<?php
date_default_timezone_set('Asia/Taipei'); //設定時區
error_reporting(E_ALL);

// load spec
require_once 'info_bot_spec.php';

// load functions
require_once 'info_bot_function.php';


// Store the latest timestamp
$latest_ts = '';

// Initialize message variable
$message = null;

while (true) 
{
    // 監聽最新的留言
    $url = "https://slack.com/api/conversations.history?token=$bot_token&channel=$channel_id&limit=1";
    if ($latest_ts) 
    {
        $url .= "&oldest=$latest_ts";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    // Execute the request and decode the response
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // Check if there are any messages
    if (isset($response['messages']) && count($response['messages']) > 0) 
    {
        // Check if the message is from a bot
        if (isset($response['messages'][0]['bot_id'])) 
        {
            // Set message variable to null
            $message = null;
            $latest_ts = '';
        }
        else
        {
            // Get user's message
            $message = $response['messages'][0]['text'];
            $user_id = $response['messages'][0]['user'];
            $ts = $response['messages'][0]['ts'];

            // 檢查回傳值
            var_dump($user_id);
            var_dump($ts);

            // ============================================================================ //
            
            // 檢查到!HELP才觸發 , 回復可用指令
            if (preg_match('/^!HELP/i', $message, $match_server))
            {
                // <@$user_id> 是tag使用者名稱
                $reply = "請參考以下指令:\n*!SN*\n*!HOSTNAME*\n*!MODEL*\n*!NIC*";
                $emoji = "hand_with_index_finger_and_thumb_crossed";
                $apiUrl = '';
            }

            // ============================================================================ //

            // 檢查到!SN才觸發 , 並判斷server IP
            elseif (preg_match('/^!SN (.*)/i', $message, $match_server))
            {
                $host = filter_var($match_server[1], FILTER_VALIDATE_IP);
                if (!$host)
                {
                    $reply = "<@$user_id> *請提供有效的IP*";
                    $emoji = "x";
                    $apiUrl = '';
                }
                else
                {
                    $type = 'SN';
                    $host = trim($host);
                    $site = checkSubnet($host);
                    $apiUrl ="${api_url}?token=${api_token}&type=${type}&host=${host}&site=${site}";
                }
            }

            // ============================================================================ //

            // 檢查到!HOSTNAME才觸發 , 並判斷server IP
            elseif (preg_match('/^!HOSTNAME (.*)/i', $message, $match_server))
            {
                $host = filter_var($match_server[1], FILTER_VALIDATE_IP);
                if (!$host)
                {
                    $reply = "<@$user_id> *請提供有效的IP*";
                    $emoji = "x";
                    $apiUrl = '';
                }
                else
                {
                    $type = 'HOSTNAME';
                    $host = trim($host);
                    $site = checkSubnet($host);
                    $apiUrl ="${api_url}?token=${api_token}&type=${type}&host=${host}&site=${site}";
                }
            }

            // ============================================================================ //

            // 檢查到!MODEL才觸發 , 並判斷server IP
            elseif (preg_match('/^!MODEL (.*)/i', $message, $match_server))
            {
                $host = filter_var($match_server[1], FILTER_VALIDATE_IP);
                if (!$host)
                {
                    $reply = "<@$user_id> *請提供有效的IP*";
                    $emoji = "x";
                    $apiUrl = '';
                }
                else
                {
                    $type = 'MODEL';
                    $host = trim($host);
                    $site = checkSubnet($host);
                    $apiUrl ="${api_url}?token=${api_token}&type=${type}&host=${host}&site=${site}";
                }
            }

            // 檢查到!IPMI才觸發 , 並判斷server IP
            elseif (preg_match('/^!IPMI (.*)/i', $message, $match_server))
            {
                $host = filter_var($match_server[1], FILTER_VALIDATE_IP);
                if (!$host)
                {
                    $reply = "<@$user_id> *請提供有效的IP*";
                    $emoji = "x";
                    $apiUrl = '';
                }
                else
                {
                    $type = 'IPMI';
                    $host = trim($host);
                    $site = checkSubnet($host);
                    $apiUrl ="${api_url}?token=${api_token}&type=${type}&host=${host}&site=${site}";
                }
            }

            // ============================================================================ //

            // 檢查到!NIC才觸發 , 並判斷server IP
            elseif (preg_match('/^!NIC (.*)/i', $message, $match_nic))
            {
                $host = filter_var($match_nic[1], FILTER_VALIDATE_IP);
                if (!$host)
                {
                    $reply = "<@$user_id> *請提供有效的IP*";
                    $emoji = "x";
                    $apiUrl = '';
                }
                else
                {
                    $type = 'NODE';
                    $host = trim($host);
                    $site = checkSubnet($host);
                    $apiUrl ="${api_url2}?token=${api_token}&type=${type}&host=${host}&site=${site}";
                }
            }

            // 什麼都沒觸發
            else
            {
                /*
                $reply = "我不知道 <@$user_id> 想幹嘛";
                $emoji = "question";
                */
                $reply = '';
                $emoji = '';
                $apiUrl = '';
            }

            // ====================== 呼叫自己的API ====================== //

            if (!empty($apiUrl))
            {
                // 建立 cURL 連線
                $ch = curl_init();

                // 設定 cURL 的選項
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // 執行 cURL 連線並取得回應
                $response = curl_exec($ch);

                // 關閉 cURL 連線
                curl_close($ch);
                $reply = "$response";
                $emoji = "ok_hand";
            }
                
            // 檢查回傳值
            var_dump($apiUrl);
            var_dump($reply);
            var_dump($emoji);

            // ====================== 呼叫Slack ====================== //

            if ($reply !== '' && $emoji !== '')
            {
                // 設定表情payload
                $payload_emoji = array(
                    'name' => $emoji,
                    'channel' => $channel_id,
                    'timestamp' => $ts
                );

                // 呼叫API
                $ch_emoji = curl_init("https://slack.com/api/reactions.add");
                curl_setopt($ch_emoji, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch_emoji, CURLOPT_POSTFIELDS, http_build_query($payload_emoji));
                curl_setopt($ch_emoji, CURLOPT_HTTPHEADER, array("Authorization: Bearer $bot_token"));
                curl_setopt($ch_emoji, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch_emoji);
                curl_close($ch_emoji);

                // Check if the reaction was successfully added
                $result = json_decode($result, true);
                if (!$result['ok']) {
                    error_log("Error adding reaction: " . $result['error']);
                }

                // 點完表情後等一秒再做回覆
                sleep(1);

                // =========================================== //
                    
                // 設定回覆payload
                $payload = array(
                    'channel' => $channel_id,
                    'text' => $reply,
                    #'thread_ts' => $ts // 加了這項會變成用"reply的方式回覆"
                );
                
                /*
                $payload = array(
                    'channel' => $channel_id,
                    'blocks' => array(
                        array(
                            'type' => 'header',
                            'text' => array(
                                'type' => 'plain_text',
                                'text' => '我是標題~~',
                                'emoji' => true
                            )
                        ),
                        array(
                            'type' => 'section',
                            'text' => array(
                                'type' => 'mrkdwn',
                                'text' => $reply
                            )
                        )
                    )
                );
                */

                $ch_reply = curl_init($webhook_url);
                curl_setopt($ch_reply, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch_reply, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch_reply, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $result = curl_exec($ch_reply);
                curl_close($ch_reply);

                // Check if the message was successfully posted
                $result = json_decode($result, true);
                if (!$result['ok']) {
                    error_log("Error posting reply message: " . $result['error']);
                }
            }

            // 當頻繁對話時, ts並不會有變化, 所以取用舊的值
            if (!empty($ts)) 
            {
                $latest_ts = $ts;
            }
        }
    }
    
    // Unset unnecessary variables
    unset($response, $reply, $result, $emoji);
    
    // Wait
    sleep(3);
}