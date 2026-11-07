<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Offer\RelinkOfferPartnersAction;
use Illuminate\Console\Command;

class RelinkOfferPartnersCommand extends Command
{
    protected $signature = 'offers:relink-partners';

    protected $description = 'Set offers.partner_id from the imported partner field (legacy id or name)';

    public function handle(RelinkOfferPartnersAction $action): int
    {
        $summary = $action();

        $this->info("Linked {$summary['linked']} offers, skipped {$summary['skipped']}.");

        return self::SUCCESS;
    }
}
