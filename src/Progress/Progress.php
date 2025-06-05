<?php

namespace Vartruexuan\HyperfExcel\Progress;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;

class Progress
{
    public bool $enabled = true;
    public string $prefix = 'HyperfExcel:';

    public Driver $driver;

    public function __construct(protected ContainerInterface $container, protected array $config)
    {
    }

    /**
     * 初始化配置
     *
     * @param BaseConfig $config
     * @return ProgressRecord
     */
    public function initRecord(BaseConfig $config): ProgressRecord
    {
        $sheetListProgress = [];
        foreach ($config->getSheets() as $sheet) {
            $sheetListProgress[$sheet->name] = new ProgressData();
        }

        $progressRecord = new ProgressRecord([
            'sheetListProgress' => $sheetListProgress,
            'progress' => new ProgressData(),
        ]);
        var_dump($progressRecord);
        return $progressRecord;
    }

}