<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatworkBotService;
use App\Helper\ChatworkHelper;

class TestBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test bot functionality and send help message';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing bot functionality...');

        try {
            // Test Chatwork connection
            $this->info('🔗 Testing Chatwork connection...');
            $chatworkHelper = new ChatworkHelper();
            $messages = $chatworkHelper->getMessages();

            if ($messages !== null) {
                $this->info('✅ Chatwork API connection successful');
                $this->line('   Found ' . count($messages) . ' recent messages');
            } else {
                $this->error('❌ Failed to connect to Chatwork API');
                return Command::FAILURE;
            }

            // Test bot service
            $this->info('🤖 Testing bot service...');
            $botService = new ChatworkBotService();

            // Send help message
            $this->info('📤 Sending help message to Chatwork...');
            $helpMessage = "[toall]\n" . $botService->getHelpMessage();
            $chatworkHelper->sendMessage($helpMessage);

            $this->info('✅ Help message sent successfully!');

            // Show bot configuration
            $this->info('⚙️ Bot Configuration:');
            $this->line('- Bot ID: ' . $chatworkHelper->getBotId());
            $this->line('- Room ID: ' . env('CW_ROOM_ID'));
            $this->line('');

            $this->info('🎉 Bot test completed successfully!');
            $this->line('');
            $this->warn('💡 To test bot mentions:');
            $this->line('   1. Go to Chatwork room');
            $this->line('   2. Type: [To:' . $chatworkHelper->getBotId() . '] /news');
            $this->line('   3. Run: php artisan chatwork:check-mentions');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
