<?php

namespace Vartruexuan\HyperfExcel\Strategy\Path;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;

class DateTimeStrategy implements StrategyInterface
{

    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string
    {
        return $config->getServiceName() . '_' . date('YmdHis') . '.' . $fileExt;
    }
}