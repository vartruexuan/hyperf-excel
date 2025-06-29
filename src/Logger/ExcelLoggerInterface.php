<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Logger;

use Psr\Log\LoggerInterface;

interface ExcelLoggerInterface
{
    public function getLogger(): LoggerInterface;
}