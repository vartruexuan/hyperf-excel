<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Vartruexuan\HyperfExcel;

use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Listener\ProgressListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DriverInterface::class => ExcelInvoker::class,
            ],
            'commands' => [
            ],
            'listeners' => [
                ProgressListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for excel.',
                    'source' => __DIR__ . '/../publish/excel.php',
                    'destination' => BASE_PATH . '/config/autoload/excel.php',
                ],
            ],
        ];
    }
}
