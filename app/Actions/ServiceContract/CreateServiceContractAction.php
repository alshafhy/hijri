<?php

declare(strict_types=1);

namespace App\Actions\ServiceContract;

use App\Models\Company;
use App\Models\ServiceContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateServiceContractAction
{
    public const FIRST_NUMBER = 100;

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, array $data): ServiceContract
    {
        return DB::transaction(function () use ($actor, $data): ServiceContract {
            $last = ServiceContract::query()->lockForUpdate()->max('number');

            return ServiceContract::query()->create([
                ...$data,
                'number' => $last !== null ? (int) $last + 1 : self::FIRST_NUMBER,
                'company_id' => Company::query()->orderBy('id')->value('id'),
                'created_by' => $actor->id,
            ]);
        });
    }
}
