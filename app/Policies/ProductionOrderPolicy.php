<?php
namespace App\Policies;
use App\Models\ProductionOrder;
use App\Models\User;
class ProductionOrderPolicy {
 private function can(User $u,string $action):bool{return method_exists($u,'hasPermission')?$u->hasPermission('production.'.$action):true;}
 public function viewAny(User $u):bool{return $this->can($u,'view');}
 public function view(User $u,ProductionOrder $p):bool{return $this->can($u,'view');}
 public function create(User $u):bool{return $this->can($u,'create');}
 public function update(User $u,ProductionOrder $p):bool{return $this->can($u,'edit');}
 public function start(User $u,ProductionOrder $p):bool{return $this->can($u,'start');}
 public function progress(User $u,ProductionOrder $p):bool{return $this->can($u,'progress');}
 public function complete(User $u,ProductionOrder $p):bool{return $this->can($u,'complete');}
 public function cancel(User $u,ProductionOrder $p):bool{return $this->can($u,'cancel');}
 public function step(User $u,ProductionOrder $p):bool{return $this->can($u,'progress');}
}
