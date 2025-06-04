<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Helper;

use Ramsey\Uuid\Uuid;
use Overtrue\Http\Client;

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

    /**
     * 下载文件
     *
     * @param string $remotePath 远程地址
     * @param string $filePath
     * @return string|false
     */
    public function downloadFile(string $remotePath, string $filePath)
    {
        $response = Client::create([
            'response_type' => 'raw',
        ])->request($remotePath, 'GET', [
            'verify' => false,
            'http_errors' => false,
        ]);

        if (@file_put_contents($filePath, $response->getBody()->getContents())) {
            return $filePath;
        }
        return false;
    }

    /**
     * 获取临时目录
     *
     * @return string
     */
    public function getTempDir(): string
    {
        return sys_get_temp_dir();
    }

    /**
     * 申请临时文件
     *
     * @param $prefix
     * @return false|string
     */
    public function getTempFileName($prefix = 'ex_')
    {
        return tempnam($this->getTempDir(), $prefix);
    }

    /**
     * 删除文件
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFile(string $filePath)
    {
        return @unlink($filePath);
    }

}