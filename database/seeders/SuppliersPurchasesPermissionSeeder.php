<?php
namespace Database\Seeders;
use App\Models\Permission; use App\Models\Role; use Illuminate\Database\Seeder;
class SuppliersPurchasesPermissionSeeder extends Seeder
{
 public function run(): void {
  $map=['suppliers'=>['view','create','edit','delete'],'purchases'=>['view','create','edit','delete','receive','pay','cancel','export']]; $p=[];
  foreach($map as $m=>$actions) foreach($actions as $a){$x=Permission::updateOrCreate(['slug'=>"$m.$a"],['name'=>ucwords($m).' '.ucwords($a),'module'=>$m,'action'=>$a]);$p[$x->slug]=$x;}
  $grant=function($role,$slugs)use($p){$r=Role::where('slug',$role)->first();if($r)$r->permissions()->syncWithoutDetaching(array_map(fn($s)=>$p[$s]->id,$slugs));};
  $all=array_keys($p); $grant('super-admin',$all); $grant('manager',$all);
  $grant('sales',['suppliers.view','purchases.view','purchases.create','purchases.edit','purchases.export']);
  $grant('production',['suppliers.view','purchases.view','purchases.receive']);
  $grant('accountant',['suppliers.view','suppliers.create','suppliers.edit','purchases.view','purchases.pay','purchases.export']);
 }
}
