<?php

namespace Vartruexuan\HyperfExcel;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;

class ExcelInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $name = $config->get('excel.default', 'default');
        $factory = $container->get(DriverFactory::class);
        return $factory->get($name);
    }
}