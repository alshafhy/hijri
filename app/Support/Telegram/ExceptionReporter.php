<?php

declare(strict_types=1);

namespace App\Support\Telegram;

use Illuminate\Support\Facades\Cache;
use Throwable;

final class ExceptionReporter
{
    private const THROTTLE_SECONDS = 300;

    public function __construct(private readonly TelegramClient $telegram) {}

    public function report(Throwable $e): void
    {
        if (! $this->telegram->configured(TelegramClient::LOG)) {
            return;
        }

        try {
            $fingerprint = 'telegram-exception:'.sha1($e::class.'|'.$e->getFile().'|'.$e->getLine());
            if (! Cache::add($fingerprint, true, self::THROTTLE_SECONDS)) {
                return;
            }

            $request = app()->runningInConsole() ? null : request();

            $this->telegram->send(TelegramClient::LOG, implode("\n", array_filter([
                '⚠️ '.config('app.name').' ('.app()->environment().')',
                $e::class.': '.$e->getMessage(),
                $e->getFile().':'.$e->getLine(),
                $request ? $request->method().' '.$request->fullUrl() : 'console',
                $request?->user() ? 'User #'.$request->user()->getAuthIdentifier() : null,
            ])));
        } catch (Throwable) {
            // Reporting must never mask the original exception.
        }
    }
}
