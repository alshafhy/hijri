<?php

declare(strict_types=1);

return [
    'default_password_suffix' => '@demo',
    'default_branch_id' => (int) env('DEFAULT_BRANCH_ID', 1),
    'api_token_name' => 'auth_token',
    'token_type' => 'Bearer',
    'staff_limit' => (int) env('STAFF_LIMIT', 99),
    'staff_roles' => ['coordinator', 'evaluator'],
    'invitation_ttl_hours' => 72,
    'status' => [
        'active' => 1,
        'inactive' => 0,
    ],
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrator'),
        'username' => env('ADMIN_USERNAME', 'admin'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],
    'demo' => [
        'password' => env('DEMO_PASSWORD', 'password'),
        'manager' => [
            'name' => env('DEMO_MANAGER_NAME', 'مدير التقييم'),
            'username' => env('DEMO_MANAGER_USERNAME', 'manager'),
            'email' => env('DEMO_MANAGER_EMAIL', 'manager@example.com'),
        ],
        'coordinator' => [
            'name' => env('DEMO_COORDINATOR_NAME', 'منسق الاختبار'),
            'username' => env('DEMO_COORDINATOR_USERNAME', 'coordinator'),
            'email' => env('DEMO_COORDINATOR_EMAIL', 'coordinator@example.com'),
        ],
        'evaluator' => [
            'name' => env('DEMO_EVALUATOR_NAME', 'مقيم الاختبار'),
            'username' => env('DEMO_EVALUATOR_USERNAME', 'evaluator'),
            'email' => env('DEMO_EVALUATOR_EMAIL', 'evaluator@example.com'),
        ],
    ],
];
