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
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterExportData;
use Vartruexuan\HyperfExcel\Event\AfterExportExcel;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\AfterImport;
use Vartruexuan\HyperfExcel\Event\AfterImportData;
use Vartruexuan\HyperfExcel\Event\AfterImportExcel;
use Vartruexuan\HyperfExcel\Event\AfterImportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeExportData;
use Vartruexuan\HyperfExcel\Event\BeforeExportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeExportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeImport;
use Vartruexuan\HyperfExcel\Event\BeforeImportData;
use Vartruexuan\HyperfExcel\Event\BeforeImportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeImportSheet;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Event\Event;

/**
 * 监听输出日志
 */
abstract class BaseListener implements ListenerInterface
{
    protected LoggerInterface $logger;
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            // 导出
            BeforeExport::class,
            BeforeExportExcel::class,
            BeforeExportData::class,
            BeforeExportSheet::class,

            AfterExport::class,
            AfterExportData::class,
            AfterExportExcel::class,
            AfterExportSheet::class,

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

    protected function getEventClass(Event $event)
    {
        return lcfirst(basename(str_replace('\\', '/', get_class($event))));

    }

    public function process(object $event): void
    {
        $className = $this->getEventClass($event);
        $this->logger = $event->driver->logger;
        $this->{$className}($event);
    }


    abstract function beforeExport(Event $event);

    abstract function beforeExportExcel(Event $event);

    abstract function beforeExportData(Event $event);

    abstract function beforeExportSheet(Event $event);

    abstract function afterExport(Event $event);

    abstract function afterExportData(Event $event);

    abstract function afterExportExcel(Event $event);

    abstract function afterExportSheet(Event $event);

    abstract function beforeImport(Event $event);

    abstract function beforeImportExcel(Event $event);

    abstract function beforeImportData(Event $event);

    abstract function beforeImportSheet(Event $event);

    abstract function afterImport(Event $event);

    abstract function afterImportData(Event $event);

    abstract function afterImportExcel(Event $event);

    abstract function afterImportSheet(Event $event);

    abstract function error(Event $event);
}