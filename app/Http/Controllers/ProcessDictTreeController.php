<?php

namespace App\Http\Controllers;

use App\Models\ProcessDictTree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessDictTreeController extends Controller
{
    /**
     * 获取指定类型字典树（返回扁平数组，直接喂给CascaderMenu modelValue）
     * GET /dict-tree/{type}
     */
    public function getTree(string $type)
    {
        $list = ProcessDictTree::where('type', $type)
            ->where('is_active', true)
            ->orderBy('_lft')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'area' => $item->name,
                    'pid' => $item->parent_id ?? 0,
                ];
            });

        return response()->json($list);
    }

    /**
     * 全量同步字典树
     * POST /dict-tree/sync
     * Request: { type: "material", items: [ {id, area, pid}, ... ] }
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:material,process,surface_process',
            'items' => 'present|array',
            'items.*.id' => 'required',          // 前端每个节点都必须有 id（新节点用临时 id）
            'items.*.area' => 'required|string',
            'items.*.pid' => 'required|integer',
        ]);

        $type = $validated['type'];
        $items = $validated['items'];

        try {
            DB::transaction(function () use ($type, $items) {
                $existingIds = ProcessDictTree::where('type', $type)->pluck('id')->flip();

                // 1. 删除：库里有、前端没传的
                $keepIds = collect($items)
                    ->pluck('id')
                    ->filter(fn($id) => is_numeric($id) && $existingIds->has((int) $id))
                    ->map(fn($id) => (int) $id);

                $toDelete = $existingIds->keys()->diff($keepIds)->values()->all();
                if ($toDelete) {
                    ProcessDictTree::where('type', $type)->whereIn('id', $toDelete)->get()->each->delete();
                }

                // 2. 按"父先子后"保存，维护 前端id => 数据库id 的映射
                $idMap = [0 => null];      // pid=0 表示根
                $pending = $items;

                while ($pending) {
                    $progress = false;

                    foreach ($pending as $k => $row) {
                        $pid = (int) $row['pid'];
                        if (!array_key_exists($pid, $idMap)) {
                            continue;      // 父节点还没处理，下一轮再来
                        }

                        $payload = [
                            'type' => $type,
                            'name' => $row['area'],
                            'parent_id' => $idMap[$pid],
                            'is_active' => true,
                        ];

                        $frontId = $row['id'];
                        $isExisting = is_numeric($frontId) && $existingIds->has((int) $frontId);

                        if ($isExisting) {
                            // 每次重新取，避免 nestedset 的 lft/rgt 过期
                            $model = ProcessDictTree::where('type', $type)->findOrFail((int) $frontId);
                            $model->fill($payload)->save();
                        } else {
                            $model = ProcessDictTree::create($payload);
                        }

                        $idMap[$frontId] = $model->id;
                        unset($pending[$k]);
                        $progress = true;
                    }

                    if (!$progress) {
                        throw new \RuntimeException('存在 pid 指向不存在节点或循环引用的数据');
                    }
                }

                // ProcessDictTree::fixTree();   
                // 若模型有 scope，改成 
                ProcessDictTree::scoped(['type' => $type])->fixTree();
            });

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'msg' => $e->getMessage()], 500);
        }
    }
}
