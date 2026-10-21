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
            'crawled' => 0, 'failed' => 0, 'error' => null, 'output_path' => null,
        ]);

        $fh = fopen($jsonlAbs, 'wb');           // wb：重跑时会清空旧结果
        if ($fh === false) {
            $task->update(['status' => 'failed', 'error' => '无法创建结果文件', 'finished_at' => now()]);
            return;
        }

        $crawled = 0;
        $failed  = 0;
        $final   = 'done';
        $error   = null;

        try {
            $regex = $this->guard->compilePattern($task->url_pattern);

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
                            $this->enqueueLinks($queue, $seen, $hosts, $page['html'], $finalUrl, $depth + 1);
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

                $task->update(['crawled' => $crawled, 'failed' => $failed, 'current_url' => $url]);

                usleep($waitMs * 1000);
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
            'status' => $final, 'crawled' => $crawled, 'failed' => $failed,
            'current_url' => null, 'output_path' => $jsonRel,
            'finished_at' => now(), 'error' => $error,
        ]);
    }

    private function enqueueLinks(SplQueue $queue, array &$seen, array $hosts, string $html, string $baseUrl, int $depth): void
    {
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

    private function isCancelled(CrawlTask $task): bool
    {
        return CrawlTask::whereKey($task->id)->value('status') === 'cancelled';
    }

    private function host(string $url): string
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST));
    }
}
