<?php

namespace Vartruexuan\HyperfExcel\Listener;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterExportData;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\AfterImportData;
use Vartruexuan\HyperfExcel\Event\AfterImportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeExportData;
use Vartruexuan\HyperfExcel\Event\BeforeExportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeImport;
use Vartruexuan\HyperfExcel\Event\BeforeImportSheet;
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
            'status' => ProgressData::PROGRESS_STATUS_PROCESS,
        ]));
    }

    function beforeExportSheet(object $event)
    {
        /**
         * @var BeforeExportSheet $event
         */
        $event->driver->progress->setSheetProgress($event->config, $event->sheet->name, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_PROCESS,
        ]));
    }

    function afterExport(object $event)
    {
        /**
         * @var AfterExport $event
         */
        $record = $event->driver->progress->getRecord($event->config);

        $status = !in_array($record->progress->status, [ProgressData::PROGRESS_STATUS_END, ProgressData::PROGRESS_STATUS_FAIL]) ? ProgressData::PROGRESS_STATUS_END : $record->progress->status;
        $data = $event->data ?: $record->data;
        $event->driver->progress->setProgress($event->config, new ProgressData([
            'status' => $status,
        ]), $data);
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


    function beforeImport(object $event)
    {
        /**
         * @var BeforeImport $event
         */
        $event->driver->progress->initRecord($event->config);
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
        /**
         * @var BeforeImportSheet $event
         */
        $event->driver->progress->setSheetProgress($event->config, $event->sheet->name, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_PROCESS,
        ]));
    }

    function afterImport(object $event)
    {
        // TODO: Implement afterImport() method.
    }

    function afterImportData(object $event)
    {
        /**
         * @var AfterImportData $event
         */
        $event->driver->progress->setSheetProgress($event->config, $event->importCallbackParam->sheet->name, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_PROCESS,
            'progress' => 1,
            'success' => $event->exception ? 0 : 1,
            'fail' => $event->exception ? 1 : 0,
        ]));
        if ($event->exception) {
            $event->driver->progress->pushMessage($event->config->getToken(), $event->exception->getMessage());
        }
    }

    function afterImportExcel(object $event)
    {
        // TODO: Implement afterImportExcel() method.
    }

    function afterImportSheet(object $event)
    {
        /**
         * @var AfterImportSheet $event
         */
        $event->driver->progress->setSheetProgress($event->config, $event->sheet->name, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_END,
        ]));
    }


    function error(object $event)
    {
        /**
         * @var Error $event
         */
        $event->driver->progress->setProgress($event->config, new ProgressData([
            'status' => ProgressData::PROGRESS_STATUS_FAIL,
        ]));
        $event->driver->progress->pushMessage($event->config->getToken(), $event->exception->getMessage());
    }
}