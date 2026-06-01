<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //添加一对多关系，用户可以有多个列表
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    //检查用户是否为管理员，然后到Admin自定义中间件中使用
    public function isAdmin() {
        // return $this->role === 'admin';//还可以追加判断条件
        return $this->role === 'admin' && $this->email ==='admin@test.com';
    }
    //用户搜索过滤
    public function scopeFilter( $query, array $filters) {
        //如果接收到search参数，查询name和email字段是否有相似的结果
        if($filters['search'] ?? false){
            // dd('User search.....');
            $query->where(function($q){
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%');
            });
        }
        //如果接收到user_role参数，查询role字段是否有相似的结果
        if($filters['user_role'] ?? false){
            // dd('User role search.....');
            $query->where(function($q){
                $q->where('role', request('user_role') );
            });
        }
    }
}
