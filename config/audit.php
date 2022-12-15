<?php

return [
    'connection' => env('DB_AUDIT_CONNECTION'),

    'route' => [
        'prefix' => [
            'web' => env('AUDIT_ROUTE_PREFIX_WEB', 'audit'),
            'api' => env('AUDIT_ROUTE_PREFIX_API', 'api/audit'),
        ],
    ],

    'actions' => [
        'log_access' => true,
    ],
];
