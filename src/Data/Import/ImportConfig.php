<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Import;

use Vartruexuan\HyperfExcel\Data\BaseConfig;

class ImportConfig extends BaseConfig
{

    /**
     * 导入地址
     *
     * @var string
     */
    public string $path = '';


    /**
     * 读取页
     * @var Sheet[]
     */
    public array $sheets = [];

    /**
     * 临时文件地址
     *
     * @var string
     */
    private string $tempPath = '';


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
     * 获取地址
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * 是否异步
     *
     * @return bool
     */
    public function getIsAsync(): bool
    {
        return $this->isAsync;
    }

    /**
     * 设置页码信息
     *
     * @param array $sheets
     * @return $this
     */
    public function setSheets(array $sheets)
    {
        $this->sheets = $sheets;
        return $this;
    }


    /**
     * 添加读取页
     *
     * @param Sheet $sheet
     * @return ImportConfig
     */
    public function addSheet(Sheet $sheet)
    {
        $this->sheets[] = $sheet;
        return $this;
    }

    /**
     * 设置导入地址
     *
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }


    /**
     * 设置临时文件地址
     *
     * @param string $tempPath
     * @return $this
     */
    final public function setTempPath(string $tempPath)
    {
        $this->tempPath = $tempPath;
        return $this;
    }

    /**
     * 获取临时文件地址
     *
     * @return string
     */
    final public function getTempPath(): string
    {
        return $this->tempPath;
    }


    /**
     * 序列化
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'path' => $this->getPath(),
            'isAsync' => $this->getIsAsync(),
            'token' => $this->getToken(),
        ];
    }

}