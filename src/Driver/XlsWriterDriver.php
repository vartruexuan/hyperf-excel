<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
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
    public Excel $excel;

    public function __construct(protected ContainerInterface $container, protected array $config, protected string $name = 'xlswriter')
    {
        parent::__construct($container, $config, $name);
        $this->excel = new Excel([
            'path' => Helper::getTempDir(),
        ]);
    }

    /**
     * export
     *
     * @param ExportConfig $config
     * @return string
     */
    public function exportExcel(ExportConfig $config): string
    {
        $filePath = Helper::getTempFileName('ex_');
        $fileName = basename($filePath);
        $this->excel->fileName($fileName, ($config->sheets[0])->name ?? 'sheet1');

        $this->event->dispatch(new BeforeExportExcel($config, $this));

        foreach (array_values($config->getSheets()) as $index => $sheet) {
            $this->exportSheet($sheet, $config, $index);
        }

        $this->excel->output();

        $this->event->dispatch(new AfterExportExcel($config, $this));

        return $filePath;
    }

    /**
     * import
     *
     * @param ImportConfig $config
     * @throws ExcelException
     */
    public function importExcel(ImportConfig $config)
    {
        $filePath = $config->getTempPath();
        $fileName = basename($filePath);

        // 校验文件
        $this->checkFile($filePath);

        $this->excel->openFile($fileName);

        $sheetList = $this->excel->sheetList();
        $sheetNames = [];

        $sheets = array_map(function ($sheet) use (&$sheetNames, $sheetList) {
            $sheetName = $sheet->name;
            if ($sheet->readType == ImportSheet::SHEET_READ_TYPE_INDEX) {
                $sheetName = $sheetList[$sheet->index];
                $sheet->name = $sheetName;
            }
            $sheetNames[] = $sheetName;
            return $sheet;
        }, array_values($config->getSheets()));

        $this->event->dispatch(new BeforeImportExcel($config, $this));

        /**
         * 页配置
         *
         * @var Sheet $sheet
         */
        foreach ($sheets as $sheet) {
            $this->importSheet($sheet, $config);
        }

        $this->excel->close();

        $this->event->dispatch(new AfterImportExcel($config, $this));
    }


    /**
     * export sheet
     *
     * @param ExportSheet $sheet
     * @param ExportConfig $config
     * @param int|string $index
     * @return void
     */
    protected function exportSheet(ExportSheet $sheet, ExportConfig $config, int|string $index)
    {
        if ($index > 0) {
            $this->excel->addSheet($sheet->getName());
        }

        $this->event->dispatch(new BeforeExportSheet($config, $this, $sheet));

        $this->excel->header($sheet->getHeaders());

        $totalCount = $sheet->getCount();
        $pageSize = $sheet->getPageSize();
        $data = $sheet->getData();

        $isCallback = is_callable($data);

        $page = 1;
        $pageNum = ceil($totalCount / $pageSize);

        do {
            $list = $dataCallback = $data;

            if (!$isCallback) {
                $totalCount = 0;
                $dataCallback = function () use (&$totalCount, $list) {
                    return $list;
                };
            }

            $list = $this->exportDataCallback($dataCallback, $config, $sheet, $page, min($totalCount, $pageSize), $totalCount);

            $listCount = count($list ?? []);

            if ($list) {
                $this->excel->data($sheet->formatList($list));
            }

            $isEnd = !$isCallback || $totalCount <= 0 || $totalCount <= $pageSize || ($listCount < $pageSize || $pageNum <= $page);

            $page++;
        } while (!$isEnd);

        $this->event->dispatch(new AfterExportSheet($config, $this, $sheet));
    }


    /**
     * import sheet
     *
     * @param ImportSheet $sheet
     * @param ImportConfig $config
     * @return void
     */
    protected function importSheet(ImportSheet $sheet, ImportConfig $config)
    {
        $sheetName = $sheet->name;

        $this->event->dispatch(new BeforeImportSheet($config, $this, $sheet));

        $this->excel->openSheet($sheetName);

        $header = [];

        if ($sheet->isSetHeader) {
            if ($sheet->headerIndex > 1) {
                // 跳过指定行
                $this->excel->setSkipRows($sheet->headerIndex - 1);
            }
            $header = $this->excel->nextRow();
            $header = $sheet->getHeader($header);
        }

        if ($sheet->callback || $header) {
            $rowIndex = 0;
            if ($sheet->isReturnSheetData) {
                // 返回全量数据
                $sheetData = $this->excel->getSheetData();
                foreach ($sheetData as $row) {

                    $this->rowCallback($config, $sheet, $row, $header, ++$rowIndex);
                }
            } else {
                // 执行回调
                while (null !== $row = $this->excel->nextRow()) {
                    $this->rowCallback($config, $sheet, $row, $header, ++$rowIndex);
                }
            }
        }

        $this->event->dispatch(new AfterImportSheet($config, $this, $sheet));
    }


    /**
     * 执行行回调
     *
     * @param ImportConfig $config
     * @param ImportSheet $sheet
     * @param $row
     * @param null $header
     * @return void
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
}