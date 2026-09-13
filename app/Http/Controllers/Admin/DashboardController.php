<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductionOrder;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Services\InventoryService;
use App\Models\Delivery;


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
            'orders' => Order::count(),
'orders_pending' => Order::whereIn('status', ['draft', 'confirmed'])->count(),
'orders_production' => Order::where('status', 'in_production')->count(),
'orders_value' => (float) Order::whereMonth('order_date', now()->month)->whereYear('order_date', now()->year)->sum('grand_total'),

        ];

        $recentOrders = Order::with(['customer'])
    ->latest('id')
    ->limit(6)
    ->get();

       $recentLeads = Lead::with(
    'assignedUser:id,name'
)
->latest()
->limit(8)
->get();

$productionStats = [
    'pending' => ProductionOrder::whereIn('status', ['pending', 'scheduled'])->count(),
    'in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
    'quality_check' => ProductionOrder::where('status', 'quality_check')->count(),
    'completed' => ProductionOrder::where('status', 'completed')->count(),
];

$stats['inventory_low_stock'] = InventoryItem::where('status','active')
    ->whereColumn('on_hand','<=','reorder_level')
    ->where('reorder_level','>',0)
    ->count();

$stats['inventory_units'] = InventoryItem::where('status','active')->sum('on_hand');

$PurchasesStats = [
    'open_purchases' => Purchase::whereIn('status',['ordered','partially_received'])->count(),
    'PurchaseValueThisMonth' => Purchase::where('status','!=','cancelled')->whereBetween('purchase_date',[now()->startOfMonth(),now()->endOfMonth()])->sum('grand_total'),
    'SupplierPayables' => Purchase::where('status','!=','cancelled')->sum('balance_amount'),
    'LowStock' => app(InventoryService::class)->lowStockQuery()->count(),
];


$DeliveryStats = [
    'ReadyToDispatch' => Delivery::where('status','ready')->count(),
    
    'OutForDelivery' => Delivery::where('status','out_for_delivery')->count(),
    'DeliveredToday' => Delivery::where('status','delivered')->whereDate('delivered_at',today())->count(),
    'Failed' => Delivery::where('status','delivered')->whereDate('delivered_at',today())->count(),
];



        return view(
            'admin.dashboard.index',
            compact(
                'stats',
                'recentLeads',
                'recentOrders',
                'productionStats',
                'PurchasesStats'
            )
        );
    }
}