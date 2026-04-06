<?php

namespace App\Policies;

use App\Models\ForumThread;
use App\Models\User;

class ForumThreadPolicy
{
    public function update(User $user, ForumThread $thread): bool
    {
        return $user->id === $thread->user_id;
    }

    public function delete(User $user, ForumThread $thread): bool
    {
        return $user->id === $thread->user_id || $user->isAdmin();
    }
}
