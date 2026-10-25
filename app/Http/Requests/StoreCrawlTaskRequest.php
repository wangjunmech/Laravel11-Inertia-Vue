<?php

namespace App\Http\Requests;

use App\Services\Crawler\CrawlException;
use App\Services\Crawler\UrlGuard;
use Illuminate\Foundation\Http\FormRequest;

class StoreCrawlTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\CrawlTask::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],

            // 可选：只跟进匹配这个规则（或 URL 匹配正则）的链接，其余直接丢弃
            'follow_pattern' => ['nullable', 'string', 'max:500', function ($attr, $value, $fail) {
                try {
                    app(UrlGuard::class)->compilePattern($value);
                } catch (CrawlException $e) {
                    $fail($e->getMessage());
                }
            }],

            'start_urls'   => ['required', 'array', 'min:1', 'max:20'],
            'start_urls.*' => ['required', 'string', 'max:2048', 'starts_with:http://,https://', 'distinct', function ($attr, $value, $fail) {
                try {
                    app(UrlGuard::class)->assertSafe($value);
                } catch (CrawlException $e) {
                    $fail($e->getMessage());
                }
            }],

            'url_pattern' => ['required', 'string', 'max:500', function ($attr, $value, $fail) {
                try {
                    app(UrlGuard::class)->compilePattern($value);
                } catch (CrawlException $e) {
                    $fail($e->getMessage());
                }
            }],

            'fields'            => ['required', 'array', 'min:1', 'max:30'],
            // url 是结果里固定存在的键，字段名不能占用
            'fields.*.name'     => ['required', 'string', 'max:50', 'distinct', 'not_in:url', 'regex:/^[\p{L}_][\p{L}\p{N}_]*$/u'],
            'fields.*.selector' => ['required', 'string', 'max:500'],
            'fields.*.type'     => ['required', 'in:text,html,attr'],
            'fields.*.attr'     => ['required_if:fields.*.type,attr', 'nullable', 'string', 'max:50'],
            'fields.*.multiple' => ['nullable', 'boolean'],

            'max_pages'      => ['required', 'integer', 'between:1,1000'],
            'max_depth'      => ['required', 'integer', 'between:0,5'],
            'delay_ms'       => ['required', 'integer', 'between:200,10000'],
            'concurrency'    => ['required', 'integer', 'in:1,2,3,5'],
            'respect_robots' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'               => '任务名称',
            'start_urls'         => '起始网址',
            'start_urls.*'       => '起始网址',
            'url_pattern'        => 'URL 正则',
            'follow_pattern'     => '跟进链接规则',
            'fields'             => '抓取字段',
            'fields.*.name'      => '字段名',
            'fields.*.selector'  => '选择器',
            'fields.*.type'      => '提取类型',
            'fields.*.attr'      => '属性名',
            'max_pages'          => '最大页数',
            'max_depth'          => '最大深度',
            'delay_ms'           => '请求间隔',
            'concurrency'        => '并发数',
            'respect_robots'     => '遵守 robots.txt',
        ];
    }
}
