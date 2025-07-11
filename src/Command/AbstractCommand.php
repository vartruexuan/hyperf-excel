<?php

declare(strict_types=1);

namespace Vartruexuan\HyperfExcel\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Vartruexuan\HyperfExcel\Progress\ProgressData;
use Vartruexuan\HyperfExcel\Progress\ProgressInterface;

abstract class AbstractCommand extends HyperfCommand
{
    /**
     * 显示进度
     *
     * @param $token
     * @return void
     */
    protected function showProgress($token)
    {
        $progress = ApplicationContext::getContainer()->get(ProgressInterface::class);
        $this->output->newLine();
        // 创建进度条
        $progressRecode = $progress->getRecordByToken($token);
        if (!$progressRecode) {
            $this->error('未找到进度记录');
            return;
        }

        $bar = new ProgressBar($this->output, 0);
        $bar->setFormat("
<fg=magenta>🔄 任务进度监控</>
%stats%
%bar%
%message%
");

        // 使用引用保存最新进度记录
        $latestProgress = $progressRecode;

        // 自定义占位符
        $bar->setPlaceholderFormatter('stats', function () use (&$latestProgress) {
            $total = $latestProgress->progress->total ?? 0;
            $current = $latestProgress->progress->progress ?? 0;
            $success = $latestProgress->progress->success ?? 0;
            $fail = $latestProgress->progress->fail ?? 0;
            $remaining = max(0, $total - $current);

            // 总数显示逻辑：总数为0时只显示进度，否则显示"总数: X (进度: Y)"
            $totalDisplay = $total > 0
                ? sprintf("总数: %d (进度: %d)", $total, $current)
                : sprintf("进度: %d", $current);

            return sprintf(
                "<fg=cyan>📊 %s</> | <fg=green>✅ 成功: %d</> | <fg=red>❌ 失败: %d</> | <fg=yellow>⏳ 剩余: %d</>",
                $totalDisplay,
                $success,
                $fail,
                $remaining
            );
        });

        $bar->setPlaceholderFormatter('bar', function () use ($bar, &$latestProgress) {
            $total = $latestProgress->progress->total ?? 1;
            $current = $latestProgress->progress->progress ?? 0;
            $percent = $total > 0 ? min(1, $current / $total) : 0;

            $barWidth = 30;
            $complete = (int)round($percent * $barWidth);
            $remaining = $barWidth - $complete;

            $color = match (true) {
                $percent >= 0.8 => 'green',
                $percent >= 0.5 => 'cyan',
                $percent >= 0.3 => 'yellow',
                default => 'red'
            };

            // 总数为0时不显示百分比
            $percentDisplay = $total > 0 ? sprintf("%d%%", round($percent * 100)) : '';

            return sprintf(
                "<fg=%s>[%s%s]</> <fg=white>%s</>",
                $color,
                str_repeat('█', max(0, $complete)),
                str_repeat('░', max(0, $remaining)),
                $percentDisplay
            );
        });

        $bar->setPlaceholderFormatter('message', function () use (&$latestProgress) {
            $spinner = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
            $spinnerChar = $spinner[time() % count($spinner)];

            $status = match ($latestProgress->progress->status) {
                ProgressData::PROGRESS_STATUS_COMPLETE => '<fg=green>✔ 处理完成</>',
                ProgressData::PROGRESS_STATUS_FAIL => '<fg=red>✖ 处理失败</>',
                ProgressData::PROGRESS_STATUS_OUTPUT => '<fg=green>✖ 上传中</>',
                default => sprintf('<fg=yellow>%s 处理中...</>', $spinnerChar)
            };

            return $status;
        });

        // 开始进度条
        $bar->start();

        do {
            // 获取最新进度记录
            $latestProgress = $progress->getRecordByToken($token);
            if ($latestProgress) {
                // 更新进度条最大值
                if ($bar->getMaxSteps() != $latestProgress->progress->total) {
                    $bar->setMaxSteps($latestProgress->progress->total);
                }

                // 更新进度
                $bar->setProgress($latestProgress->progress->progress);

                // 强制刷新显示
                $bar->display();

                usleep(100000); // 0.1秒间隔
            }
        } while (!in_array($latestProgress->progress->status, [
            ProgressData::PROGRESS_STATUS_COMPLETE,
            ProgressData::PROGRESS_STATUS_FAIL,
        ]));

        $bar->finish();
        $this->newLine();

        // 最终状态显示
        if ($latestProgress->progress->status === ProgressData::PROGRESS_STATUS_FAIL) {
            $this->error('处理失败: ' . ($latestProgress->progress->message ?? '未知原因'));
        } else {
            $this->table(['token', 'response'], [[$latestProgress->data->token, $latestProgress->data?->response ?? '']]);
        }
        $this->output->newLine();
    }
}