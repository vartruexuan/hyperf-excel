<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\Export\Column;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\SheetStyle;
use Vartruexuan\HyperfExcel\Data\Export\Style;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Data\Export\Sheet as ExportSheet;
use Vartruexuan\HyperfExcel\Data\Import\Sheet as ImportSheet;
use Vartruexuan\HyperfExcel\Event\AfterExportExcel;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\AfterImportExcel;
use Vartruexuan\HyperfExcel\Event\AfterImportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeExportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeImportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeImportSheet;
use Vartruexuan\HyperfExcel\Exception\ExcelException;
use Vartruexuan\HyperfExcel\Helper\Helper;
use Vtiful\Kernel\Excel;

class XlsWriterDriver extends Driver
{
    public function __construct(protected ContainerInterface $container, protected array $config, protected string $name = 'xlswriter')
    {
        parent::__construct($container, $config, $name);
    }

    /**
     * export
     *
     * @param ExportConfig $config
     * @param string $filePath
     * @return string
     */
    public function exportExcel(ExportConfig $config,string $filePath): string
    {
        $excel = new Excel([
            'path' => dirname($filePath),
        ]);

        $this->event->dispatch(new BeforeExportExcel($config, $this));

        foreach (array_values($config->getSheets()) as $index => $sheet) {
            $this->exportSheet($excel, $sheet, $config, $index,$filePath);
        }

        $excel->output();
        $excel->close();
        $this->event->dispatch(new AfterExportExcel($config, $this));

        return $filePath;
    }

    /**
     * import
     *
     * @param ImportConfig $config
     * @return array|null
     * @throws ExcelException
     */
    public function importExcel(ImportConfig $config): array|null
    {
        $excel = new Excel([
            'path' => $this->getTempDir(),
        ]);

        $filePath = $config->getTempPath();
        $fileName = basename($filePath);

        // 校验文件
        $this->checkFile($filePath);

        /**
         * @var ImportSheet[] $sheets
         */
        $sheets = $config->getSheets();
        $excel->openFile($fileName);

        $sheetList = $excel->sheetList();

        $this->event->dispatch(new BeforeImportExcel($config, $this));

        $sheetData = [];


        $sheets = array_map(function ($sheet) use ($sheetList) {
            if ($sheet->readType == ImportSheet::SHEET_READ_TYPE_INDEX) {
                $sheetName = $sheetList[$sheet->index];
                $sheet->name = $sheetName;
            }
            // 页码不存在
            if (!in_array($sheet->name, $sheetList)) {
                throw new ExcelException("sheet {$sheet->name} not exist");
            }
            return $sheet;
        }, $sheets);

        foreach ($sheets as $sheet) {
            $sheetData[$sheet->name] = $this->importSheet($excel, $sheet, $config);
        }

        $excel->close();

        $this->event->dispatch(new AfterImportExcel($config, $this));

        return $sheetData;
    }

    /**
     * Export sheet
     *
     * @param mixed $excel Excel实例(类型由子类决定)
     * @param ExportSheet $sheet
     * @param ExportConfig $config
     * @param int $sheetIndex
     * @param string $filePath
     * @return void
     */
    protected function exportSheet(Excel $excel, ExportSheet $sheet, ExportConfig $config, int $sheetIndex, string $filePath)
    {
        $sheetName = $sheet->getName();
        if ($sheetIndex > 0) {
            $excel->addSheet($sheetName);
        } else {
            $excel->fileName(basename($filePath), $sheetName);
        }

        $this->event->dispatch(new BeforeExportSheet($config, $this, $sheet));

        if (!empty($sheet->style)) {
            $this->exportSheetStyle($excel, $sheet->style);
        }

        [$columns, $headers, $maxDepth] = Column::processColumns($sheet->getColumns());

        $this->exportSheetHeader($excel, $headers, $maxDepth);

        $this->exportSheetData(function ($data) use ($excel) {
            $excel->data($data);
        }, $sheet, $config, $columns);

        $this->event->dispatch(new AfterExportSheet($config, $this, $sheet));
    }

    /**
     * 设置页码样式
     *
     * @param Excel $excel
     * @param SheetStyle $style
     * @return void
     */
    public function exportSheetStyle (Excel $excel, SheetStyle $style)
    {
        if ($style->gridline > 0) {
            $excel->gridline($style->gridline);
        }

        if ($style->zoom !== null) {
            $excel->zoom($style->zoom);
        }

        if ($style->hide) {
            $excel->setCurrentSheetHide();
        }
        if ($style->isFirst) {
            $excel->setCurrentSheetIsFirst();
        }
    }

