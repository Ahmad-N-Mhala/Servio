<?php

use Illuminate\Support\Str;

return [
    'use' => 'default',
    'path' => 'horizon',
    'middleware' => ['web'],
    'waits' => [
        'redis:default' => 60,
    ],
    'trim' => [
        'recent' => 60,
        'pending' => 90,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],
    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],
    'fast_termination' => false,
    'balance' => 'auto',
    'auto_scaling' => [
        'enabled' => false,
    ],
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'processes' => 10,
                'tries' => 1,
                'nice' => 0,
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],
        ],
    ],
];

