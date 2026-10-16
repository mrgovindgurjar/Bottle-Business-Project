<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\{CancelInvoiceRequest, StoreInvoiceRequest, UpdateInvoiceRequest};
use App\Models\{Customer, Invoice, Order};
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Throwable;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);
        $query = Invoice::with(['customer','order'])->latest('id');
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('invoice_number','like',"%$search%")
                ->orWhereHas('customer',fn($c)=>$c->where('business_name','like',"%$search%"))
                ->orWhereHas('order',fn($o)=>$o->where('order_number','like',"%$search%")));
        }
        foreach (['status','payment_status','customer_id'] as $field) if ($request->filled($field)) $query->where($field,$request->input($field));
        if ($request->filled('from')) $query->whereDate('invoice_date','>=',$request->input('from'));
        if ($request->filled('to')) $query->whereDate('invoice_date','<=',$request->input('to'));
        $invoices=$query->paginate(20)->withQueryString();
        $month=now()->startOfMonth();
        $stats=[
            'invoiced'=>Invoice::where('invoice_date','>=',$month)->where('status','!=','cancelled')->sum('grand_total'),
            'paid'=>Invoice::where('invoice_date','>=',$month)->sum('paid_amount'),
            'outstanding'=>Invoice::where('status','!=','cancelled')->sum('outstanding_amount'),
            'overdue'=>Invoice::where('status','!=','cancelled')->where('outstanding_amount','>',0)->whereDate('due_date','<',today())->sum('outstanding_amount'),
            'draft'=>Invoice::where('status','draft')->count(),
            'partial'=>Invoice::where('payment_status','partially_paid')->count(),
            'paid_count'=>Invoice::where('payment_status','paid')->count(),
            'cancelled'=>Invoice::where('status','cancelled')->count(),
        ];
        return view('admin.invoices.index',compact('invoices','stats'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Invoice::class);
        $customers=Customer::orderBy('business_name')->get();
        $order=$request->filled('order_id')?Order::with('items.product')->findOrFail($request->order_id):null;
        if ($order && $order->customer_id !== ($request->input('customer_id') ?: $order->customer_id)) {
            abort(422, 'Order/customer mismatch.');
        }
        return view('admin.invoices.create',compact('customers','order'));
    }

    public function createFromOrder(Order $order)
    {
        $this->authorize('create', Invoice::class);
        $customers=Customer::orderBy('business_name')->get();
        $order->load('items.product','customer');
        return view('admin.invoices.create',compact('customers','order'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        try {
            $invoice=$this->service->createInvoice($request->validated(),auth()->id());
            return redirect()->route('admin.invoices.show',$invoice)->with('success','Invoice created.');
        } catch (Throwable $e) { return back()->withInput()->withErrors(['invoice'=>$e->getMessage()]); }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view',$invoice);
        $invoice->load(['customer','order','items.product','paymentAllocations.payment']);
        $invoice=$this->service->syncPaymentStatus($invoice);
        return view('admin.invoices.show',compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update',$invoice);
        $customers=Customer::orderBy('business_name')->get();
        $invoice->load('items');
        return view('admin.invoices.edit',compact('invoice','customers'));
    }

    public function update(UpdateInvoiceRequest $request,Invoice $invoice)
    {
        $this->authorize('update',$invoice);
        $invoice=$this->service->updateInvoice($invoice,$request->validated(),auth()->id());
        return redirect()->route('admin.invoices.show',$invoice)->with('success','Invoice updated.');
    }

    public function issue(Invoice $invoice)
    {
        $this->authorize('issue',$invoice);
        $this->service->issue($invoice,auth()->id());
        return back()->with('success','Invoice issued.');
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('send',$invoice);
        $this->service->sendInvoice($invoice,auth()->id());
        return back()->with('success','Invoice marked as sent.');
    }

    public function cancel(CancelInvoiceRequest $request,Invoice $invoice)
    {
        $this->authorize('cancel',$invoice);
        $this->service->cancel($invoice,$request->validated('cancellation_reason'),auth()->id());
        return back()->with('success','Invoice cancelled.');
    }

    public function duplicate(Invoice $invoice)
    {
        $this->authorize('duplicate',$invoice);
        $new=$this->service->duplicate($invoice,auth()->id());
        return redirect()->route('admin.invoices.edit',$new)->with('success','Draft invoice duplicated.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete',$invoice);
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success','Draft invoice moved to history.');
    }

    public function print(Invoice $invoice)
    {
        $this->authorize('print',$invoice);
        $invoice->load(['customer','order','items.product']);
        return view('admin.invoices.print',compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('pdf',$invoice);
        $invoice->load(['customer','order','items.product']);
        if(class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.print',compact('invoice'))->setPaper('a4')->download($invoice->invoice_number.'.pdf');
        return redirect()->route('admin.invoices.print',$invoice)->with('warning','PDF package is not installed; use Print to save as PDF.');
    }
}
