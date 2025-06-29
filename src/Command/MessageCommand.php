<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\Progress\ProgressData;
use function Hyperf\Support\msleep;

class MessageCommand extends AbstractCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('excel:message');
    }

    public function handle()
    {
        $driver = $this->input->getOption('driver');
        $token = $this->input->getArgument('token');
        $progress= $this->input->getOption('progress');
        $num = $this->input->getOption('num');

        $factory = $this->container->get(DriverFactory::class);
        /**
         * @var Driver
         */
        $driver = $factory->get($driver);
        if (!$driver instanceof Driver) {
            $this->error("Don't support driver " . $driver::class);
            return 0;
        }

        $this->line("开始获取信息:");
        do {
            $progressRecord = $driver->progress->getRecordByToken($token);
            if (!$progressRecord) {
                $this->error('未找到进度记录');
                return;
            }
            $messages = $driver->progress->popMessage($token, $num);
            foreach ($messages as $message) {
                $this->line($message);
            }
            $isEnd = in_array($progressRecord->progress->status, [
                    ProgressData::PROGRESS_STATUS_END,
                    ProgressData::PROGRESS_STATUS_FAIL,
                ]) && empty($messages);

            msleep(500);
        } while (!$isEnd);

        if ($progressRecord) {
            $this->showProgress($driver, $token);
        }
    }


    protected function configure()
    {
        $this->setDescription('View progress messages');
        $this->addArgument('token', InputArgument::REQUIRED, 'The token of excel.');
        $this->addOption('driver', 'd', InputOption::VALUE_REQUIRED, 'The driver of excel.', 'xlswriter');
        $this->addOption('num', 'c', InputOption::VALUE_REQUIRED, 'The message num of excel.', 50);

        $this->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101');
        $this->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101 --no-progress');
    }
}