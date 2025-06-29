<?php

namespace Vartruexuan\HyperfExcel;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportData;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Progress\ProgressInterface;
use Vartruexuan\HyperfExcel\Progress\ProgressRecord;
use function Hyperf\Config\config;

class Excel implements ExcelInterface
{
    protected DriverInterface $driver;
    protected ProgressInterface $progress;
    protected array $config;

    public function __construct(protected ContainerInterface $container, ProgressInterface $progress)
    {
        $config = $container->get(ConfigInterface::class);
        $this->config = $config->get('excel', []);

        $driver = $this->container->get(DriverFactory::class)->get($this->getConfig()['default']);
        $this->setDriver($driver);
        $this->progress = $progress;

    }

    public function export(ExportConfig $config): ExportData
    {
        return $this->getDriver()->export($config);
    }

    public function import(ImportConfig $config): ImportData
    {
        return $this->getDriver()->import($config);
    }

    public function getProgressRecord(string $token): ?ProgressRecord
    {
        return $this->progress->getRecordByToken($token);
    }

    public function popMessage(string $token, int $num = 50): array
    {
        return $this->progress->popMessage($token, $num);
    }

    public function pushMessage(string $token, string $message)
    {
        return $this->progress->pushMessage($token, $message);
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function setDriver(DriverInterface $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    public function setDriverByName(string $driverName): static
    {
        $driver = $this->container->get(DriverFactory::class)->get($driverName);
        $this->setDriver($driver);
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}