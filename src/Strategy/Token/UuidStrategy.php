<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Strategy\Token;

use Vartruexuan\HyperfExcel\Helper\Helper;

class UuidStrategy implements TokenStrategyInterface
{

    public function getToken():string
    {
        return Helper::uuid4();
    }
}