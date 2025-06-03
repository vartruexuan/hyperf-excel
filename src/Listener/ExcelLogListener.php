<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Listener;

use Hyperf\AsyncQueue\Event\AfterHandle;
use Hyperf\AsyncQueue\Event\BeforeHandle;
use Hyperf\AsyncQueue\Event\FailedHandle;
use Hyperf\AsyncQueue\Event\RetryHandle;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * 监听输出日志
 */
class ExcelLogListener implements ListenerInterface
{

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('excel');
    }

    public function listen(): array
    {
        return [
        ];
    }


    public function process(object $event)
    {
        //todo 此处实现日志输出
    }
}