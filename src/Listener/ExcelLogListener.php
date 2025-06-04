<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Listener;

use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;


/**
 * 监听输出日志
 */
class ExcelLogListener extends BaseListener
{

    public function beforeExport(object $event)
    {
        $this->logger->info(sprintf('Export started,token:%s', $event->config->getToken()), ['config' => $event->config]);
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

    public function afterExport(object $event)
    {
        $this->logger->info(sprintf('Export completed,token:%s', $event->config->getToken()), ['config' => $event->config]);
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
        $this->logger->error( sprintf(
            'config:%s,token:%s, error:%s',
            get_class($event->config),
            $event->config->getToken(),
            $event->exception->getMessage() .  $event->exception->getTraceAsString(),
        ));
    }

}