<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportCallbackParam;
use Vartruexuan\HyperfExcel\Driver\Driver;

class AfterExportData extends Event
{
    public function __construct(BaseConfig $config, Driver $driver,ExportCallbackParam $exportCallbackParam)
    {
        parent::__construct($config, $driver);
    }

}