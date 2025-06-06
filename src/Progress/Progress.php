<?php

namespace Vartruexuan\HyperfExcel\Progress;

use Psr\Container\ContainerInterface;
use Vartruexuan\HyperfExcel\Data\BaseConfig;
use Vartruexuan\HyperfExcel\Driver\Driver;

class Progress implements ProgressInterface
{
    public function __construct(protected ContainerInterface $container, protected array $config, protected Driver $driver)
    {
    }

    /**
     * 初始化配置
     *
     * @param BaseConfig $config
     * @return ProgressRecord
     */
    public function initRecord(BaseConfig $config): ProgressRecord
    {
        $sheetListProgress = [];
        foreach ($config->getSheets() as $sheet) {
            $sheetListProgress[$sheet->name] = new ProgressData();
        }
        $progressRecord = new ProgressRecord([
            'sheetListProgress' => $sheetListProgress,
            'progress' => new ProgressData(),
        ]);
        $this->set($config, $progressRecord);

        return $progressRecord;
    }

    /**
     * 获取进度记录
     *
     * @param BaseConfig $config
     * @return ProgressRecord
     */
    public function getRecord(BaseConfig $config): ProgressRecord
    {
        return $this->get($config);
    }

    /**
     * 设置页面进度
     *
     * @param BaseConfig $config
     * @param string $sheetName
     * @param ProgressData $progressData
     * @return ProgressData
     */
    public function setSheetProgress(BaseConfig $config, string $sheetName, ProgressData $progressData): ProgressData
    {
        $progressRecord = $this->getRecord($config);
        $sheetProgress = $progressRecord->getProgressBySheet($sheetName);
        $sheetProgress->status = $progressData->status;
        if ($progressData->total > 0) {
            $sheetProgress->total = $progressData->total;
            $progressRecord->progress->total += $progressData->total;
        }
        if ($progressData->progress > 0) {
            $sheetProgress->progress += $progressData->progress;
            $progressRecord->progress->progress += $progressData->progress;
            if ($sheetProgress->progress == $sheetProgress->total) {
                $sheetProgress->status = ProgressData::PROGRESS_STATUS_END;
            }
        }
        if ($progressData->success > 0) {
            $sheetProgress->success += $progressData->success;
            $progressRecord->progress->success += $progressData->progress;
        }
        if ($progressData->fail > 0) {
            $sheetProgress->fail += $progressData->fail;
            $progressRecord->progress->fail += $progressData->progress;
        }
        // 处理总进度
        $progressRecord = $this->setProgressStatus($progressRecord);
        $progressRecord->setProgressBySheet($sheetName, $sheetProgress);
        $this->set($config, $progressRecord);
        return $sheetProgress;
    }

    public function setProgress(BaseConfig $config, ProgressData $progressData): ProgressRecord
    {
        $progressRecord = $this->getRecord($config);
        $progressRecord->progress->status = $progressData->status;
        if ($progressData->total > 0) {
            $progressRecord->progress->total = $progressData->total;
        }
        if ($progressData->progress > 0) {
            $progressRecord->progress->progress += $progressData->progress;
        }
        if ($progressData->success > 0) {
            $progressRecord->progress->success += $progressData->progress;
        }
        if ($progressData->fail > 0) {
            $progressRecord->progress->fail += $progressData->progress;
        }
        $this->set($config, $progressRecord);
        return $progressRecord;
    }


    public function pushMessage(BaseConfig $config, string $message)
    {
        $key = $this->getMessageKey($config);
        $this->driver->redis->lpush($key, $message);
        $this->driver->redis->expire($key, intval($this->config['expire'] ?? 3600));
    }

    public function popMessage(BaseConfig $config, int $num): array
    {
        $messages = [];
        for ($i = 0; $i < $num; $i++) {
            if ($message = $this->driver->redis->lpop($this->getMessageKey($config), $num)) {
                $messages[] = $message;
            }
        }
        return $messages;
    }

    protected function setProgressStatus(ProgressRecord $progressRecord)
    {
        // 处理中
        $status = array_map(function ($item) {
            return $item->status;
        }, $progressRecord->sheetListProgress);
        $status = array_unique($status);
        $count = count($status);
        if ($count <= 1) {
            $progressRecord->progress->status = current($status);
        } else {
            $progressRecord->progress->status = ProgressData::PROGRESS_STATUS_PROCESS;
        }
        return $progressRecord;
    }


    protected function set(BaseConfig $config, ProgressRecord $progressRecord)
    {
        $key = $this->getProgressKey($config);
        $this->driver->redis->set($key, $this->driver->packer->pack($progressRecord));
        $this->driver->redis->expire($key, intval($this->config['expire'] ?? 3600));
    }

    protected function get(BaseConfig $config): ?ProgressRecord
    {
        $record = $this->driver->redis->get($this->getProgressKey($config));

        return $this->driver->packer->unpack($record);
    }

    protected function getProgressKey(BaseConfig $config)
    {
        return sprintf('%s_progress:%s', $this->config['prefix'] ?? 'HyperfExcel', $config->token);
    }

    protected function getMessageKey(BaseConfig $config)
    {
        return sprintf('%s_message:%s', $this->config['prefix'] ?? 'HyperfExcel', $config->token);
    }

}