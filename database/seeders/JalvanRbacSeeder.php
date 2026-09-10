<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JalvanRbacSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['Super Admin','super-admin','Full system access'],
            ['Manager','manager','Business operations and approvals'],
            ['Sales','sales','Leads, customers, quotations and designs'],
            ['Production','production','Design production, batches and inventory'],
            ['Delivery','delivery','Dispatch and delivery operations'],
            ['Accountant','accountant','Payments, invoices and finance'],
            ['HR','hr','Staff and attendance'],
            ['Customer','customer','Customer portal access'],
        ];
        foreach ($roles as [$name,$slug,$description]) {
            DB::table('roles')->updateOrInsert(['slug'=>$slug], ['name'=>$name,'description'=>$description,'is_active'=>true,'updated_at'=>now(),'created_at'=>now()]);
        }

        $modules = [
            'dashboard'=>['view'], 'leads'=>['view','create','edit','delete','export','approve'],
            'customers'=>['view','create','edit','delete','export'], 'products'=>['view','create','edit','delete','export'],
            'pricing'=>['view','create','edit','delete','export'], 'designs'=>['view','create','edit','delete','approve','comment','export'],
            'quotations'=>['view','create','edit','delete','approve','export'], 'orders'=>['view','create','edit','delete','approve','export'],
            'recurring_plans'=>['view','create','edit','delete','approve'], 'daily_orders'=>['view','create','edit','approve'],
            'deliveries'=>['view','create','edit','approve','export'], 'batches'=>['view','create','edit','delete','export'],
            'inventory'=>['view','create','edit','export'], 'suppliers'=>['view','create','edit','delete','export'],
            'purchases'=>['view','create','edit','delete','approve','export'], 'payments'=>['view','create','edit','delete','approve','export'],
            'invoices'=>['view','create','edit','delete','export'], 'expenses'=>['view','create','edit','delete','approve','export'],
            'staff'=>['view','create','edit','delete','export'], 'attendance'=>['view','create','edit','export'],
            'issues'=>['view','create','edit','delete','close_issue','export'], 'notifications'=>['view','create','edit'],
            'reports'=>['view','export'], 'users'=>['view','create','edit','delete','export'],
            'roles'=>['view','create','edit','delete'], 'settings'=>['view','edit'], 'audit_logs'=>['view','export'],
        ];
        $permissionIds=[];
        foreach ($modules as $module=>$actions) foreach ($actions as $action) {
            $slug="$module.$action";
            DB::table('permissions')->updateOrInsert(['slug'=>$slug], ['name'=>ucwords(str_replace('_',' ',$module)).' '.ucfirst(str_replace('_',' ',$action)), 'module'=>$module, 'action'=>$action, 'updated_at'=>now(),'created_at'=>now()]);
            $permissionIds[] = DB::table('permissions')->where('slug',$slug)->value('id');
        }

        $allRole = DB::table('roles')->where('slug','super-admin')->value('id');
        foreach ($permissionIds as $pid) DB::table('role_permissions')->updateOrInsert(['role_id'=>$allRole,'permission_id'=>$pid], ['updated_at'=>now(),'created_at'=>now()]);

        $map = [
            'manager'=>['dashboard','leads','customers','products','pricing','designs','quotations','orders','recurring_plans','daily_orders','deliveries','batches','inventory','suppliers','purchases','payments','invoices','expenses','reports','issues'],
            'sales'=>['dashboard','leads','customers','products','pricing','designs','quotations','orders','recurring_plans','daily_orders','issues'],
            'production'=>['dashboard','customers','products','pricing','designs','orders','daily_orders','batches','inventory','purchases','suppliers','issues'],
            'delivery'=>['dashboard','customers','orders','daily_orders','deliveries','batches','issues'],
            'accountant'=>['dashboard','customers','orders','invoices','payments','expenses','purchases','reports'],
            'hr'=>['dashboard','staff','attendance','issues'],
            'customer'=>['dashboard','orders','daily_orders','deliveries','designs','invoices','payments','issues'],
        ];
        foreach ($map as $roleSlug=>$allowedModules) {
            $rid=DB::table('roles')->where('slug',$roleSlug)->value('id');
            $ids=DB::table('permissions')->whereIn('module',$allowedModules)->pluck('id');
            foreach ($ids as $pid) DB::table('role_permissions')->updateOrInsert(['role_id'=>$rid,'permission_id'=>$pid], ['updated_at'=>now(),'created_at'=>now()]);
        }
    }
}
