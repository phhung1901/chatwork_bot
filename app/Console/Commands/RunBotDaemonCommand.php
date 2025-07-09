<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatworkBotService;
use Illuminate\Support\Facades\Log;

class RunBotDaemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:daemon {--interval=10 : Check interval in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run bot as daemon - continuously check for mentions';

    private $shouldStop = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $interval = (int) $this->option('interval');
        
        $this->info("🤖 Starting Chatwork Bot Daemon...");
        $this->info("⏱️  Check interval: {$interval} seconds");
        $this->info("🛑 Press Ctrl+C to stop");
        $this->line('');

        // Handle Ctrl+C gracefully
        pcntl_signal(SIGINT, function() {
            $this->shouldStop = true;
            $this->warn("\n🛑 Stopping bot daemon...");
        });

        $botService = new ChatworkBotService();
        $checkCount = 0;

        while (!$this->shouldStop) {
            $checkCount++;
            
            try {
                $this->line("[" . date('H:i:s') . "] Check #{$checkCount} - Scanning for mentions...");
                
                $result = $botService->checkAndRespondToMentions();
                
                if ($result) {
                    $this->info("✅ Check completed successfully");
                } else {
                    $this->warn("⚠️ Check completed with warnings");
                }

                // Sleep for specified interval
                for ($i = 0; $i < $interval && !$this->shouldStop; $i++) {
                    sleep(1);
                    pcntl_signal_dispatch(); // Handle signals
                }

            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
                Log::error('Bot Daemon Error: ' . $e->getMessage());
                
                // Sleep a bit longer on error to avoid spam
                sleep(30);
            }
        }

        $this->info("🏁 Bot daemon stopped after {$checkCount} checks");
        return Command::SUCCESS;
    }
}
