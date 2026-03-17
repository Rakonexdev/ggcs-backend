<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $query = Person::with('company:id,name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('qatar_id', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:persons,phone',
            'qatar_id' => 'required|string|unique:persons,qatar_id',
            'id_expiration_date' => 'required|date',
            'id_photo_url' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $person = Person::create($validated);

        return response()->json($person->load('company:id,name'), 201);
    }

    public function show(Person $person)
    {
        return response()->json(
            $person->load(['company:id,name', 'projects', 'projects.invoices'])
        );
    }

    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:persons,phone,' . $person->id,
            'qatar_id' => 'sometimes|string|unique:persons,qatar_id,' . $person->id,
            'id_expiration_date' => 'sometimes|date',
            'id_photo_url' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $person->update($validated);

        return response()->json($person->load('company:id,name'));
    }
}
