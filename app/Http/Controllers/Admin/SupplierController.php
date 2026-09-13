<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierPurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);
        $suppliers = Supplier::query()->search($request->get('q'))
            ->when($request->filled('status'), fn($q)=>$q->where('status',$request->status))
            ->when($request->filled('category'), fn($q)=>$q->where('category',$request->category))
            ->withCount('purchases')->withSum(['purchases as purchase_value'=>fn($q)=>$q->where('status','!=','cancelled')],'grand_total')
            ->latest('id')->paginate(15)->withQueryString();
        $categories=Supplier::whereNotNull('category')->where('category','!=','')->distinct()->orderBy('category')->pluck('category');
        return view('admin.suppliers.index',compact('suppliers','categories'));
    }

    public function create(): View { $this->authorize('create', Supplier::class); return view('admin.suppliers.create'); }

    public function store(StoreSupplierRequest $request, SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('create', Supplier::class);
        $data=$request->validated(); $data['supplier_code']=$service->generateSupplierCode();
        $supplier=$service->createSupplier($data);
        return redirect()->route('admin.suppliers.show',$supplier)->with('success','Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view',$supplier);
        $supplier->load(['purchases'=>fn($q)=>$q->latest('purchase_date')->limit(10),'payments'=>fn($q)=>$q->latest('payment_date')->limit(10)]);
        $purchased=(float)$supplier->purchases()->where('status','!=','cancelled')->sum('grand_total');
        $paid=(float)$supplier->payments()->sum('amount');
        return view('admin.suppliers.show',compact('supplier','purchased','paid'));
    }

    public function edit(Supplier $supplier): View { $this->authorize('update',$supplier); return view('admin.suppliers.edit',compact('supplier')); }

    public function update(UpdateSupplierRequest $request, Supplier $supplier, SupplierPurchaseService $service): RedirectResponse
    {
        $this->authorize('update',$supplier); $service->updateSupplier($supplier,$request->validated());
        return redirect()->route('admin.suppliers.show',$supplier)->with('success','Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete',$supplier); $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success','Supplier archived successfully.');
    }
}
