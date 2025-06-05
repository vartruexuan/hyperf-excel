<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Import;

use Vartruexuan\HyperfExcel\Data\BaseObject;

class Sheet extends BaseObject
{

    /**
     * 页名
     *
     * @var string
     */
    public string $name = 'sheet1';

    // 读取sheet下标/名称
    public const SHEET_READ_TYPE_NAME = 'name';
    public const SHEET_READ_TYPE_INDEX = 'index';

    /**
     * 页下标
     *
     * @var int
     */
    public int $index = 0;

    /**
     * 读取类型
     *
     * @var string
     */
    public string $readType = self::SHEET_READ_TYPE_NAME;

    /**
     * 是否设置列头
     *
     * @var bool
     */
    public bool $isSetHeader = false;

    /**
     * 列头数据行下标（从1开始）
     *
     * @var int
     */
    public int $headerIndex = 1;

    /**
     * 列头字段映射信息
     *  [
     *      '标题' => 'title',
     *      // ...
     * ]
     *
     * @var array
     */
    public array $headerMap = [];


    /**
     * 是否全量返回整页数据
     *
     * @var bool
     */
    public bool $isReturnSheetData = false;

    /**
     * 跳过空白行
     *
     * @var bool
     */
    public bool $skipEmptyRow = true;

    /**
     * 跳过指定行
     *
     * @var int
     */
    public bool  $skipRowIndex = false;


    /**
     * 数据类型(列下标=>类型)
     *
     * @var array
     */
    public array $columnTypes = [];


    /**
     * 游标读取回调
     * `
     *    function($row){
     *          // 执行业务代码
     *    }
     * `
     * @var
     */
    public  $callback;


    /**
     * 获取name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * 获取最终列头信息
     *
     * @param array $cols
     * @return array
     */
    public function getHeader(array $cols)
    {
        return array_map(function ($n) {
            return $this->headerMap[$n] ?? $n;
        }, $cols);
    }

    /**
     * 格式化数据
     *
     * @param $sheetData
     * @param $header
     * @return array|array[]
     */
    public function formatSheetDataByHeader($sheetData, $header)
    {
        return array_map(function ($n) use ($header) {
            return $this->formatRowByHeader($n, $header);
        }, $sheetData);
    }

    /**
     * 格式化行数据
     *
     * @param $row
     * @param $header
     * @return array
     */
    public function formatRowByHeader($row, $header)
    {
        return array_combine($header, $row);
    }
}