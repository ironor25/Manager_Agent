<?php

namespace App\Http\Controllers;

use App\Models\GithubCommit;
use Illuminate\Http\Request;

class GithubCommitController extends Controller
{
    public function index()
    {
        $commits = GithubCommit::with('employee')->latest('commit_date')->paginate(10);
        return view('commits', compact('commits'));
    }
}
