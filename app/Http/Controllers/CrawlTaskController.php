<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewCrawlRequest;
use App\Http\Requests\StoreCrawlTaskRequest;
use App\Jobs\RunCrawlJob;
use App\Models\CrawlTask;
use App\Services\Crawler\CrawlException;
use App\Services\Crawler\FieldExtractor;
use App\Services\Crawler\PageFetcher;
use App\Services\Crawler\UrlGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Throwable;

class CrawlTaskController extends Controller
{
    /** 每个用户同时进行的任务上限 */
    private const MAX_ACTIVE_TASKS = 3;

    public function index(Request $request)
    {
        Gate::authorize('viewAny', CrawlTask::class);

        $tasks = CrawlTask::where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (CrawlTask $t) => $this->summary($t));

        return Inertia::render('Crawler/Index', ['tasks' => $tasks]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', CrawlTask::class);

        // /crawler/create?from=ID：带入已有任务的设置，改完后创建的是一个新任务，原任务不受影响
        $prefill = null;
        if ($request->filled('from')) {
            $from = CrawlTask::find($request->integer('from'));
            if ($from) {
                Gate::authorize('view', $from);
                $prefill = [
                    'name'           => $from->name . '（副本）',
                    'start_urls'     => $from->start_urls,
                    'url_pattern'    => $from->url_pattern,
                    'follow_pattern' => $from->follow_pattern,
                    'fields'         => $from->fields,
                    'max_pages'      => $from->max_pages,
                    'max_depth'      => $from->max_depth,
                    'delay_ms'       => $from->delay_ms,
                    'concurrency'    => $from->concurrency,
                    'respect_robots' => $from->respect_robots,
                ];
            }
        }

        return Inertia::render('Crawler/Create', ['prefill' => $prefill]);
    }

