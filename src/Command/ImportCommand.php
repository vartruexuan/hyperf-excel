<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;

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
        $this->line("import");
    }


    protected function configure()
    {
        $this->setDescription('Run import');
        $this->addArgument('driver', InputArgument::OPTIONAL, 'The driver of export.', 'xlswriter');
        $this->addArgument('config', InputArgument::OPTIONAL, 'The config of export.');
    }
}