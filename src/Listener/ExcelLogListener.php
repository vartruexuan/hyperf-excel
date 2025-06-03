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

/**
 * 监听输出日志
 */
class ExcelLogListener extends BaseListener
{

    public function afterExport(object $event)
    {

    }

    public function afterExportData(object $event)
    {

    }

    public function afterExportExcel(object $event)
    {

    }

    public function afterExportSheet(object $event)
    {

    }


    public function beforeExport(object $event)
    {

    }

    public function beforeExportExcel(object $event)
    {

    }


    public function beforeExportData(object $event)
    {

    }

    public function beforeExportSheet(object $event)
    {

    }

    public function afterImport(object $event)
    {

    }

    public function afterImportData(object $event)
    {

    }

    public function afterImportExcel(object $event)
    {

    }

    public function afterImportSheet(object $event)
    {

    }


    public function beforeImport(object $event)
    {

    }

    public function beforeImportExcel(object $event)
    {

    }


    public function beforeImportData(object $event)
    {

    }

    public function beforeImportSheet(object $event)
    {

    }

    public function error(object $event)
    {

    }

}