<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Export;

use Psr\Http\Message\ResponseInterface;
use Vartruexuan\HyperfExcel\Data\BaseObject;

class ExportData extends BaseObject
{
    public ResponseInterface|string $response;

    public ExportConfig $config;

    /**
     * @return ResponseInterface|string
     */
    public function getResponse(): string|ResponseInterface
    {
        return $this->response;
    }

}