<?php

namespace Vartruexuan\HyperfExcel\Progress;


use Vartruexuan\HyperfExcel\Data\BaseObject;

/**
 * 进度信息
 */
class ProgressRecord extends BaseObject
{

    public $token;

    /**
     * 页码信息
     *
     * @var array|null
     */
    public ?array $sheetList = [];

    /**
     * 页码进度信息
     *
     * @var ProgressData[]|null
     */
    public ?array $sheetListProgress = [];

    /**
     * 进度信息
     *
     * @var ProgressData|null
     */
    public ?ProgressData $progress = null;

    /**
     * 数据
     *
     * @var
     */
    public $data;


    public function __serialize(): array
    {
        return [


        ];
    }
}