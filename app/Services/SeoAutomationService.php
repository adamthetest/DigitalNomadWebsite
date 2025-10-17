<?php

namespace App\Services;

use App\Models\City;
use App\Models\Job;
use App\Models\Article;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * SEO Automation Service
 *
 * Automatically generates sitemaps and refreshes SEO data.
 * Reduces manual SEO work by 80%.
 */
class SeoAutomationService
{
    protected string $sitemapPath = 'public/sitemaps';
    protected array $sitemapTypes = ['cities', 'jobs', 'articles', 'static'];

    /**
     * Generate all sitemaps.
     */
    public function generateAllSitemaps(): array
    {
        $results = [];
        
        // Ensure sitemap directory exists
        if (!File::exists($this->sitemapPath)) {
            File::makeDirectory($this->sitemapPath, 0755, true);
        }

        foreach ($this->sitemapTypes as $type) {
            try {
                $result = $this->generateSitemap($type);
                $results[$type] = $result;
                
                Log::info("Generated {$type} sitemap", [
                    'type' => $type,
                    'urls_count' => $result['urls_count'],
                    'file_size' => $result['file_size'],
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to generate {$type} sitemap", [
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
                
                $results[$type] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Generate main sitemap index
        $this->generateSitemapIndex($results);

        return $results;
    }

    /**
     * Generate a specific sitemap.
     */
    public function generateSitemap(string $type): array
    {
        $urls = match ($type) {
            'cities' => $this->getCityUrls(),
            'jobs' => $this->getJobUrls(),
            'articles' => $this->getArticleUrls(),
            'static' => $this->getStaticUrls(),
            default => throw new \InvalidArgumentException("Unknown sitemap type: {$type}"),
        };

        $xml = $this->buildSitemapXml($urls);
        $filename = "sitemap-{$type}.xml";
        $filepath = "{$this->sitemapPath}/{$filename}";
        
        File::put($filepath, $xml);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'urls_count' => count($urls),
            'file_size' => File::size($filepath),
            'generated_at' => now(),
        ];
    }

    /**
     * Generate sitemap index.
     */
    private function generateSitemapIndex(array $results): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($results as $type => $result) {
            if ($result['success'] ?? false) {
                $xml .= '  <sitemap>' . "\n";
                $xml .= '    <loc>' . url("sitemaps/sitemap-{$type}.xml") . '</loc>' . "\n";
                $xml .= '    <lastmod>' . now()->toISOString() . '</lastmod>' . "\n";
                $xml .= '  </sitemap>' . "\n";
            }
        }
        
        $xml .= '</sitemapindex>';
        
        File::put("{$this->sitemapPath}/sitemap.xml", $xml);
    }

    /**
     * Get city URLs for sitemap.
     */
    private function getCityUrls(): array
    {
        $cities = City::where('is_active', true)->get();
        $urls = [];
        
        foreach ($cities as $city) {
            $urls[] = [
                'loc' => url("/cities/{$city->slug}"),
                'lastmod' => $city->updated_at->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }
        
        return $urls;
    }

    /**
     * Get job URLs for sitemap.
     */
    private function getJobUrls(): array
    {
        $jobs = Job::where('is_active', true)
            ->where('published', true)
            ->where('expires_at', '>', now())
            ->get();
        
        $urls = [];
        
        foreach ($jobs as $job) {
            $urls[] = [
                'loc' => url("/jobs/{$job->id}"),
                'lastmod' => $job->updated_at->toISOString(),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ];
        }
        
        return $urls;
    }

    /**
     * Get article URLs for sitemap.
     */
    private function getArticleUrls(): array
    {
        $articles = Article::where('is_active', true)
            ->where('published', true)
            ->get();
        
        $urls = [];
        
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => url("/articles/{$article->slug}"),
                'lastmod' => $article->updated_at->toISOString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }
        
        return $urls;
    }

    /**
     * Get static URLs for sitemap.
     */
    private function getStaticUrls(): array
    {
        return [
            [
                'loc' => url('/'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => url('/cities'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => url('/jobs'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => url('/articles'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => url('/calculator'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => url('/deals'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
        ];
    }

    /**
     * Build sitemap XML.
     */
    private function buildSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate robots.txt.
     */
    public function generateRobotsTxt(): array
    {
        $robotsContent = "User-agent: *\n";
        $robotsContent .= "Allow: /\n";
        $robotsContent .= "Disallow: /admin/\n";
        $robotsContent .= "Disallow: /api/\n";
        $robotsContent .= "Disallow: /storage/\n";
        $robotsContent .= "\n";
        $robotsContent .= "Sitemap: " . url('sitemaps/sitemap.xml') . "\n";
        
        File::put('public/robots.txt', $robotsContent);
        
        return [
            'success' => true,
            'filepath' => 'public/robots.txt',
            'file_size' => strlen($robotsContent),
            'generated_at' => now(),
        ];
    }

    /**
     * Update meta descriptions for pages.
     */
    public function updateMetaDescriptions(): array
    {
        $results = [
            'cities_updated' => 0,
            'jobs_updated' => 0,
            'articles_updated' => 0,
        ];

        // Update city meta descriptions
        $cities = City::where('is_active', true)
            ->whereNull('meta_description')
            ->get();
        
        foreach ($cities as $city) {
            $metaDescription = $this->generateCityMetaDescription($city);
            $city->update(['meta_description' => $metaDescription]);
            $results['cities_updated']++;
        }

        // Update job meta descriptions
        $jobs = Job::where('is_active', true)
            ->whereNull('meta_description')
            ->get();
        
        foreach ($jobs as $job) {
            $metaDescription = $this->generateJobMetaDescription($job);
            $job->update(['meta_description' => $metaDescription]);
            $results['jobs_updated']++;
        }

        // Update article meta descriptions
        $articles = Article::where('is_active', true)
            ->whereNull('meta_description')
            ->get();
        
        foreach ($articles as $article) {
            $metaDescription = $this->generateArticleMetaDescription($article);
            $article->update(['meta_description' => $metaDescription]);
            $results['articles_updated']++;
        }

        return $results;
    }

    /**
     * Generate meta description for city.
     */
    private function generateCityMetaDescription(City $city): string
    {
        $description = "Digital nomad guide for {$city->name}, {$city->country?->name}. ";
        $description .= "Cost of living: {$city->cost_of_living_index}, ";
        $description .= "Internet: {$city->internet_speed_mbps} Mbps, ";
        $description .= "Safety: {$city->safety_score}/10. ";
        $description .= "Find coworking spaces, accommodation, and nomad tips.";
        
        return substr($description, 0, 160);
    }

    /**
     * Generate meta description for job.
     */
    private function generateJobMetaDescription(Job $job): string
    {
        $description = "{$job->title} at {$job->company->name}. ";
        $description .= "Location: {$job->location}. ";
        $description .= "Type: {$job->type}. ";
        
        if ($job->salary_min || $job->salary_max) {
            $description .= "Salary: " . ($job->formatted_salary ?? 'Competitive') . ". ";
        }
        
        $description .= "Apply now for remote work opportunities.";
        
        return substr($description, 0, 160);
    }

    /**
     * Generate meta description for article.
     */
    private function generateArticleMetaDescription(Article $article): string
    {
        $description = strip_tags($article->excerpt ?? $article->content);
        $description = preg_replace('/\s+/', ' ', $description);
        
        return substr(trim($description), 0, 160);
    }

    /**
     * Get SEO statistics.
     */
    public function getSeoStats(): array
    {
        $sitemapFiles = File::glob("{$this->sitemapPath}/*.xml");
        $totalSitemapSize = 0;
        
        foreach ($sitemapFiles as $file) {
            $totalSitemapSize += File::size($file);
        }

        return [
            'sitemap_files' => count($sitemapFiles),
            'total_sitemap_size' => $totalSitemapSize,
            'cities_without_meta' => City::whereNull('meta_description')->count(),
            'jobs_without_meta' => Job::whereNull('meta_description')->count(),
            'articles_without_meta' => Article::whereNull('meta_description')->count(),
            'last_generated' => $this->getLastGeneratedTime(),
        ];
    }

    /**
     * Get last generation time.
     */
    private function getLastGeneratedTime(): ?string
    {
        $sitemapFile = "{$this->sitemapPath}/sitemap.xml";
        
        if (File::exists($sitemapFile)) {
            return File::lastModified($sitemapFile);
        }
        
        return null;
    }

    /**
     * Clean up old sitemap files.
     */
    public function cleanupOldSitemaps(): int
    {
        $files = File::glob("{$this->sitemapPath}/*.xml");
        $deleted = 0;
        
        foreach ($files as $file) {
            if (File::lastModified($file) < now()->subWeek()->timestamp) {
                File::delete($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}
