<?php

namespace App\Http\Controllers;

use App\Models\GithubCommit;
use Illuminate\Http\Request;
use App\Traits\DateFilterable;

class GithubCommitController extends Controller
{
    use DateFilterable;

    public function index(Request $request)
    {
        $query = GithubCommit::with('employee');

        if ($request->filled('employee')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->employee . '%');
            });
        }

        $filter = $request->get('date_filter', 'all_time');
        [$start, $end] = $this->getDateRange($filter, $request->get('start_date'), $request->get('end_date'));

        if ($filter !== 'all_time' && $start && $end) {
            $query->whereBetween('commit_date', [$start, $end]);
        }

        $commits = $query->latest('commit_date')->paginate(10)->withQueryString();
        return view('commits', compact('commits'));
    }

    public function diff($id)
    {
        $commit = GithubCommit::findOrFail($id);
        
        if (!$commit->project_id || !$commit->commit_hash) {
            return response()->json(['error' => 'Missing project ID or commit hash to fetch diff.'], 400);
        }

        try {
            $url = "http://167.172.235.254/api/v4/projects/{$commit->project_id}/repository/commits/{$commit->commit_hash}/diff";
            $token = env('GITLAB_TOKEN') ?: env('GITLAB_WEBHOOK_SECRET');
            
            $headers = [];
            if ($token) {
                $headers['PRIVATE-TOKEN'] = $token;
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Failed to fetch diff from GitLab API. Status: ' . $response->status()], $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching diff: ' . $e->getMessage()], 500);
        }
    }
}
