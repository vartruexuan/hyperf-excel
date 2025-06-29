# hyperf-excel

[![php](https://img.shields.io/badge/php-%3E=8.2-brightgreen.svg?maxAge=2592000)](https://github.com/php/php-src)
[![Latest Stable Version](https://img.shields.io/packagist/v/vartruexuan/hyperf-excel)](https://packagist.org/packages/vartruexuan/hyperf-excel)
[![License](https://img.shields.io/packagist/l/vartruexuan/hyperf-excel)](https://github.com/vartruexuan/hyperf-excel)

# 概述
excel 同步|异步智能配置导入导出

## 组件能力
- [x] 支持异步导入导出
- [x] 复杂表头(`无限极`|`跨行`|`跨列`)
- [x] 样式配置(`页码样式`|`表头样式`|`列样式`)
- [x] 进度信息
- [x] 消息构建查询
- [x] 格式 `xlsx`
- [x] 支持驱动 `xlswriter`

# 安装
- 安装依赖拓展 [xlswriter](https://xlswriter-docs.viest.me/zh-cn/an-zhuang)
```bash
pecl install xlswriter
```
- 依赖组件包 <项目中安装,构建配置>
  - [hyperf/filesystem](https://hyperf.wiki/3.1/#/zh-cn/filesystem?id=%e5%ae%89%e8%a3%85)
  - [hyperf/async-queue](https://hyperf.wiki/3.1/#/zh-cn/async-queue?id=%e5%bc%82%e6%ad%a5%e9%98%9f%e5%88%97)
  - [hyperf/logger](https://hyperf.wiki/3.1/#/zh-cn/logger?id=%e6%97%a5%e5%bf%97)
  - [hyperf/redis](https://hyperf.wiki/3.1/#/zh-cn/redis?id=redis)
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
        ]
    ],
    'options' => [
        'logger' => [
            'name' => 'hyperf-excel',
        ],
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
        'export' => [
            'rootDir' => 'export',
            // 导出文件地址构建策略
            'pathStrategy' => \Vartruexuan\HyperfExcel\Strategy\Path\DateTimeStrategy::class,
        ],
        // 进度处理
        'progress' => [
            'enable' => true,
            'prefix' => 'HyperfExcel',
            'expire' => 3600, // 数据失效时间
        ],
        'dbLog' => [
            'model' => \Vartruexuan\HyperfExcel\Db\Model\ExcelLog::class,
        ],
    ],
    // 清除临时文件
    'cleanTempFile' => [
        'enable' => true, // 是否允许
        'time' => 1800, // 文件未操作时间(秒)
        'interval' => 1800,// 间隔检查时间
    ],
];
```
# 使用
### api
```php
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use \Vartruexuan\HyperfExcel\Data\Export\ExportData;
use \Vartruexuan\HyperfExcel\Data\Import\ImportData;
use \Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use \Vartruexuan\HyperfExcel\Data\Import\ImportConfig

$excel = ApplicationContext::getContainer()->get(DriverInterface::class);
// 工厂类方式
// $excel = ApplicationContext::getContainer()->get(DriverFactory::class)->get('xlswriter');

// 导出
$excel->export(ExportConfig $config):ExportData;
// 导入
$excel->import(ImportConfig $config):ImportData;
// 进度信息
$excel->progress->getRecordByToken($token);
// 进度消息
$excel->progress->popMessage($token);
```

### 导入导出config类配置
#### 导出
- config
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
                        'title' => '用户名',
                        'field' => 'username',
                        // 子列
                        'children' => []
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
- Sheet 页码
```php
 new Sheet([
      'name' => 'sheet1', // 页码名
      'columns' => [ // 列配置
         new \Vartruexuan\HyperfExcel\Data\Export\Column([
         
        ]),
      ],
      'count' => 0, // 数据数量
      'data' => [], // 数据(array|callback)
      'pageSize' => 1, // 分批导出数
      // 页码样式
      'style'=> new  \Vartruexuan\HyperfExcel\Data\Export\SheetStyle([
         // 网格线
         'gridline'=> \Vartruexuan\HyperfExcel\Data\Export\SheetStyle::GRIDLINES_HIDE_ALL,
         // 缩放 (10 <= $scale <= 400)
         'zoom'=> 50,  
         // 隐藏当前页码 
         'hide' => false, 
         // 选中当前页码
         'isFirst' => true,
      ]);
]),
```

- Column 列
```php
 new Column([
      'title' => "一级列", // 列名
      //'width' => 32, // 宽度
      'height' => 58,
      // header 单元样式
      'headerStyle' => new Style([
          'wrap' => true,
          'fontColor' => 0x2972F4,
          'font' => '等线',
          'align' => [Style::FORMAT_ALIGN_LEFT, Style::FORMAT_ALIGN_VERTICAL_CENTER],
          'fontSize' => 10,
      ]),
      // 子列 <自动跨列>
      'children' => [
          new Column([
              'title' => '二级列1',
              'field' => 'key1', // 数据字段名
              'width' => 32, // 宽度
              'headerStyle' => new Style([
                  'align' => [Style::FORMAT_ALIGN_CENTER],
                  'bold' => true,
              ]),
          ]),
          new Column([
              'title' => '二级列2',
              'field' => 'key2',
              'width' => 32,
              'headerStyle' => new Style([
                  'align' => [Style::FORMAT_ALIGN_CENTER],
                  'bold' => true,
              ])
          ]),
      ],
]),
```
#### 导入
- config
```php
<?php

namespace App\Excel\Import;

use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use App\Exception\BusinessException;
use Hyperf\Collection\Arr;
use Vartruexuan\HyperfExcel\Data\Import\ImportRowCallbackParam;
use Vartruexuan\HyperfExcel\Data\Import\Sheet;
use function Hyperf\Support\make;
use Vartruexuan\HyperfExcel\Data\Import\Column;

class UserImportConfig extends AbstractImportConfig
{
    public string $serviceName = '用户';

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
                'headerIndex' => 1, // 列头下标<0则无列头>
                'columns' => [
                      new Column([
                          'title' => '用户名', // excel中列头
                          'field' => 'username', // 映射字段名
                          'type' => Column::TYPE_STRING, // 数据类型(默认 string)
                      ]),
                      new Column([
                          'title' => '年龄',
                          'field' => 'age',
                           'type' => Column::TYPE_INT,
                      ]),
                      new Column([
                          'title' => '身高',
                          'field' => 'height',
                          'type' => Column::TYPE_INT,
                      ]),
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
            // 异常信息将会推入进度消息中 <组件进度监听器会自动处理>
            // $param->driver->progress->pushMessage($param->config->getToken(),'也可以主动推送一些信息');
            throw new BusinessException(ResultCode::FAIL, '第' . $param->rowIndex . '行:' . $throwable->getMessage());
        }
    }
}
```
# 监听器 
## 日志监听器
```php
// config/autoload/listeners.php
return [
    Vartruexuan\HyperfExcel\Listener\ExcelLogListener::class,
];
```
## db日志监听器
```php
// config/autoload/listeners.php
return [
    Vartruexuan\HyperfExcel\Listener\ExcelLogDbListener::class,
];
```
- 构建数据库表
```bash
php bin/hyperf.php migrate  --path=./vendor/vartruexuan/hyperf-excel/src/migrations
```
或
```sql
# 直接执行sql
CREATE TABLE `excel_log` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `token` varchar(64) NOT NULL DEFAULT '',
     `type` enum('export','import') NOT NULL DEFAULT 'export' COMMENT '类型:export导出import导入',
     `config_class` varchar(250) NOT NULL DEFAULT '',
     `config` json DEFAULT NULL COMMENT 'config信息',
     `service_name` varchar(20) NOT NULL DEFAULT '' COMMENT '服务名',
     `sheet_progress` json DEFAULT NULL COMMENT '页码进度',
     `progress` json DEFAULT NULL COMMENT '总进度信息',
     `status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '状态:1.待处理2.正在处理3.处理完成4.处理失败',
     `data` json NOT NULL COMMENT '数据信息',
     `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
     `url` varchar(300) NOT NULL DEFAULT '' COMMENT 'url地址',
     `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
     `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
     PRIMARY KEY (`id`),
     UNIQUE KEY `uniq_token` (`token`)
) ENGINE=InnoDB  COMMENT='导入导出日志';

```

## 自定义监听器
- 继承`Vartruexuan\HyperfExcel\Listener\BaseListener`
## License

MIT
