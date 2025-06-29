<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\ExcelInterface;

class ExportCommand extends AbstractCommand
{
    protected ContainerInterface $container;
    protected ExcelInterface $excel;

    public function __construct(ContainerInterface $container, ExcelInterface $excel)
    {
        $this->container = $container;
        $this->excel = $excel;
        parent::__construct('excel:export');
    }

    public function handle()
    {
        $driver = $this->input->getOption('driver');
        $config = $this->input->getArgument('config');
        $progress = $this->input->getOption('progress');

        if ($driver) {
            $this->excel->serDriverByName($driver);
        }

        /**
         * @var ExportConfig $config
         */
        $config = new $config([]);
        if (!$config instanceof ExportConfig) {
            $this->error('Invalid config: expected instance of ' . ExportConfig::class);
        }

        $data = $this->excel->export($config);

        $this->table(['token'], [[$data->token]]);

        if ($progress) {
            $this->showProgress($data->token);
        }
    }

    protected function configure()
    {
        $this->setDescription('Run export');
        $this->addArgument('config', InputArgument::REQUIRED, 'The config of export.');
        $this->addOption('driver', 'd', InputOption::VALUE_OPTIONAL, 'The driver of export.');
        $this->addOption('progress', 'g', InputOption::VALUE_NEGATABLE, 'The progress of export.', true);

        $this->addUsage('excel:export "App\Excel\DemoExportConfig"');
        $this->addUsage('excel:export "App\Excel\DemoExportConfig" --no-progress');
    }
}