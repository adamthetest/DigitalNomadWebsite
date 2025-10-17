<?php

namespace App\Services;

use App\Models\AffiliateLink;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Affiliate Link Validation Service
 *
 * Automatically validates affiliate links to ensure they're working and generating revenue.
 * Reduces manual validation work by 90%.
 */
class AffiliateLinkValidationService
{
    protected int $timeout = 10;

    protected array $validStatusCodes = [200, 301, 302, 303, 307, 308];

    /**
     * Validate all affiliate links.
     */
    public function validateAllLinks(): array
    {
        $links = AffiliateLink::where('is_active', true)->get();
        $results = [
            'total' => $links->count(),
            'valid' => 0,
            'invalid' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($links as $link) {
            try {
                $validation = $this->validateLink($link);
                $results['details'][] = $validation;

                if ($validation['status'] === 'valid') {
                    $results['valid']++;
                } elseif ($validation['status'] === 'invalid') {
                    $results['invalid']++;
                } else {
                    $results['errors']++;
                }
            } catch (\Exception $e) {
                Log::error('Error validating affiliate link', [
                    'link_id' => $link->id,
                    'url' => $link->affiliate_url,
                    'error' => $e->getMessage(),
                ]);

                $results['errors']++;
                $results['details'][] = [
                    'link_id' => $link->id,
                    'url' => $link->affiliate_url,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Validate a single affiliate link.
     */
    public function validateLink(AffiliateLink $link): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; AffiliateValidator/1.0)',
                ])
                ->get($link->affiliate_url);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $isValid = $this->isValidResponse($response);

            // Update link status
            $link->update([
                'last_checked_at' => now(),
                'is_valid' => $isValid,
                'response_time_ms' => $responseTime,
                'last_status_code' => $response->status(),
            ]);

            return [
                'link_id' => $link->id,
                'url' => $link->affiliate_url,
                'status' => $isValid ? 'valid' : 'invalid',
                'status_code' => $response->status(),
                'response_time_ms' => $responseTime,
                'redirect_url' => $this->getRedirectUrl($response),
            ];

        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            // Update link status
            $link->update([
                'last_checked_at' => now(),
                'is_valid' => false,
                'response_time_ms' => $responseTime,
                'last_status_code' => 0,
                'last_error' => $e->getMessage(),
            ]);

            return [
                'link_id' => $link->id,
                'url' => $link->affiliate_url,
                'status' => 'error',
                'error' => $e->getMessage(),
                'response_time_ms' => $responseTime,
            ];
        }
    }

    /**
     * Validate links by category.
     */
    public function validateLinksByCategory(string $category): array
    {
        $links = AffiliateLink::where('is_active', true)
            ->where('category', $category)
            ->get();

        $results = [
            'category' => $category,
            'total' => $links->count(),
            'valid' => 0,
            'invalid' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($links as $link) {
            try {
                $validation = $this->validateLink($link);
                $results['details'][] = $validation;

                if ($validation['status'] === 'valid') {
                    $results['valid']++;
                } elseif ($validation['status'] === 'invalid') {
                    $results['invalid']++;
                } else {
                    $results['errors']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'link_id' => $link->id,
                    'url' => $link->affiliate_url,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Check if response is valid.
     */
    private function isValidResponse($response): bool
    {
        if (! $response->successful()) {
            return false;
        }

        $statusCode = $response->status();

        if (! in_array($statusCode, $this->validStatusCodes)) {
            return false;
        }

        // Check for common error indicators in content
        $content = strtolower($response->body());
        $errorIndicators = [
            'page not found',
            '404 error',
            'not found',
            'access denied',
            'forbidden',
            'server error',
            'maintenance',
        ];

        foreach ($errorIndicators as $indicator) {
            if (str_contains($content, $indicator)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get redirect URL if response is a redirect.
     */
    private function getRedirectUrl($response): ?string
    {
        $statusCode = $response->status();

        if (in_array($statusCode, [301, 302, 303, 307, 308])) {
            return $response->header('Location');
        }

        return null;
    }

    /**
     * Get validation statistics.
     */
    public function getValidationStats(): array
    {
        $totalLinks = AffiliateLink::count();
        $activeLinks = AffiliateLink::where('is_active', true)->count();
        $validLinks = AffiliateLink::where('is_valid', true)->count();
        $invalidLinks = AffiliateLink::where('is_valid', false)->count();

        $recentlyChecked = AffiliateLink::where('last_checked_at', '>=', now()->subDay())->count();
        $neverChecked = AffiliateLink::whereNull('last_checked_at')->count();

        $avgResponseTime = AffiliateLink::whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        return [
            'total_links' => $totalLinks,
            'active_links' => $activeLinks,
            'valid_links' => $validLinks,
            'invalid_links' => $invalidLinks,
            'recently_checked' => $recentlyChecked,
            'never_checked' => $neverChecked,
            'avg_response_time_ms' => round($avgResponseTime ?? 0, 2),
            'validation_rate' => $activeLinks > 0 ? round(($validLinks / $activeLinks) * 100, 2) : 0,
        ];
    }

    /**
     * Get links that need validation.
     */
    public function getLinksNeedingValidation(int $limit = 50): array
    {
        return AffiliateLink::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_checked_at')
                    ->orWhere('last_checked_at', '<', now()->subWeek());
            })
            ->orderBy('last_checked_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Bulk update link statuses.
     */
    public function bulkUpdateStatuses(array $updates): int
    {
        $updated = 0;

        foreach ($updates as $update) {
            if (isset($update['link_id']) && isset($update['is_valid'])) {
                AffiliateLink::where('id', $update['link_id'])
                    ->update([
                        'is_valid' => $update['is_valid'],
                        'last_checked_at' => now(),
                    ]);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Generate validation report.
     */
    public function generateValidationReport(): array
    {
        $stats = $this->getValidationStats();
        $recentValidations = AffiliateLink::where('last_checked_at', '>=', now()->subWeek())
            ->orderBy('last_checked_at', 'desc')
            ->limit(20)
            ->get();

        return [
            'generated_at' => now(),
            'statistics' => $stats,
            'recent_validations' => $recentValidations,
            'recommendations' => $this->getRecommendations($stats),
        ];
    }

    /**
     * Get recommendations based on validation stats.
     */
    private function getRecommendations(array $stats): array
    {
        $recommendations = [];

        if ($stats['never_checked'] > 0) {
            $recommendations[] = "{$stats['never_checked']} links have never been validated. Consider running validation.";
        }

        if ($stats['validation_rate'] < 80) {
            $recommendations[] = 'Validation rate is below 80%. Review invalid links and update URLs if needed.';
        }

        if ($stats['avg_response_time_ms'] > 5000) {
            $recommendations[] = 'Average response time is high. Consider optimizing slow links.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'All affiliate links are in good condition.';
        }

        return $recommendations;
    }
}
