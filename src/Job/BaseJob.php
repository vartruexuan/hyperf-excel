<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Job;

use Psr\Container\ContainerInterface;
use Hyperf\AsyncQueue\Job;
use Vartruexuan\HyperfExcel\Data\Config\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;

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

    abstract function handle();

    public function fail(\Throwable $e): void
    {
        $this->getDriver()->logger->error('job failed:' . $e->getMessage(), ['exception' => $e]);
    }
}