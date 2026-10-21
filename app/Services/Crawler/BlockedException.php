<?php

namespace App\Services\Crawler;

/** 目标站点返回了验证码 / 拦截页面 */
class BlockedException extends CrawlException
{
}
