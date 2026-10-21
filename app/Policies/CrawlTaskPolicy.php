<?php

namespace App\Policies;

use App\Models\CrawlTask;
use App\Models\User;

class CrawlTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, CrawlTask $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function cancel(User $user, CrawlTask $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function download(User $user, CrawlTask $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function delete(User $user, CrawlTask $task): bool
    {
        return $task->user_id === $user->id;
    }
}
