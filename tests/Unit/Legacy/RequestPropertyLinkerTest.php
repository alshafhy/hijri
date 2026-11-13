<?php

declare(strict_types=1);

use App\Support\Legacy\RequestPropertyLinker;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['database.connections.legacy_test' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]]);

    Schema::connection('legacy_test')->create('location', function (Blueprint $table) {
        $table->integer('id')->primary();
    });
    Schema::connection('legacy_test')->create('request', function (Blueprint $table) {
        $table->integer('idRequest')->primary();
        $table->integer('Location')->nullable();
    });
    Schema::connection('legacy_test')->create('real_estate_information', function (Blueprint $table) {
        $table->integer('idReal_Estate_Information')->primary();
        $table->integer('Location')->nullable();
    });
});

afterEach(function () {
    Schema::connection('legacy_test')->dropIfExists('real_estate_information');
    Schema::connection('legacy_test')->dropIfExists('request');
    Schema::connection('legacy_test')->dropIfExists('location');
});

it('resolves certain when location has exactly one request and one REI', function () {
    DB::connection('legacy_test')->table('location')->insert(['id' => 100]);
    DB::connection('legacy_test')->table('request')->insert(['idRequest' => 10, 'Location' => 100]);
    DB::connection('legacy_test')->table('real_estate_information')->insert([
        'idReal_Estate_Information' => 55,
        'Location' => 100,
    ]);

    $request = (object) ['idRequest' => 10, 'Location' => 100];
    $result = (new RequestPropertyLinker)->resolve($request, 'legacy_test');

    expect($result['resolution'])->toBe(RequestPropertyLinker::RESOLUTION_CERTAIN)
        ->and($result['rei_id'])->toBe(55)
        ->and($result['reason'])->toBe('exact_one_rei_unique_location');
});

it('marks orphan when request has no location', function () {
    $request = (object) ['idRequest' => 1, 'Location' => 0];
    $result = (new RequestPropertyLinker)->resolve($request, 'legacy_test');

    expect($result['resolution'])->toBe(RequestPropertyLinker::RESOLUTION_ORPHAN)
        ->and($result['reason'])->toBe('request_missing_location');
});

it('marks ambiguous when multiple REIs share a location', function () {
    DB::connection('legacy_test')->table('location')->insert(['id' => 100]);
    DB::connection('legacy_test')->table('request')->insert(['idRequest' => 10, 'Location' => 100]);
    DB::connection('legacy_test')->table('real_estate_information')->insert([
        ['idReal_Estate_Information' => 55, 'Location' => 100],
        ['idReal_Estate_Information' => 56, 'Location' => 100],
    ]);

    $request = (object) ['idRequest' => 10, 'Location' => 100];
    $result = (new RequestPropertyLinker)->resolve($request, 'legacy_test');

    expect($result['resolution'])->toBe(RequestPropertyLinker::RESOLUTION_AMBIGUOUS)
        ->and($result['reason'])->toBe('multiple_rei_for_location')
        ->and($result['rei_ids'])->toBe([55, 56]);
});
