<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Helper;

use Ramsey\Uuid\Uuid;

class Helper
{

    /**
     * 获取uuid4
     *
     * @return void
     */
    public function uuid4()
    {
        return Uuid::uuid4()->getHex()->toString();
    }

}