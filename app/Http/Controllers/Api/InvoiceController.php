<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('project:id,project_code,person_id', 'project.person:id,name');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('issued_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('issued_at', '<=', $request->to_date);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'total_amount' => 'required|numeric|min:0.01',
            'file_url' => 'nullable|string',
            'status' => 'nullable|in:draft,issued,partially_paid,paid,cancelled',
        ]);

        $validated['invoice_code'] = Invoice::generateCode();
        $validated['issued_at'] = now();
        $validated['created_by'] = $request->user()->id;

        $invoice = Invoice::create($validated);

        return response()->json(
            $invoice->load('project:id,project_code'),
            201
        );
    }

    public function show(Invoice $invoice)
    {
        return response()->json(
            $invoice->load([
                'project:id,project_code,person_id',
                'project.person:id,name',
                'collections',
                'collections.collector:id,name',
                'creator:id,name',
            ])
        );
    }
}
