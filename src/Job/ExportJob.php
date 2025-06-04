<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Job;

class ExportJob extends BaseJob
{

    public function handle()
    {
        $this->getDriver()->logger->info('测试队列是否执行');
        throw  new \Exception('测试下异常');
        $this->config->setAsync(false);
        $this->getDriver()->export($this->config);
    }

}