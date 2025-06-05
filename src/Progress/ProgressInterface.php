<?php

namespace Vartruexuan\HyperfExcel\Progress;

use Vartruexuan\HyperfExcel\Data\BaseConfig;

interface ProgressInterface
{
    public function initRecord(BaseConfig $config): ProgressRecord;

    public function updateRecord(BaseConfig $config): ProgressRecord;

    public function getRecord(BaseConfig $config): ProgressRecord;

    public function setSheetProgress(BaseConfig $config): ProgressRecord;

}