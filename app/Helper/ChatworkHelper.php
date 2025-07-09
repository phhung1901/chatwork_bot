<?php
namespace App\Helper;

class ChatworkHelper
{
    private $room_id;
    private $api_token;
    private $bot_id;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->room_id = env('CW_ROOM_ID');
        $this->api_token = env('CW_API_TOKEN');
        $this->bot_id = env('CW_BOT_ID');
    }

    public function sendMessage($body)
    {
        header("Content-type: text/html; charset=utf-8");

        $params = array(
            'body' => $body
        );

        $options = array(
            CURLOPT_URL => "https://api.chatwork.com/v2/rooms/{$this->room_id}/messages", // URL
            CURLOPT_HTTPHEADER => array('X-ChatWorkToken: ' . $this->api_token),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
        );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    /**
     * Get recent messages from the room
     *
     * @param int $force Force to get 100 recent messages (1) or not (0)
     * @return array|null
     */
    public function getMessages($force = 0)
    {
        $url = "https://api.chatwork.com/v2/rooms/{$this->room_id}/messages";
        if ($force) {
            $url .= "?force=1";
        }

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('X-ChatWorkToken: ' . $this->api_token),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPGET => true,
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        } elseif ($httpCode == 204) {
            // No content - no new messages, this is normal
            return [];
        }

        // Log error for debugging only if it's a real error
        if ($httpCode >= 400) {
            \Illuminate\Support\Facades\Log::error('Chatwork getMessages failed', [
                'http_code' => $httpCode,
                'response' => $response,
                'url' => $url
            ]);
        }

        return null;
    }

    /**
     * Check if bot is mentioned in a message with /news command
     *
     * @param array $message
     * @return bool
     */
    public function isBotMentionedWithNewsCommand($message)
    {
        $body = $message['body'] ?? '';

        // Check if bot is mentioned: [To:10515142]
        $botMentionPattern = "/\[To:{$this->bot_id}\]/";

        // Check if message contains /news command
        $newsCommandPattern = "/\/news/i";

        return preg_match($botMentionPattern, $body) && preg_match($newsCommandPattern, $body);
    }

    /**
     * Get bot ID
     *
     * @return string
     */
    public function getBotId()
    {
        return $this->bot_id;
    }
}
