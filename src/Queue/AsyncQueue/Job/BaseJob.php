<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Queue\AsyncQueue\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\ExcelInterface;

abstract class BaseJob extends Job
{
    public BaseConfig $config;
    protected int $maxAttempts = 0;

    public function __construct(BaseConfig $config)
    {
        $this->config = $config;
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    protected function getDriver(): ExcelInterface
    {
        /**
         * @var ExcelInterface $excel
         */
        $excel = $this->getContainer()->get(ExcelInterface::class);
        $driver = $this->config->getDriver();
        if (!empty($driver)) {
            $excel->setDriverByName($driver);
        }
        return $excel;
    }

    public function fail(\Throwable $e): void
    {
        $driver = $this->getDriver();
        //$driver->logger->error('job failed:' . $e->getMessage(), ['exception' => $e]);
        $driver->event->dispatch(new Error($this->config, $driver, $e));
    }

    abstract function handle();
}