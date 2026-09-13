<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryStatusRequest;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Delivery::class);
        $query=Delivery::with(['order','customer','assignee'])->withCount('items')->latest('id');
        if($request->filled('q')) $query->search($request->string('q'));
        if($request->filled('status')) $query->where('status',$request->string('status'));
        if($request->filled('customer_id')) $query->where('customer_id',$request->integer('customer_id'));
        if($request->filled('date_from')) $query->whereDate('delivery_date','>=',$request->date('date_from'));
        if($request->filled('date_to')) $query->whereDate('delivery_date','<=',$request->date('date_to'));
        $deliveries=$query->paginate(20)->withQueryString();
        $customers=Customer::orderBy('business_name')->get(['id','business_name','customer_code']);
        $stats=[
            'total'=>Delivery::count(),
            'ready'=>Delivery::where('status','ready')->count(),
            'out'=>Delivery::where('status','out_for_delivery')->count(),
            'delivered'=>Delivery::where('status','delivered')->count(),
            'failed'=>Delivery::where('status','failed')->count(),
            'cancelled'=>Delivery::where('status','cancelled')->count(),
        ];
        return view('admin.deliveries.index',compact('deliveries','customers','stats'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create',Delivery::class);
        $orders=Order::with(['customer.user','items.product','items.design'])
            ->whereNotIn('status',['draft','cancelled','delivered'])->latest('id')->limit(200)->get();
        $selectedOrder=$request->filled('order') ? $orders->firstWhere('id',(int)$request->integer('order')) : null;
        $users=User::orderBy('name')->get(['id','name']);
        $batches=Batch::with('product')->whereIn('status',['released','allocated'])->where('quality_status','passed')->where(function($q){$q->whereNull('expiry_date')->orWhereDate('expiry_date','>=',today());})->latest('id')->limit(500)->get(['id','batch_number','product_id','customer_id','available_quantity','status','manufacturing_date']);
         $delivery =    new Delivery();
        return view('admin.deliveries.create',compact('orders','selectedOrder','users','batches','delivery'));
    }

    public function store(StoreDeliveryRequest $request): RedirectResponse
    {
        $this->authorize('create',Delivery::class);
        $delivery=$this->service->create($request->validated(),(int)$request->user()->id);
        return redirect()->route('admin.deliveries.show',$delivery)->with('success','Delivery created and marked ready for dispatch.');
    }

    public function show(Delivery $delivery): View
    {
        $this->authorize('view',$delivery);
        $delivery->load(['order.customer.user','customer.user','items.product','items.batch','items.orderItem','assignee','creator','dispatcher','deliverer','canceller']);
        return view('admin.deliveries.show',compact('delivery'));
    }

    public function edit(Delivery $delivery): View
    {
        $this->authorize('update',$delivery);
        $delivery->load(['order.items.product','items.product','items.batch']);
        $orders=Order::with(['customer.user','items.product'])->where('id',$delivery->order_id)->get();
        $users=User::orderBy('name')->get(['id','name']);
        $batches=Batch::with('product')->whereIn('status',['released','allocated'])->where('quality_status','passed')->where(function($q){$q->whereNull('expiry_date')->orWhereDate('expiry_date','>=',today());})->latest('id')->limit(500)->get(['id','batch_number','product_id','customer_id','available_quantity','status','manufacturing_date']);
        return view('admin.deliveries.edit',compact('delivery','orders','users','batches'));
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('update',$delivery);
        $this->service->update($delivery,$request->validated());
        return redirect()->route('admin.deliveries.show',$delivery)->with('success','Delivery updated successfully.');
    }

    public function dispatch(Delivery $delivery): RedirectResponse
    {
        $this->authorize('dispatch',$delivery);
        $this->service->dispatch($delivery,(int)auth()->id());
        return back()->with('success','Delivery dispatched. Inventory and batch allocation were recorded.');
    }

    public function status(DeliveryStatusRequest $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize($request->string('action')->value()==='deliver' ? 'deliver' : 'fail',$delivery);
        $data=$request->validated();
        if($data['action']==='deliver'){
            if($request->hasFile('proof')) $data['proof_path']=$request->file('proof')->store('deliveries/proof','public');
            if($request->hasFile('signature')) $data['signature_path']=$request->file('signature')->store('deliveries/signatures','public');
            $result=$this->service->deliver($delivery,$data,(int)auth()->id());
            if(isset($data['proof_path'],$data['signature_path'])) $result->update(['proof_path'=>$data['proof_path'],'signature_path'=>$data['signature_path']]);
            elseif(isset($data['proof_path'])) $result->update(['proof_path'=>$data['proof_path']]);
            elseif(isset($data['signature_path'])) $result->update(['signature_path'=>$data['signature_path']]);
            return back()->with('success','Delivery marked as completed.');
        }
        $this->service->fail($delivery,$data['failed_reason'],(int)auth()->id());
        return back()->with('success','Delivery marked as failed and dispatched stock was returned.');
    }

    public function cancel(Delivery $delivery): RedirectResponse
    {
        $this->authorize('cancel',$delivery);
        $this->service->cancel($delivery,(int)auth()->id());
        return back()->with('success','Delivery cancelled.');
    }

    public function destroy(Delivery $delivery): RedirectResponse
    {
        $this->authorize('delete',$delivery);
        $delivery->delete();
        return redirect()->route('admin.deliveries.index')->with('success','Delivery moved to history.');
    }

    public function print(Delivery $delivery): View
    {
        $this->authorize('view',$delivery);
        $delivery->load(['order','customer.user','items.product','items.batch','assignee','deliverer']);
        return view('admin.deliveries.print',compact('delivery'));
    }

    public function orderData(Order $order)
    {
        $this->authorize('create',Delivery::class);
        $order->load(['customer.user','items.product','items.design']);
        return response()->json($order);
    }
}
