<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Vartruexuan\HyperfExcel\Data\Config\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Config\ImportConfig;

interface DriverInterface
{
    public function export(ExportConfig $config);
    public function import(ImportConfig$config);

}