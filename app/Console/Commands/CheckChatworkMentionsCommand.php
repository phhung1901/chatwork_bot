<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatworkBotService;
use Illuminate\Support\Facades\Log;

class CheckChatworkMentionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatwork:check-mentions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Chatwork for bot mentions and respond to commands';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking Chatwork for bot mentions...');

        try {
            $botService = new ChatworkBotService();
            $result = $botService->checkAndRespondToMentions();

            if ($result) {
                $this->info('✅ Successfully checked and processed mentions');
                return Command::SUCCESS;
            } else {
                $this->warn('⚠️ No mentions found or processing failed');
                return Command::SUCCESS;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('CheckChatworkMentionsCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
