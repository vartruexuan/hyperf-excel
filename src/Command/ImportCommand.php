<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;

class ImportCommand extends AbstractCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('excel:import');
    }

    public function handle()
    {
        $driver = $this->input->getOption('driver');
        $config = $this->input->getOption('config');
        $path = $this->input->getOption('path');
        $progress= $this->input->getOption('progress');

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
         * @var ImportConfig $config
         */
        $config = new $config([]);
        if (!$config instanceof ImportConfig) {
            $this->error('Invalid config: expected instance of ' . ImportConfig::class);
        }
        if ($path) {
            $config->setPath($path);
        }
        $data = $driver->import($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress($driver, $data->token);
        }
    }

    protected function configure()
    {
        $this->setDescription('Run import');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of import.', 'xlswriter');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The config of import.');
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'The file path of import.');
        $this->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress path of import.', true);

        $this->addUsage('excel:import --config "App\Excel\DemoImportConfig" --path="https://xxx.com/demo.xlsx"');
        $this->addUsage('excel:import --config "App\Excel\DemoImportConfig" --path="/excel/demo.xlsx"');
        $this->addUsage('excel:import --config "App\Excel\DemoImportConfig" --path="/excel/demo.xlsx" --no-progress');
    }
}