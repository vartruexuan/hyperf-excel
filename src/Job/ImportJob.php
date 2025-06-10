<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Job;
class ImportJob extends BaseJob
{
    public function handle()
    {
        $this->getDriver()->import($this->config->setIsAsync(false));
    }
}