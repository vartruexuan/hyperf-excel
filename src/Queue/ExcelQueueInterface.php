<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Queue;

use Vartruexuan\HyperfExcel\Data\BaseConfig;

interface ExcelQueueInterface
{
    public function push(BaseConfig $config);
}