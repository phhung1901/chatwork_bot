<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatworkBotService;
use App\Helper\ChatworkHelper;

class DemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:full-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo full system functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 DEMO FULL CHATWORK NEWS BOT SYSTEM');
        $this->line('');

        // Show system status
        $this->info('📊 System Status:');
        $this->call('news:status');
        $this->line('');

        // Demo bot functionality
        $this->info('🤖 Bot Demo:');
        $chatworkHelper = new ChatworkHelper();
        $botService = new ChatworkBotService();
        
        $demoMessage = "[toall]\n";
        $demoMessage .= "🎬 DEMO CHATWORK NEWS BOT 🎬\n\n";
        $demoMessage .= "Hệ thống tin tức tự động đã được kích hoạt!\n\n";
        $demoMessage .= "📋 TÍNH NĂNG:\n";
        $demoMessage .= "• Tin tức tự động hàng ngày lúc 8:30 sáng\n";
        $demoMessage .= "• Bot phản hồi khi được tag với /news\n";
        $demoMessage .= "• Kiểm tra mentions mỗi 2 phút\n\n";
        $demoMessage .= "🔧 THỬ NGAY:\n";
        $demoMessage .= "Tag bot: [To:{$chatworkHelper->getBotId()}] /news\n\n";
        $demoMessage .= "🤖 Bot sẽ tự động phản hồi với tin tức mới nhất!";

        $chatworkHelper->sendMessage($demoMessage);
        $this->info('✅ Demo message sent to Chatwork!');
        $this->line('');

        // Instructions
        $this->info('📝 Next Steps:');
        $this->line('1. Kiểm tra Chatwork room để xem demo message');
        $this->line('2. Tag bot với: [To:' . $chatworkHelper->getBotId() . '] /news');
        $this->line('3. Chạy: php artisan chatwork:check-mentions');
        $this->line('4. Bot sẽ phản hồi với tin tức!');
        $this->line('');

        $this->info('🎉 Demo completed! System is ready to use.');

        return Command::SUCCESS;
    }
}
