<?php

namespace Vartruexuan\HyperfExcel\Listener;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\Event;
use Vartruexuan\HyperfExcel\Progress\Progress;

class ProgressListener extends BaseListener
{

    protected Progress $progress;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    function beforeExport(Event $event)
    {
       // 初始化进度信息
        $event->driver->progress->initRecord($event->config);
    }

    function beforeExportExcel(object $event)
    {
        // TODO: Implement beforeExportExcel() method.
    }

    function beforeExportData(object $event)
    {
        // TODO: Implement beforeExportData() method.
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
        // TODO: Implement afterExportData() method.
    }

    function afterExportExcel(object $event)
    {
        // TODO: Implement afterExportExcel() method.
    }

    function afterExportSheet(object $event)
    {
        // TODO: Implement afterExportSheet() method.
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
        // TODO: Implement error() method.
    }
}