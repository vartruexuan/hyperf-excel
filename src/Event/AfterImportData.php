<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportRowCallbackParam;
use Vartruexuan\HyperfExcel\Driver\Driver;

class AfterImportData extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public ImportRowCallbackParam $importCallbackParam)
    {
        parent::__construct($config, $driver);
    }
}