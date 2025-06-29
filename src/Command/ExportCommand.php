<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;

class ExportCommand extends AbstractCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('excel:export');
    }

    public function handle()
    {
        $driver = $this->input->getOption('driver');
        $config = $this->input->getOption('config');
        $progress = $this->input->getOption('progress');

        $factory = $this->container->get(DriverFactory::class);
        /**
         * @var Driver
         */
        $driver = $factory->get($driver);
        if (!$driver instanceof Driver) {
            $this->error("Don't support driver " . $driver::class);
            return 0;
        }
        /**
         * @var ExportConfig $config
         */
        $config = new $config([]);
        if (!$config instanceof ExportConfig) {
            $this->error('Invalid config: expected instance of ' . ExportConfig::class);
        }

        $data = $driver->export($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress($driver, $data->token);
        }
    }

    protected function configure()
    {
        $this->setDescription('Run export');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of export.', 'xlswriter');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The config of export.');
        $this->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress of export.', true);

        $this->addUsage('excel:export --config "App\Excel\DemoExportConfig"');
        $this->addUsage('excel:export --config "App\Excel\DemoExportConfig" --no-progress');
    }
}