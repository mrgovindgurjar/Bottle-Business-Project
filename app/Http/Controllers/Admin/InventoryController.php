<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);
        $query=InventoryItem::with('product')->latest('id');
        if($request->filled('q')) $query->search($request->string('q'));
        if($request->filled('category')) $query->where('category',$request->string('category'));
        if($request->filled('status')) $query->where('status',$request->string('status'));
        if($request->boolean('low_stock')) $query->whereColumn('on_hand','<=','reorder_level')->where('reorder_level','>',0);
        $items=$query->paginate(20)->withQueryString();
        $stats=[
            'skus'=>InventoryItem::where('status','active')->count(),
            'low_stock'=>$this->service->lowStockQuery()->count(),
            'out_of_stock'=>InventoryItem::where('status','active')->where('on_hand','<=',0)->count(),
            'units'=>InventoryItem::where('status','active')->sum('on_hand'),
            'reserved'=>InventoryItem::where('status','active')->sum('reserved'),
        ];
        return view('admin.inventory.index',compact('items','stats'));
    }

    public function create(): View
    {
        $this->authorize('create',InventoryItem::class);
        $products=Product::where('status','active')->orderBy('name')->get(['id','name','sku','unit']);
        return view('admin.inventory.create',compact('products'));
    }

    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        $this->authorize('create',InventoryItem::class);
        $item=$this->service->createItem($request->validated(),(int)$request->user()->id);
        return redirect()->route('admin.inventory.show',$item)->with('success','Inventory item created successfully.');
    }

    public function show(InventoryItem $inventory): View
    {
        $this->authorize('view',$inventory);
        $inventory->load(['product','movements'=>fn($q)=>$q->with(['batch','performer'])->latest('id')->limit(50)]);
        return view('admin.inventory.show',['item'=>$inventory]);
    }

    public function edit(InventoryItem $inventory): View
    {
        $this->authorize('update',$inventory);
        $products=Product::where('status','active')->orderBy('name')->get(['id','name','sku','unit']);
        return view('admin.inventory.edit',['item'=>$inventory,'products'=>$products]);
    }

    public function update(UpdateInventoryItemRequest $request,InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('update',$inventory);
        $this->service->updateItem($inventory,$request->validated());
        return back()->with('success','Inventory item updated.');
    }

    public function receive(Request $request,InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('adjust',$inventory);
        $data=$request->validate(['quantity'=>['required','numeric','gt:0'],'unit_cost'=>['nullable','numeric','gte:0'],'notes'=>['nullable','string','max:2000']]);
        $this->service->receive($inventory,(float)$data['quantity'],(int)auth()->id(),isset($data['unit_cost'])?(float)$data['unit_cost']:null,null,'manual_receipt',null,$data['notes']??null);
        return back()->with('success','Stock received successfully.');
    }

    public function issue(Request $request,InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('adjust',$inventory);
        $data=$request->validate(['quantity'=>['required','numeric','gt:0'],'notes'=>['nullable','string','max:2000']]);
        $this->service->issue($inventory,(float)$data['quantity'],(int)auth()->id(),null,'manual_issue',null,$data['notes']??null);
        return back()->with('success','Stock issued successfully.');
    }

    public function adjust(Request $request,InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('adjust',$inventory);
        $data=$request->validate(['delta'=>['required','numeric','not_in:0'],'notes'=>['required','string','max:2000']]);
        $this->service->adjust($inventory,(float)$data['delta'],(int)auth()->id(),$data['notes']);
        return back()->with('success','Stock adjustment recorded.');
    }

    public function reserve(Request $request,InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('reserve',$inventory);
        $data=$request->validate(['quantity'=>['required','numeric','gt:0'],'reference_type'=>['nullable','string','max:50'],'reference_id'=>['nullable','integer'],'notes'=>['nullable','string','max:2000']]);
        $this->service->reserve($inventory,(float)$data['quantity'],(int)auth()->id(),$data['reference_type']??null,$data['reference_id']??null,$data['notes']??null);
        return back()->with('success','Stock reserved.');
    }

    public function releaseReservation(Request $request,InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('reserve',$inventory);
        $data=$request->validate(['quantity'=>['required','numeric','gt:0'],'reference_type'=>['nullable','string','max:50'],'reference_id'=>['nullable','integer'],'notes'=>['nullable','string','max:2000']]);
        $this->service->releaseReservation($inventory,(float)$data['quantity'],(int)auth()->id(),$data['reference_type']??null,$data['reference_id']??null,$data['notes']??null);
        return back()->with('success','Reservation released.');
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $this->authorize('delete',$inventory);
        if($inventory->movements()->exists()) return back()->with('error','Items with movement history cannot be deleted. Deactivate them instead.');
        $inventory->delete();
        return redirect()->route('admin.inventory.index')->with('success','Inventory item moved to history.');
    }
}
