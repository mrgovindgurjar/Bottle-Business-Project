<?php
namespace App\Policies;
use App\Models\Payment; use App\Models\User;
class PaymentPolicy {
 public function viewAny(User $u):bool{return $u->can('payments.view');} public function view(User $u,Payment $p):bool{return $u->can('payments.view') && ($u->can('payments.view_all') || $p->customer?->user_id===$u->id || $p->supplier_id===null);}
 public function create(User $u):bool{return $u->can('payments.create');} public function update(User $u,Payment $p):bool{return $u->can('payments.edit') && $p->status==='pending';} public function delete(User $u,Payment $p):bool{return $u->can('payments.delete');} public function allocate(User $u,Payment $p):bool{return $u->can('payments.allocate') && $p->status==='completed';} public function refund(User $u,Payment $p):bool{return $u->can('payments.refund') && in_array($p->status,['completed','partially_refunded','refunded'],true);} public function approve(User $u,Payment $p):bool{return $u->can('payments.approve') && $p->status==='pending';}
}
