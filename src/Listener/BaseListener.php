<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Listener;

use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterExportData;
use Vartruexuan\HyperfExcel\Event\AfterExportExcel;
use Vartruexuan\HyperfExcel\Event\AfterExportOutput;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\AfterImport;
use Vartruexuan\HyperfExcel\Event\AfterImportData;
use Vartruexuan\HyperfExcel\Event\AfterImportExcel;
use Vartruexuan\HyperfExcel\Event\AfterImportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeExportData;
use Vartruexuan\HyperfExcel\Event\BeforeExportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeExportOutput;
use Vartruexuan\HyperfExcel\Event\BeforeExportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeImport;
use Vartruexuan\HyperfExcel\Event\BeforeImportData;
use Vartruexuan\HyperfExcel\Event\BeforeImportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeImportSheet;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Logger\ExcelLoggerInterface;

/**
 * 监听输出日志
 */
abstract class BaseListener implements ListenerInterface
{
    protected ContainerInterface $container;
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container, ExcelLoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger->getLogger();
    }

    public function listen(): array
    {
        return [
            // 导出
            BeforeExport::class,
            BeforeExportExcel::class,
            BeforeExportData::class,
            BeforeExportSheet::class,
            BeforeExportOutput::class,

            AfterExport::class,
            AfterExportData::class,
            AfterExportExcel::class,
            AfterExportSheet::class,
            AfterExportOutput::class,

            // 导入
            BeforeImport::class,
            BeforeImportExcel::class,
            BeforeImportData::class,
            BeforeImportSheet::class,

            AfterImport::class,
            AfterImportData::class,
            AfterImportExcel::class,
            AfterImportSheet::class,

            // error
            Error::class,
        ];
    }

    protected function getEventClass(object $event)
    {
        return lcfirst(basename(str_replace('\\', '/', get_class($event))));
    }

    protected function getLogger(object $event): LoggerInterface
    {
        return $event->driver->logger;
    }

    public function process(object $event): void
    {
        $className = $this->getEventClass($event);
        $this->{$className}($event);
    }

    abstract function beforeExport(object $event);

    abstract function beforeExportExcel(object $event);

    abstract function beforeExportData(object $event);

    abstract function beforeExportSheet(object $event);

    abstract function beforeExportOutput(object $event);

    abstract function afterExport(object $event);

    abstract function afterExportData(object $event);

    abstract function afterExportExcel(object $event);

    abstract function afterExportSheet(object $event);

    abstract function afterExportOutput(object $event);

    abstract function beforeImport(object $event);

    abstract function beforeImportExcel(object $event);

    abstract function beforeImportData(object $event);

    abstract function beforeImportSheet(object $event);

    abstract function afterImport(object $event);

    abstract function afterImportData(object $event);

    abstract function afterImportExcel(object $event);

    abstract function afterImportSheet(object $event);

    abstract function error(object $event);
}