<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Data\Export;

use Psr\Http\Message\ResponseInterface;
use Vartruexuan\HyperfExcel\Data\BaseObject;

class ExportData extends BaseObject
{
    public ResponseInterface|string $response = '';

    public string $token = '';

    /**
     * @return ResponseInterface|string
     */
    public function getResponse(): string|ResponseInterface
    {
        return $this->response;
    }

    public function __serialize(): array
    {
        return [
            'response' => is_string($this->response) ? $this->response : '',
            'token' => $this->token,
        ];
    }

}