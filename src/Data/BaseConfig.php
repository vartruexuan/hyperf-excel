<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data;

abstract class BaseConfig extends BaseObject
{
    /**
     * 服务名
     *
     * @var string
     */
    public string $serviceName = 'default';

    /**
     * 驱动(未指定默认驱动)
     * @var string
     */
    public string $driver = '';

    /**
     * 是否异步
     *
     * @var bool
     */
    public bool $isAsync = false;

    /**
     * 是否设置进度
     *
     * @var bool
     */
    public bool $isProgress = true;

    /**
     * 是否设置dbLog
     *
     * @var bool
     */
    public bool $isDbLog = true;

    /**
     * 页码配置
     *
     * @var array
     */
    public array $sheets = [];

    /**
     * token
     *
     * @var string
     */
    public string $token = '';

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setIsAsync(bool $isAsync): static
    {
        $this->isAsync = $isAsync;
        return $this;
    }

    public function getIsAsync(): bool
    {
        return $this->isAsync;
    }

    public function getIsProgress(): bool
    {
        return $this->isProgress;
    }

    public function getIsDbLog(): bool
    {
        return $this->isDbLog;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * 获取页配置
     *
     * @return array
     */
    public function getSheets(): array
    {
        return $this->sheets;
    }

    /**
     * 设置页
     *
     * @param $sheets
     * @return $this
     */
    public function setSheets($sheets): static
    {
        $this->sheets = $sheets;
        return $this;
    }
}