    public function store(StoreCrawlTaskRequest $request)
    {
        $user = $request->user();

        $active = CrawlTask::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'running', 'cancelling'])
            ->count();

        if ($active >= self::MAX_ACTIVE_TASKS) {
            throw ValidationException::withMessages([
                'name' => '同时进行的任务最多 ' . self::MAX_ACTIVE_TASKS . ' 个，请等待现有任务完成或先取消。',
            ]);
        }

        $d = $request->validated();

        $task = CrawlTask::create([
            'user_id'        => $user->id,
            'name'           => $d['name'],
            'start_urls'     => array_values($d['start_urls']),
            'url_pattern'    => $d['url_pattern'],
            'follow_pattern' => filled($d['follow_pattern'] ?? null) ? $d['follow_pattern'] : null,
            'fields'         => array_map(fn ($f) => [
                'name'     => $f['name'],
                'selector' => $f['selector'],
                'type'     => $f['type'],
                'attr'     => $f['type'] === 'attr' ? ($f['attr'] ?? null) : null,
                'multiple' => (bool) ($f['multiple'] ?? false),
            ], $d['fields']),
            'max_pages'      => $d['max_pages'],
            'max_depth'      => $d['max_depth'],
            'delay_ms'       => $d['delay_ms'],
            'concurrency'    => $d['concurrency'],
            'respect_robots' => (bool) $d['respect_robots'],
            'status'         => 'pending',
        ]);

        RunCrawlJob::dispatch($task);

        return redirect()->route('crawler.show', $task);
    }

    public function show(CrawlTask $task)
    {
        Gate::authorize('view', $task);

        return Inertia::render('Crawler/Show', [
            'task'   => $this->detail($task),
            // 闭包：只有完整加载或 only 里包含 sample 时才会执行，轮询进度时不会读文件
            'sample' => fn () => $this->sample($task),
        ]);
    }

    public function cancel(CrawlTask $task)
    {
        Gate::authorize('cancel', $task);

        if ($task->isFinished()) {
            return back();
        }

        // worker 每处理一页都会刷新 updated_at；超过 5 分钟没动静，说明 worker 已经停了
        $workerGone = $task->updated_at && $task->updated_at->lt(now()->subMinutes(5));

        if ($task->status === 'pending' || $workerGone) {
            // 还没开始执行，或没人在执行：直接结束
            // （pending 的任务稍后被 worker 取到时，会因为状态不是 pending 而直接跳过）
            $task->update(['status' => 'cancelled', 'finished_at' => now(), 'current_url' => null]);
        } else {
            // 正在运行：只发出取消请求，由 worker 在检查点停止，并写入最终的 cancelled 状态
            $task->update(['status' => 'cancelling']);
        }

        return back();
    }

    public function download(Request $request, CrawlTask $task)
    {
        Gate::authorize('download', $task);

        $format = $request->query('format') === 'jsonl' ? 'jsonl' : 'json';
        $rel    = "crawls/{$task->id}.{$format}";
        $disk   = Storage::disk('local');

        abort_unless($task->isFinished() && $disk->exists($rel), 404);

        return $disk->download($rel, "crawl-{$task->id}.{$format}");
    }

    public function destroy(CrawlTask $task)
    {
        Gate::authorize('delete', $task);

        abort_unless($task->isFinished(), 422, '任务还在进行中，请先取消');

        Storage::disk('local')->delete(["crawls/{$task->id}.json", "crawls/{$task->id}.jsonl"]);
        $task->delete();

        return redirect()->route('crawler.index');
    }

    /** 单页测试：同步抓一页，返回提取结果，不落库 */
    public function preview(
        PreviewCrawlRequest $request,
        PageFetcher $fetcher,
        FieldExtractor $extractor,
        UrlGuard $guard
    ) {
        $data = $request->validated();

        try {
            $page   = $fetcher->fetch($data['url']);
            $result = $extractor->extract($page['html'], $data['fields']);

            $matches = !empty($data['url_pattern'])
                ? $guard->matches($data['url_pattern'], $page['url'])
                : null;

            return response()->json([
                'ok'              => true,
                'final_url'       => $page['url'],
                'status'          => $page['status'],
                'matches_pattern' => $matches,
                'data'            => $result['data'],
                'field_errors'    => $result['errors'],
            ]);
        } catch (CrawlException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'message' => '抓取失败，请稍后重试'], 500);
        }
    }

    // ------------------------------------------------------------------

    private function summary(CrawlTask $t): array
    {
        return [
            'id'           => $t->id,
            'name'         => $t->name,
            'status'       => $t->status,
            'crawled'      => $t->crawled,
            'failed'       => $t->failed,
            'fetched'      => $t->fetched,
            'max_pages'    => $t->max_pages,
            'progress'     => $t->progressPercent(),
            'current_url'  => $t->current_url,
            'error'        => $t->error,
            'created_at'   => $t->created_at?->format('Y-m-d H:i:s'),
            'started_at'   => $t->started_at?->format('Y-m-d H:i:s'),
            'finished_at'  => $t->finished_at?->format('Y-m-d H:i:s'),
            'can_download' => $t->isFinished() && $t->output_path !== null,
        ];
    }

    private function detail(CrawlTask $t): array
    {
        return $this->summary($t) + [
            'start_urls'     => $t->start_urls,
            'url_pattern'    => $t->url_pattern,
            'follow_pattern' => $t->follow_pattern,
            'fields'         => $t->fields,
            'max_depth'      => $t->max_depth,
            'delay_ms'       => $t->delay_ms,
            'concurrency'    => $t->concurrency,
            'respect_robots' => $t->respect_robots,
        ];
    }

    /** 读取结果文件前 N 行用于预览，过长的文本会被截断 */
    private function sample(CrawlTask $task, int $limit = 20): array
    {
        $disk = Storage::disk('local');
        $rel  = "crawls/{$task->id}.jsonl";
        if (!$disk->exists($rel)) {
            return [];
        }

        $fh = fopen($disk->path($rel), 'rb');
        if (!$fh) {
            return [];
        }

        $rows = [];
        while (count($rows) < $limit && ($line = fgets($fh)) !== false) {
            $row = json_decode($line, true);
            if (!is_array($row)) {
                continue;
            }
            array_walk_recursive($row, function (&$v) {
                if (is_string($v) && mb_strlen($v) > 300) {
                    $v = mb_substr($v, 0, 300) . '…';
                }
            });
            $rows[] = $row;
        }
        fclose($fh);

        return $rows;
    }
}
