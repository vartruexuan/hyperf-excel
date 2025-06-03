<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Event;

use Vartruexuan\HyperfExcel\Data\Config\BaseConfig;

class Error extends Event
{
    public \Throwable $exception;

    public function __construct(BaseConfig $config, \Throwable $exception)
    {
        parent::__construct($config);
    }
}