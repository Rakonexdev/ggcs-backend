<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectProfession;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('person:id,name,company_id', 'person.company:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('person_id')) {
            $query->where('person_id', $request->person_id);
        }
        if ($request->filled('search')) {
            $query->where('project_code', 'like', "%{$request->search}%");
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'type' => 'required|in:fixed,variable',
            'status' => 'nullable|in:draft,active,on_hold,completed,withdrawn',
            'lpo_url' => 'nullable|string',
            'fixed_total_amount' => 'nullable|numeric|min:0',
            'fixed_description' => 'nullable|string',
            'invoice_interval_days' => 'nullable|integer|min:1',
            'professions' => 'nullable|array',
            'professions.*.profession_name' => 'required_with:professions|string',
            'professions.*.hourly_rate' => 'required_with:professions|numeric|min:0',
            'professions.*.no_of_persons' => 'required_with:professions|integer|min:1',
        ]);

        $validated['project_code'] = Project::generateCode();

        $project = Project::create($validated);

        if ($request->type === 'variable' && !empty($validated['professions'])) {
            foreach ($validated['professions'] as $prof) {
                $project->professions()->create($prof);
            }
        }

        return response()->json(
            $project->load(['person:id,name', 'professions']),
            201
        );
    }

    public function show(Project $project)
    {
        return response()->json(
            $project->load([
                'person:id,name,phone,company_id',
                'person.company:id,name',
                'professions',
                'timesheets',
                'invoices',
                'invoices.collections',
            ])
        );
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'person_id' => 'sometimes|exists:persons,id',
            'type' => 'sometimes|in:fixed,variable',
            'lpo_url' => 'nullable|string',
            'fixed_total_amount' => 'nullable|numeric|min:0',
            'fixed_description' => 'nullable|string',
            'invoice_interval_days' => 'nullable|integer|min:1',
        ]);

        $project->update($validated);

        return response()->json($project->load(['person:id,name', 'professions']));
    }

    public function updateStatus(Request $request, Project $project)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,active,on_hold,completed,withdrawn',
            'withdrawal_reason' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    // --- Profession sub-resource ---

    public function storeProfession(Request $request, Project $project)
    {
        $validated = $request->validate([
            'profession_name' => 'required|string',
            'hourly_rate' => 'required|numeric|min:0',
            'no_of_persons' => 'required|integer|min:1',
        ]);

        $profession = $project->professions()->create($validated);

        return response()->json($profession, 201);
    }

    public function updateProfession(Request $request, Project $project, ProjectProfession $profession)
    {
        $validated = $request->validate([
            'profession_name' => 'sometimes|string',
            'hourly_rate' => 'sometimes|numeric|min:0',
            'no_of_persons' => 'sometimes|integer|min:1',
        ]);

        $profession->update($validated);

        return response()->json($profession);
    }

    public function destroyProfession(Project $project, ProjectProfession $profession)
    {
        $profession->delete();

        return response()->json(['message' => 'Profession removed']);
    }
}
