<?php

namespace App\Infrastructure\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TelegramService — Infrastructure Service.
 * Menangani integrasi dengan Telegram Bot API.
 */
class TelegramService
{
    private ?string $token;

    private ?string $defaultChatId;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->defaultChatId = config('services.telegram.group_id');
    }

    /**
     * Kirim pesan teks ke chat tertentu.
     *
     * @param  string  $message Format teks (mendukung Markdown)
     * @param  string|null  $chatId Target Chat ID (fallback ke Group IT)
     */
    public function sendMessage(string $message, ?string $chatId = null): bool
    {
        $targetId = $chatId ?? $this->defaultChatId;

        if (empty($this->token) || empty($targetId)) {
            Log::warning('Telegram Notification skipped: Token atau Chat ID kosong.');

            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id'    => $targetId,
                'text'       => $message,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->failed()) {
                Log::error('Telegram API Error: '.$response->body());

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Service Exception: '.$e->getMessage());

            return false;
        }
    }
}
