<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeminiService;
use App\Helper\ChatworkHelper;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendMissedNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:send-missed {--date= : Date in Y-m-d format (default: yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send missed daily news summary for a specific date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();
        
        $this->info("Sending missed news summary for: " . $date->format('d/m/Y'));

        try {
            // Get news summary from Gemini for specific date
            $geminiService = new GeminiService();
            $newsSummary = $geminiService->getMissedNewsSummary($date);

            if (!$newsSummary) {
                $this->error('Failed to get news summary from Gemini API');
                Log::error('Failed to get missed news summary from Gemini API for date: ' . $date->format('Y-m-d'));
                return Command::FAILURE;
            }

            // Prepare message for Chatwork
            $message = "[toall]\n";
            $message .= "📰 TIN TỨC BỔ SUNG NGÀY {$date->format('d/m/Y')} 📰\n";
            $message .= "(Tin tức đã bị bỏ lỡ)\n\n";
            $message .= $newsSummary;
            $message .= "\n\n";
            $message .= "🤖 Tin tức được tổng hợp tự động bởi AI";
            $message .= "\n⏰ Gửi lúc: " . now()->format('d/m/Y H:i:s');

            // Send to Chatwork
            $chatworkHelper = new ChatworkHelper();
            $chatworkHelper->sendMessage($message);

            $this->info('Missed news summary sent successfully to Chatwork!');
            Log::info('Missed news summary sent successfully to Chatwork for date: ' . $date->format('Y-m-d'));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('SendMissedNewsCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
