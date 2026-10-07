<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Jobs\SendTelegramMessageJob;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Telegram\TelegramClient;

final class ValuationActivity
{
    /** @var array<string, string> */
    private const TELEGRAM_EVENTS = [
        'created' => 'New valuation request #:reference created by :user',
        'sent' => 'Valuation request #:reference sent by :user',
        'under_evaluation' => 'Valuation request #:reference moved under evaluation by :user',
        'official_report_uploaded' => 'Official report uploaded for valuation #:reference by :user',
        'toggled_qima_status' => 'Qima status of valuation #:reference changed by :user',
    ];

    public static function log(
        User $actor,
        ValuationRequest $request,
        string $event,
        string $description,
        array $properties = []
    ): void {
        activity('valuation')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties(array_merge([
                'event' => $event,
                'valuation_request_id' => $request->id,
                'reference' => $request->reference,
                'number' => $request->number,
            ], $properties))
            ->event($event)
            ->log($description);

        self::notifyTelegram($actor, $request, $event);
    }

    private static function notifyTelegram(User $actor, ValuationRequest $request, string $event): void
    {
        if (! isset(self::TELEGRAM_EVENTS[$event]) || ! app(TelegramClient::class)->configured(TelegramClient::NOTIFY)) {
            return;
        }

        SendTelegramMessageJob::dispatch(__(self::TELEGRAM_EVENTS[$event], [
            'reference' => (string) ($request->reference ?? $request->id),
            'user' => (string) $actor->name,
        ]));
    }
}
