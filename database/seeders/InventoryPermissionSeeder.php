<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class InventoryPermissionSeeder extends Seeder {
 public function run(): void {
  $items=[
   ['inventory.view','Inventory','view'],['inventory.create','Inventory','create'],['inventory.edit','Inventory','edit'],
   ['inventory.delete','Inventory','delete'],['inventory.adjust','Inventory','adjust'],['inventory.reserve','Inventory','reserve'],
   ['inventory.transfer','Inventory','transfer'],['inventory.export','Inventory','export'],
  ];
  foreach($items as [$slug,$module,$action]) DB::table('permissions')->updateOrInsert(['slug'=>$slug],['name'=>ucwords(str_replace(['.','_'],' ',$slug)),'module'=>$module,'action'=>$action,'updated_at'=>now(),'created_at'=>now()]);
  $ids=DB::table('permissions')->whereIn('slug',array_column($items,0))->pluck('id');
  foreach(['super-admin','manager','production'] as $roleSlug){$role=DB::table('roles')->where('slug',$roleSlug)->first(); if($role) foreach($ids as $id) DB::table('role_permissions')->updateOrInsert(['role_id'=>$role->id,'permission_id'=>$id],['created_at'=>now(),'updated_at'=>now()]);}
 }
}
