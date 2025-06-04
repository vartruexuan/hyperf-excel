<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Hyperf\AsyncQueue\Driver\DriverInterface as QueueDriverInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\Redis;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;
use Vartruexuan\HyperfExcel\Data\Config\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Config\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Config\ImportConfig;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Exception\ExcelException;
use Vartruexuan\HyperfExcel\Helper\Helper;
use Vartruexuan\HyperfExcel\Job\BaseJob;
use Vartruexuan\HyperfExcel\Job\ExportJob;
use function Hyperf\Support\make;
use Hyperf\Filesystem\FilesystemFactory;
use Hyperf\Contract\PackerInterface;
use Hyperf\Logger\LoggerFactory;

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

    public function export(ExportConfig $config)
    {
        try {
            $this->formatConfig($config);

            $eventParam = [
                'config' => $config,
                'driver' => $this
            ];
            if ($config->getIsAsync()) {
                return $this->pushQueue(new ExportJob($this->name, $config));
            }

            $this->event->dispatch(make(BeforeExport::class, $eventParam));

            // 导出
            $this->exportExcel($config);

            $this->event->dispatch(make(AfterExport::class, $eventParam));
            return ['ok'];

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
            throw $throwable;
        }
    }

    public function import(ImportConfig $config)
    {

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
        return make(Helper::class)->uuid4();
    }

    abstract function exportExcel(ExportConfig $config): string;

    abstract function importExcel(ImportConfig $config);
}