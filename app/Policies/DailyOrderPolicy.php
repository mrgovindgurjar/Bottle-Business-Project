<?php
namespace App\Policies;
use App\Models\DailyOrder; use App\Models\User;
class DailyOrderPolicy {private function p(User $u,string $p):bool{return $u->hasPermission($p);} public function viewAny(User $u):bool{return $this->p($u,'daily_orders.view');} public function view(User $u,DailyOrder $m):bool{return $this->p($u,'daily_orders.view');} public function create(User $u):bool{return $this->p($u,'daily_orders.create');} public function update(User $u,DailyOrder $m):bool{return $m->status==='pending'&&$this->p($u,'daily_orders.edit');} public function approve(User $u,DailyOrder $m):bool{return $m->status==='pending'&&$this->p($u,'daily_orders.approve');} public function cancel(User $u,DailyOrder $m):bool{return in_array($m->status,['pending'],true)&&$this->p($u,'daily_orders.edit');}}
