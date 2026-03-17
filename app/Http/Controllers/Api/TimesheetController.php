<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $query = Timesheet::with('project:id,project_code', 'creator:id,name');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->where('date_logged', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date_logged', '<=', $request->date_to);
        }
        if ($request->filled('profession_name')) {
            $query->where('profession_name', 'like', "%{$request->profession_name}%");
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'date_logged' => 'nullable|date',
            'profession_name' => 'required|string',
            'rate_per_hour' => 'required|numeric|min:0',
            'total_hours' => 'required|numeric|min:0',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        $validated['created_by'] = $request->user()->id;

        $timesheet = Timesheet::create($validated);

        return response()->json($timesheet->load('project:id,project_code'), 201);
    }

    public function show(Timesheet $timesheet)
    {
        return response()->json($timesheet->load(['project:id,project_code', 'creator:id,name']));
    }

    public function update(Request $request, Timesheet $timesheet)
    {
        $validated = $request->validate([
            'date_logged' => 'nullable|date',
            'profession_name' => 'sometimes|string',
            'rate_per_hour' => 'sometimes|numeric|min:0',
            'total_hours' => 'sometimes|numeric|min:0',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        $timesheet->update($validated);

        return response()->json($timesheet);
    }

    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();

        return response()->json(['message' => 'Timesheet deleted']);
    }
}
