<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit_logs.index', compact('logs'));
    }
}
