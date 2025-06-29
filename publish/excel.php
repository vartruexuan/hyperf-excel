<?php

declare(strict_types=1);

return [
    'default' => 'xlswriter',
    'drivers' => [
        'xlswriter' => [
            'driver' => \Vartruexuan\HyperfExcel\Driver\XlsWriterDriver::class,
        ]
    ],
    'options' => [
        'logger' => [
            'name' => 'hyperf-excel',
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
        'export' => [
            'rootDir' => 'export',
            // 导出文件地址构建策略
            'pathStrategy' => \Vartruexuan\HyperfExcel\Strategy\Path\DateTimeStrategy::class,
        ],

    ],
    // 进度处理
    'progress' => [
        'enable' => true,
        'prefix' => 'HyperfExcel',
        'expire' => 3600, // 数据失效时间
    ],
    'dbLog' => [
        'enable' => true,
        'model' => \Vartruexuan\HyperfExcel\Db\Model\ExcelLog::class,
    ],
    // 清除临时文件
    'cleanTempFile' => [
        'enable' => true, // 是否允许
        'time' => 1800, // 文件未操作时间(秒)
        'interval' => 1800,// 间隔检查时间
    ],
];
