<?php

declare(strict_types=1);

use App\Actions\Database\DumpDatabaseAction;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Telegram\ExceptionReporter;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.telegram.notify' => ['token' => 'NOTIFY', 'chat_id' => '111'],
        'services.telegram.log' => ['token' => 'LOG', 'chat_id' => '222'],
    ]);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
});

it('sends Telegram notifications for tracked valuation events only', function () {
    $actor = User::factory()->create(['name' => 'منسق', 'status' => UserStatus::Active]);
    $request = ValuationRequest::query()->create(['number' => 'T-1', 'reference' => 10055, 'state' => 'waiting', 'approve' => 0]);

    ValuationActivity::log($actor, $request, 'sent', 'Valuation request sent');
    ValuationActivity::log($actor, $request, 'notes_updated', 'Notes updated');

    Http::assertSentCount(1);
    Http::assertSent(fn (HttpRequest $r): bool => str_contains($r->url(), 'botNOTIFY/sendMessage')
        && $r['chat_id'] === '111'
        && str_contains($r['text'], '10055')
        && str_contains($r['text'], 'منسق'));
});

it('skips Telegram entirely when the bot is not configured', function () {
    config(['services.telegram.notify.token' => null]);
    $actor = User::factory()->create(['status' => UserStatus::Active]);
    $request = ValuationRequest::query()->create(['number' => 'T-2', 'reference' => 10056, 'state' => 'waiting', 'approve' => 0]);

    ValuationActivity::log($actor, $request, 'created', 'Valuation request created');

    Http::assertNothingSent();
});

it('reports exceptions to the log bot once per fingerprint', function () {
    $reporter = app(ExceptionReporter::class);
    $exception = new RuntimeException('Boom');

    $reporter->report($exception);
    $reporter->report($exception);

    Http::assertSentCount(1);
    Http::assertSent(fn (HttpRequest $r): bool => str_contains($r->url(), 'botLOG/') && str_contains($r['text'], 'RuntimeException: Boom'));
});

it('never throws when Telegram is unreachable', function () {
    Http::fake(fn () => throw new ConnectionException('down'));

    app(ExceptionReporter::class)->report(new LogicException('x'));
})->throwsNoExceptions();

it('writes browser errors to the client log channel', function () {
    Log::shouldReceive('channel')->once()->with('client')->andReturnSelf();
    Log::shouldReceive('warning')->once()->withArgs(fn (string $message, array $context): bool => $message === '[error] x is not defined'
        && $context['source'] === 'https://app.test/app.js'
        && $context['line'] === 12);

    $this->postJson(route('client-log.store'), [
        'type' => 'error', 'message' => 'x is not defined', 'source' => 'https://app.test/app.js', 'line' => 12, 'column' => 4,
    ])->assertNoContent();
});

it('validates client log payloads', function () {
    $this->postJson(route('client-log.store'), ['type' => 'nope'])->assertUnprocessable()->assertJsonValidationErrors(['type', 'message']);
});

it('prunes stale cached PDFs and old backups', function () {
    $cacheDir = storage_path('app/private/report-pdf-cache/999999');
    File::ensureDirectoryExists($cacheDir);
    file_put_contents($old = $cacheDir.'/old.pdf', 'x');
    touch($old, now()->subDays(40)->getTimestamp());
    file_put_contents($fresh = $cacheDir.'/fresh.pdf', 'x');

    $this->artisan('report:prune-pdf-cache', ['--days' => 30])->assertSuccessful();
    expect(is_file($old))->toBeFalse()->and(is_file($fresh))->toBeTrue();
    File::deleteDirectory($cacheDir);

    $backupDir = storage_path(DumpDatabaseAction::DIRECTORY);
    File::ensureDirectoryExists($backupDir);
    $created = [];
    foreach (range(1, 4) as $i) {
        file_put_contents($created[] = $backupDir."/test-backup-{$i}.sql.gz", 'x');
        touch(end($created), now()->subDays(10 - $i)->getTimestamp());
    }

    $this->artisan('db:delete', ['--keep' => 2])->assertSuccessful();
    expect(array_map('is_file', $created))->toBe([false, false, true, true]);
    array_map(fn (string $f) => @unlink($f), $created);
});

it('refuses to dump a non-MySQL connection', function () {
    $this->artisan('db:dump')->assertFailed();
});

it('schedules backups and PDF cache pruning', function () {
    $events = collect(app(Schedule::class)->events())->mapWithKeys(fn ($e) => [$e->command => $e->expression]);

    expect($events->first(fn ($expr, $cmd) => str_contains((string) $cmd, 'db:dump')))->toBe('0 20 * * *')
        ->and($events->first(fn ($expr, $cmd) => str_contains((string) $cmd, 'report:prune-pdf-cache --days=30')))->toBe('30 3 * * *');
});
