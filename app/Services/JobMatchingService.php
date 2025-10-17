<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobMatch;
use App\Models\User;

/**
 * Job Matching Service
 *
 * Handles intelligent job matching using AI and algorithmic approaches
 */
class JobMatchingService
{
    private OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Find matching jobs for a user
     */
    public function findMatchingJobs(User $user, int $limit = 10): array
    {
        $userProfile = $this->buildUserProfile($user);
        $jobs = $this->getEligibleJobs($user);

        $matches = [];

        foreach ($jobs as $job) {
            /** @var Job $job */
            $matchScore = $this->calculateMatchScore($userProfile, $job);

            if ($matchScore['overall_score'] >= 60) { // Only include decent matches
                $matches[] = [
                    'job' => $job,
                    'score' => $matchScore,
                    'ai_insights' => $this->getAiInsights($userProfile, $job),
                ];
            }
        }

        // Sort by overall score descending
        usort($matches, function ($a, $b) {
            return $b['score']['overall_score'] <=> $a['score']['overall_score'];
        });

        return array_slice($matches, 0, $limit);
    }

    /**
     * Calculate detailed match score between user and job
     */
    public function calculateMatchScore(array $userProfile, Job $job): array
    {
        $scores = [
            'skills_score' => $this->calculateSkillsScore($userProfile, $job),
            'experience_score' => $this->calculateExperienceScore($userProfile, $job),
            'location_score' => $this->calculateLocationScore($userProfile, $job),
            'salary_score' => $this->calculateSalaryScore($userProfile, $job),
            'culture_score' => $this->calculateCultureScore($userProfile, $job),
        ];

        // Calculate weighted overall score
        $weights = [
            'skills_score' => 0.3,
            'experience_score' => 0.25,
            'location_score' => 0.2,
            'salary_score' => 0.15,
            'culture_score' => 0.1,
        ];

        $overallScore = 0;
        foreach ($scores as $type => $score) {
            $overallScore += $score * $weights[$type];
        }

        $scores['overall_score'] = round($overallScore, 2);

        return $scores;
    }

    /**
     * Store job match in database
     */
    public function storeJobMatch(User $user, Job $job, array $matchData): JobMatch
    {
        return JobMatch::updateOrCreate(
            ['user_id' => $user->id, 'job_id' => $job->id],
            [
                'overall_score' => $matchData['overall_score'],
                'skills_score' => $matchData['skills_score'] ?? null,
                'experience_score' => $matchData['experience_score'] ?? null,
                'location_score' => $matchData['location_score'] ?? null,
                'salary_score' => $matchData['salary_score'] ?? null,
                'culture_score' => $matchData['culture_score'] ?? null,
                'matching_factors' => $matchData['matching_factors'] ?? null,
                'ai_insights' => $matchData['ai_insights'] ?? null,
                'match_reasoning' => $matchData['match_reasoning'] ?? null,
                'recommendation_type' => $matchData['recommendation_type'] ?? 'algorithmic',
                'ai_application_tips' => $matchData['ai_application_tips'] ?? null,
                'ai_resume_suggestions' => $matchData['ai_resume_suggestions'] ?? null,
                'ai_cover_letter_tips' => $matchData['ai_cover_letter_tips'] ?? null,
            ]
        );
    }

