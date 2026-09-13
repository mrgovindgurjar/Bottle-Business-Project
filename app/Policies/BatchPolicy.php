<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    private function can(User $user, string $action): bool
    {
        return method_exists($user, 'hasPermission') ? $user->hasPermission('batches.' . $action) : true;
    }

    public function viewAny(User $user): bool { return $this->can($user, 'view'); }
    public function view(User $user, Batch $batch): bool { return $this->can($user, 'view'); }
    public function create(User $user): bool { return $this->can($user, 'create'); }
    public function update(User $user, Batch $batch): bool { return $this->can($user, 'edit'); }
    public function delete(User $user, Batch $batch): bool { return $this->can($user, 'delete'); }
    public function release(User $user, Batch $batch): bool { return $this->can($user, 'release'); }
    public function block(User $user, Batch $batch): bool { return $this->can($user, 'block'); }
    public function allocate(User $user, Batch $batch): bool { return $this->can($user, 'allocate'); }
    public function export(User $user): bool { return $this->can($user, 'export'); }
}
