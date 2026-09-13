<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Customer;
use App\Models\DesignRequest;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\PricingService;
use App\Services\QuotationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(private QuotationService $service, private PricingService $pricing) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);
        $query = Quotation::with(['customer','creator'])->withCount('items')->latest();
        if ($q = trim((string)$request->get('q'))) $query->where(function($w) use ($q) { $w->where('quotation_number','like',"%{$q}%")->orWhereHas('customer', fn($c) => $c->where('business_name','like',"%{$q}%")->orWhere('customer_code','like',"%{$q}%")->orWhere('mobile','like',"%{$q}%")); });
        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('customer_id')) $query->where('customer_id',$request->customer_id);
        if ($request->filled('date_from')) $query->whereDate('quotation_date','>=',$request->date_from);
        if ($request->filled('date_to')) $query->whereDate('quotation_date','<=',$request->date_to);
        if ($request->filled('created_by')) $query->where('created_by',$request->created_by);
        $quotations = $query->paginate(20)->withQueryString();
        $stats = [
            'total' => Quotation::count(), 'draft' => Quotation::where('status','draft')->count(),
            'sent' => Quotation::whereIn('status',['sent','viewed'])->count(), 'pending' => Quotation::whereIn('status',['sent','viewed'])->count(),
            'approved' => Quotation::where('status','approved')->count(), 'converted' => Quotation::where('status','converted')->count(),
            'value' => (float)Quotation::sum('grand_total'),
        ];
        return view('admin.quotations.index', compact('quotations','stats'));
    }

    public function create(): View
    {
        $this->authorize('create', Quotation::class);
        return view('admin.quotations.create', $this->formData());
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $quotation = $this->service->createQuotation($request->validated(), (int)$request->user()->id);
        return redirect()->route('admin.quotations.show',$quotation)->with('success','Quotation created successfully.');
    }

    public function show(Quotation $quotation): View
    {
        $this->authorize('view',$quotation); $quotation->load(['customer.user','items.product','items.design','design.versions','creator','approver']);
        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorize('update',$quotation); $quotation->load(['customer','items.product','items.design','design']);
        return view('admin.quotations.edit', array_merge(['quotation'=>$quotation], $this->formData()));
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update',$quotation); $this->service->updateQuotation($quotation,$request->validated());
        return redirect()->route('admin.quotations.show',$quotation)->with('success','Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete',$quotation); $quotation->delete();
        return redirect()->route('admin.quotations.index')->with('success','Quotation deleted successfully.');
    }

    public function send(Quotation $quotation): RedirectResponse { $this->authorize('send',$quotation); $this->service->send($quotation); return back()->with('success','Quotation marked as sent.'); }
    public function approve(Quotation $quotation): RedirectResponse { $this->authorize('approve',$quotation); $this->service->approve($quotation,(int)auth()->id()); return back()->with('success','Quotation approved.'); }
    public function reject(Quotation $quotation): RedirectResponse { $this->authorize('reject',$quotation); $this->service->reject($quotation); return back()->with('success','Quotation rejected.'); }
    public function duplicate(Quotation $quotation): RedirectResponse { $this->authorize('view',$quotation); $copy=$this->service->duplicate($quotation,(int)auth()->id()); return redirect()->route('admin.quotations.edit',$copy)->with('success','Quotation duplicated as '.$copy->quotation_number.'.'); }

    public function preview(Quotation $quotation): View { $this->authorize('view',$quotation); $quotation->load(['customer','items.product','design']); return view('admin.quotations.preview',compact('quotation')); }
    public function pdf(Quotation $quotation) { $this->authorize('view',$quotation); $quotation->load(['customer','items.product','design']); return Pdf::loadView('admin.quotations.pdf',compact('quotation'))->setPaper('a4')->download($quotation->quotation_number.'.pdf'); }

    public function convert(Quotation $quotation): RedirectResponse
    {
        $this->authorize('convert',$quotation); $order=$this->service->convertToOrder($quotation,(int)auth()->id());
        return redirect()->route('admin.orders.show',$order)->with('success','Quotation converted to order successfully.');
    }

    public function price(Request $request): JsonResponse
    {
        $product=Product::findOrFail($request->integer('product_id')); $customer=$request->filled('customer_id') ? Customer::find($request->integer('customer_id')) : null; $qty=max(1,(int)$request->input('quantity',1));
        $types=['standard','bulk','recurring']; foreach($types as $type){ $price=$this->pricing->getPrice($product,$customer,$qty,$type); if($price) return response()->json(['price'=>(float)$price->unit_price,'pricing_type'=>$type]); }
        return response()->json(['price'=>0,'pricing_type'=>null]);
    }

    public function designs(Request $request): JsonResponse
    {
        $customerId=$request->integer('customer_id');
        $designs=DesignRequest::where('customer_id',$customerId)->with('versions')->latest()->get()->map(fn($d)=>['id'=>$d->id,'code'=>$d->design_code,'title'=>$d->title,'status'=>$d->status,'versions'=>$d->versions->sortByDesc('version_no')->map(fn($v)=>['id'=>$v->id,'version_no'=>$v->version_no,'name'=>$v->name,'status'=>$v->status])->values()]);
        return response()->json($designs);
    }

    private function formData(): array
    {
        return ['customers'=>Customer::orderBy('business_name')->get(['id','business_name','customer_code','address','city','state','pincode']), 'products'=>Product::where('status','active')->orderBy('name')->get(['id','name','sku','bottle_size_ml','unit']), 'designs'=>collect()];
    }
}
