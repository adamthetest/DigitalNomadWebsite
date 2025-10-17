<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobMatch;
use App\Models\User;
use App\Services\JobMatchingService;
use App\Services\ResumeOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobMatchingController extends Controller
{
    private JobMatchingService $jobMatchingService;

    private ResumeOptimizationService $resumeOptimizationService;

    public function __construct(
        JobMatchingService $jobMatchingService,
        ResumeOptimizationService $resumeOptimizationService
    ) {
        $this->jobMatchingService = $jobMatchingService;
        $this->resumeOptimizationService = $resumeOptimizationService;
    }

    /**
     * Get job recommendations for the authenticated user
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $limit = min($request->get('limit', 10), 50); // Max 50 recommendations

        try {
            $matches = $this->jobMatchingService->findMatchingJobs($user, $limit);

            $recommendations = [];
            foreach ($matches as $match) {
                $jobMatch = $this->jobMatchingService->storeJobMatch($user, $match['job'], $match['score']);

                $recommendations[] = [
                    'job_match_id' => $jobMatch->id,
                    'job' => [
                        'id' => $match['job']->id,
                        'title' => $match['job']->title,
                        'company' => $match['job']->company->name ?? 'Unknown Company',
                        'location' => $match['job']->location,
                        'remote_type' => $match['job']->remote_type,
                        'salary_range' => $match['job']->formatted_salary,
                        'description' => $match['job']->description,
                        'requirements' => $match['job']->requirements,
                        'skills_required' => $match['job']->skills_required ?? [],
                        'experience_level' => $match['job']->experience_level ?? [],
                        'apply_url' => $match['job']->apply_url,
                        'expires_at' => $match['job']->expires_at,
                    ],
                    'match_score' => $match['score']['overall_score'],
                    'match_breakdown' => [
                        'skills_score' => $match['score']['skills_score'] ?? 0,
                        'experience_score' => $match['score']['experience_score'] ?? 0,
                        'location_score' => $match['score']['location_score'] ?? 0,
                        'salary_score' => $match['score']['salary_score'] ?? 0,
                        'culture_score' => $match['score']['culture_score'] ?? 0,
                    ],
                    'ai_insights' => $match['ai_insights'] ?? null,
                    'quality_level' => $jobMatch->quality_level,
                    'quality_color' => $jobMatch->quality_color,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'message' => 'Job recommendations generated successfully',
                'total_matches' => count($recommendations),
                'user_profile_completeness' => $user->profile_completion,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate job recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed match analysis for a specific job
     */
    public function getMatchAnalysis(Job $job): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $userProfile = $this->buildUserProfile($user);
            $jobData = $this->buildJobData($job);
            $matchScore = $this->jobMatchingService->calculateMatchScore($userProfile, $job);
            $aiInsights = $this->jobMatchingService->getAiInsights($userProfile, $job);

            // Store or update the match
            $jobMatch = $this->jobMatchingService->storeJobMatch($user, $job, $matchScore);

            return response()->json([
                'success' => true,
                'data' => [
                    'job_match_id' => $jobMatch->id,
                    'job' => [
                        'id' => $job->id,
                        'title' => $job->title,
                        'company' => $job->company->name ?? 'Unknown Company',
                        'description' => $job->description,
                        'requirements' => $job->requirements,
                        'skills_required' => $job->skills_required ?? [],
                        'experience_level' => $job->experience_level ?? [],
                        'location' => $job->location,
                        'remote_type' => $job->remote_type,
                        'salary_range' => $job->formatted_salary,
                        'visa_support' => $job->visa_support,
                        'apply_url' => $job->apply_url,
                    ],
                    'match_analysis' => [
                        'overall_score' => $matchScore['overall_score'],
                        'skills_score' => $matchScore['skills_score'] ?? 0,
                        'experience_score' => $matchScore['experience_score'] ?? 0,
                        'location_score' => $matchScore['location_score'] ?? 0,
                        'salary_score' => $matchScore['salary_score'] ?? 0,
                        'culture_score' => $matchScore['culture_score'] ?? 0,
                        'quality_level' => $jobMatch->quality_level,
                        'quality_color' => $jobMatch->quality_color,
                    ],
                    'ai_insights' => $aiInsights,
                    'matching_factors' => $jobMatch->formatted_matching_factors,
                    'recommendations' => [
                        'application_tips' => $jobMatch->application_tips,
                        'resume_suggestions' => $jobMatch->resume_suggestions,
                        'cover_letter_tips' => $jobMatch->cover_letter_tips,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze job match',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize resume for a specific job
     */
    public function optimizeResume(Request $request, Job $job): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $request->validate([
            'resume_content' => 'nullable|string|max:50000',
        ]);

        try {
            $resumeContent = $request->get('resume_content') ?? $user->resume_content;

            if (empty($resumeContent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No resume content provided. Please upload your resume first.',
                ], 400);
            }

            $optimization = $this->resumeOptimizationService->optimizeResumeForJob($user, $job, $resumeContent);

            return response()->json([
                'success' => $optimization['success'],
                'data' => [
                    'optimized_resume' => $optimization['optimized_resume'],
                    'changes_made' => $optimization['changes_made'] ?? [],
                    'skills_to_highlight' => $optimization['skills_to_highlight'] ?? [],
                    'keywords' => $optimization['keywords'] ?? [],
                    'suggestions' => $optimization['suggestions'] ?? [],
                ],
                'message' => $optimization['message'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize resume',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate cover letter for a specific job
     */
    public function generateCoverLetter(Job $job): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $coverLetter = $this->resumeOptimizationService->generateCoverLetter($user, $job);

            return response()->json([
                'success' => $coverLetter['success'],
                'data' => [
                    'cover_letter' => $coverLetter['cover_letter'],
                    'key_points' => $coverLetter['key_points'] ?? [],
                    'suggestions' => $coverLetter['suggestions'] ?? [],
                ],
                'message' => $coverLetter['message'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate cover letter',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload resume file
     */
    public function uploadResume(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $request->validate([
            'resume' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // 10MB max
        ]);

        try {
            $upload = $this->resumeOptimizationService->uploadResume($user, $request->file('resume'));

            return response()->json([
                'success' => $upload['success'],
                'data' => [
                    'content' => $upload['content'] ?? null,
                    'metadata' => $upload['metadata'] ?? null,
                ],
                'message' => $upload['message'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload resume',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract skills from job description
     */
    public function extractSkills(Job $job): JsonResponse
    {
        try {
            $skills = $this->resumeOptimizationService->extractSkillsFromJob($job);

            return response()->json([
                'success' => $skills['success'],
                'data' => [
                    'skills' => $skills['skills'] ?? [],
                ],
                'message' => $skills['message'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract skills',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark job match as viewed
     */
    public function markAsViewed(JobMatch $jobMatch): JsonResponse
    {
        $user = Auth::user();

        if (! $user || $jobMatch->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobMatch->markAsViewed();

        return response()->json([
            'success' => true,
            'message' => 'Job match marked as viewed',
        ]);
    }

    /**
     * Mark job match as applied
     */
    public function markAsApplied(JobMatch $jobMatch): JsonResponse
    {
        $user = Auth::user();

        if (! $user || $jobMatch->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobMatch->markAsApplied();

        return response()->json([
            'success' => true,
            'message' => 'Job match marked as applied',
        ]);
    }

    /**
     * Mark job match as saved
     */
    public function markAsSaved(JobMatch $jobMatch): JsonResponse
    {
        $user = Auth::user();

        if (! $user || $jobMatch->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobMatch->markAsSaved();

        return response()->json([
            'success' => true,
            'message' => 'Job match marked as saved',
        ]);
    }

    /**
     * Get user's job match history
     */
    public function getMatchHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $limit = min($request->get('limit', 20), 100);
        $type = $request->get('type', 'all'); // all, viewed, applied, saved

        $query = $user->jobMatches()->with(['job.company']);

        switch ($type) {
            case 'viewed':
                $query->where('user_viewed', true);
                break;
            case 'applied':
                $query->where('user_applied', true);
                break;
            case 'saved':
                $query->where('user_saved', true);
                break;
        }

        $matches = $query->orderBy('overall_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $matches->items(),
            'pagination' => [
                'current_page' => $matches->currentPage(),
                'last_page' => $matches->lastPage(),
                'per_page' => $matches->perPage(),
                'total' => $matches->total(),
            ],
        ]);
    }

    /**
     * Build user profile for matching
     */
    private function buildUserProfile(User $user): array
    {
        return [
            'skills' => $user->skills ?? [],
            'experience_years' => $user->experience_years ?? 0,
            'profession' => $user->profession ?? '',
            'education_level' => $user->education_level ?? '',
            'preferred_locations' => $user->preferred_locations ?? [],
            'preferred_climates' => $user->preferred_climates ?? [],
            'budget_monthly_min' => $user->budget_monthly_min ?? 0,
            'budget_monthly_max' => $user->budget_monthly_max ?? 0,
            'work_type_preferences' => $user->work_type_preferences ?? [],
            'remote_work_preferences' => $user->remote_work_preferences ?? [],
            'timezone_preferences' => $user->timezone_preferences ?? [],
            'salary_expectations' => $user->salary_expectations ?? [],
            'ai_profile_summary' => $user->ai_profile_summary ?? '',
        ];
    }

    /**
     * Build job data for matching
     */
    private function buildJobData(Job $job): array
    {
        return [
            'title' => $job->title,
            'description' => $job->description,
            'requirements' => $job->requirements ?? '',
            'skills_required' => $job->skills_required ?? [],
            'experience_level' => $job->experience_level ?? [],
            'job_type' => $job->type,
            'remote_type' => $job->remote_type,
            'location' => $job->location,
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'company_name' => $job->company->name ?? '',
            'company_description' => $job->company->description ?? '',
            'company_culture' => $job->company->culture ?? '',
            'visa_support' => $job->visa_support ?? false,
            'timezone_requirements' => $job->timezone_requirements ?? [],
        ];
    }
}
