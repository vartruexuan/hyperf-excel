<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportCallbackParam;
use Vartruexuan\HyperfExcel\Driver\Driver;

class BeforeExportData extends Event
{

    public function __construct(public BaseConfig $config, public Driver $driver,public ExportCallbackParam $exportCallbackParam)
    {
        parent::__construct($config, $driver);
    }
}