<?php

declare(strict_types=1);

use Hyperf\Codec\Packer\PhpSerializerPacker;

return [
    'default' => [
        'driver' => \Vartruexuan\HyperfExcel\Driver\XlsWriterDriver::class,
        // redis 配置
        'redis' => [
            'pool' => 'default',
        ],
        // filesystem 配置
        'filesystem' => [
            'storage' => 'local', // 默认本地
        ],
        // queue配置
        'queue' => [
            'name' => 'default',
            'jobs' => [
                'export' => \Vartruexuan\HyperfExcel\Job\ExportJob::class,
                'import' => \Vartruexuan\HyperfExcel\Job\ImportJob::class,
            ],
        ],
        'logger' => [
            'name' => 'hyperf-excel',
        ],
        'export' => [
            'rootDir' => 'export',
            // 导出文件地址构建策略
            'pathStrategy' => \Vartruexuan\HyperfExcel\Strategy\Path\DateTimeStrategy::class,
        ],
        // 进度处理
        'progress' => [
            'enabled' => true,
            'prefix' => 'HyperfExcel',
            'expire' => 3600, // 数据失效时间
        ]
    ]
];
