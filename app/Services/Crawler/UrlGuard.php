<?php

namespace App\Services\Crawler;

use Illuminate\Support\Facades\Cache;

class UrlGuard
{
    private const ALLOWED_PORTS = [80, 443, 8080, 8443];

    /** @var array<string, string[]> 同一个实例内的 DNS 结果缓存 */
    private array $dnsMemo = [];

    /**
     * SSRF 防护：只允许 http/https、常见端口，且域名不能解析到内网/保留地址。
     */
    public function assertSafe(string $url): void
    {
        $p = parse_url($url);

        if ($p === false || empty($p['scheme']) || empty($p['host'])) {
            throw new CrawlException('URL 格式不正确');
        }
        if (!in_array(strtolower($p['scheme']), ['http', 'https'], true)) {
            throw new CrawlException('只支持 http / https 链接');
        }
        if (isset($p['port']) && !in_array($p['port'], self::ALLOWED_PORTS, true)) {
            throw new CrawlException('不允许访问该端口');
        }
        if (isset($p['user']) || isset($p['pass'])) {
            throw new CrawlException('URL 中不能包含账号密码');
        }

        $host = trim($p['host'], '[]');
        $ips  = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->resolve($host);

        if (!$ips) {
            throw new CrawlException('无法解析域名：' . $host);
        }

        foreach ($ips as $ip) {
            if (stripos($ip, '::ffff:') === 0) {      // IPv4 映射的 IPv6
                $ip = substr($ip, 7);
            }
            $ok = filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            if ($ok === false) {
                throw new CrawlException('不允许访问内网或保留地址');
            }
        }
    }

    /** 去掉 #fragment、解析 ./ ../、统一大小写和默认端口 */
    public function normalize(string $url): string
    {
        $url = preg_replace('/#.*$/', '', trim($url));
        $p   = parse_url($url);

        if ($p === false || empty($p['host'])) {
            return $url;
        }

        $out = [];
        foreach (explode('/', $p['path'] ?? '/') as $seg) {
            if ($seg === '.') {
                continue;
            }
            if ($seg === '..') {
                if (count($out) > 1) {
                    array_pop($out);
                }
                continue;
            }
            $out[] = $seg;
        }
        $path = implode('/', $out);
        if ($path === '') {
            $path = '/';
        }

        $scheme = strtolower($p['scheme'] ?? 'http');
        $port   = $p['port'] ?? null;
        if (($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443)) {
            $port = null;
        }

        return $scheme . '://' . strtolower($p['host'])
            . ($port ? ':' . $port : '')
            . $path
            . (isset($p['query']) ? '?' . $p['query'] : '');
    }

    /** 把页面里的 href 转成绝对 URL；mailto / javascript / 纯锚点返回 null */
    public function absolute(string $base, string $href): ?string
    {
        $href = trim($href);
        if ($href === '' || $href[0] === '#' || preg_match('/^(javascript|mailto|tel|data):/i', $href)) {
            return null;
        }
        if (preg_match('~^https?://~i', $href)) {
            return $this->normalize($href);
        }

        $b = parse_url($base);
        if ($b === false || empty($b['host'])) {
            return null;
        }
        $scheme = $b['scheme'] ?? 'http';
        $origin = $scheme . '://' . $b['host'] . (isset($b['port']) ? ':' . $b['port'] : '');

        if (str_starts_with($href, '//')) {
            return $this->normalize($scheme . ':' . $href);
        }
        if ($href[0] === '/') {
            return $this->normalize($origin . $href);
        }

        $path = $b['path'] ?? '/';
        if ($href[0] === '?') {
            return $this->normalize($origin . $path . $href);
        }

        $dir = substr($path, 0, strrpos($path, '/') + 1);
        return $this->normalize($origin . $dir . $href);
    }

    public function sameHost(string $a, string $b): bool
    {
        return strtolower((string) parse_url($a, PHP_URL_HOST)) === strtolower((string) parse_url($b, PHP_URL_HOST));
    }

    /** 用户输入的是不带分隔符的正则，由后端统一包装并校验 */
    public function compilePattern(string $pattern): string
    {
        $regex = '~' . str_replace('~', '\~', $pattern) . '~u';

        if (@preg_match($regex, '') === false) {
            throw new CrawlException('URL 正则表达式不合法');
        }
        return $regex;
    }

    public function matches(string $pattern, string $url): bool
    {
        $regex  = $this->compilePattern($pattern);
        $result = @preg_match($regex, $url);

        if ($result === false) {
            throw new CrawlException('正则匹配出错（可能过于复杂）：' . preg_last_error_msg());
        }
        return $result === 1;
    }

    /** DNS 解析结果缓存 2 分钟，避免每个请求前都查一次（Windows 上可能要几百毫秒） */
    private function resolve(string $host): array
    {
        if (isset($this->dnsMemo[$host])) {
            return $this->dnsMemo[$host];
        }

        $key = 'crawler:dns:' . md5($host);
        $ips = Cache::get($key);

        if (!is_array($ips) || $ips === []) {
            $ips = [];
            foreach ((@dns_get_record($host, DNS_A | DNS_AAAA) ?: []) as $r) {
                $ips[] = $r['ip'] ?? $r['ipv6'] ?? null;
            }
            $ips = array_values(array_filter($ips));

            if ($ips === []) {
                $ips = array_values(array_filter((array) (@gethostbynamel($host) ?: [])));
            }
            if ($ips !== []) {
                Cache::put($key, $ips, 120);
            }
        }

        return $ips === [] ? [] : ($this->dnsMemo[$host] = $ips);
    }
}
