<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
        $this->line("progress");
    }

    protected function configure()
    {
        $this->setDescription('Run progress');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of import.', 'xlswriter');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The config of import.');
    }
}