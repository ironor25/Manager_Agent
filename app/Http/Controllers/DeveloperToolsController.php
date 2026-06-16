<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeveloperToolsController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $apiKeys = $user->apiKeys()->orderBy('created_at', 'desc')->get();
        return view('developer-tools.index', compact('apiKeys'));
    }
}