    /**
     * Get AI insights for a job match
     */
    public function getAiInsights(array $userProfile, Job $job): array
    {
        $jobData = $this->buildJobData($job);

        return $this->openAiService->generateJobMatchingInsights($userProfile, $jobData);
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

    /**
     * Get eligible jobs for matching
     */
    private function getEligibleJobs(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Job::with(['company'])
            ->active()
            ->published()
            ->notExpired()
            ->where(function ($query) use ($user) {
                // Filter by user preferences
                if (! empty($user->preferred_job_types)) {
                    $query->whereIn('job_type', $user->preferred_job_types);
                }

                if (! empty($user->preferred_remote_types)) {
                    $query->whereIn('remote_type', $user->preferred_remote_types);
                }

                // Filter by salary expectations
                if ($user->budget_monthly_min > 0) {
                    $query->where(function ($q) use ($user) {
                        $q->whereNull('salary_min')
                            ->orWhere('salary_min', '>=', $user->budget_monthly_min);
                    });
                }

                if ($user->budget_monthly_max > 0) {
                    $query->where(function ($q) use ($user) {
                        $q->whereNull('salary_max')
                            ->orWhere('salary_max', '<=', $user->budget_monthly_max);
                    });
                }
            })
            ->get();
    }

    /**
     * Calculate skills matching score
     */
    private function calculateSkillsScore(array $userProfile, Job $job): float
    {
        $userSkills = $userProfile['skills'] ?? [];
        $jobSkills = $job->skills_required ?? [];

        if (empty($jobSkills)) {
            return 70; // Neutral score if no skills specified
        }

        $matchingSkills = array_intersect($userSkills, $jobSkills);
        $matchPercentage = count($matchingSkills) / count($jobSkills);

        return min(100, $matchPercentage * 100 + 20); // Bonus for partial matches
    }

    /**
     * Calculate experience matching score
     */
    private function calculateExperienceScore(array $userProfile, Job $job): float
    {
        $userExperience = $userProfile['experience_years'] ?? 0;
        $jobExperienceLevel = $job->experience_level ?? [];

        if (empty($jobExperienceLevel)) {
            return 75; // Neutral score
        }

        // Map experience levels to years
        $experienceMap = [
            'entry' => 0,
            'junior' => 2,
            'mid' => 5,
            'senior' => 8,
            'lead' => 12,
            'executive' => 15,
        ];

        $jobMinYears = min(array_map(fn ($level) => $experienceMap[$level] ?? 0, $jobExperienceLevel));
        $jobMaxYears = max(array_map(fn ($level) => $experienceMap[$level] ?? 0, $jobExperienceLevel));

        if ($userExperience >= $jobMinYears && $userExperience <= $jobMaxYears + 3) {
            return 90; // Good match
        } elseif ($userExperience < $jobMinYears) {
            return max(30, 100 - ($jobMinYears - $userExperience) * 10); // Penalty for under-qualification
        } else {
            return max(60, 100 - ($userExperience - $jobMaxYears) * 5); // Slight penalty for over-qualification
        }
    }

    /**
     * Calculate location compatibility score
     */
    private function calculateLocationScore(array $userProfile, Job $job): float
    {
        $userLocations = $userProfile['preferred_locations'] ?? [];
        $jobLocation = $job->location;
        $jobRemoteType = $job->remote_type;

        // If job is fully remote, high score
        if ($jobRemoteType === 'fully_remote') {
            return 95;
        }

        // If job is hybrid/onsite and user has location preferences
        if (! empty($userLocations) && $jobLocation) {
            $locationMatch = false;
            foreach ($userLocations as $preferredLocation) {
                if (stripos($jobLocation, $preferredLocation) !== false) {
                    $locationMatch = true;
                    break;
                }
            }

            return $locationMatch ? 90 : 40;
        }

        return 70; // Neutral score
    }

    /**
     * Calculate salary expectation score
     */
    private function calculateSalaryScore(array $userProfile, Job $job): float
    {
        $userMinSalary = $userProfile['budget_monthly_min'] ?? 0;
        $userMaxSalary = $userProfile['budget_monthly_max'] ?? 0;
        $jobMinSalary = $job->salary_min ?? 0;
        $jobMaxSalary = $job->salary_max ?? 0;

        if ($jobMinSalary === 0 && $jobMaxSalary === 0) {
            return 75; // Neutral score if no salary info
        }

        if ($userMinSalary === 0 && $userMaxSalary === 0) {
            return 80; // User has no salary expectations
        }

        // Check if salary ranges overlap
        $userRange = [$userMinSalary, $userMaxSalary ?: $userMinSalary * 1.5];
        $jobRange = [$jobMinSalary, $jobMaxSalary ?: $jobMinSalary * 1.5];

        if ($userRange[1] >= $jobRange[0] && $userRange[0] <= $jobRange[1]) {
            return 90; // Good overlap
        } elseif ($userRange[0] > $jobRange[1]) {
            return max(20, 100 - ($userRange[0] - $jobRange[1]) / $jobRange[1] * 100); // User expects more
        } else {
            return max(30, 100 - ($jobRange[0] - $userRange[1]) / $userRange[1] * 100); // Job pays more than user expects
        }
    }

    /**
     * Calculate company culture fit score
     */
    private function calculateCultureScore(array $userProfile, Job $job): float
    {
        // This is a simplified calculation
        // In a real implementation, you'd analyze company culture, values, etc.

        $userWorkType = $userProfile['work_type_preferences'] ?? [];
        $jobType = $job->type;

        if (in_array($jobType, $userWorkType)) {
            return 85;
        }

        return 70; // Neutral score
    }
}
