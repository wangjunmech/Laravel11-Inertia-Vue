<?php

namespace App\Services\Crawler;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * 读取并缓存 robots.txt，判断 URL 是否允许抓取。
 *
 * 规则（与主流搜索引擎一致）：
 *  - robots.txt 不存在（4xx）  → 允许
 *  - 服务器错误 / 无法连接     → 保守处理，视为不可抓取
 *  - 同一 URL 命中多条规则     → 路径最长的优先，长度相同时 Allow 优先
 */
class RobotsChecker
{
    private const MAX_BYTES      = 512 * 1024;
    private const MAX_DELAY_SECS = 30;

    /** @var array<string, array> 同一次任务内的内存缓存 */
    private array $memo = [];

    public function __construct(private UrlGuard $guard)
    {
    }

    /** robots.txt 是否能正常获取（只有网络错误 / 5xx 才返回 false） */
    public function reachable(string $url): bool
    {
        return $this->load($url)['status'] !== 'error';
    }

    public function allowed(string $url): bool
    {
        $robots = $this->load($url);

        if ($robots['status'] === 'error') {
            return false;
        }
        if ($robots['status'] === 'none') {
            return true;
        }

        $path   = parse_url($url, PHP_URL_PATH) ?: '/';
        $query  = parse_url($url, PHP_URL_QUERY);
        $target = $path . ($query ? '?' . $query : '');

        $best = null; // [规则长度, 是否允许]
        foreach ($this->rulesFor($robots)['rules'] as [$type, $pattern]) {
            if ($pattern === '' || !$this->pathMatches($pattern, $target)) {
                continue;
            }
            $len   = strlen($pattern);
            $allow = $type === 'allow';
            if ($best === null || $len > $best[0] || ($len === $best[0] && $allow)) {
                $best = [$len, $allow];
            }
        }

        return $best === null ? true : $best[1];
    }

    /** Crawl-delay（秒），没有则为 0 */
    public function crawlDelay(string $url): float
    {
        $robots = $this->load($url);
        if ($robots['status'] !== 'ok') {
            return 0.0;
        }
        $delay = (float) ($this->rulesFor($robots)['delay'] ?? 0);

        return max(0.0, min($delay, self::MAX_DELAY_SECS));
    }

    // ------------------------------------------------------------------

    private function load(string $url): array
    {
        $p = parse_url($url);
        if (!$p || empty($p['scheme']) || empty($p['host'])) {
            return ['status' => 'error'];
        }
        $origin = strtolower($p['scheme'] . '://' . $p['host']) . (isset($p['port']) ? ':' . $p['port'] : '');

        if (isset($this->memo[$origin])) {
            return $this->memo[$origin];
        }

        $key  = 'crawler:robots:' . md5($origin);
        $data = Cache::get($key);

        if (!is_array($data)) {
            $data = $this->download($origin);
            Cache::put($key, $data, $data['status'] === 'error' ? 300 : 3600);
        }

        return $this->memo[$origin] = $data;
    }

    private function download(string $origin): array
    {
        $url = $origin . '/robots.txt';

        try {
            for ($hop = 0; $hop <= 3; $hop++) {
                $this->guard->assertSafe($url);

                $resp = Http::withHeaders(['User-Agent' => PageFetcher::userAgent()])
                    ->timeout(10)
                    ->withOptions(['connect_timeout' => 5, 'allow_redirects' => false])
                    ->get($url);

                $status = $resp->status();

                if ($status >= 300 && $status < 400) {
                    $next = $this->guard->absolute($url, (string) $resp->header('Location'));
                    if (!$next) {
                        return ['status' => 'error'];
                    }
                    $url = $next;
                    continue;
                }
                if ($status >= 500) {
                    return ['status' => 'error'];
                }
                if ($status >= 400) {
                    return ['status' => 'none'];      // 没有 robots.txt，视为允许
                }

                return $this->parse(substr($resp->body(), 0, self::MAX_BYTES)) + ['status' => 'ok'];
            }
        } catch (Throwable $e) {
            // 落到下面返回 error
        }

        return ['status' => 'error'];
    }

    private function parse(string $body): array
    {
        $body   = ltrim($body, "\xEF\xBB\xBF");
        $groups = [];
        $cur    = null;
        $lastWasAgent = false;

        foreach (preg_split('/\r\n|\r|\n/', $body) as $line) {
            $line = trim(preg_replace('/#.*/', '', $line));
            if ($line === '' || !str_contains($line, ':')) {
                continue;
            }

            [$k, $v] = array_map('trim', explode(':', $line, 2));
            $k = strtolower($k);

            if ($k === 'user-agent') {
                if (!$lastWasAgent) {
                    $groups[] = ['agents' => [], 'rules' => [], 'delay' => null];
                    $cur = array_key_last($groups);
                }
                $groups[$cur]['agents'][] = strtolower($v);
                $lastWasAgent = true;
                continue;
            }

            $lastWasAgent = false;
            if ($cur === null) {
                continue;
            }

            if ($k === 'allow' || $k === 'disallow') {
                $groups[$cur]['rules'][] = [$k, $v];
            } elseif ($k === 'crawl-delay' && is_numeric($v)) {
                $groups[$cur]['delay'] = (float) $v;
            }
        }

        return ['groups' => $groups];
    }

    /** 选出适用于我们 UA 的规则：有专属分组用专属，否则用 * */
    private function rulesFor(array $robots): array
    {
        $ua       = strtolower(PageFetcher::userAgent());
        $specific = [];
        $star     = [];

        foreach ($robots['groups'] as $g) {
            foreach ($g['agents'] as $agent) {
                if ($agent === '*') {
                    $star[] = $g;
                    break;
                }
                if ($agent !== '' && str_contains($ua, $agent)) {
                    $specific[] = $g;
                    break;
                }
            }
        }

        $rules = [];
        $delay = null;
        foreach (($specific ?: $star) as $g) {
            $rules = array_merge($rules, $g['rules']);
            $delay = $g['delay'] ?? $delay;
        }

        return ['rules' => $rules, 'delay' => $delay];
    }

    /** 支持 robots 的 * 通配符和结尾 $ */
    private function pathMatches(string $pattern, string $target): bool
    {
        $anchored = str_ends_with($pattern, '$');
        if ($anchored) {
            $pattern = substr($pattern, 0, -1);
        }

        $regex = '~^' . str_replace('\*', '.*', preg_quote($pattern, '~')) . ($anchored ? '$' : '') . '~';

        return preg_match($regex, $target) === 1;
    }
}
