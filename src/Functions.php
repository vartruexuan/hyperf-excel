<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel;

use RuntimeException;
use Hyperf\Context\ApplicationContext;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportData;
use Vartruexuan\HyperfExcel\Progress\ProgressRecord;

function excel_export(ExportConfig $config): ExportData
{
    if (!ApplicationContext::hasContainer()) {
        throw new RuntimeException('The application context lacks the container.');
    }

    $container = ApplicationContext::getContainer();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }

    return $container->get(ExcelInterface::class)->export($config);
}

function excel_import(ImportConfig $config): ImportData
{
    if (!ApplicationContext::hasContainer()) {
        throw new RuntimeException('The application context lacks the container.');
    }

    $container = ApplicationContext::getContainer();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->import($config);
}

function excel_progress_pop_message(string $token, int $num = 50, bool &$isEnd = true): array
{
    if (!ApplicationContext::hasContainer()) {
        throw new RuntimeException('The application context lacks the container.');
    }

    $container = ApplicationContext::getContainer();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->popMessageAndIsEnd($token, $num, $isEnd);
}

function excel_progress_push_message(string $token, string $message)
{
    if (!ApplicationContext::hasContainer()) {
        throw new RuntimeException('The application context lacks the container.');
    }

    $container = ApplicationContext::getContainer();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->pushMessage($token, $message);
}

function excel_progress(string $token): ?ProgressRecord
{
    if (!ApplicationContext::hasContainer()) {
        throw new RuntimeException('The application context lacks the container.');
    }

    $container = ApplicationContext::getContainer();

    if (!$container->has(ExcelInterface::class)) {
        throw new RuntimeException('ExcelInterface is missing in container.');
    }
    return $container->get(ExcelInterface::class)->getProgressRecord($token);
}
