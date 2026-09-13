<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions=[
            ['deliveries.view','Delivery','view'],['deliveries.create','Delivery','create'],['deliveries.edit','Delivery','edit'],
            ['deliveries.delete','Delivery','delete'],['deliveries.dispatch','Delivery','dispatch'],['deliveries.deliver','Delivery','deliver'],
            ['deliveries.fail','Delivery','fail'],['deliveries.cancel','Delivery','cancel'],['deliveries.export','Delivery','export'],
        ];
        foreach($permissions as [$slug,$module,$action]){
            DB::table('permissions')->updateOrInsert(
                ['slug'=>$slug],
                ['name'=>ucwords(str_replace(['.','_'],' ', $slug)),'module'=>$module,'action'=>$action,'updated_at'=>now(),'created_at'=>now()]
            );
        }
    }
}
