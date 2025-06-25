<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

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
        $this->addArgument('driver', InputArgument::OPTIONAL, 'The driver of export.', 'xlswriter');
        $this->addArgument('config', InputArgument::OPTIONAL, 'The config of export.');
    }
}