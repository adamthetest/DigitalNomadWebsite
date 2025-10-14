<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use App\Models\JobUserInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Display a listing of jobs.
     */
    public function index(Request $request)
    {
        $query = Job::with('company')
            ->active()
            ->published()
            ->notExpired();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by job type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by remote type
        if ($request->filled('remote_type')) {
            $query->byRemoteType($request->remote_type);
        }

        // Filter by salary range
        if ($request->filled('salary_min') || $request->filled('salary_max')) {
            $min = $request->filled('salary_min') ? (int) $request->salary_min : 0;
            $max = $request->filled('salary_max') ? (int) $request->salary_max : null;
            $query->bySalaryRange($min, $max);
        }

        // Filter by tags/skills
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->byTags($tags);
        }

        // Filter by visa support
        if ($request->filled('visa_support')) {
            $query->visaFriendly();
        }

        // Filter by date posted
        if ($request->filled('date_posted')) {
            switch ($request->date_posted) {
                case '24h':
                    $query->where('created_at', '>=', now()->subDay());
                    break;
                case '7d':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case '30d':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
            }
        }

        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'salary_high':
                $query->orderBy('salary_max', 'desc')->orderBy('salary_min', 'desc');
                break;
            case 'salary_low':
                $query->orderBy('salary_min', 'asc');
                break;
            case 'featured':
                $query->orderBy('featured', 'desc')->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $jobs = $query->paginate(12)->withQueryString();

        // Get filter options for the form
        $jobTypes = [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'freelance' => 'Freelance',
            'internship' => 'Internship',
        ];

        $remoteTypes = [
            'fully-remote' => 'Fully Remote',
            'hybrid' => 'Hybrid',
            'timezone-limited' => 'Timezone Limited',
            'onsite' => 'On-site',
        ];

        $sortOptions = [
            'newest' => 'Newest First',
            'salary_high' => 'Highest Salary',
            'salary_low' => 'Lowest Salary',
            'featured' => 'Featured Jobs',
        ];

        return view('jobs.index', compact('jobs', 'jobTypes', 'remoteTypes', 'sortOptions'));
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job)
    {
        // Check if job is active and published
        if (! $job->is_active || ! $job->isPublished()) {
            abort(404);
        }

        // Increment view count
        $job->incrementViews();

        // Get user's interaction with this job (if authenticated)
        $userInteraction = null;
        if (Auth::check()) {
            $userInteraction = JobUserInteraction::where('user_id', Auth::id())
                ->where('job_id', $job->id)
                ->first();
        }

        // Get related jobs from the same company
        $relatedJobs = Job::with('company')
            ->where('company_id', $job->company_id)
            ->where('id', '!=', $job->id)
            ->active()
            ->published()
            ->notExpired()
            ->limit(4)
            ->get();

        return view('jobs.show', compact('job', 'userInteraction', 'relatedJobs'));
    }

    /**
     * Save or unsave a job for the authenticated user.
     */
    public function toggleSave(Request $request, Job $job)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        $interaction = JobUserInteraction::where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->first();

        if ($interaction) {
            if ($interaction->status === 'saved') {
                $interaction->delete();
                $saved = false;
            } else {
                $interaction->updateStatus('saved');
                $saved = true;
            }
        } else {
            JobUserInteraction::create([
                'user_id' => $user->id,
                'job_id' => $job->id,
                'status' => 'saved',
            ]);
            $saved = true;
        }

        return response()->json(['saved' => $saved]);
    }

    /**
     * Apply to a job.
     */
    public function apply(Request $request, Job $job)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to apply for jobs.');
        }

        $user = Auth::user();

        // Check if user already applied
        $existingInteraction = JobUserInteraction::where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->first();

        if ($existingInteraction && $existingInteraction->status === 'applied') {
            return redirect()->back()->with('error', 'You have already applied to this job.');
        }

        // Create or update interaction
        if ($existingInteraction) {
            $existingInteraction->updateStatus('applied', [
                'application_data' => $request->only(['cover_letter', 'resume_url', 'portfolio_url']),
            ]);
        } else {
            JobUserInteraction::create([
                'user_id' => $user->id,
                'job_id' => $job->id,
                'status' => 'applied',
                'applied_at' => now(),
                'application_data' => $request->only(['cover_letter', 'resume_url', 'portfolio_url']),
            ]);
        }

        // Increment applications count
        $job->incrementApplications();

        return redirect()->back()->with('success', 'Application submitted successfully!');
    }

    /**
     * Display company profile.
     */
    public function company(Company $company)
    {
        $company->load('activeJobs');

        $jobs = $company->activeJobs()
            ->published()
            ->notExpired()
            ->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('jobs.company', compact('company', 'jobs'));
    }
}
