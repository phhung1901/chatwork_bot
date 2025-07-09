#!/bin/bash

# Script kiểm tra và gửi tin tức bị bỏ lỡ khi khởi động máy
# Đặt file này vào thư mục dự án và chạy khi khởi động

PROJECT_DIR="/var/www/phamhung/chatwork_bot"
LOG_FILE="$PROJECT_DIR/storage/logs/startup_check.log"

echo "$(date): Checking for missed news..." >> $LOG_FILE

cd $PROJECT_DIR

# Kiểm tra xem hôm nay đã gửi tin tức chưa
TODAY=$(date +%Y-%m-%d)
YESTERDAY=$(date -d "yesterday" +%Y-%m-%d)

# Kiểm tra log xem có gửi tin tức hôm nay chưa
if ! grep -q "Daily news summary sent successfully" storage/logs/laravel.log | grep -q "$TODAY"; then
    echo "$(date): No news sent today, checking if we need to send..." >> $LOG_FILE
    
    # Nếu hiện tại đã qua 8:30 sáng và chưa gửi tin tức hôm nay
    CURRENT_TIME=$(date +%H%M)
    if [ $CURRENT_TIME -gt 0830 ]; then
        echo "$(date): Sending today's news..." >> $LOG_FILE
        php artisan news:send-daily >> $LOG_FILE 2>&1
    fi
    
    # Kiểm tra xem hôm qua có gửi tin tức không
    if ! grep -q "Daily news summary sent successfully" storage/logs/laravel.log | grep -q "$YESTERDAY"; then
        echo "$(date): Sending yesterday's missed news..." >> $LOG_FILE
        php artisan news:send-missed --date=$YESTERDAY >> $LOG_FILE 2>&1
    fi
else
    echo "$(date): News already sent today" >> $LOG_FILE
fi

echo "$(date): Startup check completed" >> $LOG_FILE
