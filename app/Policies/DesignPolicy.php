<?php

namespace App\Policies;

use App\Models\DesignRequest;
use App\Models\User;

class DesignPolicy
{
    private function can(User $user, string $action): bool
    {
        return $user->roles()
            ->where('is_active', true)
            ->whereHas('permissions', fn ($q) => $q->where('slug', "designs.$action"))
            ->exists();
    }

    public function viewAny(User $user): bool { return $this->can($user, 'view'); }
    public function view(User $user, DesignRequest $design): bool { return $this->can($user, 'view'); }
    public function create(User $user): bool { return $this->can($user, 'create'); }
    public function update(User $user, DesignRequest $design): bool { return $this->can($user, 'edit'); }
    public function approve(User $user, DesignRequest $design): bool { return $this->can($user, 'approve'); }
    public function comment(User $user, DesignRequest $design): bool { return $this->can($user, 'comment'); }
}
