<?php

namespace Vartruexuan\HyperfExcel\Progress;

use Vartruexuan\HyperfExcel\Data\BaseObject;

class ProgressData extends BaseObject
{

    /**
     * 进度状态
     */
    public const PROGRESS_STATUS_AWAIT = 1; // 待处理
    public const PROGRESS_STATUS_PROCESS = 2; // 处理中
    public const PROGRESS_STATUS_END = 3; // 处理完成
    public const PROGRESS_STATUS_FAIL = 4; // 处理失败

    public int $total = 0;
    public int $progress = 0;
    public int $success = 0;
    public int $fail = 0;

    public int $status = self::PROGRESS_STATUS_AWAIT;
}