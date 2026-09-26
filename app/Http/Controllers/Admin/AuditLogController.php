<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->hasPermission('audit_logs.view'), 403);

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->string('q')->toString());
                $q->where(function ($query) use ($term) {
                    $query->where('event', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('ip_address', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->event))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $events = AuditLog::query()->select('event')->distinct()->orderBy('event')->pluck('event');

        return view('admin.security.audit.index', compact('logs', 'events'));
    }
}
