<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Vartruexuan\HyperfExcel;

use Vartruexuan\HyperfExcel\Command\ExportCommand;
use Vartruexuan\HyperfExcel\Command\ImportCommand;
use Vartruexuan\HyperfExcel\Command\MessageCommand;
use Vartruexuan\HyperfExcel\Command\ProgressCommand;
use Vartruexuan\HyperfExcel\Db\ExcelLogInterface;
use Vartruexuan\HyperfExcel\Db\ExcelLogManager;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Listener\ProgressListener;
use Vartruexuan\HyperfExcel\Logger\ExcelLogger;
use Vartruexuan\HyperfExcel\Logger\ExcelLoggerInterface;
use Vartruexuan\HyperfExcel\Process\CleanFileProcess;
use Vartruexuan\HyperfExcel\Progress\Progress;
use Vartruexuan\HyperfExcel\Progress\ProgressInterface;
use Vartruexuan\HyperfExcel\Queue\AsyncQueue\ExcelQueue;
use Vartruexuan\HyperfExcel\Queue\ExcelQueueInterface;
use Vartruexuan\HyperfExcel\Strategy\Path\DateTimeExportPathStrategy;
use Vartruexuan\HyperfExcel\Strategy\Path\ExportPathStrategyInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DriverInterface::class => ExcelInvoker::class,
                ProgressInterface::class => Progress::class,
                ExcelLogInterface::class => ExcelLogManager::class,
                ExcelInterface::class => Excel::class,
                ExcelLoggerInterface::class => ExcelLogger::class,
                ExcelQueueInterface::class => ExcelQueue::class,
                ExportPathStrategyInterface::class => DateTimeExportPathStrategy::class
            ],
            'commands' => [
                ExportCommand::class,
                ImportCommand::class,
                ProgressCommand::class,
                MessageCommand::class
            ],
            'listeners' => [
                ProgressListener::class,
            ],
            'processes' => [
                CleanFileProcess::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for excel.',
                    'source' => __DIR__ . '/../publish/excel.php',
                    'destination' => BASE_PATH . '/config/autoload/excel.php',
                ],
            ],
        ];
    }
}
