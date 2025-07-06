<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportData;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Progress\ProgressRecord;

interface ExcelInterface
{
    public function export(ExportConfig $config): ExportData;

    public function import(ImportConfig $config): ImportData;

    public function getProgressRecord(string $token): ?ProgressRecord;

    public function popMessage(string $token, int $num = 50): array;

    public function popMessageAndIsEnd(string $token, int $num = 50, bool &$isEnd = true): array;

    public function pushMessage(string $token, string $message);

    public function getDefaultDriver(): DriverInterface;

    public function getDriverByName(string $driverName): DriverInterface;

    public function getDriver(?string $driverName = null): DriverInterface;

    public function getConfig(): array;

}