<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::with([
            'invoice:id,invoice_code,total_amount,project_id',
            'invoice.project:id,project_code',
            'collector:id,name',
        ]);

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }
        if ($request->filled('from_date')) {
            $query->where('collection_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('collection_date', '<=', $request->to_date);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'collection_date' => 'required|date',
            'method' => 'required|in:bank_transfer,cash',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $collection = Collection::create($validated);

        return response()->json(
            $collection->load('invoice:id,invoice_code'),
            201
        );
    }

    public function show(Collection $collection)
    {
        return response()->json(
            $collection->load([
                'invoice:id,invoice_code,total_amount',
                'collector:id,name',
                'verifier:id,name',
            ])
        );
    }

    public function verify(Request $request, Collection $collection)
    {
        $collection->update([
            'verified' => true,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return response()->json($collection->load('verifier:id,name'));
    }
}
