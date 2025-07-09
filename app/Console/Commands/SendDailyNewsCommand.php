<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeminiService;
use App\Helper\ChatworkHelper;
use Illuminate\Support\Facades\Log;

class SendDailyNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:send-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily news summary to Chatwork using Gemini AI';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting daily news summary...');

        try {
            // Get news summary from Gemini
            $geminiService = new GeminiService();
            $newsSummary = $geminiService->getDailyNewsSummary();

            if (!$newsSummary) {
                $this->error('Failed to get news summary from Gemini API');
                Log::error('Failed to get news summary from Gemini API');
                return Command::FAILURE;
            }

            // Prepare message for Chatwork
            $currentDate = date('d/m/Y');
            $message = "[toall]\n";
            $message .= "📰 TIN TỨC TỔNG HỢP NGÀY {$currentDate} 📰\n\n";
            $message .= $newsSummary;
            $message .= "\n\n";
            $message .= "🤖 Tin tức được tổng hợp tự động bởi AI";

            // Send to Chatwork
            $chatworkHelper = new ChatworkHelper();
            $chatworkHelper->sendMessage($message);

            $this->info('Daily news summary sent successfully to Chatwork!');
            Log::info('Daily news summary sent successfully to Chatwork');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('SendDailyNewsCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
