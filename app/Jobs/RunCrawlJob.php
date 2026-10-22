<?php

namespace App\Jobs;

use App\Models\CrawlTask;
use App\Services\Crawler\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class RunCrawlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** 单个任务最长运行时间（秒）。config/queue.php 里的 retry_after 必须大于它 */
    public int $timeout = 3600;

    /** 不自动重试，避免重复抓取 */
    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public CrawlTask $task)
    {
    }

    public function handle(CrawlerService $service): void
    {
        $task = $this->task->fresh();

        // 排队期间被取消 / 删除
        if (!$task || $task->status !== 'pending') {
            return;
        }

        $service->run($task);
    }

    public function failed(Throwable $e): void
    {
        $task = $this->task->fresh();

        // 已经有最终状态（比如已取消）就不要再覆盖
        if (!$task || $task->isFinished()) {
            return;
        }

        $cancelling = $task->status === 'cancelling';

        $task->update([
            'status'      => $cancelling ? 'cancelled' : 'failed',
            'error'       => $cancelling ? null : Str::limit($e->getMessage(), 300),
            'finished_at' => now(),
        ]);
    }
}
