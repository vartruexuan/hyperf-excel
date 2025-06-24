<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportData;

interface DriverInterface
{
    public function export(ExportConfig $config): ExportData;

    public function import(ImportConfig $config): ImportData;

    public function getConfig(): array;

    public function getTempDir(): string;

    public function getTempFileName():string;

}