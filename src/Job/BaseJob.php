<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\Event\Error;
use function Hyperf\Support\make;

abstract class BaseJob extends Job
{
    /**
     * 驱动名
     *
     * @var string
     */
    public string $driverName = 'default';
    public BaseConfig $config;
    protected int $maxAttempts = 0;

    public function __construct(string $driverName, BaseConfig $config)
    {
        $this->driverName = $driverName;
        $this->config = $config;
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    protected function getDriver(): Driver
    {
        return $this->getContainer()->get(DriverFactory::class)->get($this->driverName);
    }

    public function fail(\Throwable $e): void
    {
        $driver = $this->getDriver();
        $driver->logger->error('job failed:' . $e->getMessage(), ['exception' => $e]);
        $driver->event->dispatch(make(Error::class, ['config' => $this->config, 'driver' => $driver, 'exception' => $e]));
    }

    abstract function handle();
}