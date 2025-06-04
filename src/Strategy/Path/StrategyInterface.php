<?php

namespace Vartruexuan\HyperfExcel\Strategy\Path;

use Vartruexuan\HyperfExcel\Data\Config\ExportConfig;

interface StrategyInterface
{
    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string;
}