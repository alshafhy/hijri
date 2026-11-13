<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('queue:prune-failed --hours=168')->weekly();
Schedule::command('db:dump')->dailyAt('20:00')->withoutOverlapping()->onSuccess(function (): void {
    Artisan::call('db:delete', ['--keep' => 7]);
});
Schedule::command('report:prune-pdf-cache --days=30')->dailyAt('03:30');
