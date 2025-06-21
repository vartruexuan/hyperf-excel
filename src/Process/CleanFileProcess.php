<?php

namespace Vartruexuan\HyperfExcel\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;

class CleanFileProcess extends AbstractProcess
{
    public string $name = 'HyperfExcel_CleanFileProcess';

    public array $configs = [];

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $config = $this->container->get(ConfigInterface::class);
        $this->configs = $config->get('excel.drivers', []);
    }

    public function handle(): void
    {
        foreach ($this->configs as $key => $item) {
            /**
             * @var Driver $driver
             */
            $driver = $this->container->get(DriverFactory::class)->get($key);
            // 清除临时文件
            $driver->cleanTempFile();
        }
    }
}