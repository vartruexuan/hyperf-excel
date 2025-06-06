<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Driver\Driver;

class AfterExport extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public ExportData $data)
    {
    }
}