<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Support\Telegram\TelegramClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly string $text)
    {
        $this->afterCommit();
    }

    public function handle(TelegramClient $telegram): void
    {
        $telegram->send(TelegramClient::NOTIFY, $this->text);
    }
}
