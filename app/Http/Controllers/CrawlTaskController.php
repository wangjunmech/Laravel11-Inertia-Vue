<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewCrawlRequest;
use App\Services\Crawler\CrawlException;
use App\Services\Crawler\FieldExtractor;
use App\Services\Crawler\PageFetcher;
use App\Services\Crawler\UrlGuard;
use Inertia\Inertia;
use Throwable;

class CrawlTaskController extends Controller
{
    public function create()
    {
        return Inertia::render('Crawler/Create');
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
}
