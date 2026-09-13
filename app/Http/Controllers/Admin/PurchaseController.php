<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceivePurchaseRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\SupplierPaymentRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PricingService;
use App\Services\SupplierPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Purchase::class);
        $purchases=Purchase::query()->search($request->get('q'))
            ->when($request->filled('status'),fn($q)=>$q->where('status',$request->status))
            ->when($request->filled('supplier_id'),fn($q)=>$q->where('supplier_id',$request->supplier_id))
            ->when($request->filled('from'),fn($q)=>$q->whereDate('purchase_date','>=',$request->from))
            ->when($request->filled('to'),fn($q)=>$q->whereDate('purchase_date','<=',$request->to))
            ->with(['supplier','creator'])->withCount('items')->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();
        $suppliers=Supplier::where('status','active')->orderBy('business_name')->get(['id','business_name','supplier_code']);
        $stats=[
            'total'=>Purchase::where('status','!=','cancelled')->count(),
            'draft'=>Purchase::where('status','draft')->count(),
            'ordered'=>Purchase::whereIn('status',['ordered','partially_received'])->count(),
            'received'=>Purchase::where('status','received')->count(),
            'value'=>(float)Purchase::where('status','!=','cancelled')->sum('grand_total'),
            'due'=>(float)Purchase::where('status','!=','cancelled')->sum('balance_amount'),
        ];
        return view('admin.purchases.index',compact('purchases','suppliers','stats'));
    }

    public function create(): View
    {
         $this->authorize('create',Purchase::class);
        return view('admin.purchases.create',$this->formData());
    }

    public function store(StorePurchaseRequest $request,SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('create',Purchase::class);
        $purchase=$service->createPurchase($request->validated(),$request->user()->id);
        return redirect()->route('admin.purchases.show',$purchase)->with('success','Purchase created successfully.');
    }

    public function show(Purchase $purchase): View
    {
        $this->authorize('view',$purchase); $purchase->load(['supplier','items.product','items.inventoryItem','payments.creator','creator']);
        return view('admin.purchases.show',compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $this->authorize('update',$purchase); $purchase->load('items');
        return view('admin.purchases.edit',array_merge(['purchase'=>$purchase],$this->formData()));
    }

    public function update(UpdatePurchaseRequest $request,Purchase $purchase,SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('update',$purchase); $service->updatePurchase($purchase,$request->validated());
        return redirect()->route('admin.purchases.show',$purchase)->with('success','Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $this->authorize('delete',$purchase); $purchase->delete();
        return redirect()->route('admin.purchases.index')->with('success','Purchase archived successfully.');
    }

    public function receive(ReceivePurchaseRequest $request,Purchase $purchase,SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('receive',$purchase); $service->receive($purchase,$request->validated()['quantities'],$request->user()->id);
        return back()->with('success','Stock received into inventory successfully.');
    }

    public function pay(SupplierPaymentRequest $request,Purchase $purchase,SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('pay',$purchase); $service->recordPayment($purchase,$request->validated(),$request->user()->id);
        return back()->with('success','Supplier payment recorded successfully.');
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        $this->authorize('cancel',$purchase);
        $purchase->update(['status'=>'cancelled']);
        return back()->with('success','Purchase cancelled.');
    }

    public function duplicate(Purchase $purchase,SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('create',Purchase::class); $purchase->load('items');
        $data=[
            'supplier_id'=>$purchase->supplier_id,'purchase_date'=>now()->toDateString(),'expected_date'=>null,'status'=>'draft',
            'discount_type'=>$purchase->discount_type,'discount_value'=>$purchase->discount_value,'tax_rate'=>$purchase->tax_rate,
            'shipping_amount'=>$purchase->shipping_amount,'other_amount'=>$purchase->other_amount,'notes'=>$purchase->notes,
            'terms_conditions'=>$purchase->terms_conditions,
            'items'=>$purchase->items->map(fn($i)=>[
                'product_id'=>$i->product_id,'inventory_item_id'=>$i->inventory_item_id,'description'=>$i->description,'quantity'=>$i->quantity,
                'unit'=>$i->unit,'unit_price'=>$i->unit_price,'discount_type'=>$i->discount_type,'discount_value'=>$i->discount_value,
                'tax_rate'=>$i->tax_rate,'metadata'=>$i->metadata,
            ])->all(),
        ];
        $new=$service->createPurchase($data,auth()->id());
        return redirect()->route('admin.purchases.edit',$new)->with('success','Purchase duplicated as a new draft.');
    }

    public function price(Request $request,PricingService $pricing): JsonResponse
    {
        $data=$request->validate(['product_id'=>'required|integer|exists:products,id','quantity'=>'required|numeric|gt:0']);
        $product=Product::findOrFail($data['product_id']);
        foreach(['bulk','standard'] as $type){ $price=$pricing->getPrice($product,null,(int)ceil($data['quantity']),$type); if($price) return response()->json(['price'=>(float)$price->unit_price,'pricing_type'=>$type]); }
        return response()->json(['price'=>0,'pricing_type'=>null]);
    }

    public function inventoryItems(Request $request): JsonResponse
    {
        $request->validate(['q'=>'nullable|string|max:100']);
        return response()->json(InventoryItem::search($request->q)->where('status','active')->orderBy('name')->limit(30)->get(['id','name','sku','unit','product_id']));
    }

    public function pdf(Purchase $purchase)
    {
        $this->authorize('export',$purchase); $purchase->load(['supplier','items.product','creator']);
        if(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.purchases.pdf',compact('purchase'))->stream($purchase->purchase_number.'.pdf');
        return response()->view('admin.purchases.pdf',compact('purchase'))->header('Content-Disposition','inline; filename="'.$purchase->purchase_number.'.html"');
    }

    private function formData(): array
    {
        return [
            'suppliers'=>Supplier::where('status','active')->orderBy('business_name')->get(),
            'products'=>Product::where('status','active')->orderBy('name')->get(['id','name','sku','bottle_size_ml','unit']),
            'inventoryItems'=>InventoryItem::where('status','active')->orderBy('name')->get(['id','name','sku','unit','product_id']),
        ];
    }
}
