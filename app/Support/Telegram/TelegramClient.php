<?php

declare(strict_types=1);

namespace App\Support\Telegram;

use Illuminate\Support\Facades\Http;
use Throwable;

final class TelegramClient
{
    public const NOTIFY = 'notify';

    public const LOG = 'log';

    private const MAX_LENGTH = 4000;

    public function configured(string $bot): bool
    {
        return filled(config("services.telegram.{$bot}.token")) && filled(config("services.telegram.{$bot}.chat_id"));
    }

    /**
     * Never throws: Telegram must not break the request or the exception handler.
     */
    public function send(string $bot, string $text): bool
    {
        if (! $this->configured($bot)) {
            return false;
        }

        try {
            return Http::timeout(5)
                ->asForm()
                ->post('https://api.telegram.org/bot'.config("services.telegram.{$bot}.token").'/sendMessage', [
                    'chat_id' => config("services.telegram.{$bot}.chat_id"),
                    'text' => mb_substr($text, 0, self::MAX_LENGTH),
                    'disable_web_page_preview' => 'true',
                ])
                ->successful();
        } catch (Throwable) {
            return false;
        }
    }
}
