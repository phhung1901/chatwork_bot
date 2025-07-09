<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NewsStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of daily news system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('📊 DAILY NEWS SYSTEM STATUS');
        $this->line('');

        // Check environment variables
        $this->info('🔧 Configuration:');
        $this->line('- Chatwork Room ID: ' . (env('CW_ROOM_ID') ? '✅ Set' : '❌ Not set'));
        $this->line('- Chatwork API Token: ' . (env('CW_API_TOKEN') ? '✅ Set' : '❌ Not set'));
        $this->line('- Chatwork Bot ID: ' . (env('CW_BOT_ID') ? '✅ Set (' . env('CW_BOT_ID') . ')' : '❌ Not set'));
        $this->line('- Gemini API Key: ' . (env('GEMINI_API_KEY') ? '✅ Set' : '❌ Not set'));
        $this->line('');

        // Check scheduled commands
        $this->info('⏰ Scheduled Commands:');
        $this->call('schedule:list');
        $this->line('');

        // Check cron job
        $this->info('🔄 Cron Job Status:');
        $cronOutput = shell_exec('crontab -l 2>/dev/null | grep "schedule:run"');
        if ($cronOutput) {
            $this->line('✅ Cron job is configured');
            $this->line('   ' . trim($cronOutput));
        } else {
            $this->line('❌ Cron job not found');
        }
        $this->line('');

        // Show next run time
        $this->info('📅 Next scheduled run: Tomorrow at 8:30 AM (Asia/Ho_Chi_Minh timezone)');
        $this->line('');

        // Instructions
        $this->info('🛠️  Available Commands:');
        $this->line('- php artisan news:send-daily        → Send today\'s news now');
        $this->line('- php artisan news:send-missed       → Send missed news (yesterday)');
        $this->line('- php artisan news:send-missed --date=2024-01-01 → Send news for specific date');
        $this->line('- php artisan news:test              → Test system');
        $this->line('- php artisan news:status            → Show this status');
        $this->line('- php artisan bot:test               → Test bot functionality');
        $this->line('- php artisan chatwork:check-mentions → Check for bot mentions');
        $this->line('');

        // Bot usage instructions
        $this->info('🤖 Bot Usage:');
        $this->line('- Tag bot trong Chatwork: [To:' . (env('CW_BOT_ID') ?: 'BOT_ID') . '] /news');
        $this->line('- Bot sẽ phản hồi tự động khi được tag với lệnh /news');
        $this->line('- Bot kiểm tra mentions mỗi 30 giây (rất nhanh!)');
        $this->line('');

        // Warning about cron dependency
        $this->warn('⚠️  IMPORTANT: Cron job chỉ chạy khi máy tính đang bật!');
        $this->line('   Nếu máy tắt vào 8:30 sáng, tin tức sẽ không được gửi tự động.');
        $this->line('   Sử dụng "news:send-missed" để gửi tin tức bị bỏ lỡ.');

        return Command::SUCCESS;
    }
}
