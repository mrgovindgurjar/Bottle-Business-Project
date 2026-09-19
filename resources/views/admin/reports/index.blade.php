@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@endpush

@section('content')
<div class="report-page">
    <div class="report-head">
        <div>
            <h1>Reports & Analytics</h1>
            <p>Real-time business reports from the ERP database.</p>
        </div>
        <form method="GET" class="report-filters">
            <input type="date" name="from" value="{{ $filters['from']->toDateString() }}">
            <input type="date" name="to" value="{{ $filters['to']->toDateString() }}">
            <button type="submit">Apply</button>
            <a href="{{ route('admin.reports.export',['type'=>'summary','from'=>$filters['from']->toDateString(),'to'=>$filters['to']->toDateString()]) }}">Export</a>
        </form>
    </div>

    <div class="report-grid">
        <div class="report-card"><span>Orders</span><strong>{{ $summary['orders'] }}</strong></div>
        <div class="report-card"><span>Customers</span><strong>{{ $summary['customers'] }}</strong></div>
        <div class="report-card"><span>Deliveries</span><strong>{{ $summary['deliveries'] }}</strong></div>
        <div class="report-card"><span>Issues</span><strong>{{ $summary['issues'] }}</strong></div>
    </div>

    @php
        $sections = [
            ['Sales','sales',['Total'=>$sales['total'],'Orders'=>$sales['orders'],'Delivered'=>$sales['delivered'],'Pending'=>$sales['pending']],'sales'],
            ['Production','production',['Jobs'=>$production['jobs'],'Completed'=>$production['completed'],'In Progress'=>$production['in_progress'],'Rejected'=>$production['rejected'],'Waste'=>$production['waste']],null],
            ['Inventory','inventory',['On Hand'=>$inventory['on_hand'],'Reserved'=>$inventory['reserved'],'Available'=>$inventory['available'],'Low Stock'=>$inventory['low_stock']],null],
            ['Delivery','delivery',['Total'=>$delivery['total'],'Delivered'=>$delivery['delivered'],'Pending'=>$delivery['pending'],'Failed'=>$delivery['failed']],null],
            ['Finance','finance',['Income'=>$finance['income'],'Expenses'=>$finance['expense'],'Net'=>$finance['net'],'Received'=>$finance['received'],'Paid'=>$finance['paid']],'finance'],
            ['Staff & Attendance','staff',['Staff'=>$staff['staff'],'Present'=>$staff['present'],'Absent'=>$staff['absent'],'Late'=>$staff['late']],'staff'],
            ['Complaints / Issues','issues',['Total'=>$issues['total'],'Open'=>$issues['open'],'Resolved'=>$issues['resolved'],'Closed'=>$issues['closed']],null],
        ];
    @endphp

    @foreach($sections as [$title,$key,$data,$exportType])
        <section class="report-section">
            <div class="report-section-head">
                <h2>{{ $title }}</h2>
                @if($exportType)
                    <a href="{{ route('admin.reports.export',['type'=>$exportType,'from'=>$filters['from']->toDateString(),'to'=>$filters['to']->toDateString()]) }}">Export CSV</a>
                @endif
            </div>
            <div class="report-mini-grid">
                @foreach($data as $label=>$value)
                    <div><span>{{ $label }}</span><b>{{ is_numeric($value) && in_array($label,['Total','Income','Expenses','Net','Received','Paid']) ? '₹'.number_format((float)$value,2) : number_format((float)$value) }}</b></div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection
