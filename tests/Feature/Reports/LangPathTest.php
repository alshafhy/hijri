<?php

declare(strict_types=1);

it('loads translations from resources/lang only', function () {
    expect(app()->langPath())->toBe(resource_path('lang'))
        ->and(is_dir(base_path('lang')))->toBeFalse();
});
