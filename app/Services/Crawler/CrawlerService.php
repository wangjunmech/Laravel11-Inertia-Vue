<?php

namespace App\Services\Crawler;

use App\Models\CrawlTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SplQueue;
use Throwable;

class CrawlerService
{
    /** 最多发起 max_pages × 5 次请求，防止"页面很多但几乎没有匹配"时无限爬下去 */
    private const FETCH_FACTOR = 5;
    private const MAX_QUEUE = 50000;
    private const MAX_CONSECUTIVE_BLOCKS = 5;
    private const SKIP_EXT = '~\.(jpe?g|png|gif|webp|svg|ico|bmp|pdf|zip|rar|7z|gz|tar|exe|dmg|apk|mp3|mp4|avi|mov|wmv|flv|css|js|json|xml|docx?|xlsx?|pptx?)(\?.*)?$~i';

    public function __construct(
        private PageFetcher $fetcher,
        private FieldExtractor $extractor,
        private UrlGuard $guard,
        private RobotsChecker $robots,
    ) {
    }

    public function run(CrawlTask $task): void
    {
        $disk     = Storage::disk('local');
        $disk->makeDirectory('crawls');
        $jsonlRel = "crawls/{$task->id}.jsonl";
        $jsonRel  = "crawls/{$task->id}.json";
        $jsonlAbs = $disk->path($jsonlRel);
        $jsonAbs  = $disk->path($jsonRel);

        $task->update([
            'status' => 'running', 'started_at' => now(), 'finished_at' => null,
            'crawled' => 0, 'failed' => 0, 'fetched' => 0, 'error' => null, 'output_path' => null,
        ]);

        $fh = fopen($jsonlAbs, 'wb');           // wb：重跑时会清空旧结果
        if ($fh === false) {
            $task->update(['status' => 'failed', 'error' => '无法创建结果文件', 'finished_at' => now()]);
            return;
        }

        $crawled = 0;
        $failed  = 0;
        $fetched = 0;
        $final   = 'done';
        $error   = null;

        try {
            $regex = $this->guard->compilePattern($task->url_pattern);
            $followRegex = filled($task->follow_pattern)
                ? $this->guard->compilePattern($task->follow_pattern)
                : null;

            $queue = new SplQueue();
            $seen  = [];   // 已入队的 URL
            $done  = [];   // 已处理的最终 URL（防止不同入口重定向到同一页重复提取）
            $hosts = [];   // 允许抓取的域名 = 起始 URL 的域名

            foreach ($task->start_urls as $u) {
                $n = $this->guard->normalize($u);
                if (isset($seen[$n])) {
                    continue;
                }
                $seen[$n] = true;
                $hosts[$this->host($n)] = true;
                $queue->enqueue([$n, 0]);
            }

            if ($task->respect_robots) {
                foreach ($task->start_urls as $u) {
                    if (!$this->robots->reachable($u)) {
                        throw new CrawlException('无法读取 ' . $this->host($u) . ' 的 robots.txt（网络错误或服务器返回 5xx），已停止，请稍后重试');
                    }
                }
            }

            $fetched   = 0;
            $maxFetch  = max($task->max_pages * self::FETCH_FACTOR, 50);
            $blocks    = 0;

            while (!$queue->isEmpty() && $crawled < $task->max_pages && $fetched < $maxFetch) {
                if ($this->isCancelled($task)) {
                    Log::info('crawler cancel detected', ['task' => $task->id, 'at' => 'loop-start']);
                    $final = 'cancelled';
                    break;
                }

                [$url, $depth] = $queue->dequeue();

                if ($task->respect_robots && !$this->robots->allowed($url)) {
                    continue;
                }

                $fetched++;
                $waitMs = $task->delay_ms;
                if ($task->respect_robots) {
                    $waitMs = max($waitMs, (int) ($this->robots->crawlDelay($url) * 1000));
                }

                try {
                    $page     = $this->fetcher->fetch($url);
                    $blocks   = 0;

                    // 请求期间收到了取消：丢弃这一页，立刻结束
                    if ($this->isCancelled($task)) {
                        Log::info('crawler cancel detected', ['task' => $task->id, 'at' => 'after-fetch']);
                        $final = 'cancelled';
                        break;
                    }
                    $finalUrl = $page['url'];

                    $skip = isset($done[$finalUrl])
                        || !isset($hosts[$this->host($finalUrl)])
                        || ($task->respect_robots && $finalUrl !== $url && !$this->robots->allowed($finalUrl));

                    if (!$skip) {
                        $done[$finalUrl] = true;
                        $seen[$finalUrl] = true;

                        if (preg_match($regex, $finalUrl) === 1) {
                            $res  = $this->extractor->extract($page['html'], $task->fields);
                            $line = json_encode(
                                ['url' => $finalUrl] + $res['data'],
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
                            );
                            if ($line !== false) {
                                fwrite($fh, $line . PHP_EOL);
                                $crawled++;
                            }
                        }

                        if ($depth < $task->max_depth) {
                            $this->enqueueLinks($queue, $seen, $hosts, $page['html'], $finalUrl, $depth + 1, $regex, $followRegex, $task->max_depth);
                        }
                    }
                } catch (BlockedException $e) {
                    $failed++;
                    $blocks++;
                    Log::info('crawler blocked', ['task' => $task->id, 'url' => $url]);
                    if ($blocks >= self::MAX_CONSECUTIVE_BLOCKS) {
                        throw new CrawlException('目标站点连续触发验证页面，已停止抓取');
                    }
                } catch (CrawlException $e) {
                    $failed++;
                    Log::info('crawler page failed', ['task' => $task->id, 'url' => $url, 'msg' => $e->getMessage()]);
                } catch (Throwable $e) {
                    $failed++;
                    Log::warning('crawler page error', ['task' => $task->id, 'url' => $url, 'error' => $e->getMessage()]);
                }

                $task->update(['crawled' => $crawled, 'failed' => $failed, 'fetched' => $fetched, 'current_url' => $url]);

                // 请求间隔分段等待，期间也能响应取消
                if ($this->pause($task, $waitMs)) {
                    Log::info('crawler cancel detected', ['task' => $task->id, 'at' => 'pause']);
                    $final = 'cancelled';
                    break;
                }
            }
        } catch (CrawlException $e) {
            $final = 'failed';
            $error = $e->getMessage();
        } catch (Throwable $e) {
            report($e);
            $final = 'failed';
            $error = '抓取过程中发生内部错误';
        } finally {
            fclose($fh);
        }

        if ($final === 'done' && $this->isCancelled($task)) {
            $final = 'cancelled';
        }
        if ($final === 'done' && $crawled === 0) {
            $error = '抓取结束，但没有页面匹配 URL 规则，或所有请求都失败了';
        }

        $this->buildJson($jsonlAbs, $jsonAbs);

        $task->update([
            'status' => $final, 'crawled' => $crawled, 'failed' => $failed, 'fetched' => $fetched,
            'current_url' => null, 'output_path' => $jsonRel,
            'finished_at' => now(), 'error' => $error,
        ]);
    }

