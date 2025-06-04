<?php

namespace Vartruexuan\HyperfExcel\Strategy\Path;

use Vartruexuan\HyperfExcel\Data\Config\ExportConfig;

class DateTimeStrategy implements StrategyInterface
{

    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string
    {
        return $config->getServiceName() . '_' . date('YmdHis') . '.' . $fileExt;
    }
}