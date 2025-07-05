<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Strategy\Token;

interface TokenStrategyInterface
{
    public function getToken(): string;
}