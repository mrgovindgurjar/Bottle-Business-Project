<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'customers' => Customer::count(),

            'leads' => Lead::count(),

            'products' => Product::where(
                'status',
                'active'
            )->count(),

            'new_leads' => Lead::where(
                'status',
                'new'
            )->count(),
        ];

       $recentLeads = Lead::with(
    'assignedUser:id,name'
)
->latest()
->limit(8)
->get();


        return view(
            'admin.dashboard.index',
            compact(
                'stats',
                'recentLeads'
            )
        );
    }
}