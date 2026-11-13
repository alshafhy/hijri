<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportRun extends Model
{
    protected $fillable = [
        'name',
        'status',
        'started_at',
        'finished_at',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(ImportCheckpoint::class);
    }

    public function quarantine(): HasMany
    {
        return $this->hasMany(ImportQuarantine::class);
    }
}
