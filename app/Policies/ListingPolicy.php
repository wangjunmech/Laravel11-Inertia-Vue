<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ListingPolicy
{
//
    public function before(User $user){
        //before是一个特殊的函数放到Policy所有函数的最前面，在这里判断一下用户是否为管理员
        if($user->isAdmin()){
            return true;
        }
        return null;
    }


    public function view(?User $user, Listing $listing): bool
    {
        // dd($listing->user->role !== 'suspended'); //地址栏输入listingID如果用户的角色为suspended则返回false,到ListingConstroller的Show方法中使用Gate来进行控制
        return $listing->user->role !== 'suspended' && $listing->approved;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    { 
        //用户的role为'suspended'则到控制器中对应方法加上Gate::authorize控制不能创建Listing
        return $user->role !== 'suspended';
    }

    
    public function modify(User $user, Listing $listing): bool
    {
        //如果listing属于该用户则可修改
        return $user->role !=='suspended' && $user->id === $listing->user_id;
    }

    
    public function approve(User $user, Listing $listing): bool
    {
        //如果用户为admin才返回
        return $user->isAdmin();
    }
}
