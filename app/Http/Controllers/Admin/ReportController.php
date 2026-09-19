<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->authorizePermission('reports.view');

        $filters = $this->reports->normalizeFilters($request);
        $summary = $this->reports->summary($filters);
        $sales = $this->reports->sales($filters);
        $production = $this->reports->production($filters);
        $inventory = $this->reports->inventory($filters);
        $delivery = $this->reports->delivery($filters);
        $finance = $this->reports->finance($filters);
        $staff = $this->reports->staff($filters);
        $issues = $this->reports->issues($filters);

        return view('admin.reports.index', compact(
            'filters','summary','sales','production','inventory',
            'delivery','finance','staff','issues'
        ));
    }

    public function export(Request $request)
    {
        $this->authorizePermission('reports.export');

        $filters = $this->reports->normalizeFilters($request);
        $type = $request->string('type')->toString() ?: 'summary';
        return $this->reports->exportCsv($type, $filters);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(
            auth()->check() &&
            method_exists(auth()->user(), 'hasPermission') &&
            auth()->user()->hasPermission($permission),
            403
        );
    }
}
