<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Import;

use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\BaseObject;

/**
 * 导入数据
 */
class ImportData extends BaseObject
{

    public BaseConfig $config;

    /**
     * 页码返回数据
     *
     * @var
     */
    public array $sheetData =[];


    /**
     * 页码数据
     *
     * @param array $sheetData
     * @param string $sheetName
     * @return ImportData
     */
    public function addSheetData(array $sheetData, string $sheetName = 'sheet1')
    {
        $this->sheetData[strtolower($sheetName)] = $sheetData;
        return $this;
    }

    /**
     * 获取页数据
     *
     * @param string $sheetName
     * @return mixed
     */
    public function getSheetData(string $sheetName = 'sheet1')
    {
        return $this->getSheetData(strtolower($sheetName));
    }

}