<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data;

use Psr\Http\Message\ResponseInterface ;
use Vartruexuan\HyperfExcel\Data\Config\ExportConfig;

class ExportData
{
    public ResponseInterface|string $response;

    public ExportConfig $exportConfig;

    /**
     * @return ResponseInterface|string
     */
    public function getResponse(): string|ResponseInterface
    {
        return $this->response;
    }

}