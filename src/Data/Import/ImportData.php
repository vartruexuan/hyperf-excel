<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Import;

use Vartruexuan\HyperfExcel\Data\BaseObject;

/**
 * 导入数据
 */
class ImportData extends BaseObject
{
    public string $token = '';

    /**
     * 页码数据
     *
     * @var array
     */
    public array $sheetData = [];
}