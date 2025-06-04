<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Driver;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\Sheet;
use Vartruexuan\HyperfExcel\Event\AfterExportExcel;
use Vartruexuan\HyperfExcel\Event\AfterExportSheet;
use Vartruexuan\HyperfExcel\Event\BeforeExportExcel;
use Vartruexuan\HyperfExcel\Event\BeforeExportSheet;
use Vartruexuan\HyperfExcel\Helper\Helper;
use Vtiful\Kernel\Excel;
use function Hyperf\Support\make;

class XlsWriterDriver extends Driver
{
    public Excel $excel;

    public function __construct(protected ContainerInterface $container, protected array $config)
    {
        parent::__construct($container, $config);
        $this->excel = new Excel([
            'path' => Helper::getTempDir(),
        ]);
    }

    /**
     * 导出
     *
     * @param ExportConfig $config
     * @return string
     */
    public function exportExcel(ExportConfig $config): string
    {
        $eventParam = [
            'config' => $config,
            'driver' => $this,
        ];
        $filePath = Helper::getTempFileName('ex_');
        $fileName = basename($filePath);
        $this->excel->fileName($fileName, ($config->sheets[0])->name ?? 'sheet1');

        //todo 触发事件before
        $this->event->dispatch(make(BeforeExportExcel::class, $eventParam));

        /**
         * 写入页码数据
         *
         * @var  $sheet
         */
        foreach (array_values($config->getSheets()) as $index => $sheet) {
            $this->exportSheet($sheet, $config, $index);
        }

        $this->excel->output();

        $this->event->dispatch(make(AfterExportExcel::class, $eventParam));

        return $filePath;
    }

    public function importExcel($config)
    {
    }


    private function exportSheet(Sheet $sheet, ExportConfig $config, int|string $index)
    {

        if ($index > 0) {
            $this->excel->addSheet($sheet->getName());
        }
        $eventParam=[
            'config' => $config,
            'driver' => $this,
        ];

        $this->event->dispatch(make(BeforeExportSheet::class, $eventParam));

        // header
        $this->excel->header($sheet->getHeaders());

        $totalCount = $sheet->getCount();
        $pageSize = $sheet->getPageSize();
        $data = $sheet->getData();

        $isCallback = is_callable($data);

        $page = 1;
        $pageNum = ceil($totalCount / $pageSize);

        // 导出数据
        do {
            $list = $dataCallback = $data;

            if (!$isCallback) {
                $totalCount = 0;
                $dataCallback = function () use (&$totalCount, $list) {
                    return $list;
                };
            }

            $list = $this->exportDataCallback($dataCallback, $config, $sheet, $page, $pageSize, $totalCount);

            $listCount = count($list ?? []);

            if ($list) {
                $this->excel->data($sheet->formatList($list));
            }

            $isEnd = !$isCallback || $totalCount <= 0 || ($listCount < $pageSize || $pageNum <= $page);

            $page++;
        } while (!$isEnd);

        $this->event->dispatch(make(AfterExportSheet::class, $eventParam));
    }
}