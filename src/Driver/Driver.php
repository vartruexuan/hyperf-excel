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
use Vartruexuan\HyperfExcel\Data\Import\ImportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportRowCallbackParam;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterExportData;
use Vartruexuan\HyperfExcel\Event\AfterImport;
use Vartruexuan\HyperfExcel\Event\AfterImportData;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeExportData;
use Vartruexuan\HyperfExcel\Event\BeforeImport;
use Vartruexuan\HyperfExcel\Event\BeforeImportData;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Exception\ExcelException;
use Vartruexuan\HyperfExcel\Helper\Helper;
use Vartruexuan\HyperfExcel\Job\BaseJob;
use Vartruexuan\HyperfExcel\Data\Import\Sheet as ImportSheet;

use Vartruexuan\HyperfExcel\Data\Export\Sheet as ExportSheet;
use Vartruexuan\HyperfExcel\Progress\Progress;
use function Hyperf\Support\make;


abstract class Driver implements DriverInterface
{
    public EventDispatcherInterface $event;
    public Redis $redis;
    public Filesystem $filesystem;
    public QueueDriverInterface $queue;
    public PackerInterface $packer;

    public LoggerInterface $logger;

    public Progress $progress;

    public function __construct(protected ContainerInterface $container, protected array $config, protected string $name = 'xlswriter')
    {
        $this->event = $container->get(EventDispatcherInterface::class);
        $this->redis = $this->container->get(RedisFactory::class)->get($this->config['redis']['pool'] ?? 'default');
        $this->queue = $this->container->get(DriverFactory::class)->get($this->config['queue']['name'] ?? 'default');
        $this->filesystem = $this->container->get(FilesystemFactory::class)->get($this->config['filesystem']['storage'] ?? 'local');
        $this->logger = $this->container->get(LoggerFactory::class)->get($this->config['logger']['name'] ?? 'hyperf-excel');
        $this->packer = $container->get(PhpSerializerPacker::class);
        $this->progress = make(Progress::class, [
            'config' => $this->config['progress'] ?? [],
            'driver' => $this,
        ]);
    }

    public function export(ExportConfig $config): ExportData
    {
        try {
            $config = $this->formatConfig($config);

            $exportData = new ExportData(['token' => $config->getToken()]);

            $this->event->dispatch(new BeforeExport($config, $this));

            if ($config->getIsAsync()) {
                if ($config->getOutPutType() == ExportConfig::OUT_PUT_TYPE_OUT) {
                    throw new ExcelException('Async does not support output type ExportConfig::OUT_PUT_TYPE_OUT');
                }
                $this->pushQueue(new $this->config['queue']['jobs']['export']($this->name, $config));
                return $exportData;
            }

            $path = $this->exportExcel($config);

            $exportData->response = $this->exportOutPut($config, $path);

            $this->event->dispatch(new AfterExport($config, $this, $exportData));

            return $exportData;
        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $this, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $this, $throwable));
            $this->logger->error('export error:' . $throwable->getMessage(), ['exception' => $throwable]);
            throw $throwable;
        }
    }

    public function import(ImportConfig $config): importData
    {
        $config = $this->formatConfig($config);

        try {
            $importData = new ImportData(['token' => $config->getToken()]);

            $this->event->dispatch(new BeforeImport($config, $this));

            if ($config->getIsAsync()) {
                $this->pushQueue(new $this->config['queue']['jobs']['import']($this->name, $config));
                return $importData;
            }
            $config->setTempPath($this->fileToTemp($config->getPath()));

            $this->importExcel($config);

            // 删除临时文件
            Helper::deleteFile($config->getTempPath());

            $this->event->dispatch(new AfterImport($config, $this, $importData));
        } catch (ExcelException $exception) {

            $this->event->dispatch(new Error($config, $this, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {

            $this->event->dispatch(new Error($config, $this, $throwable));
            $this->logger->error('export error:' . $throwable->getMessage(), ['exception' => $throwable]);
            throw $throwable;
        }

        return $importData;
    }


    /**
     * 文件to临时文件
     *
     * @param $path
     * @return false|string
     * @throws ExcelException
     */
    protected function fileToTemp($path)
    {
        $filePath = Helper::getTempFileName();

        if (!Helper::isUrl($path)) {
            // 本地文件
            if (!is_file($path)) {
                throw new ExcelException('File not exists');
            }
            if (!copy($path, $filePath)) {
                throw new ExcelException('File copy error');
            }
        } else {
            // 远程文件
            if (!Helper::downloadFile($path, $filePath)) {
                throw new ExcelException('File download error');
            }
        }
        return $filePath;
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
            'config' => $config,
            'sheet' => $sheet,

            'page' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
        ]);

        $this->event->dispatch(new BeforeExportData($config, $this, $exportCallbackParam));

        $result = call_user_func($callback, $exportCallbackParam);

        $this->event->dispatch(new AfterExportData($config, $this, $exportCallbackParam, $result ?? []));

        return $result;
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
    protected function importRowCallback(callable $callback, ImportConfig $config, ImportSheet $sheet, array $row, int $rowIndex)
    {
        $importRowCallbackParam = new ImportRowCallbackParam([
            'excel' => $this,
            'sheet' => $sheet,
            'config' => $config,
            'row' => $row,
            'rowIndex' => $rowIndex,
        ]);

        $this->event->dispatch(new BeforeImportData($config, $this, $importRowCallbackParam));
        try {
            $result = call_user_func($callback, $importRowCallbackParam);
        } catch (\Throwable $throwable) {
            $exception = $throwable;
        }
        $this->event->dispatch(new AfterImportData($config, $this, $importRowCallbackParam, $exception ?? null));

        return $result ?? null;
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
                $resp = $response->download($filePath, $fileName);
                $resp->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $resp->setHeader('Content-Disposition', 'attachment;filename="' . rawurlencode($fileName) . '"');
                $resp->setHeader('Content-Length', filesize($filePath));
                $resp->setHeader('Content-Transfer-Encoding', 'binary');
                $resp->setHeader('Cache-Control', 'must-revalidate');
                $resp->setHeader('Cache-Control', 'max-age=0');
                $resp->setHeader('Pragma', 'public');
                return $resp;
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
     * token
     *
     * @return string
     */
    protected function buildToken(): string
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
        return $this->config['export']['rootDir'] . DIRECTORY_SEPARATOR . (new $this->config['export']['pathStrategy'])->getPath($config);
    }

    abstract function exportExcel(ExportConfig $config): string;

    abstract function importExcel(ImportConfig $config);
}