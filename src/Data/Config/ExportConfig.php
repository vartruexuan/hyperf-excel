<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Config;

class ExportConfig extends BaseConfig
{
    public const OUT_PUT_TYPE_UPLOAD = 'upload'; // 上传第三方
    public const OUT_PUT_TYPE_LOCAL = 'local'; // 保存到本地
    public const OUT_PUT_TYPE_OUT = 'out'; // 直接输出

    /**
     * 输出类型
     *
     * @var string
     */
    public string $outPutType = self::OUT_PUT_TYPE_OUT;

    public string $path='';
    public array $params = [];


    public function getOutPutType():string
    {
        return $this->outPutType;
    }
    public function getPath():string
    {
        return $this->path;
    }

    public function getParams():array
    {
        return $this->params;
    }

    public function __serialize(): array
    {
        return [
            'token' => $this->getToken(),
            'path' => $this->getPath(),
            'isAsync' => $this->getIsAsync(),
            'outPutType' => $this->getOutPutType(),
            'params' => $this->getParams(),
        ];
    }
}