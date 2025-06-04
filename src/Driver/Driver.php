<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface as QueueDriverInterface;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Contract\PackerInterface;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportCallbackParam;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterExportData;
use Vartruexuan\HyperfExcel\Event\AfterImportData;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeExportData;
use Vartruexuan\HyperfExcel\Event\BeforeImportData;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Exception\ExcelException;
use Vartruexuan\HyperfExcel\Helper\Helper;
use Vartruexuan\HyperfExcel\Job\BaseJob;
use function Hyperf\Support\make;

use Vartruexuan\HyperfExcel\Data\Export\Sheet as ExportSheet;

abstract class Driver implements DriverInterface
{
    public string $name = 'default';

    public EventDispatcherInterface $event;
    public Redis $redis;
    public Filesystem $filesystem;
    public QueueDriverInterface $queue;
    protected PackerInterface $packer;

    public LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container, protected array $config)
    {
        $this->event = $container->get(EventDispatcherInterface::class);
        $this->redis = $this->container->get(RedisFactory::class)->get($config['redis']['pool'] ?? 'default');
        $this->queue = $this->container->get(DriverFactory::class)->get($config['queue']['name'] ?? 'default');
        $this->filesystem = $this->container->get(FilesystemFactory::class)->get($config['filesystem']['storage'] ?? 'local');
        $this->logger = $this->container->get(LoggerFactory::class)->get($this->config['logger']['name'] ?? 'hyperf-excel');
        $this->packer = $container->get($config['packer'] ?? PhpSerializerPacker::class);
    }

    public function export(ExportConfig $config): ExportData
    {
        try {
            $this->formatConfig($config);
            /**
             * @var ExportData $exportData
             */
            $exportData = make(ExportData::class, ['config' => $config]);
            $eventParam = [
                'config' => $config,
                'driver' => $this
            ];
            if ($config->getIsAsync()) {
                if ($config->getOutPutType() == ExportConfig::OUT_PUT_TYPE_OUT) {
                    throw new ExcelException('Async does not support output type ExportConfig::OUT_PUT_TYPE_OUT');
                }
                $this->pushQueue(new $this->config['queue']['jobs']['export']($this->name, $config));
                return $exportData;
            }
            $this->event->dispatch(make(BeforeExport::class, $eventParam));

            $path = $this->exportExcel($config);

            $exportData->response = $this->exportOutPut($config, $path);

            $this->event->dispatch(make(AfterExport::class, $eventParam));

            return $exportData;
        } catch (ExcelException $exception) {
            $this->event->dispatch(make(Error::class, [
                'config' => $config,
                'driver' => $this,
                'exception' => $exception,
            ]));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(make(Error::class, [
                'config' => $config,
                'driver' => $this,
                'exception' => $throwable,
            ]));
            $this->logger->error('export error:' . $throwable->getMessage(), ['exception' => $throwable]);
            throw $throwable;
        }
    }

    public function import(ImportConfig $config)
    {

    }


    /**
     * 导入行回调
     *
     * @param callable $callback
     * @param ImportConfig $config
     * @param ImportSheet $sheet
     * @param array $row
     *
     * @return mixed|null
     */
    protected function importRowCallback(callable $callback, ImportConfig $config, ImportSheet $sheet, array $row)
    {
        $importRowCallbackParam = new ImportRowCallbackParam([
            'excel' => $this,
            'sheet' => $sheet,
            'importConfig' => $config,
            'row' => $row,
        ]);
        $eventParam = [
            'config' => $config,
            'driver' => $this,
        ];
        $this->event->dispatch(make(BeforeImportData::class, $eventParam));

        $result = call_user_func($callback, $importRowCallbackParam);

        $this->event->dispatch(make(AfterImportData::class, $eventParam));

        return $result ?? null;
    }


    /**
     * 导出数据回调
     *
     * @param callable $callback 回调
     * @param ExportConfig $config
     * @param ExportSheet $sheet
     * @param int $page 页码
     * @param int $pageSize 限制每页数量
     * @param int|null $totalCount
     * @return mixed
     */
    protected function exportDataCallback(callable $callback, ExportConfig $config, ExportSheet $sheet, int $page, int $pageSize, ?int $totalCount)
    {
        $exportCallbackParam = new ExportCallbackParam([
            'driver' => $this,
            'exportConfig' => $config,
            'sheet' => $sheet,

            'page' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
        ]);

        $eventParam = [
            'config' => $config,
            'driver' => $this,
        ] ;

        $this->event->dispatch(make(BeforeExportData::class, $eventParam));

        $result = call_user_func($callback, $exportCallbackParam);

        $this->event->dispatch(make(AfterExportData::class, $eventParam));

        return $result;
    }

    /**
     * 导出文件输出
     *
     * @param ExportConfig $config
     * @param string $filePath
     * @return string|Psr\Http\Message\ResponseInterface
     * @throws ExcelException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function exportOutPut(ExportConfig $config, string $filePath): string|\Psr\Http\Message\ResponseInterface
    {
        $path = $this->buildExportPath($config);
        $fileName = basename($path);
        switch ($config->outPutType) {
            // 上传
            case ExportConfig::OUT_PUT_TYPE_UPLOAD:
                $this->filesystem->writeStream($path, fopen($filePath, 'r+'));
                if (!$this->filesystem->fileExists($path)) {
                    throw new ExcelException('File upload failed');
                }
                return $path;
            // 直接输出
            case ExportConfig::OUT_PUT_TYPE_OUT:
                $response = $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class);
                return $response->download($filePath, $fileName);
            default:
                throw new ExcelException('outPutType error');
        }
    }


    /**
     * 构建配置
     *
     * @param BaseConfig $config
     * @return BaseConfig
     */
    public function formatConfig(BaseConfig $config)
    {
        if (empty($config->getToken())) {
            $config->setToken($this->buildToken());
        }
        return $config;
    }

    /**
     * 推送队列
     *
     * @param BaseJob $job
     * @return bool
     */
    public function pushQueue(BaseJob $job): bool
    {
        $job->driverName = $this->name; // 设置名
        return $this->queue->push($job);
    }

    /**
     * 构建token
     *
     * @return string
     * @throws \yii\base\Exception
     */
    protected function buildToken()
    {
        return Helper::uuid4();
    }

    /**
     * 构建导出地址
     *
     * @param ExportConfig $config
     * @return string
     */
    protected function buildExportPath(ExportConfig $config)
    {
        return $this->config['export']['rootDir'] . DIRECTORY_SEPARATOR . make($this->config['export']['pathStrategy'], ['config' => $config])->getPath($config);
    }

    abstract function exportExcel(ExportConfig $config): string;

    abstract function importExcel(ImportConfig $config);
}