<?php

namespace App\Livewire;

use App\Models\Job;
use App\Models\JobMatch;
use App\Services\JobMatchingService;
use App\Services\ResumeOptimizationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JobRecommendationsWidget extends Component
{
    public $recommendations = [];

    public $loading = false;

    public $error = null;

    public $showResumeOptimization = false;

    public $showCoverLetter = false;

    public $selectedJob = null;

    public $resumeContent = '';

    public $optimizedResume = '';

    public $coverLetter = '';

    public $optimizationLoading = false;

    public $coverLetterLoading = false;

    private JobMatchingService $jobMatchingService;

    private ResumeOptimizationService $resumeOptimizationService;

    public function boot(
        JobMatchingService $jobMatchingService,
        ResumeOptimizationService $resumeOptimizationService
    ) {
        $this->jobMatchingService = $jobMatchingService;
        $this->resumeOptimizationService = $resumeOptimizationService;
    }

    public function mount()
    {
        if (Auth::check()) {
            $this->loadRecommendations();
        }
    }

    public function loadRecommendations()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $user = Auth::user();
            $matches = $this->jobMatchingService->findMatchingJobs($user, 5);

            $this->recommendations = [];
            foreach ($matches as $match) {
                $jobMatch = $this->jobMatchingService->storeJobMatch($user, $match['job'], $match['score']);

                $this->recommendations[] = [
                    'job_match_id' => $jobMatch->id,
                    'job' => $match['job'],
                    'score' => $match['score']['overall_score'],
                    'quality_level' => $jobMatch->quality_level,
                    'quality_color' => $jobMatch->quality_color,
                    'ai_insights' => $match['ai_insights'] ?? null,
                ];
            }

        } catch (\Exception $e) {
            $this->error = 'Failed to load job recommendations: '.$e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function optimizeResume($jobId)
    {
        $this->selectedJob = Job::find($jobId);
        $this->optimizationLoading = true;
        $this->showResumeOptimization = true;

        try {
            $user = Auth::user();
            $optimization = $this->resumeOptimizationService->optimizeResumeForJob($user, $this->selectedJob);

            $this->optimizedResume = $optimization['optimized_resume'] ?? '';
            $this->resumeContent = $user->resume_content ?? '';

        } catch (\Exception $e) {
            $this->error = 'Failed to optimize resume: '.$e->getMessage();
        } finally {
            $this->optimizationLoading = false;
        }
    }

    public function generateCoverLetter($jobId)
    {
        $this->selectedJob = Job::find($jobId);
        $this->coverLetterLoading = true;
        $this->showCoverLetter = true;

        try {
            $user = Auth::user();
            $coverLetter = $this->resumeOptimizationService->generateCoverLetter($user, $this->selectedJob);

            $this->coverLetter = $coverLetter['cover_letter'] ?? '';

        } catch (\Exception $e) {
            $this->error = 'Failed to generate cover letter: '.$e->getMessage();
        } finally {
            $this->coverLetterLoading = false;
        }
    }

    public function markAsViewed($jobMatchId)
    {
        try {
            $jobMatch = JobMatch::find($jobMatchId);
            if ($jobMatch && $jobMatch->user_id === Auth::id()) {
                $jobMatch->markAsViewed();
            }
        } catch (\Exception $e) {
            // Silently fail for now
        }
    }

    public function markAsApplied($jobMatchId)
    {
        try {
            $jobMatch = JobMatch::find($jobMatchId);
            if ($jobMatch && $jobMatch->user_id === Auth::id()) {
                $jobMatch->markAsApplied();
                $this->dispatch('job-applied', ['job_match_id' => $jobMatchId]);
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to mark as applied: '.$e->getMessage();
        }
    }

    public function markAsSaved($jobMatchId)
    {
        try {
            $jobMatch = JobMatch::find($jobMatchId);
            if ($jobMatch && $jobMatch->user_id === Auth::id()) {
                $jobMatch->markAsSaved();
                $this->dispatch('job-saved', ['job_match_id' => $jobMatchId]);
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to save job: '.$e->getMessage();
        }
    }

    public function closeModals()
    {
        $this->showResumeOptimization = false;
        $this->showCoverLetter = false;
        $this->selectedJob = null;
        $this->optimizedResume = '';
        $this->coverLetter = '';
    }

    public function render()
    {
        return view('livewire.job-recommendations-widget');
    }
}
