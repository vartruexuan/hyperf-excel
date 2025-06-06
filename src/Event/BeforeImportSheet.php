<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Import\Sheet;
use Vartruexuan\HyperfExcel\Driver\Driver;

class BeforeImportSheet extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public Sheet $sheet)
    {
        parent::__construct($config, $driver);
    }

}