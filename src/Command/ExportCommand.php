<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

class ExportCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('excel:export');
    }

    public function handle()
    {
        $this->line("export");
    }
}