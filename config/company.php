<?php

declare(strict_types=1);

return [
    'name' => env('COMPANY_NAME', 'Muqayem'),
    'name_ar' => env('COMPANY_NAME_AR', 'مقيم'),
    'url' => env('COMPANY_URL', env('APP_URL', '/')),
    'copyright_holder' => env('COMPANY_COPYRIGHT', env('COMPANY_NAME_AR', 'مقيم')),
];
