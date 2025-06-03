<?php

declare(strict_types=1);

use Vartruexuan\HyperfExcel\Job\ExportJob;

return [
    'default' => [
        'driver' => \Vartruexuan\HyperfExcel\Driver\XlsWriterDriver::class,
        // redis 配置
        'redis' => [
            'pool' => 'default',
        ],
        // queue配置
        'queue' => [
            'name' => 'default',
            'jobs' => [
                'export' => \Vartruexuan\HyperfExcel\Job\ExportJob::class,
                'import' => \Vartruexuan\HyperfExcel\Job\ImportJob::class,
            ],
        ],
        // filesystem 配置
        'filesystem' => [
            'storage' => 'local', // 默认本地
        ]
    ]
];
