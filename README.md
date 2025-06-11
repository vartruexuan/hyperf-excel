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
- 依赖组件包 <项目中安装,构建配置>
  - 上传文件 [hyperf/filesystem](https://hyperf.wiki/3.1/#/zh-cn/filesystem?id=%e5%ae%89%e8%a3%85)
  - 异步队列 [hyperf/async-queue](https://hyperf.wiki/3.1/#/zh-cn/async-queue?id=%e5%bc%82%e6%ad%a5%e9%98%9f%e5%88%97)
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
### api
```php
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use \Vartruexuan\HyperfExcel\Data\Export\ExportData;
use \Vartruexuan\HyperfExcel\Data\Import\ImportData;
use \Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use \Vartruexuan\HyperfExcel\Data\Export\ImportConfig

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
#### config
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
            // 异常信息将会推入进度消息中 <组件进度监听器会自动处理>
            // $param->driver->progress->pushMessage($param->config->getToken(),'也可以主动推送一些信息');
            throw new BusinessException(ResultCode::FAIL, '第' . $param->rowIndex . '行:' . $throwable->getMessage());
        }
    }
}
```
### 业务方式使用
- 业务config映射关系配置 `config->autoload->excel_business.php`
```php

<?php

declare(strict_types=1);

return [
    // 导出配置
    'export' => [
        // 用户导出
        'userExport' => [
            'config' => \App\Excel\Export\UserExportConfig::class,
        ],
    ],
    // 导入配置
    'import' => [
        // 用户导入
        'userImport' => [
            'config' => \App\Excel\Import\UserImportConfig::class,
            // 基础信息
            'info' => [
                // 模版地址
                'templateUrl' => 'https://oss-xxx.com/template/用户导入模版.xlsx',
            ],
        ],
    ],
];

```
- 导出、导入、进度查询、消息查询 <大多数代码会写到业务层,为了体现文档,都提到控制器中>
```php

<?php

namespace App\Http\Admin\Controller;

use App\Http\Admin\Request\ExcelRequest;
use App\Kernel\Http\AbstractController;
use App\Service\ExcelLogService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;

class ExcelController extends AbstractController
{
    #[Inject]
    public DriverInterface $excel;
   
    // 导出
    public function export(ExcelRequest $request)
    {
        // 参数校验
        $request->scene('export')->validateResolved();
        
        if (! $config = config('excel_business.export.'.$request->input('businessId'])) {
            throw new BusinessException(ResultCode::FAIL, '对应业务ID不存在');
        }
        $config = new $config['config']([
            'params' => $request->input('param'), // 筛选条件或额外参数
        ]);
        $data = $this->excel->export($config);
        
        // 直接输出
        if($result['response'] instanceof ResponseInterface){
            return $result['response'];
        }
        return return $this->response->success([
            'token' => $data->token,
            'response' => $data->getResponse(), // 同步上传时可直接获取到地址
        ]);
    }

    // 导入
    public function import(ExcelRequest $request)
    {
        $request->scene('import')->validateResolved();
        
        if (! $config = config('excel_business.import.'.$request->input('businessId'])) {
            throw new BusinessException(ResultCode::FAIL, '对应业务ID不存在');
        }
        $importConfig = new $config['config'](['path' => $request->input('url')]);
        $data = $this->excel->import($importConfig);
       
        
        return $this->response->success([
            'token' => $data->token,
        ]);
    }

    // 查询进度
    public function progress(ExcelRequest $request)
    {
        $request->scene('progress')->validateResolved();
        
        // 获取进度记录
        $record = $this->excel->progress->getRecordByToken($request->input('token']))?->toArray();
        if (!$record) {
            throw  new BusinessException(ResultCode::FAIL, '对应记录不存在');
        }
        return $this->response->success($record);
    }

    // 查询消息
    public function message(ExcelRequest $request)
    {
        $request->scene('message')->validateResolved();
    
        $record = $this->getProgressByToken($token);
        $message = $this->excel->progress->popMessage($token);

        if (!$record) {
            throw  new BusinessException(ResultCode::FAIL, '对应记录不存在');
        }    
        return $this->response->success([
            // 是否结束
            'isEnd' => in_array($record->progress->status, [ProgressData::PROGRESS_STATUS_END, ProgressData::PROGRESS_STATUS_FAIL]),
            // 消息集合
            'message' => $message
        ]);
    }

}
```

# 监听器 
## 组件已实现监听器
- 日志输出监听器: `Vartruexuan\HyperfExcel\Listener\ExcelLogListener`
- 自定义监听器,需实现 `Vartruexuan\HyperfExcel\Listener\BaseListener`
- demo:实现一个自定义监听器,记录导入导出到数据库中
监听器
```php

<?php

namespace App\Listener;

use App\Exception\BusinessException;
use App\Kernel\Http\ResultCode;
use App\Service\ExcelLogService;
use Hyperf\Di\Annotation\Inject;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\AfterImport;
use Vartruexuan\HyperfExcel\Event\AfterImportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeImport;
use Vartruexuan\HyperfExcel\Listener\BaseListener;
use Vartruexuan\HyperfExcel\Event\Error;

class ExcelLogListener extends BaseListener
{
    #[inject]
    public ExcelLogService $excelLogService;
    
    function beforeExport(object $event)
    {
        $this->excelLogService->saveLog($event->config);
    }

    function beforeExportExcel(object $event)
    {
        // TODO: Implement beforeExportExcel() method.
    }

    function beforeExportData(object $event)
    {
        // TODO: Implement beforeExportData() method.
    }

    function beforeExportSheet(object $event)
    {
        // TODO: Implement beforeExportSheet() method.
    }

    function afterExport(object $event)
    {
        /**
         * @var AfterExport $event
         */
        $this->excelLogService->saveLog($event->config));
    }

    function afterExportData(object $event)
    {
        // TODO: Implement afterExportData() method.
    }

    function afterExportExcel(object $event)
    {
        // TODO: Implement afterExportExcel() method.
    }

    function afterExportSheet(object $event)
    {
        /**
         * @var AfterExportSheet $event
         */
        $this->excelLogService->saveLog($event->config));
    }

    function beforeImport(object $event)
    {
        /**
         * @var BeforeImport $event
         */
        $this->excelLogService->saveLog($event->config));
    }

    function beforeImportExcel(object $event)
    {
        // TODO: Implement beforeImportExcel() method.
    }

    function beforeImportData(object $event)
    {
        // TODO: Implement beforeImportData() method.
    }

    function beforeImportSheet(object $event)
    {
        // TODO: Implement beforeImportSheet() method.
    }

    function afterImport(object $event)
    {
        /**
         * @var AfterImport $event
         */
        $this->excelLogService->saveLog($event->config))
    }

    function afterImportData(object $event)
    {
        // TODO: Implement afterImportData() method.
    }

    function afterImportExcel(object $event)
    {
        // TODO: Implement afterImportExcel() method.
    }

    function afterImportSheet(object $event)
    {
        /**
         * @var AfterImportSheet $event
         */
       !$this->excelLogService->saveLog($event->config));
    }

    function error(object $event)
    {
        /**
         * @var Error $event
         */
        $this->excelLogService->saveLog($event->config,[
            'remark' => $event->exception->getMessage(),
        ]));
    }
}

```
sql
```sql
CREATE TABLE `excel_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL DEFAULT '',
  `type` enum('export','import') NOT NULL DEFAULT 'export' COMMENT '类型:export导出import导入',
  `config_class` varchar(250) NOT NULL DEFAULT '',
  `config` json DEFAULT NULL COMMENT 'config信息',
  `service_name` varchar(20) NOT NULL DEFAULT '' COMMENT '服务名',
  `sheet_progress` json DEFAULT NULL COMMENT '页码进度',
  `progress` json DEFAULT NULL COMMENT '总进度信息',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态:1.待处理2.正在处理3.处理完成4.处理失败',
  `data` json NOT NULL COMMENT '数据信息',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `url` varchar(300) NOT NULL DEFAULT '' COMMENT 'url地址',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='导入导出日志';
```
## License

MIT
