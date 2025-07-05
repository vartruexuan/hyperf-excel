<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Strategy\Path;

use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;

interface ExportPathStrategyInterface
{
    public function getPath(ExportConfig $config, string $fileExt = 'xlsx'): string;
}