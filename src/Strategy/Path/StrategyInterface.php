<?php

namespace Vartruexuan\HyperfExcel\Strategy\Path;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;

interface StrategyInterface
{
    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string;
}