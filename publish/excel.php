<?php

declare(strict_types=1);

return [
    // 驱动：xlswriter
    'default' => [
        'driver' => '',
        // redis 配置
        'redis' => [
            'pool' => 'default',
        ],
        // queue配置
        'queue' => [
            'name' => 'default',
        ],
        // filesystem 配置
        'filesystem' => [
            'storage' => 'local', // 默认本地
        ]
    ]
];
