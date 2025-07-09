# Daily News System - Chatwork Bot

Hệ thống tự động gửi tin tức hàng ngày vào Chatwork sử dụng Gemini AI.

## 🚀 Tính năng

- Tự động tạo 15 gạch đầu dòng tóm tắt tin tức mới nhất trong ngày
- Sử dụng Gemini AI để tạo nội dung
- Gửi tin tức vào Chatwork room với TO ALL
- Chạy tự động vào 8:30 sáng hàng ngày
- **🤖 Bot tương tác**: Phản hồi khi được tag với lệnh `/news`
- **⚡ Real-time**: Kiểm tra mentions mỗi 30 giây
- Timezone: Asia/Ho_Chi_Minh

## 📋 Cấu hình

Các biến môi trường trong `.env`:

```env
# Chatwork Configuration
CW_ROOM_ID=404707831
CW_API_TOKEN=d6981f02a256b845fa2eac388686b8d7
CW_BOT_ID=10515142

# Gemini AI Configuration
GEMINI_API_KEY=AIzaSyB121IavjeLBaudqcUIhwrbH6R1JU7Igvw
```

## 🛠️ Commands

### Gửi tin tức ngay lập tức
```bash
php artisan news:send-daily
```

### Test hệ thống
```bash
php artisan news:test
```

### Kiểm tra trạng thái
```bash
php artisan news:status
```

### Test bot functionality
```bash
php artisan bot:test
```

### Kiểm tra bot mentions thủ công
```bash
php artisan chatwork:check-mentions
```

### Xem danh sách scheduled commands
```bash
php artisan schedule:list
```

## 🤖 Bot Usage

### Cách sử dụng bot trong Chatwork:

1. **Lấy tin tức hôm nay:**
   ```
   [To:10515142] /news
   ```

2. **Lấy tin tức ngày cụ thể:**
   ```
   [To:10515142] /news 08/07/2024
   ```

### Bot sẽ tự động:
- Kiểm tra mentions mỗi 30 giây (rất nhanh!)
- Phản hồi trực tiếp cho người tag
- Tạo tin tức theo yêu cầu bằng Gemini AI

## ⏰ Scheduler

Hệ thống sử dụng Laravel Scheduler để chạy tự động:

- **Tin tức hàng ngày**: 8:30 sáng mỗi ngày
- **Kiểm tra bot mentions**: Mỗi 30 giây
- **Timezone**: Asia/Ho_Chi_Minh
- **Cron job**: Đã được thiết lập tự động

## 📁 Cấu trúc Files

```
app/
├── Console/Commands/
│   ├── SendDailyNewsCommand.php         # Command chính gửi tin tức
│   ├── SendMissedNewsCommand.php        # Command gửi tin tức bị bỏ lỡ
│   ├── CheckChatworkMentionsCommand.php # Command kiểm tra bot mentions
│   ├── TestNewsCommand.php              # Command test hệ thống
│   ├── TestBotCommand.php               # Command test bot
│   └── NewsStatusCommand.php            # Command kiểm tra trạng thái
├── Services/
│   ├── GeminiService.php                # Service tương tác với Gemini API
│   └── ChatworkBotService.php           # Service xử lý bot logic
└── Helper/
    └── ChatworkHelper.php               # Helper gửi/đọc tin nhắn Chatwork
```

## 🔧 Cài đặt

1. **Cấu hình môi trường**: Đảm bảo các biến trong `.env` đã được thiết lập
2. **Cron job**: Đã được thiết lập tự động
3. **Test hệ thống**: Chạy `php artisan news:test` để kiểm tra

## 📝 Log

Logs được lưu tại `storage/logs/laravel.log`

## 🚨 Troubleshooting

### Lỗi Gemini API
- Kiểm tra GEMINI_API_KEY trong .env
- Đảm bảo API key còn hiệu lực
- Kiểm tra logs tại storage/logs/laravel.log

### Lỗi Chatwork API  
- Kiểm tra CW_API_TOKEN và CW_ROOM_ID trong .env
- Đảm bảo bot có quyền gửi tin nhắn trong room

### Scheduler không chạy
- Kiểm tra cron job: `crontab -l`
- Kiểm tra logs: `tail -f storage/logs/laravel.log`
- Test manual: `php artisan schedule:run`

## 📞 Support

Sử dụng command `php artisan news:status` để kiểm tra trạng thái hệ thống.
