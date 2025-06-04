<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Export;

use Vartruexuan\HyperfExcel\Data\BaseObject;
use Vartruexuan\HyperfExcel\Driver\Driver;

class ExportCallbackParam extends BaseObject
{
    public Driver $driver;

    public ExportConfig $exportConfig;

    public Sheet $sheet;

    /**
     * 当前分页
     *
     * @var int
     */
    public int $page = 1;

    /**
     * 当前分页限制数量
     *
     * @var int
     */
    public int $pageSize = 10;

    /**
     * 数据总数
     *
     * @var int
     */
    public int $totalCount = 0;

}