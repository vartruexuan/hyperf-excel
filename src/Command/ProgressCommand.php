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
        $token = $this->input->getArgument('token');
        // 显示进度条
        $this->showProgress($token);
    }

    protected function configure()
    {
        $this->setDescription('View progress information');
        $this->addArgument('token', InputArgument::REQUIRED, 'The token of excel.');

        $this->addUsage('excel:progress 168d8baf7fbc435c8ef18239e932b101');
    }
}