<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Listener;

/**
 * 监听输出日志
 */
class ExcelLogListener extends BaseListener
{
    public function beforeExport(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeExportExcel(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeExportData(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeExportSheet(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterExport(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterExportData(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterExportExcel(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterExportSheet(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterImport(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterImportData(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterImportExcel(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function afterImportSheet(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeImport(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeImportExcel(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeImportData(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function beforeImportSheet(object $event)
    {
        $this->getLogger($event)->info(sprintf('event:%s,token:%s', $this->getEventClass($event), $event->config->getToken()), ['config' => $event->config]);
    }

    public function error(object $event)
    {
        $this->getLogger($event)->error(sprintf('event:%s,token:%s,error:%s', $this->getEventClass($event), $event->config->getToken(), $event->exception->getMessage()), [
            'config' => $event->config,
            'exception' => $event->exception
        ]);
    }

}