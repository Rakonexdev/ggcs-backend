<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function idPhoto(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = $request->file('file')->store('id-photos', 'public');

        return response()->json(['url' => Storage::url($path)]);
    }

    public function lpo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $path = $request->file('file')->store('lpo-files', 'public');

        return response()->json(['url' => Storage::url($path)]);
    }

    public function invoiceCopy(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $path = $request->file('file')->store('invoice-copies', 'public');

        return response()->json(['url' => Storage::url($path)]);
    }

    public function expenseDocument(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $path = $request->file('file')->store('expense-documents', 'public');

        return response()->json(['url' => Storage::url($path)]);
    }
}
