<?php

namespace App\Services\Crawler;

use RuntimeException;

/**
 * 只有这个异常的 message 会原样展示给用户，
 * 其他异常一律给通用提示，避免泄露内部信息。
 */
class CrawlException extends RuntimeException
{
}
