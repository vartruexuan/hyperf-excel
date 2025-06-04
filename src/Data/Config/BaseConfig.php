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


    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): string
    {
        return  $this->token;
    }

    public function setAsync(bool $isAsync): static
    {
        $this->isAsync = $isAsync;
        return $this;
    }
    public function getIsAsync(): bool
    {
        return  $this->isAsync;
    }
}