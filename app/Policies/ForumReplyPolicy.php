<?php

namespace App\Policies;

use App\Models\ForumReply;
use App\Models\User;

class ForumReplyPolicy
{
    public function update(User $user, ForumReply $reply): bool
    {
        return $user->id === $reply->user_id;
    }

    public function delete(User $user, ForumReply $reply): bool
    {
        return $user->id === $reply->user_id || $user->isAdmin();
    }
}
