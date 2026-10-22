<?php

namespace App\Services\Crawler;

use QL\QueryList;
use Throwable;

class FieldExtractor
{
    /**
     * @param  array<int, array{name:string,selector:string,type?:string,attr?:?string,multiple?:bool}> $fields
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function extract(string $html, array $fields): array
    {
        if (trim($html) === '') {
            throw new CrawlException('页面内容为空');
        }

        $ql   = QueryList::html($html);
        $data = [];
        $errs = [];

        foreach ($fields as $f) {
            $name = $f['name'];
            $type = $f['type'] ?? 'text';
            $attr = $f['attr'] ?? null;

            try {
                $values = $ql->find($f['selector'])
                    ->map(fn ($el) => match ($type) {
                        'html'  => trim((string) $el->html()),
                        'attr'  => $el->attr($attr),
                        default => trim((string) $el->text()),
                    })
                    ->all();

                $data[$name] = !empty($f['multiple']) ? array_values($values) : ($values[0] ?? null);

                if ($values === []) {
                    $errs[$name] = '选择器没有匹配到任何元素';
                }
            } catch (Throwable $e) {
                $data[$name] = null;
                $errs[$name] = '选择器无效或提取失败';
            }
        }

        return ['data' => $data, 'errors' => $errs];
    }

    /**
     * 页面里所有 <a href> 的原始值（相对路径由 UrlGuard::absolute 处理）。
     *
     * 这里用正则而不是完整解析 DOM：论坛页面很大，建 DOM 比正则慢得多，
     * 而且大部分页面只是用来找链接，根本不需要提取内容。
     *
     * @return string[]
     */
    public function links(string $html): array
    {
        if ($html === '' || !preg_match_all(
            '~<a\s(?:[^>]*?\s)?href\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))~i',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }

        $links = [];
        foreach ($matches as $m) {
            $href = $m[1] ?? '';
            if ($href === '') {
                $href = $m[2] ?? '';
            }
            if ($href === '') {
                $href = $m[3] ?? '';
            }

            $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($href !== '') {
                $links[] = $href;
            }
        }

        return array_values(array_unique($links));
    }
}
