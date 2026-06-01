<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    /** @use HasFactory<\Database\Factories\ListingFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'desc',
        'tags',
        'email',
        'link',
        'image',
        'approved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeFilterUser($query,array $filters) {
        // dd($filters);
        // if ($filters['search']) {// 这种写法会在$filters['search']不存在时抛出错误，所以改为下面的写法
        // // -----------------orWhere这样使用可能会有搜索不正确/-----------------//
        //         if ($filters['search'] ?? false) {
        //             $query
        //                 ->where('title', 'like', '%' . request('search') . '%')
        //                 ->orWhere('desc', 'like', '%' . request('search') . '%');
        //         }

        // -----------------orWhere这需要优化为闭包的方式-----------------//
        // 下面各条件$filters[]中的参数与控制器中使用filter时的参数要对应一致

        if ($filters['search'] ?? false) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . request('search') . '%')
                    ->orWhere('desc', 'like', '%' . request('search') . '%');
            });
        }//request('search')是从请求中获取搜索关键词的值，模糊搜索，这样就可以在搜索时同时匹配标题title和描述字段desc，提供更全面的搜索结果

        if ($filters['user_id'] ?? false) {
            $query->where('user_id', request('user_id'));
        }//搜索用户ID时，直接在查询中添加一个条件，要求user_id字段的值必须与请求中的user_id参数相匹配，这样就可以过滤出属于特定用户的列表数据
        if ($filters['tag'] ?? false) {
            $query->where('tags', 'like', '%' . request('tag') . '%');
        } //搜索标签时,传入参数有tag时，使用模糊搜索的方式，在tags因为有的一条记录里面有两个或多个标签

        if ($filters['disapproved'] ?? false) {
            $query->where('approved', false);
        } //搜索Listing,如果有传入disapproved参数，查询approved字段值为false的listings

        // $query->where('approved', true);
    }
}
