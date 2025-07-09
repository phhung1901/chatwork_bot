<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeminiService;
use App\Helper\ChatworkHelper;

class TestNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test news summary generation and Chatwork sending';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Gemini API...');

        // Test Gemini API
        $geminiService = new GeminiService();
        $newsSummary = $geminiService->getDailyNewsSummary();

        if ($newsSummary) {
            $this->info('✅ Gemini API working successfully!');
            $this->line('Generated news summary:');
            $this->line('---');
            $this->line($newsSummary);
            $this->line('---');
        } else {
            $this->error('❌ Gemini API failed!');
            return Command::FAILURE;
        }

        // Test Chatwork
        $this->info('Testing Chatwork API...');
        
        $testMessage = "[toall]\n🧪 TEST MESSAGE 🧪\n\nĐây là tin nhắn test từ hệ thống tin tức tự động.\nThời gian: " . date('d/m/Y H:i:s');
        
        $chatworkHelper = new ChatworkHelper();
        $chatworkHelper->sendMessage($testMessage);
        
        $this->info('✅ Test message sent to Chatwork!');

        return Command::SUCCESS;
    }
}
