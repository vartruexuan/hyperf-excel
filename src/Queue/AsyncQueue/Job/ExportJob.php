<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Queue\AsyncQueue\Job;

class ExportJob extends BaseJob
{

    public function handle()
    {
        $this->getDriver()->export($this->config->setIsAsync(false));
    }

}