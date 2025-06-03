<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

interface DriverInterface
{
    public function export($config);
    public function import($config);

}