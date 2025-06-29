<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\ExcelInterface;

class ImportCommand extends AbstractCommand
{
    protected ContainerInterface $container;
    protected ExcelInterface $excel;

    public function __construct(ContainerInterface $container, ExcelInterface $excel)
    {
        $this->container = $container;
        $this->excel = $excel;
        parent::__construct('excel:import');
    }

    public function handle()
    {
        $config = $this->input->getArgument('config');
        $driver = $this->input->getOption('driver');
        $path = $this->input->getArgument('path');
        $progress = $this->input->getOption('progress');

        $factory = $this->container->get(DriverFactory::class);
        /**
         * @var Driver
         */
        if ($driver) {
            $this->excel->serDriverByName($driver);
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
        $data = $this->excel->import($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress( $data->token);
        }
    }

    protected function configure()
    {
        $this->setDescription('Run import');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of import.', 'xlswriter');

        $this->addArgument('config', InputArgument::REQUIRED, 'The config of import.');
        $this->addArgument('path', InputArgument::REQUIRED, 'The file path of import.');
        $this->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress path of import.', true);

        $this->addUsage('excel:import "App\Excel\DemoImportConfig" "https://xxx.com/demo.xlsx"');
        $this->addUsage('excel:import  "App\Excel\DemoImportConfig" "/excel/demo.xlsx"');
        $this->addUsage('excel:import "App\Excel\DemoImportConfig"  "/excel/demo.xlsx" --no-progress');
    }
}