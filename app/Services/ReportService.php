<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    public function normalizeFilters($request): array
    {
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    public function summary(array $f): array
    {
        return [
            'orders' => $this->countBetween('orders', $f),
            'customers' => $this->countBetween('customers', $f),
            'deliveries' => $this->countBetween('deliveries', $f),
            'issues' => $this->countBetween('issues', $f),
        ];
    }

    public function sales(array $f): array
    {
        $total = $this->sumIfExists('orders', 'grand_total', $f);
        return [
            'total' => $total,
            'orders' => $this->countBetween('orders', $f),
            'delivered' => $this->countStatus('orders', 'status', 'delivered'),
            'pending' => $this->countWhereStatuses('orders', 'status', ['draft','confirmed','in_production','ready','dispatched']),
        ];
    }

    public function production(array $f): array
    {
        return [
            'jobs' => $this->countBetween('production_orders', $f),
            'completed' => $this->countStatus('production_orders', 'status', 'completed'),
            'in_progress' => $this->countStatus('production_orders', 'status', 'in_progress'),
            'rejected' => $this->sumIfExists('production_orders', 'rejected_quantity', $f),
            'waste' => $this->sumIfExists('production_orders', 'waste_quantity', $f),
        ];
    }

    public function inventory(array $f): array
    {
        $onHand = $this->sumAllIfExists('inventory_items', 'on_hand');
        $reserved = $this->sumAllIfExists('inventory_items', 'reserved');
        $low = 0;
        if (Schema::hasTable('inventory_items') && Schema::hasColumn('inventory_items','reorder_level')) {
            $low = DB::table('inventory_items')->whereColumn('on_hand','<=','reorder_level')->count();
        }
        return ['on_hand'=>$onHand, 'reserved'=>$reserved, 'available'=>$onHand-$reserved, 'low_stock'=>$low];
    }

    public function delivery(array $f): array
    {
        return [
            'total' => $this->countBetween('deliveries', $f),
            'delivered' => $this->countStatus('deliveries','status','delivered'),
            'pending' => $this->countWhereStatuses('deliveries','status',['ready','out_for_delivery']),
            'failed' => $this->countStatus('deliveries','status','failed'),
        ];
    }

    public function finance(array $f): array
    {
        $income = $this->sumIfExists('incomes','amount',$f);
        $expense = $this->sumIfExists('expenses','amount',$f);
        $received = $this->sumWhere('payments','amount',$f, fn($q) => $q->where('direction','received')->where('status','completed'));
        $paid = $this->sumWhere('payments','amount',$f, fn($q) => $q->where('direction','paid')->where('status','completed'));
        return [
            'income'=>$income,
            'expense'=>$expense,
            'net'=>$income-$expense,
            'received'=>$received,
            'paid'=>$paid,
        ];
    }

    public function staff(array $f): array
    {
        $staff = $this->countAllIfExists('staff');
        $present = $this->attendanceCount($f,'present');
        $absent = $this->attendanceCount($f,'absent');
        $late = $this->attendanceCount($f,'late');
        return compact('staff','present','absent','late');
    }

    public function issues(array $f): array
    {
        return [
            'total'=>$this->countBetween('issues',$f),
            'open'=>$this->countWhereStatuses('issues','status',['open','in_progress','pending_customer']),
            'resolved'=>$this->countStatus('issues','status','resolved'),
            'closed'=>$this->countStatus('issues','status','closed'),
        ];
    }

    public function exportCsv(string $type, array $f): Response
    {
        $rows = match ($type) {
            'sales' => [['Metric','Value'],['Sales Total',$this->sales($f)['total']],['Orders',$this->sales($f)['orders']],['Delivered',$this->sales($f)['delivered']]],
            'finance' => [['Metric','Value'],['Income',$this->finance($f)['income']],['Expenses',$this->finance($f)['expense']],['Net',$this->finance($f)['net']],['Received',$this->finance($f)['received']],['Paid',$this->finance($f)['paid']]],
            'staff' => [['Metric','Value'],['Staff',$this->staff($f)['staff']],['Present',$this->staff($f)['present']],['Absent',$this->staff($f)['absent']],['Late',$this->staff($f)['late']]],
            default => [['Report','Value'],['Orders',$this->summary($f)['orders']],['Customers',$this->summary($f)['customers']],['Deliveries',$this->summary($f)['deliveries']],['Issues',$this->summary($f)['issues']]],
        };

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"','""',(string)$v) . '"', $row)) . "\r\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="jalvan-'.$type.'-report.csv"',
        ]);
    }

    private function tableExists(string $table): bool { return Schema::hasTable($table); }
    private function countAllIfExists(string $table): int { return $this->tableExists($table) ? DB::table($table)->count() : 0; }
    private function countBetween(string $table, array $f): int
    {
        if (!$this->tableExists($table)) return 0;
        return DB::table($table)->whereBetween('created_at',[$f['from'],$f['to']])->count();
    }
    private function sumIfExists(string $table,string $column,array $f): float
    {
        if (!$this->tableExists($table) || !Schema::hasColumn($table,$column)) return 0;
        return (float) DB::table($table)->whereBetween('created_at',[$f['from'],$f['to']])->sum($column);
    }
    private function sumAllIfExists(string $table,string $column): float
    {
        if (!$this->tableExists($table) || !Schema::hasColumn($table,$column)) return 0;
        return (float) DB::table($table)->sum($column);
    }
    private function sumWhere(string $table,string $column,array $f,callable $callback): float
    {
        if (!$this->tableExists($table) || !Schema::hasColumn($table,$column)) return 0;
        $q = DB::table($table)->whereBetween('created_at',[$f['from'],$f['to']]);
        $callback($q);
        return (float) $q->sum($column);
    }
    private function countStatus(string $table,string $column,string $status): int
    {
        if (!$this->tableExists($table) || !Schema::hasColumn($table,$column)) return 0;
        return DB::table($table)->where($column,$status)->count();
    }
    private function countWhereStatuses(string $table,string $column,array $statuses): int
    {
        if (!$this->tableExists($table) || !Schema::hasColumn($table,$column)) return 0;
        return DB::table($table)->whereIn($column,$statuses)->count();
    }
    private function attendanceCount(array $f,string $status): int
    {
        if (!$this->tableExists('attendances') || !Schema::hasColumn('attendances','date')) return 0;
        $q = DB::table('attendances')->whereBetween('date',[$f['from']->toDateString(),$f['to']->toDateString()]);
        if (Schema::hasColumn('attendances','status')) $q->where('status',$status);
        return $q->count();
    }
}
