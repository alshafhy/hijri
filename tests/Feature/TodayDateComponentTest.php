<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders the footer clock with the current time instead of midnight', function () {
    $this->travelTo(now()->setTime(14, 35, 12));

    $html = Blade::render('<x-today-date />');

    expect($html)
        ->toContain('14:35:12')
        ->toContain('today-clock')
        ->not->toContain('00:00:00');
});
