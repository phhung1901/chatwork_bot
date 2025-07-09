<?php

namespace App\Services;

use App\Helper\ChatworkHelper;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatworkBotService
{
    private $chatworkHelper;
    private $geminiService;

    public function __construct()
    {
        $this->chatworkHelper = new ChatworkHelper();
        $this->geminiService = new GeminiService();
    }

    /**
     * Check for new messages and respond to bot mentions
     *
     * @return bool
     */
    public function checkAndRespondToMentions()
    {
        try {
            // Get recent messages
            $messages = $this->chatworkHelper->getMessages(1);
            
            if (!$messages || !is_array($messages)) {
                Log::info('No messages found or invalid response from Chatwork API');
                return false;
            }

            // Get the last processed message ID from cache
            $lastProcessedId = Cache::get('chatwork_last_processed_message_id', 0);
            $newLastProcessedId = $lastProcessedId;

            $processedCount = 0;

            // Process messages in reverse order (oldest first)
            $messages = array_reverse($messages);

            foreach ($messages as $message) {
                $messageId = $message['message_id'] ?? 0;
                
                // Skip if we've already processed this message
                if ($messageId <= $lastProcessedId) {
                    continue;
                }

                // Update the newest message ID we're processing
                if ($messageId > $newLastProcessedId) {
                    $newLastProcessedId = $messageId;
                }

                // Check if bot is mentioned with /news command
                if ($this->chatworkHelper->isBotMentionedWithNewsCommand($message)) {
                    $this->handleNewsCommand($message);
                    $processedCount++;
                }
            }

            // Update the last processed message ID
            if ($newLastProcessedId > $lastProcessedId) {
                Cache::put('chatwork_last_processed_message_id', $newLastProcessedId, now()->addDays(7));
            }

            if ($processedCount > 0) {
                Log::info("Processed {$processedCount} bot mentions");
            }

            return true;

        } catch (\Exception $e) {
            Log::error('ChatworkBotService Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle /news command
     *
     * @param array $message
     * @return void
     */
    private function handleNewsCommand($message)
    {
        try {
            $senderId = $message['account']['account_id'] ?? 'Unknown';
            $senderName = $message['account']['name'] ?? 'Unknown User';
            $messageBody = $message['body'] ?? '';

            Log::info("Processing /news command from user: {$senderName} (ID: {$senderId})");

            // Check if message contains specific date request
            $dateMatch = null;
            if (preg_match('/\/news\s+(\d{1,2}\/\d{1,2}\/\d{4}|\d{4}-\d{1,2}-\d{1,2})/', $messageBody, $matches)) {
                $dateMatch = $matches[1];
            }

            // Generate news summary
            if ($dateMatch) {
                // Handle specific date request
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $dateMatch);
                } catch (\Exception $e) {
                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $dateMatch);
                    } catch (\Exception $e) {
                        $date = \Carbon\Carbon::today();
                    }
                }
                $newsSummary = $this->geminiService->getMissedNewsSummary($date);
                $dateStr = $date->format('d/m/Y');
            } else {
                // Handle today's news request
                $newsSummary = $this->geminiService->getDailyNewsSummary();
                $dateStr = date('d/m/Y');
            }

            if (!$newsSummary) {
                $errorMessage = "[To:{$senderId}] {$senderName}\n";
                $errorMessage .= "❌ Xin lỗi, tôi không thể tạo tin tức lúc này. Vui lòng thử lại sau.";
                
                $this->chatworkHelper->sendMessage($errorMessage);
                return;
            }

            // Prepare response message
            $responseMessage = "[To:{$senderId}] {$senderName}\n";
            $responseMessage .= "📰 TIN TỨC NGÀY {$dateStr} 📰\n";
            $responseMessage .= "(Theo yêu cầu)\n\n";
            $responseMessage .= $newsSummary;
            $responseMessage .= "\n\n🤖 Tin tức được tổng hợp bởi AI";
            $responseMessage .= "\n⏰ Thời gian: " . now()->format('d/m/Y H:i:s');

            // Send response
            $this->chatworkHelper->sendMessage($responseMessage);

            Log::info("News summary sent successfully to user: {$senderName}");

        } catch (\Exception $e) {
            Log::error('Error handling news command: ' . $e->getMessage());
            
            // Send error message to user
            $senderId = $message['account']['account_id'] ?? '';
            $senderName = $message['account']['name'] ?? 'User';
            
            if ($senderId) {
                $errorMessage = "[To:{$senderId}] {$senderName}\n";
                $errorMessage .= "❌ Đã xảy ra lỗi khi xử lý yêu cầu. Vui lòng thử lại sau.";
                $this->chatworkHelper->sendMessage($errorMessage);
            }
        }
    }

    /**
     * Get help message for bot commands
     *
     * @return string
     */
    public function getHelpMessage()
    {
        $botId = $this->chatworkHelper->getBotId();
        
        return "🤖 CHATWORK NEWS BOT - HƯỚNG DẪN SỬ DỤNG\n\n" .
               "Để sử dụng bot, hãy tag bot và sử dụng các lệnh sau:\n\n" .
               "📋 CÁC LỆNH AVAILABLE:\n" .
               "• [To:{$botId}] /news → Tin tức hôm nay\n" .
               "• [To:{$botId}] /news dd/mm/yyyy → Tin tức ngày cụ thể\n\n" .
               "📝 VÍ DỤ:\n" .
               "• [To:{$botId}] /news\n" .
               "• [To:{$botId}] /news 08/07/2024\n\n" .
               "⏰ Bot cũng tự động gửi tin tức hàng ngày vào 8:30 sáng";
    }
}
