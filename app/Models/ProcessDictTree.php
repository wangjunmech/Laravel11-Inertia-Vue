<?php

namespace App\Models;

use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;

class ProcessDictTree extends Model
{
    use NodeTrait;

    protected $table = 'process_dict_tree';

    protected $fillable = [
        'type',
        'name',
        'code',
        'parent_id',
        'sort',
        'is_active',
    ];

    /**
     * NestedSet 约定，父字段名
     */
    public function getParentIdName()
    {
        return 'parent_id';
    }
    protected function getScopeAttributes()
    {
        return ['type'];
    }
}
