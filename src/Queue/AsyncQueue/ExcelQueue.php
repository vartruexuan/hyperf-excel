<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Queue\AsyncQueue;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Job\ExportJob;
use Vartruexuan\HyperfExcel\Job\ImportJob;
use Vartruexuan\HyperfExcel\Queue\ExcelQueueInterface;
use Hyperf\AsyncQueue\Driver\DriverFactory;

class ExcelQueue implements ExcelQueueInterface
{
    public QueueDriverInterface $queue;

    protected array $config;
    public function __construct(protected ContainerInterface $container)
    {
        $config = $this->get(ConfigInterface::class);
        $this->config = $config->get('excel.queue', []);
        $this->queue = $this->container->get(DriverFactory::class)->get($this->config['name'] ?? 'default');
    }

    public function push(BaseConfig $config)
    {
        $job = $config instanceof ExportConfig ? ExportJob::class : ImportJob::class;
        return $this->queue->push(new $job($config));
    }

}