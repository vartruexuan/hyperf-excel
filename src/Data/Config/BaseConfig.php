<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Config;

abstract class BaseConfig
{
    /**
     * 服务名
     *
     * @var string
     */
    public string $serviceName = 'default';

    /**
     * 是否异步
     *
     * @var bool
     */
    public bool $isAsync = false;

    /**
     * token
     *
     * @var string
     */
    public string $token = '';
    
}