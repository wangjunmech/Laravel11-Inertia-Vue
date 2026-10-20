<?php

namespace App\Http\Requests;

use App\Services\Crawler\CrawlException;
use App\Services\Crawler\UrlGuard;
use Illuminate\Foundation\Http\FormRequest;

class PreviewCrawlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 已被 auth 中间件保护
    }

    public function rules(): array
    {
        return [
            'url'         => ['required', 'string', 'max:2048', 'starts_with:http://,https://'],
            'url_pattern' => ['nullable', 'string', 'max:500', function ($attr, $value, $fail) {
                try {
                    app(UrlGuard::class)->compilePattern($value);
                } catch (CrawlException $e) {
                    $fail($e->getMessage());
                }
            }],

            'fields'                   => ['required', 'array', 'min:1', 'max:30'],
            'fields.*.name'            => ['required', 'string', 'max:50', 'distinct', 'regex:/^[\p{L}_][\p{L}\p{N}_]*$/u'],
            'fields.*.selector'        => ['required', 'string', 'max:500'],
            'fields.*.type'            => ['required', 'in:text,html,attr'],
            'fields.*.attr'            => ['required_if:fields.*.type,attr', 'nullable', 'string', 'max:50'],
            'fields.*.multiple'        => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'url'                => '测试网址',
            'url_pattern'        => 'URL 正则',
            'fields'             => '抓取字段',
            'fields.*.name'      => '字段名',
            'fields.*.selector'  => '选择器',
            'fields.*.type'      => '提取类型',
            'fields.*.attr'      => '属性名',
        ];
    }
}
