<?php

namespace Vartruexuan\HyperfExcel\Progress;

use Vartruexuan\HyperfExcel\Data\BaseConfig;

interface ProgressInterface
{
    public function initRecord(BaseConfig $config): ProgressRecord;

    public function getRecord(BaseConfig $config): ProgressRecord;

    public function setSheetProgress(BaseConfig $config, string $sheetName, ProgressData $progressData): ProgressData;

    public function setProgress(BaseConfig $config, ProgressData $progressData): ProgressRecord;

    public function pushMessage(BaseConfig $config, string $message);

    public function popMessage(BaseConfig $config, int $num): array;

}