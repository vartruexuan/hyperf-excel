<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;

class Error extends Event
{
    public function __construct(public BaseConfig $config, public Driver $driver, public \Throwable $exception)
    {
        parent::__construct($config, $driver);
    }
}