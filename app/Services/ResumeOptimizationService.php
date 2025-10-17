<?php

namespace App\Services;

use App\Models\User;
use App\Models\Job;
use App\Services\OpenAiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Resume Optimization Service
 * 
 * Handles AI-powered resume optimization and cover letter generation
 */
class ResumeOptimizationService
{
    private OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Optimize resume for a specific job
     */
    public function optimizeResumeForJob(User $user, Job $job, string $resumeContent = null): array
    {
        $resumeContent = $resumeContent ?? $user->resume_content;
        
        if (empty($resumeContent)) {
            return [
                'success' => false,
                'message' => 'No resume content found. Please upload your resume first.',
                'optimized_resume' => null,
                'suggestions' => []
            ];
        }

        try {
            $jobData = $this->buildJobData($job);
            $optimization = $this->openAiService->optimizeResumeForJob($resumeContent, $jobData);
            
            return [
                'success' => true,
                'message' => 'Resume optimized successfully',
                'optimized_resume' => $optimization['optimized_resume'] ?? $resumeContent,
                'changes_made' => $optimization['changes_made'] ?? [],
                'skills_to_highlight' => $optimization['skills_to_highlight'] ?? [],
                'keywords' => $optimization['keywords'] ?? [],
                'suggestions' => $this->generateResumeSuggestions($user, $job, $optimization)
            ];
            
        } catch (\Exception $e) {
            Log::error('Resume optimization failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Resume optimization failed. Please try again later.',
                'optimized_resume' => $resumeContent,
                'suggestions' => $this->getBasicResumeSuggestions($job)
            ];
        }
    }

    /**
     * Generate a personalized cover letter
     */
    public function generateCoverLetter(User $user, Job $job): array
    {
        try {
            $userProfile = $this->buildUserProfile($user);
            $jobData = $this->buildJobData($job);
            
            $coverLetter = $this->openAiService->generateCoverLetter($userProfile, $jobData);
            
            return [
                'success' => true,
                'message' => 'Cover letter generated successfully',
                'cover_letter' => $coverLetter['cover_letter'] ?? '',
                'key_points' => $coverLetter['key_points'] ?? [],
                'suggestions' => $this->generateCoverLetterSuggestions($user, $job)
            ];
            
        } catch (\Exception $e) {
            Log::error('Cover letter generation failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Cover letter generation failed. Please try again later.',
                'cover_letter' => $this->getFallbackCoverLetter($user, $job),
                'key_points' => ['Relevant experience', 'Company interest'],
                'suggestions' => []
            ];
        }
    }

    /**
     * Extract skills from job description
     */
    public function extractSkillsFromJob(Job $job): array
    {
        try {
            $jobDescription = $job->description . ' ' . ($job->requirements ?? '');
            $skills = $this->openAiService->extractSkillsFromJob($jobDescription);
            
            return [
                'success' => true,
                'skills' => $skills,
                'message' => 'Skills extracted successfully'
            ];
            
        } catch (\Exception $e) {
            Log::error('Skills extraction failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'skills' => $this->getBasicSkills($job),
                'message' => 'Skills extraction failed, showing basic skills'
            ];
        }
    }

