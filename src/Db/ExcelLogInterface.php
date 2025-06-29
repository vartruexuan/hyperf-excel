<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Db;

use Vartruexuan\HyperfExcel\Data\BaseConfig;

interface ExcelLogInterface
{
    public function saveLog(BaseConfig $config, array $saveParam = []): int;

    public function getConfig(): array;
}