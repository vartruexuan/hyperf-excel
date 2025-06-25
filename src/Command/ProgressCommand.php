<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;

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
        $this->addArgument('driver', InputArgument::OPTIONAL, 'The driver of export.', 'xlswriter');
        $this->addArgument('config', InputArgument::OPTIONAL, 'The config of export.');
    }
}