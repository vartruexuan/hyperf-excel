<?php

namespace Vartruexuan\HyperfExcel;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportData;
use Vartruexuan\HyperfExcel\Data\Import\ImportConfig;
use Vartruexuan\HyperfExcel\Data\Import\ImportData;
use Vartruexuan\HyperfExcel\Driver\DriverFactory;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Event\AfterExport;
use Vartruexuan\HyperfExcel\Event\AfterImport;
use Vartruexuan\HyperfExcel\Event\BeforeExport;
use Vartruexuan\HyperfExcel\Event\BeforeImport;
use Vartruexuan\HyperfExcel\Event\Error;
use Vartruexuan\HyperfExcel\Exception\ExcelException;
use Vartruexuan\HyperfExcel\Progress\ProgressData;
use Vartruexuan\HyperfExcel\Progress\ProgressInterface;
use Vartruexuan\HyperfExcel\Progress\ProgressRecord;
use Vartruexuan\HyperfExcel\Queue\ExcelQueueInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Vartruexuan\HyperfExcel\Strategy\Token\TokenStrategyInterface;

class Excel implements ExcelInterface
{
    public EventDispatcherInterface $event;
    protected array $config;

    public function __construct(protected ContainerInterface $container, protected ProgressInterface $progress)
    {
        $config = $container->get(ConfigInterface::class);
        $this->config = $config->get('excel', []);
        $this->event = $container->get(EventDispatcherInterface::class);
    }

    public function export(ExportConfig $config): ExportData
    {
        if (empty($config->getToken())) {
            $config->setToken($this->buildToken());
        }
        $driver = $this->getDriver($config->getDriverName());
        $exportData = new ExportData(['token' => $config->getToken()]);

        try {

            $this->event->dispatch(new BeforeExport($config, $driver));

            if ($config->getIsAsync()) {
                if ($config->getOutPutType() == ExportConfig::OUT_PUT_TYPE_OUT) {
                    throw new ExcelException('Async does not support output type ExportConfig::OUT_PUT_TYPE_OUT');
                }
                $this->pushQueue($config);
                return $exportData;
            }

            $exportData = $driver->export($config);

            $this->event->dispatch(new AfterExport($config, $driver, $exportData));

            return $exportData;

        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $driver, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $driver, $throwable));
            throw $throwable;
        }
    }

    public function import(ImportConfig $config): ImportData
    {
        if (empty($config->getToken())) {
            $config->setToken($this->buildToken());
        }
        $importData = new ImportData(['token' => $config->getToken()]);
        $driver = $this->getDriver($config->getDriverName());

        try {
            $this->event->dispatch(new BeforeImport($config, $driver));
            if ($config->getIsAsync()) {
                if ($config->isReturnSheetData) {
                    throw new ExcelException('Asynchronous does not support returning sheet data');
                }
                $this->pushQueue($config);
                return $importData;
            }

            $importData = $driver->import($config);

            $this->event->dispatch(new AfterImport($config, $driver, $importData));

            return $importData;

        } catch (ExcelException $exception) {
            $this->event->dispatch(new Error($config, $driver, $exception));
            throw $exception;
        } catch (\Throwable $throwable) {
            $this->event->dispatch(new Error($config, $driver, $throwable));
            throw $throwable;
        }
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

    public function popMessageAndIsEnd(string $token, int $num = 50, bool &$isEnd = true): array
    {
        $progressRecord = $this->getProgressRecord($token);
        $messages = $this->popMessage($token, $num);
        $isEnd = $this->isEnd($progressRecord) && empty($messages);
        return $messages;
    }

    public function isEnd(?ProgressRecord $progressRecord): bool
    {
        return empty($progressRecord) || in_array($progressRecord->progress->status, [
                ProgressData::PROGRESS_STATUS_COMPLETE,
                ProgressData::PROGRESS_STATUS_FAIL,
            ]);
    }

    public function getDefaultDriver(): DriverInterface
    {
        return $this->container->get(DriverInterface::class);
    }

    public function getDriverByName(string $driverName): DriverInterface
    {
        return $this->container->get(DriverFactory::class)->get($driverName);
    }

    public function getDriver(?string $driverName = null): DriverInterface
    {
        $driver = $this->getDefaultDriver();
        if (!empty($driverName)) {
            $driver = $this->getDriverByName($driverName);
        }
        return $driver;
    }

    /**
     * 推送队列
     *
     * @param BaseConfig $config
     * @return bool
     */
    protected function pushQueue(BaseConfig $config): bool
    {
        return $this->container->get(ExcelQueueInterface::class)->push($config);
    }

    /**
     * token
     *
     * @return string
     */
    protected function buildToken(): string
    {
        return $this->container->get(TokenStrategyInterface::class)->getToken();
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}