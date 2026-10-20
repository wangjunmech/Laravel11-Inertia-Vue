<?php

namespace App\Services\Crawler;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use App\Services\Crawler\CrawlException;
use App\Services\Crawler\UrlGuard;
use Illuminate\Support\Facades\Log;

class PageFetcher
{
    private const MAX_BYTES     = 5 * 1024 * 1024;
    private const MAX_REDIRECTS = 5;

    public function __construct(private UrlGuard $guard)
    {
    }

    /**
     * @return array{url:string,status:int,html:string}  url 是跟随重定向后的最终地址
     */
    public function fetch(string $url): array
    {
        $url = $this->guard->normalize($url);

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            $this->guard->assertSafe($url);          // 每一跳都要检查，防止重定向到内网
            $response = $this->request($url);
            $status   = $response->status();

            if ($status >= 300 && $status < 400) {
                $next = $this->guard->absolute($url, (string) $response->header('Location'));
                if (!$next) {
                    throw new CrawlException('重定向地址无效');
                }
                $url = $next;
                continue;
            }

            if ($status >= 400) {
                throw new CrawlException("目标页面返回 HTTP {$status}");
            }

            $type = (string) $response->header('Content-Type');
            if ($type !== '' && !preg_match('~text/html|application/xhtml~i', $type)) {
                throw new CrawlException('目标不是 HTML 页面（' . $type . '）');
            }

            $body = $response->body();
            if (strlen($body) > self::MAX_BYTES) {
                throw new CrawlException('页面内容过大');
            }

            return ['url' => $url, 'status' => $status, 'html' => $this->toUtf8($body, $type)];
        }

        throw new CrawlException('重定向次数过多');
    }

    private function request(string $url): Response
    {
        try {
            return Http::withHeaders([
                    'User-Agent'      => config('app.name', 'Laravel') . '-Crawler/1.0',
                    'Accept'          => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8',
                ])
                ->timeout(15)
                ->withOptions([
                    'connect_timeout'  => 5,
                    'allow_redirects'  => false,      // 手动处理重定向
                    'on_headers'       => function (ResponseInterface $r) {
                        if ((int) $r->getHeaderLine('Content-Length') > self::MAX_BYTES) {
                            throw new CrawlException('页面内容过大');
                        }
                    },
                ])
                ->retry(2, 300, null, false)          // 失败重试 2 次，不抛异常
                ->get($url);
        } catch (Throwable $e) {
            if ($e->getPrevious() instanceof CrawlException) {
                throw $e->getPrevious();
            }
            \Log::warning('crawler request failed', ['url' => $url, 'error' => $e->getMessage()]);
            throw new CrawlException(
                '请求失败，请检查网址是否可以访问'
                    . (config('app.debug') ? '（' . $e->getMessage() . '）' : '')
            );
        }
    }

    private function toUtf8(string $body, string $contentType): string
    {
        $charset = null;

        if (preg_match('/charset=["\']?([\w-]+)/i', $contentType, $m)) {
            $charset = $m[1];
        } elseif (preg_match('/<meta[^>]+charset=["\']?([\w-]+)/i', substr($body, 0, 4096), $m)) {
            $charset = $m[1];
        }

        if (!$charset) {
            $charset = mb_detect_encoding($body, ['UTF-8', 'GB18030', 'BIG5', 'SJIS', 'EUC-JP'], true) ?: 'UTF-8';
        }

        $charset = strtoupper($charset);
        if (in_array($charset, ['GB2312', 'GBK'], true)) {
            $charset = 'GB18030';                     // GB18030 是 GBK/GB2312 的超集
        }

        if ($charset !== 'UTF-8') {
            try {
                $body = mb_convert_encoding($body, 'UTF-8', $charset);
            } catch (Throwable $e) {
                // 未知编码，保持原样
            }
            // 避免解析器再按 meta 里的旧编码重新解释一遍
            $body = preg_replace('/(<meta[^>]+charset=["\']?)[\w-]+/i', '$1utf-8', $body, 1);
        }

        return $body;
    }
}
