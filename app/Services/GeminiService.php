<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    }

    /**
     * Generate content using Gemini API
     *
     * @param string $prompt
     * @return string|null
     */
    public function generateContent($prompt)
    {
        try {
            $response = Http::timeout(60)->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            Log::error('Gemini API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get daily news summary
     *
     * @return string|null
     */
    public function getDailyNewsSummary()
    {
        $prompt = "Hãy tạo cho tôi 15 gạch đầu dòng tóm tắt các tin tức quan trọng nhất trong ngày hôm nay (ngày " . date('d/m/Y') . ") tại Việt Nam và thế giới.

Yêu cầu:
- Mỗi gạch đầu dòng ngắn gọn, súc tích (không quá 2 dòng)
- Ưu tiên tin tức về: kinh tế, chính trị, công nghệ, xã hội, thể thao
- Sử dụng tiếng Việt
- Định dạng: • [Tiêu đề ngắn gọn]
- Không cần thêm nguồn tin hoặc thời gian cụ thể

Ví dụ format:
• Chính phủ công bố chính sách mới về thuế thu nhập cá nhân
• Giá vàng tăng mạnh do căng thẳng địa chính trị
• Công ty công nghệ X ra mắt sản phẩm AI mới";

        return $this->generateContent($prompt);
    }

    /**
     * Get missed news summary for a specific date
     *
     * @param \Carbon\Carbon $date
     * @return string|null
     */
    public function getMissedNewsSummary($date)
    {
        $prompt = "Hãy tạo cho tôi 15 gạch đầu dòng tóm tắt các tin tức quan trọng nhất trong ngày " . $date->format('d/m/Y') . " tại Việt Nam và thế giới.

Yêu cầu:
- Mỗi gạch đầu dòng ngắn gọn, súc tích (không quá 2 dòng)
- Ưu tiên tin tức về: kinh tế, chính trị, công nghệ, xã hội, thể thao
- Sử dụng tiếng Việt
- Định dạng: • [Tiêu đề ngắn gọn]
- Không cần thêm nguồn tin hoặc thời gian cụ thể
- Lưu ý: Đây là tin tức bổ sung cho ngày đã qua

Ví dụ format:
• Chính phủ công bố chính sách mới về thuế thu nhập cá nhân
• Giá vàng tăng mạnh do căng thẳng địa chính trị
• Công ty công nghệ X ra mắt sản phẩm AI mới";

        return $this->generateContent($prompt);
    }
}