    /**
     * 设置header
     *
     * @param Excel $excel
     * @param Column[] $columns
     * @param int $maxDepth
     * @return void
     */
    public function exportSheetHeader(Excel $excel, array $columns,int $maxDepth)
    {
        foreach ($columns as $column) {
            // 设置列header
            $colStr = Excel::stringFromColumnIndex($column->col);
            $rowIndex = $column->row + 1;
            $endStr = Excel::stringFromColumnIndex($column->col + $column->colSpan - 1); // 结束列
            $endRowIndex = $rowIndex + $column->rowSpan - 1; // 结束行
            $range = "{$colStr}{$rowIndex}:{$endStr}{$endRowIndex}";

            // 合并单元格|设置header单元格
            $excel->mergeCells($range, $column->title, !empty($column->headerStyle) ? $this->styleToResource($excel, $column->headerStyle) : null);

            // 设置高度
            if ($column->height > 0) {
                $excel->setRow($range, $column->height);
            }
            // 设置宽度|列样式
            $defaultWidth = 5 * mb_strlen($column->title, 'utf-8');
            $excel->setColumn($range, $column->width > 0 ? $column->width : $defaultWidth, !empty($column->style) ? $this->styleToResource($excel, $column->style) : null);
        }
        $excel->setCurrentLine($maxDepth);
    }

    /**
     * import sheet
     *
     * @param Excel $excel
     * @param ImportSheet $sheet
     * @param ImportConfig $config
     * @return array|null
     * @throws ExcelException
     */
    protected function importSheet(Excel $excel, ImportSheet $sheet, ImportConfig $config): array|null
    {
        $sheetName = $sheet->name;

        $this->event->dispatch(new BeforeImportSheet($config, $this, $sheet));

        $excel->openSheet($sheetName);

        $header = [];
        $sheetData = [];

        if ($sheet->headerIndex > 0) {
            if ($sheet->headerIndex > 1) {
                // 跳过指定行
                $excel->setSkipRows($sheet->headerIndex - 1);
            }
            $header = $excel->nextRow();
            $sheet->validateHeader($header);
        }

        $columnTypes = $sheet->getColumnTypes($header ?? []);

        if ($sheet->callback || $header) {
            $rowIndex = 0;
            if ($config->isReturnSheetData) {
                $excel->setType($columnTypes);
                // 返回全量数据
                $sheetData = $excel->getSheetData();
                if ($sheet->isSetHeader) {
                    $sheetData = $sheet->formatSheetDataByHeader($sheetData, $header);
                }
            } else {
                // 执行回调
                while (null !== $row = $excel->nextRow($columnTypes)) {
                    $this->rowCallback($config, $sheet, $row, $header, ++$rowIndex);
                }
            }
        }

        $this->event->dispatch(new AfterImportSheet($config, $this, $sheet));

        return $sheetData;
    }

    /**
     * 执行行回调
     *
     * @param ImportConfig $config
     * @param ImportSheet $sheet
     * @param $row
     * @param null $header
     * @param int $rowIndex
     * @return void
     * @throws ExcelException
     */
    protected function rowCallback(ImportConfig $config, ImportSheet $sheet, $row, $header = null, int $rowIndex = 0)
    {
        if ($header) {
            $row = $sheet->formatRowByHeader($row, $header);
        }
        // 执行回调
        if (is_callable($sheet->callback)) {
            $this->importRowCallback($sheet->callback, $config, $sheet, $row, $rowIndex);
        }
    }

    /**
     * 校验文件mimeType类型
     *
     * @param $filePath
     * @return void
     * @throws ExcelException
     */
    protected function checkFile($filePath)
    {
        // 本地地址
        if (!file_exists($filePath)) {
            throw new ExcelException('File does not exist');
        }
        // 校验mime type
        $mimeType = Helper::getMimeType($filePath);
        if (!in_array($mimeType, [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream',
        ])) {
            throw new ExcelException('File mime type error');
        }
    }

    /**
     * 样式转换
     *
     * @param Excel $excel
     * @param Style $style
     * @return resource
     */
    protected function styleToResource(Excel $excel, Style $style)
    {
        $format = new \Vtiful\Kernel\Format($excel->getHandle());

        if (!empty($style->align)) {
            $format->align(...$style->align);
        }

        if ($style->bold) {
            $format->bold();
        }

        if (!empty($style->font)) {
            $format->font($style->font);
        }

        if ($style->italic) {
            $format->italic();
        }

        if ($style->wrap) {
            $format->wrap();
        }

        if ($style->underline > 0) {
            $format->underline($style->underline);
        }

        if ($style->backgroundColor && $style->backgroundStyle) {
            $format->background($style->backgroundColor, $style->backgroundStyle > 0 ? $style->backgroundStyle : Style::PATTERN_SOLID);
        }

        if ($style->fontSize > 0) {
            $format->fontSize($style->fontSize);
        }

        if ($style->fontColor) {
            $format->fontColor($style->fontColor);
        }

        if ($style->strikeout) {
            $format->strikeout();
        }

        return $format->toResource();
    }
}