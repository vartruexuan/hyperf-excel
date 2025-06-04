<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\Config\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;

class Error extends Event
{
    public \Throwable $exception;

    public function __construct(BaseConfig $config, Driver $driver, \Throwable $exception)
    {
        parent::__construct($config, $driver);
        $this->exception = $exception;
    }
}