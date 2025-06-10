# hyperf-excel

[![php](https://img.shields.io/badge/php-%3E=8.2-brightgreen.svg?maxAge=2592000)](https://github.com/php/php-src)
[![Latest Stable Version](https://img.shields.io/packagist/v/vartruexuan/hyperf-excel)](https://packagist.org/packages/vartruexuan/hyperf-excel)
[![License](https://img.shields.io/packagist/l/vartruexuan/hyperf-excel)](https://github.com/vartruexuan/hyperf-excel)

# 概述
excel 导入导出,支持异步、进度构建。

## 组件能力

- [x] 导入、导出excel
- [x] 支持异步操作,进度构建,进度消息输出
- [x] 格式 `xlsx`
- [x] 支持驱动 `xlswriter`

# 安装
- 安装依赖拓展 [xlswriter](https://xlswriter-docs.viest.me/zh-cn/an-zhuang)
```bash
pecl install xlswriter
```
- 安装组件
```shell
composer require vartruexuan/hyperf-excel
```
- 构建配置
```shell
php bin/hyperf.php vendor:publish vartruexuan/hyperf-excel
```

# 配置
```php
<?php

declare(strict_types=1);

return [
    'default' => 'xlswriter',
    'drivers' => [
        'xlswriter' => [
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
                'prefix' => 'HyperfExcel', // 缓存key前缀
                'expire' => 3600, // 数据失效时间
            ]
        ]
    ],
];
```
# 使用
- 导出
```php


```


## License

MIT
