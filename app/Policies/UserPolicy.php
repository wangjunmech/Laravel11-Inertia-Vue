<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * modifyRole修改用户角色
     */
    public function modifyRole(User $user)
    {
        return $user->isAdmin();
    }
}
