<?php

namespace Vartruexuan\HyperfExcel\Listener;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Event\AfterExportData;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeExportData;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Event\Event;
use Vartruexuan\HyperfExcel\Progress\Progress;
use Vartruexuan\HyperfExcel\Progress\ProgressData;

class ProgressListener extends BaseListener
{

    protected Progress $progress;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    function beforeExport(Event $event)
    {
        /**
         * @var BeforeExport $event
         */
        $event->driver->progress->initRecord($event->config);
    }

    function beforeExportExcel(object $event)
    {
        // TODO: Implement beforeExportExcel() method.
    }

    function beforeExportData(object $event)
    {
        /**
         * @var BeforeExportData $event
         */
        $event->driver->progress->setSheetProgress($event->config, $event->exportCallbackParam->sheet->name, new ProgressData([
            'total' => $event->exportCallbackParam->totalCount,
            'status'=> ProgressData::PROGRESS_STATUS_PROCESS,
        ]));
    }

    function beforeExportSheet(object $event)
    {
        // TODO: Implement beforeExportSheet() method.
    }

    function afterExport(object $event)
    {
        // TODO: Implement afterExport() method.
    }

    function afterExportData(object $event)
    {
        /**
         * @var AfterExportData $event
         */
        $success = count($event->data ?? []);
        $event->driver->progress->setSheetProgress($event->config, $event->exportCallbackParam->sheet->name, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_PROCESS,
            'success' => $success,
            'progress' => $success,
        ]));
    }

    function afterExportExcel(object $event)
    {

        // TODO: Implement afterExportExcel() method.
    }

    function afterExportSheet(object $event)
    {
        /**
         * @var AfterExportSheet $event
         */
        $event->driver->progress->setSheetProgress($event->config, $event->sheet->name, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_END,
        ]));
    }

    function afterImport(object $event)
    {
        // TODO: Implement afterImport() method.
    }

    function afterImportData(object $event)
    {
        // TODO: Implement afterImportData() method.
    }

    function afterImportExcel(object $event)
    {
        // TODO: Implement afterImportExcel() method.
    }

    function afterImportSheet(object $event)
    {
        // TODO: Implement afterImportSheet() method.
    }

    function beforeImport(object $event)
    {
        // TODO: Implement beforeImport() method.
    }

    function beforeImportExcel(object $event)
    {
        // TODO: Implement beforeImportExcel() method.
    }

    function beforeImportData(object $event)
    {
        // TODO: Implement beforeImportData() method.
    }

    function beforeImportSheet(object $event)
    {
        // TODO: Implement beforeImportSheet() method.
    }

    function error(object $event)
    {
        /**
         * @var Error  $event
         */
        $event->driver->progress->setProgress($event->config, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_FAIL,
        ]));
    }
}