    /**
     * 把页面里的链接放进待抓队列。
     *
     * - 设置了跟进规则：只有「目标页（匹配 URL 正则）」或「匹配跟进规则」的链接才入队，其余直接丢弃
     * - 没设置：跟进所有同域名链接（保持原行为）
     * - 已经到最大深度时，再往下的页面不会被继续跟进，所以非目标页没有抓取价值，直接不入队
     */
    private function enqueueLinks(
        SplQueue $queue,
        array &$seen,
        array $hosts,
        string $html,
        string $baseUrl,
        int $depth,
        string $regex,
        ?string $followRegex,
        int $maxDepth
    ): void {
        foreach ($this->extractor->links($html) as $href) {
            if ($queue->count() >= self::MAX_QUEUE) {
                return;
            }

            $abs = $this->guard->absolute($baseUrl, $href);
            if ($abs === null || isset($seen[$abs]) || !str_starts_with($abs, 'http')) {
                continue;
            }
            if (!isset($hosts[$this->host($abs)]) || preg_match(self::SKIP_EXT, $abs)) {
                continue;
            }

            $isTarget = preg_match($regex, $abs) === 1;

            if ($followRegex !== null && !$isTarget && preg_match($followRegex, $abs) !== 1) {
                continue;
            }
            if ($depth >= $maxDepth && !$isTarget) {
                continue;
            }

            $seen[$abs] = true;
            $queue->enqueue([$abs, $depth]);
        }
    }

    /** 把 .jsonl 流式转成一个合法的 JSON 数组文件，不会把全部结果读进内存 */
    private function buildJson(string $jsonlAbs, string $jsonAbs): void
    {
        $in  = fopen($jsonlAbs, 'rb');
        $out = fopen($jsonAbs, 'wb');
        if (!$in || !$out) {
            return;
        }

        fwrite($out, "[\n");
        $first = true;
        while (($line = fgets($in)) !== false) {
            $line = rtrim($line);
            if ($line === '') {
                continue;
            }
            fwrite($out, ($first ? '' : ",\n") . $line);
            $first = false;
        }
        fwrite($out, "\n]\n");

        fclose($in);
        fclose($out);
    }

    /** 控制器收到取消请求时会把状态改成 cancelling（运行中）或 cancelled（未开始） */
    private function isCancelled(CrawlTask $task): bool
    {
        return in_array(CrawlTask::whereKey($task->id)->value('status'), ['cancelling', 'cancelled'], true);
    }

    /** 等待 $ms 毫秒，每 0.5 秒检查一次是否被取消；被取消返回 true */
    private function pause(CrawlTask $task, int $ms): bool
    {
        $end = microtime(true) + $ms / 1000;

        while (($left = $end - microtime(true)) > 0) {
            usleep((int) (min($left, 0.5) * 1_000_000));
            if ($this->isCancelled($task)) {
                return true;
            }
        }

        return false;
    }

    private function host(string $url): string
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST));
    }
}
