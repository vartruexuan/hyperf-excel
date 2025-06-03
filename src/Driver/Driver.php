<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Psr\EventDispatcher\EventDispatcherInterface;

use Hyperf\Redis\RedisFactory;
use Hyperf\Redis\Redis;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Filesystem\Filesystem;
use Vartruexuan\HyperfExcel\Job\BaseJob;

abstract class Driver implements DriverInterface
{

    public string $name = 'default';
    public EventDispatcherInterface $event;
    public Redis $redis;
    public Filesystem $filesystem;
    public DriverFactory $queue;

    public function __construct(protected ContainerInterface $container, protected array $config)
    {
        $this->event = $container->get(EventDispatcherInterface::class);
        $this->redis = $this->container->get(RedisFactory::class)->get($config['redis']['pool'] ?? 'default');
        $this->queue = $this->container->get(DriverFactory::class)->get($config['queue']['name'] ?? 'default');
        $this->filesystem = $this->container->get(FilesystemFactory::class)->get($config['filesystem']['storage'] ?? 'local');
    }

    public function export($config)
    {

    }

    public function import($config)
    {

    }


    /**
     * 推送队列
     *
     * @param BaseJob $job
     * @return void
     */
    public function pushQueue(BaseJob $job)
    {
        $job->driverName = $this->name; // 设置名
        return $this->queue->push($job);
    }


    abstract function exportExcel($config): string;

    abstract function importExcel($config);
}