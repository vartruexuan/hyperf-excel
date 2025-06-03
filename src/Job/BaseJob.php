<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Job;

use Psr\Container\ContainerInterface;
use Hyperf\AsyncQueue\Job;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;

class BaseJob extends Job
{
    /**
     * 驱动名
     *
     * @var string
     */
    public string $driverName = 'default';

    /**
     * 当前驱动
     *
     * @var DriverInterface
     */
    public DriverInterface $driver;

    public $config;

    public function __construct(ContainerInterface $container, $params)
    {
        $this->container = $container;
        $this->driver = $this->container->get(DriverFactory::class)->get($this->driverName);
    }

}