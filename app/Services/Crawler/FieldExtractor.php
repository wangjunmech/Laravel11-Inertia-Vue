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
}
