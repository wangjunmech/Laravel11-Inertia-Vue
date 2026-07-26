<?php

namespace App\Http\Controllers;

use App\Models\BomItem;
use App\Models\BomVersion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BomController extends Controller
{
    // 打开BOM编辑页面
    public function edit(Request $request, $versionId)
    {
        $version = BomVersion::findOrFail($versionId);
        $tree = BomItem::getTree($versionId);
        // dd($tree->toArray());
        return Inertia::render('Bom/Edit', [
            'bomTree' => $tree,
            'bomVersion' => $version,
            'product' => $version->product,
            'materialList' => \App\Models\Material::all()
        ]);
    }

    // 保存编辑后的树形BOM
    public function save(Request $request, $versionId)
    {
        // 兜底：无数据默认空数组，杜绝null报错
        $treeData = $request->input('tree', []);
        $version = BomVersion::findOrFail($versionId);

        // 清空原有明细
        BomItem::where('bom_version_id', $versionId)->delete();

        // 递归入库树形数据
        $this->saveTreeRecursive($treeData, $versionId, 0);

        // 保存完成后测试打印数据库数据，验证是否存入
        // dd(BomItem::where('bom_version_id', $versionId)->get()->toArray());

        return back()->with('success', 'BOM保存成功');
    }

    // 递归插入节点
    // 递归插入节点
    protected function saveTreeRecursive($nodes, $vid, $pid)
    {
        foreach ($nodes as $node) {
            // 确保 material_id 存在且不为空，避免保存 null
            $materialId = $node['material_id'] ?? null;

            $item = BomItem::create([
                'bom_version_id' => $vid,
                'material_id' => $materialId,
                'parent_id' => $pid,
                'qty' => $node['qty'] ?? 1,
                'loss_rate' => $node['loss_rate'] ?? 0,
                'sort' => $node['sort'] ?? 0,
                'subtotal' => $node['subtotal'] ?? 0
            ]);

            // 关键：如果该节点包含子节点，递归保存，并将当前刚创建的 $item->id 作为子节点的 parent_id 传下去
            if (!empty($node['children']) && is_array($node['children'])) {
                $this->saveTreeRecursive($node['children'], $vid, $item->id);
            }
        }
    }

    // 复制BOM新版本
    public function copyVersion(Request $request, $versionId)
    {
        $oldVersion = BomVersion::findOrFail($versionId);
        // 生成新版本号
        $newVerNo = 'V' . (floatval(ltrim($oldVersion->version_no, 'V')) + 0.1);
        // 创建版本记录
        $newVersion = BomVersion::create([
            'product_id' => $oldVersion->product_id,
            'version_no' => $newVerNo,
            'version_note' => '复制版本生成',
            'is_default' => false
        ]);
        // 复制所有BOM明细
        $this->copyItemsRecursive($oldVersion->items()->where('parent_id', 0)->get(), $newVersion->id, 0);

        return redirect()->route('bom.edit', $newVersion->id);
    }

    protected function copyItemsRecursive($nodes, $newVid, $newPid)
    {
        foreach ($nodes as $node) {
            $newItem = BomItem::create([
                'bom_version_id' => $newVid,
                'material_id' => $node->material_id,
                'parent_id' => $newPid,
                'qty' => $node->qty,
                'loss_rate' => $node->loss_rate,
                'sort' => $node->sort,
                'subtotal' => $node->subtotal
            ]);
            if ($node->children->count()) {
                $this->copyItemsRecursive($node->children, $newVid, $newItem->id);
            }
        }
    }
}
