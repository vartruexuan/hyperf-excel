<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $this->output->writeln('export');
    }

    protected function configure()
    {
        $this->setDescription('Run export');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of import.', 'xlswriter');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The config of import.');
    }
}