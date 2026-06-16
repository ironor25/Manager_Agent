<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ApiKeyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $key = 'sk_agent_' . Str::random(32);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->apiKeys()->create([
            'name' => $request->name,
            'api_key' => $key,
            'is_active' => true,
        ]);

        return back()->with('success', 'API Key generated successfully.');
    }

    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $apiKey = $user->apiKeys()->findOrFail($id);
        $apiKey->delete();

        return back()->with('success', 'API Key deleted successfully.');
    }

    public function toggleStatus($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $apiKey = $user->apiKeys()->findOrFail($id);
        $apiKey->update([
            'is_active' => !$apiKey->is_active,
        ]);

        $status = $apiKey->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "API Key {$status} successfully.");
    }
}
