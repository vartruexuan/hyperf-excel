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
## config
- 导出
```php
<?php

namespace App\Excel\Export;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;

use Vartruexuan\HyperfExcel\Data\Export\Column;
use Vartruexuan\HyperfExcel\Data\Export\ExportCallbackParam;
use Vartruexuan\HyperfExcel\Data\Export\Sheet;
use function Hyperf\Support\make;

class UserExportConfig extends ExportConfig
{
    public string $serviceName = '用户';

    /**
      * 
      *  输出类型
      *      OUT_PUT_TYPE_UPLOAD 导出=>上传<filesystem>
      *      OUT_PUT_TYPE_OUT    直接同步输出 <isAsync=false>    
      * @var string 
      */
    public string $outPutType = self::OUT_PUT_TYPE_UPLOAD;
    /**
     * 是否异步
     *   true 则会推入队列之中  
     *
     * @var bool
     */
    public bool $isAsync = true;


    public function getSheets(): array
    {
        $this->setSheets([
            new Sheet([
                'name' => 'sheet1',
                'columns' => [
                    new Column([
                        'title' => 'username',
                        'field' => '用户名',
                    ]),
                    new Column([
                        'title' => '姓名',
                        'field' => 'name',
                    ]),
                    new Column([
                        'title' => '年龄',
                        'field' => 'age',
                    ]),
                    // ...
                ],
                'count' => $this->getDataCount(),
                'data' => [$this, 'getData'],
                'pageSize' => 500,
            ])
        ]);
        return $this->sheets;
    }

    /**
     * 获取数据数量
     *
     * @return int
     */
    public function getDataCount(): int
    {
        return make(UserService::class)->getCount($this->getParams());
    }

    /**
     * 获取数据
     *
     * @param ExportCallbackParam $exportCallbackParam
     * @return array
     */
    public function getData(ExportCallbackParam $exportCallbackParam): array
    {
       return make(UserService::class)->getList($this->getParams(),$exportCallbackParam->pageSize,$exportCallbackParam->page);
    }
}

```
- 导入
```php
<?php

namespace App\Excel\Import;

use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use App\Exception\BusinessException;
use Hyperf\Collection\Arr;
use Vartruexuan\HyperfExcel\Data\Import\ImportRowCallbackParam;
use Vartruexuan\HyperfExcel\Data\Import\Sheet;
use function Hyperf\Support\make;

class UserImportConfig extends AbstractImportConfig
{
    public string $serviceName = '用户';

    public function getSheets(): array
    {
        $this->setSheets([
            new Sheet([
                'name' => 'sheet1',
                'isSetHeader' => true,
                // 字段映射
                'headerMap' => [
                    '用户名' => 'username',
                    '姓名' => 'name',
                    '年龄' => 'age',
                    // ...
                ],
                // 数据回调
                'callback' => [$this, 'rowCallback']
            ])
        ]);
        return parent::getSheets();
    }

    public function rowCallback(ImportRowCallbackParam $param)
    {
        try {
            if (!empty($param->row)) {
                if (!Arr::get($param->row, 'username')) {
                    throw new BusinessException(ResultCode::FAIL, '用户名不能为空');
                }
                if (!Arr::get($param->row, 'name')) {
                    throw new BusinessException(ResultCode::FAIL, '姓名不能为空');
                }
                if (!Arr::get($param->row, 'age')) {
                    throw new BusinessException(ResultCode::FAIL, '年龄不能为空');
                }
                // 保存用户信息
                make(UserService::class)->saveUser($param->row);
               
                // ...
            }
        } catch (\Throwable $throwable) {
            // 异常信息将会推入进度消息中 <组件会自动处理>
            // $param->driver->progress->pushMessage($param->config->getToken(),'也可以主动推送一些信息');
            throw new BusinessException(ResultCode::FAIL, '第' . $param->rowIndex . '行:' . $throwable->getMessage());
        }
    }
}
```
## 使用

```php





```




## License

MIT
