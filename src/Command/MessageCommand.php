<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Vartruexuan\HyperfExcel\ExcelInterface;
use Vartruexuan\HyperfExcel\Progress\ProgressData;
use Vartruexuan\HyperfExcel\Progress\ProgressInterface;
use function Hyperf\Support\msleep;

class MessageCommand extends AbstractCommand
{
    protected ContainerInterface $container;
    protected ExcelInterface $excel;
    protected ProgressInterface $progress;

    public function __construct(ContainerInterface $container, ExcelInterface $excel, ProgressInterface $progress)
    {
        $this->container = $container;
        $this->progress = $progress;
        parent::__construct('excel:message');
    }

    public function handle()
    {
        $token = $this->input->getArgument('token');
        $num = $this->input->getOption('num');

        $this->line("开始获取信息:");
        do {
            $progressRecord = $this->progress->getRecordByToken($token);
            if (!$progressRecord) {
                $this->error('未找到进度记录');
                return;
            }
            $messages = $this->progress->popMessage($token, $num);
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
            $this->showProgress($token);
        }
    }


    protected function configure()
    {
        $this->setDescription('View progress messages');
        $this->addArgument('token', InputArgument::REQUIRED, 'The token of excel.');
        $this->addOption('num', 'c', InputOption::VALUE_REQUIRED, 'The message num of excel.', 50);

        $this->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101');
        $this->addUsage('excel:message 168d8baf7fbc435c8ef18239e932b101 --no-progress');
    }
}