<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class IssuePermissionSeeder extends Seeder
{
    public function run(): void {
        $permissions=[
            ['name'=>'View Issues','slug'=>'issues.view','module'=>'issues','action'=>'view'],
            ['name'=>'Create Issues','slug'=>'issues.create','module'=>'issues','action'=>'create'],
            ['name'=>'Edit Issues','slug'=>'issues.edit','module'=>'issues','action'=>'edit'],
            ['name'=>'Delete Issues','slug'=>'issues.delete','module'=>'issues','action'=>'delete'],
            ['name'=>'Change Issue Status','slug'=>'issues.status','module'=>'issues','action'=>'status'],
            ['name'=>'Assign Issues','slug'=>'issues.assign','module'=>'issues','action'=>'assign'],
            ['name'=>'Comment on Issues','slug'=>'issues.comment','module'=>'issues','action'=>'comment'],
            ['name'=>'Manage Issue Categories','slug'=>'issues.categories','module'=>'issues','action'=>'categories'],
        ];
        foreach($permissions as $p) DB::table('permissions')->updateOrInsert(['slug'=>$p['slug']],['name'=>$p['name'],'module'=>$p['module'],'action'=>$p['action'],'updated_at'=>now()]);
        $categories = [
            ['name'=>'Customer Complaint','slug'=>'customer-complaint'],
            ['name'=>'Delivery Issue','slug'=>'delivery-issue'],
            ['name'=>'Product / Bottle Issue','slug'=>'product-bottle-issue'],
            ['name'=>'Printing / Label Issue','slug'=>'printing-label-issue'],
            ['name'=>'Production Issue','slug'=>'production-issue'],
            ['name'=>'Payment / Billing Issue','slug'=>'payment-billing-issue'],
            ['name'=>'Quality Issue','slug'=>'quality-issue'],
            ['name'=>'Internal Operational Issue','slug'=>'internal-operational-issue'],
            ['name'=>'Other','slug'=>'other'],
        ];
        foreach ($categories as $index => $category) {
            DB::table('issue_categories')->updateOrInsert(
                ['slug'=>$category['slug']],
                ['name'=>$category['name'],'is_active'=>true,'sort_order'=>$index,'updated_at'=>now()]
            );
        }

        $ids=DB::table('permissions')->whereIn('slug',array_column($permissions,'slug'))->pluck('id');
        foreach(['super-admin','manager','hr'] as $roleSlug){$role=DB::table('roles')->where('slug',$roleSlug)->first(); if(!$role) continue; foreach($ids as $id) DB::table('role_permissions')->updateOrInsert(['role_id'=>$role->id,'permission_id'=>$id],['updated_at'=>now()]);}
    }
}
