<?php

declare(strict_types=1);

use App\Support\Legacy\YesNoNormalizer;

it('normalizes arabic and english yes/no without guessing', function () {
    expect(YesNoNormalizer::toBool('نعم'))->toBeTrue()
        ->and(YesNoNormalizer::toBool('لا'))->toBeFalse()
        ->and(YesNoNormalizer::toBool('Yes'))->toBeTrue()
        ->and(YesNoNormalizer::toBool('NO'))->toBeFalse()
        ->and(YesNoNormalizer::toBool(1))->toBeTrue()
        ->and(YesNoNormalizer::toBool(0))->toBeFalse()
        ->and(YesNoNormalizer::toBool('maybe'))->toBeNull()
        ->and(YesNoNormalizer::toBool(''))->toBeNull()
        ->and(YesNoNormalizer::toBool(null))->toBeNull();
});
