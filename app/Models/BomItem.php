<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'bom_version_id',
        'material_id',
        'parent_id',
        'qty',
        'loss_rate',
        'sort',
        'subtotal'
    ];

    // 子节点无限递归
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->orderBy('sort');
    }

    // 关联物料档案
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // 递归组装完整树形结构
    public static function getTree(int $versionId)
    {
        // 先查询所有顶层根节点 parent_id=0
        $rootNodes = self::with('material')
            ->where('bom_version_id', $versionId)
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        // 匿名递归函数：无限遍历所有层级，每一级都加载物料信息
        $recursiveLoadChildren = function ($items) use (&$recursiveLoadChildren) {
            foreach ($items as $item) {
                // 查询当前节点的直接子节点，同时预加载子节点物料
                $item->children = self::with('material')
                    ->where('parent_id', $item->id)
                    ->orderBy('sort')
                    ->get();

                // 子节点不为空，继续递归加载下级
                if ($item->children->count() > 0) {
                    $recursiveLoadChildren($item->children);
                }
            }
            return $items;
        };

        return $recursiveLoadChildren($rootNodes);
    }
}
