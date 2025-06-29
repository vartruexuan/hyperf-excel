<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;

class ProgressCommand extends AbstractCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('excel:progress');
    }

    public function handle()
    {
        $driver = $this->input->getOption('driver');
        $token = $this->input->getArgument('token');

        $factory = $this->container->get(DriverFactory::class);
        /**
         * @var Driver
         */
        $driver = $factory->get($driver);
        if (!$driver instanceof Driver) {
            $this->error("Don't support driver " . $driver::class);
            return 0;
        }

        // 显示进度条
        $this->showProgress($driver, $token);
    }

    protected function configure()
    {
        $this->setDescription('View progress information');
        $this->addArgument('token', InputArgument::REQUIRED, 'The token of excel.');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of excel.', 'xlswriter');

        $this->addUsage('excel:progress 168d8baf7fbc435c8ef18239e932b101');

    }
}