    /**
     * Upload and process resume file
     */
    public function uploadResume(User $user, $file): array
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Invalid file upload'
                ];
            }

            // Check file type
            $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
            $extension = $file->getClientOriginalExtension();
            
            if (!in_array(strtolower($extension), $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Only PDF, DOC, DOCX, and TXT files are allowed'
                ];
            }

            // Store file
            $filename = 'resume_' . $user->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('resumes', $filename, 'private');
            
            // Extract text content (simplified - in production you'd use a proper parser)
            $content = $this->extractTextFromFile($file, $extension);
            
            // Update user record
            $user->update([
                'resume_file_path' => $path,
                'resume_content' => $content,
                'resume_metadata' => [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'uploaded_at' => now()->toISOString(),
                ],
                'last_profile_update' => now(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Resume uploaded and processed successfully',
                'content' => $content,
                'metadata' => $user->resume_metadata
            ];
            
        } catch (\Exception $e) {
            Log::error('Resume upload failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Resume upload failed. Please try again.'
            ];
        }
    }

    /**
     * Generate resume optimization suggestions
     */
    public function generateResumeSuggestions(User $user, Job $job, array $optimization = []): array
    {
        $suggestions = [];
        
        // Skills gap analysis
        $userSkills = $user->skills ?? [];
        $jobSkills = $job->skills_required ?? [];
        $missingSkills = array_diff($jobSkills, $userSkills);
        
        if (!empty($missingSkills)) {
            $suggestions[] = [
                'type' => 'skills_gap',
                'title' => 'Skills to Develop',
                'description' => 'Consider developing these skills to improve your match:',
                'items' => array_slice($missingSkills, 0, 5)
            ];
        }
        
        // Experience alignment
        $userExperience = $user->experience_years ?? 0;
        $jobExperienceLevel = $job->experience_level ?? [];
        
        if (!empty($jobExperienceLevel)) {
            $experienceMap = ['entry' => 0, 'junior' => 2, 'mid' => 5, 'senior' => 8, 'lead' => 12];
            $jobMinYears = min(array_map(fn($level) => $experienceMap[$level] ?? 0, $jobExperienceLevel));
            
            if ($userExperience < $jobMinYears) {
                $suggestions[] = [
                    'type' => 'experience',
                    'title' => 'Experience Gap',
                    'description' => 'Consider highlighting relevant projects or freelance work to demonstrate experience.',
                    'items' => ['Add project portfolios', 'Highlight relevant achievements', 'Emphasize transferable skills']
                ];
            }
        }
        
        // Formatting suggestions
        $suggestions[] = [
            'type' => 'formatting',
            'title' => 'Resume Formatting',
            'description' => 'Improve your resume presentation:',
            'items' => [
                'Use bullet points for achievements',
                'Include quantifiable results',
                'Keep formatting consistent',
                'Use action verbs',
                'Tailor keywords to job description'
            ]
        ];
        
        return $suggestions;
    }

    /**
     * Build user profile for AI processing
     */
    private function buildUserProfile(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'profession' => $user->profession ?? '',
            'skills' => $user->skills ?? [],
            'experience_years' => $user->experience_years ?? 0,
            'education_level' => $user->education_level ?? '',
            'location' => $user->current_location ?? '',
            'bio' => $user->bio ?? '',
            'ai_profile_summary' => $user->ai_profile_summary ?? '',
        ];
    }

    /**
     * Build job data for AI processing
     */
    private function buildJobData(Job $job): array
    {
        return [
            'title' => $job->title,
            'description' => $job->description,
            'requirements' => $job->requirements ?? '',
            'skills_required' => $job->skills_required ?? [],
            'experience_level' => $job->experience_level ?? [],
            'job_type' => $job->job_type,
            'remote_type' => $job->remote_type,
            'location' => $job->location,
            'company_name' => $job->company->name ?? '',
            'company_description' => $job->company->description ?? '',
            'company_culture' => $job->company->culture ?? '',
        ];
    }

    /**
     * Extract text from uploaded file
     */
    private function extractTextFromFile($file, string $extension): string
    {
        // This is a simplified implementation
        // In production, you'd use proper libraries like PhpWord for DOCX, etc.
        
        if ($extension === 'txt') {
            return file_get_contents($file->getPathname());
        }
        
        // For other formats, return a placeholder
        // In production, implement proper text extraction
        return "Resume content extraction not implemented for {$extension} files. Please copy and paste your resume content manually.";
    }

    /**
     * Generate cover letter suggestions
     */
    private function generateCoverLetterSuggestions(User $user, Job $job): array
    {
        return [
            [
                'type' => 'personalization',
                'title' => 'Personalization Tips',
                'description' => 'Make your cover letter stand out:',
                'items' => [
                    'Research the company culture',
                    'Mention specific company values',
                    'Reference recent company news',
                    'Show genuine interest in the role'
                ]
            ],
            [
                'type' => 'structure',
                'title' => 'Cover Letter Structure',
                'description' => 'Follow this structure:',
                'items' => [
                    'Opening: Why you\'re interested',
                    'Body: Relevant experience and skills',
                    'Closing: Call to action and enthusiasm'
                ]
            ]
        ];
    }

    /**
     * Get basic resume suggestions when AI fails
     */
    private function getBasicResumeSuggestions(Job $job): array
    {
        return [
            [
                'type' => 'general',
                'title' => 'General Resume Tips',
                'description' => 'Improve your resume:',
                'items' => [
                    'Highlight relevant skills for ' . $job->title,
                    'Use keywords from the job description',
                    'Include quantifiable achievements',
                    'Keep it concise and well-formatted'
                ]
            ]
        ];
    }

    /**
     * Get basic skills when extraction fails
     */
    private function getBasicSkills(Job $job): array
    {
        $skills = [];
        
        // Extract basic skills from job title and description
        $text = strtolower($job->title . ' ' . $job->description);
        
        $commonSkills = [
            'javascript', 'python', 'php', 'java', 'react', 'vue', 'angular',
            'node.js', 'laravel', 'django', 'mysql', 'postgresql', 'mongodb',
            'aws', 'docker', 'kubernetes', 'git', 'agile', 'scrum',
            'communication', 'leadership', 'problem solving', 'teamwork'
        ];
        
        foreach ($commonSkills as $skill) {
            if (strpos($text, $skill) !== false) {
                $skills[] = ucfirst($skill);
            }
        }
        
        return array_unique($skills);
    }

    /**
     * Get fallback cover letter when AI fails
     */
    private function getFallbackCoverLetter(User $user, Job $job): string
    {
        return "Dear Hiring Manager,\n\n" .
               "I am writing to express my interest in the {$job->title} position at {$job->company->name ?? 'your company'}.\n\n" .
               "With my background in " . ($user->profession ?? 'my field') . " and " . ($user->experience_years ?? 0) . " years of experience, " .
               "I believe I would be a valuable addition to your team.\n\n" .
               "I am particularly drawn to this opportunity because of [your specific interest in the role/company]. " .
               "My skills in " . implode(', ', array_slice($user->skills ?? [], 0, 3)) . " align well with the requirements for this position.\n\n" .
               "I would welcome the opportunity to discuss how my experience and enthusiasm can contribute to your team's success.\n\n" .
               "Thank you for your consideration.\n\n" .
               "Best regards,\n" .
               $user->name;
    }
}
