<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Import;

use Vartruexuan\HyperfExcel\Data\BaseObject;
use Vartruexuan\HyperfExcel\Driver\Driver;

class ImportRowCallbackParam extends BaseObject
{
    public Driver  $driver;

    /**
     * 导入配置
     * 
     * @var ImportConfig 
     */
    public ImportConfig $config;

    /**
     * 页码信息
     * 
     * @var Sheet 
     */
    public Sheet $sheet;

    /**
     * 数据行
     *
     * @var array
     */
    public array $row;

    /**
     * 行下标
     *
     * @var int
     */
    public int $rowIndex;

}