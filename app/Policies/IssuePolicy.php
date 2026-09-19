<?php
namespace App\Policies;
use App\Models\Issue;
class IssuePolicy
{
    private function allow($user,string $permission): bool { return method_exists($user,'hasPermission') ? $user->hasPermission($permission) : false; }
    public function viewAny($user): bool { return $this->allow($user,'issues.view'); }
    public function view($user, Issue $issue): bool { return $this->allow($user,'issues.view'); }
    public function create($user): bool { return $this->allow($user,'issues.create'); }
    public function update($user, Issue $issue): bool { return $this->allow($user,'issues.edit'); }
    public function delete($user, Issue $issue): bool { return $this->allow($user,'issues.delete'); }
    public function changeStatus($user, Issue $issue): bool { return $this->allow($user,'issues.status'); }
    public function assign($user, Issue $issue): bool { return $this->allow($user,'issues.assign'); }
    public function comment($user, Issue $issue): bool { return $this->allow($user,'issues.comment'); }
}
