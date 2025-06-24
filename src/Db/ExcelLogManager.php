<?php

namespace Vartruexuan\HyperfExcel\Db;

use Hyperf\Database\Model\Model;
use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Data\Export\ExportConfig;
use Vartruexuan\HyperfExcel\Db\Model\ExcelLog;
use Vartruexuan\HyperfExcel\Driver\Driver;
use Vartruexuan\HyperfExcel\Driver\DriverInterface;
use Vartruexuan\HyperfExcel\Progress\ProgressData;
use Vartruexuan\HyperfExcel\Progress\ProgressRecord;

class ExcelLogManager
{
    public string $model;
    public const TYPE_EXPORT = 'export';
    public const TYPE_IMPORT = 'import';


    public function __construct(protected ContainerInterface $container, protected array $config, protected Driver $driver)
    {
        $this->model = $this->config['model'] ?? ExcelLog::class;
    }


    /**
     * 保存记录信息
     *
     * @param BaseConfig $config
     * @param array $saveParam
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveLog(BaseConfig $config, array $saveParam = []): bool
    {
        $token = $config->getToken();

        $type = $config instanceof ExportConfig ? static::TYPE_EXPORT : static::TYPE_IMPORT;

        $progressRecord = $this->getProgressByToken($token);

        $saveParam = array_merge($saveParam, [
            'token' => $token,
            'config_class' => get_class($config),
            'config' => json_encode($config->__serialize()),
            'type' => $type,
            'service_name' => $config->serviceName,
            'progress' => json_encode($progressRecord?->progress), // 进度信息
            'sheet_progress' => json_encode($progressRecord?->sheetListProgress), // 页码进度信息
            'status' => $progressRecord?->progress->status ?: ProgressData::PROGRESS_STATUS_AWAIT,// 状态
            'data' => json_encode($progressRecord?->data ?: []),
        ]);
        if ($type == static::TYPE_EXPORT) {
            $saveParam['url'] = $progressRecord?->data?->response ?? "";
        } else {
            $saveParam['url'] = $config->getPath();
        }
        return $this->model::query()->upsert([$saveParam], ['token']);
    }


    /**
     * 获取进度
     *
     * @param string $token
     * @return ProgressRecord|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProgressByToken(string $token): ?ProgressRecord
    {
        return $this->driver->progress->getRecordByToken($token);
    }

}