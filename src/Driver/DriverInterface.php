<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;

interface DriverInterface
{
    public function export(ExportConfig $config);
    public function import(ImportConfig$config);

}