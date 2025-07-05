<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Strategy\Path;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;

class DateTimeExportPathStrategy implements ExportPathStrategyInterface
{

    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string
    {
        return $config->getServiceName() . '_' . date('YmdHis') . '.' . $fileExt;
    }
}