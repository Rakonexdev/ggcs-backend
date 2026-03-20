<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collection;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Person;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard Quick Stats
     */
    public function dashboardStats(Request $request)
    {
        $pendingInvoiceTotal = Invoice::whereIn('status', ['issued', 'partially_paid'])
            ->sum('outstanding_amount');

        $todayCollections = Collection::whereDate('collection_date', now()->toDateString())
            ->sum('amount');

        $qidExpiryCount = Person::whereNotNull('id_expiration_date')
            ->where('id_expiration_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        $totalProjects = Project::count();

        return response()->json([
            'pending_invoice_total' => $pendingInvoiceTotal,
            'today_collections' => $todayCollections,
            'qid_expiry_count' => $qidExpiryCount,
            'total_projects' => $totalProjects,
        ]);
    }

    /**
     * Outstanding invoices with aging buckets (30/60/90 days)
     */
    public function outstandingInvoices(Request $request)
    {
        $query = Invoice::with([
            'project:id,project_code,person_id',
            'project.person:id,name,company_id',
            'project.person.company:id,name',
        ])
        ->whereIn('status', ['issued', 'partially_paid']);

        if ($request->filled('from_date')) {
            $query->where('issued_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('issued_at', '<=', $request->to_date);
        }

        $invoices = $query->get()->map(function ($inv) {
            $daysOverdue = now()->diffInDays($inv->issued_at);
            $bucket = match (true) {
                $daysOverdue <= 30 => '0-30',
                $daysOverdue <= 60 => '31-60',
                $daysOverdue <= 90 => '61-90',
                default => '90+',
            };

            return [
                'id' => $inv->id,
                'invoice_code' => $inv->invoice_code,
                'project_code' => $inv->project->project_code ?? null,
                'person' => $inv->project->person->name ?? null,
                'company' => $inv->project->person->company->name ?? null,
                'total_amount' => $inv->total_amount,
                'paid_amount' => $inv->paid_amount,
                'outstanding_amount' => $inv->outstanding_amount,
                'issued_at' => $inv->issued_at->toDateString(),
                'days_overdue' => $daysOverdue,
                'aging_bucket' => $bucket,
            ];
        });

        return response()->json([
            'data' => $invoices,
            'summary' => [
                'total_outstanding' => $invoices->sum('outstanding_amount'),
                'count' => $invoices->count(),
            ],
        ]);
    }

    /**
     * Collection summary with breakdown
     */
    public function collectionsSummary(Request $request)
    {
        $query = Collection::query();

        if ($request->filled('from_date')) {
            $query->where('collection_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('collection_date', '<=', $request->to_date);
        }

        $total = $query->sum('amount');
        $count = $query->count();
        $byMethod = $query->clone()->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('method')
            ->get();

        return response()->json([
            'total_collected' => round($total, 2),
            'count' => $count,
            'average' => $count > 0 ? round($total / $count, 2) : 0,
            'by_method' => $byMethod,
        ]);
    }

    /**
     * Live collection feed
     */
    public function collectionsFeed(Request $request)
    {
        $query = Collection::with([
            'invoice:id,invoice_code,project_id',
            'invoice.project:id,person_id',
            'invoice.project.person:id,company_id',
            'invoice.project.person.company:id,name',
            'collector:id,name',
        ])->latest('collection_date');

        if ($request->filled('from_date')) {
            $query->where('collection_date', '>=', $request->from_date);
        } else {
            $query->where('collection_date', '>=', now()->toDateString());
        }

        return response()->json($query->paginate(50));
    }

    /**
     * Expenses grouped by category
     */
    public function expensesByCategory(Request $request)
    {
        $query = Expense::query();

        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $data = $query->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category:id,name,parent_id', 'category.parent:id,name')
            ->get();

        return response()->json([
            'data' => $data,
            'grand_total' => $data->sum('total'),
        ]);
    }

    /**
     * Audit logs search
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user:id,name');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        return response()->json($query->latest('created_at')->paginate(50));
    }
}
