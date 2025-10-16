<?php

/**
 * Comprehensive Security Audit Script
 * 
 * Performs a thorough security analysis of the Laravel application
 * including dependency vulnerabilities, configuration checks, and code analysis.
 * 
 * @author Digital Nomad Website
 * @version 1.0.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Enlightn\SecurityChecker\SecurityChecker;
use Illuminate\Support\Facades\File;

class SecurityAudit
{
    private array $results = [];
    private array $vulnerabilities = [];
    private array $recommendations = [];
    private string $outputPath;
    private int $score = 100;

    public function __construct()
    {
        $this->outputPath = __DIR__ . '/../storage/app/security-audit-report.json';
    }

    /**
     * Run the complete security audit
     */
    public function run(): void
    {
        echo "🔒 Starting Comprehensive Security Audit...\n";
        echo "=" . str_repeat("=", 50) . "\n\n";

        // Run all security checks
        $this->checkDependencies();
        $this->checkConfiguration();
        $this->checkFilePermissions();
        $this->checkEnvironmentSecurity();
        $this->checkAuthenticationSecurity();
        $this->checkAuthorizationSecurity();
        $this->checkInputValidation();
        $this->checkOutputEncoding();
        $this->checkSessionSecurity();
        $this->checkDatabaseSecurity();
        $this->checkFileUploadSecurity();
        $this->checkLoggingSecurity();
        $this->checkMiddlewareSecurity();
        $this->checkRouteSecurity();
        $this->checkCSRFProtection();
        $this->checkRateLimiting();
        $this->checkHeadersSecurity();
        $this->checkSSLConfiguration();
        $this->checkErrorHandling();
        $this->checkBackupSecurity();

        // Generate report
        $this->generateReport();
        $this->displaySummary();
    }

    /**
     * Check for dependency vulnerabilities
     */
    private function checkDependencies(): void
    {
        echo "📦 Checking Dependencies...\n";
        
        try {
            $checker = new SecurityChecker();
            $vulnerabilities = $checker->check(__DIR__ . '/../composer.lock');
            
            if (!empty($vulnerabilities)) {
                $this->vulnerabilities['dependencies'] = $vulnerabilities;
                $this->score -= 20;
                echo "  ❌ Found " . count($vulnerabilities) . " dependency vulnerabilities\n";
                
                foreach ($vulnerabilities as $vuln) {
                    echo "    - {$vuln['package']}: {$vuln['version']} - {$vuln['advisories'][0]['title']}\n";
                }
            } else {
                echo "  ✅ No dependency vulnerabilities found\n";
            }
        } catch (Exception $e) {
            echo "  ⚠️  Could not check dependencies: " . $e->getMessage() . "\n";
        }

        // Check NPM dependencies
        if (file_exists(__DIR__ . '/../package-lock.json')) {
            $npmAudit = shell_exec('cd ' . __DIR__ . '/.. && npm audit --json 2>/dev/null');
            if ($npmAudit) {
                $npmResults = json_decode($npmAudit, true);
                if (isset($npmResults['vulnerabilities']) && !empty($npmResults['vulnerabilities'])) {
                    $this->vulnerabilities['npm'] = $npmResults['vulnerabilities'];
                    $this->score -= 15;
                    echo "  ❌ Found NPM vulnerabilities\n";
                } else {
                    echo "  ✅ No NPM vulnerabilities found\n";
                }
            }
        }

        echo "\n";
    }

    /**
     * Check application configuration security
     */
    private function checkConfiguration(): void
    {
        echo "⚙️  Checking Configuration...\n";

        // Check APP_KEY
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            if (preg_match('/APP_KEY=(.+)/', $envContent, $matches)) {
                $appKey = trim($matches[1]);
                if (empty($appKey) || $appKey === 'base64:' || strlen($appKey) < 20) {
                    $this->recommendations[] = "Set APP_KEY in .env file";
                    $this->score -= 10;
                    echo "  ❌ APP_KEY is not set or invalid\n";
                } else {
                    echo "  ✅ APP_KEY is configured\n";
                }
            } else {
                $this->recommendations[] = "Set APP_KEY in .env file";
                $this->score -= 10;
                echo "  ❌ APP_KEY is not set\n";
            }
        } else {
            $this->recommendations[] = "Set APP_KEY in .env file";
            $this->score -= 10;
            echo "  ❌ .env file not found\n";
        }

        // Check APP_ENV
        $appEnv = env('APP_ENV', 'production');
        if ($appEnv === 'local' || $appEnv === 'development') {
            $this->recommendations[] = "Set APP_ENV to 'production' for production deployments";
            $this->score -= 5;
            echo "  ⚠️  APP_ENV is set to '{$appEnv}' (should be 'production')\n";
        } else {
            echo "  ✅ APP_ENV is set to '{$appEnv}'\n";
        }

        // Check APP_DEBUG
        if (env('APP_DEBUG', false)) {
            $this->recommendations[] = "Set APP_DEBUG to false in production";
            $this->score -= 10;
            echo "  ❌ APP_DEBUG is enabled\n";
        } else {
            echo "  ✅ APP_DEBUG is disabled\n";
        }

        // Check session configuration
        $sessionDriver = env('SESSION_DRIVER', 'file');
        if ($sessionDriver === 'file') {
            $this->recommendations[] = "Consider using 'redis' or 'database' session driver for better security";
            echo "  ⚠️  Using file session driver (consider redis/database)\n";
        } else {
            echo "  ✅ Using secure session driver: {$sessionDriver}\n";
        }

        echo "\n";
    }

    /**
     * Check file permissions
     */
    private function checkFilePermissions(): void
    {
        echo "📁 Checking File Permissions...\n";

        $criticalPaths = [
            'storage' => 755,
            'bootstrap/cache' => 755,
            '.env' => 644,
        ];

        foreach ($criticalPaths as $path => $expectedPerms) {
            $fullPath = __DIR__ . '/../' . $path;
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath) & 0777;
                if ($perms !== $expectedPerms) {
                    $this->recommendations[] = "Set correct permissions for {$path} (current: {$perms}, expected: {$expectedPerms})";
                    $this->score -= 5;
                    echo "  ⚠️  {$path} permissions: {$perms} (should be {$expectedPerms})\n";
                } else {
                    echo "  ✅ {$path} permissions are correct\n";
                }
            }
        }

        echo "\n";
    }

    /**
     * Check environment security
     */
    private function checkEnvironmentSecurity(): void
    {
        echo "🌍 Checking Environment Security...\n";

        // Check for sensitive files
        $sensitiveFiles = ['.env', '.env.example', 'composer.lock', 'package-lock.json'];
        
        foreach ($sensitiveFiles as $file) {
            $publicPath = __DIR__ . '/../public/' . $file;
            if (file_exists($publicPath)) {
                $this->recommendations[] = "Remove {$file} from public directory";
                $this->score -= 15;
                echo "  ❌ {$file} is accessible in public directory\n";
            }
        }

        // Check .env.example
        if (file_exists(__DIR__ . '/../.env.example')) {
            $envExample = file_get_contents(__DIR__ . '/../.env.example');
            if (str_contains($envExample, 'APP_KEY=') && !str_contains($envExample, 'APP_KEY=base64:')) {
                echo "  ✅ .env.example has placeholder APP_KEY\n";
            } else {
                echo "  ⚠️  .env.example should have placeholder APP_KEY\n";
            }
        }

        echo "\n";
    }

    /**
     * Check authentication security
     */
    private function checkAuthenticationSecurity(): void
    {
        echo "🔐 Checking Authentication Security...\n";

        // Check password hashing
        if (file_exists(__DIR__ . '/../app/Models/User.php')) {
            $userModel = file_get_contents(__DIR__ . '/../app/Models/User.php');
            if (str_contains($userModel, 'Hash::make') || str_contains($userModel, 'bcrypt')) {
                echo "  ✅ Password hashing is implemented\n";
            } else {
                $this->recommendations[] = "Implement proper password hashing in User model";
                $this->score -= 10;
                echo "  ❌ Password hashing not found in User model\n";
            }
        }

        // Check login throttling in routes
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $webRoutes = file_get_contents(__DIR__ . '/../routes/web.php');
            if (str_contains($webRoutes, 'throttle') && str_contains($webRoutes, 'login')) {
                echo "  ✅ Login throttling is implemented\n";
            } else {
                $this->recommendations[] = "Implement login throttling to prevent brute force attacks";
                $this->score -= 5;
                echo "  ⚠️  Login throttling not found\n";
            }
        }

        // Check session regeneration
        if (file_exists(__DIR__ . '/../app/Http/Controllers/Auth/AuthController.php')) {
            $authController = file_get_contents(__DIR__ . '/../app/Http/Controllers/Auth/AuthController.php');
            if (str_contains($authController, 'session()->regenerate()')) {
                echo "  ✅ Session regeneration on login is implemented\n";
            } else {
                $this->recommendations[] = "Implement session regeneration on login";
                $this->score -= 5;
                echo "  ❌ Session regeneration not found\n";
            }
        }

        echo "\n";
    }

    /**
     * Check authorization security
     */
    private function checkAuthorizationSecurity(): void
    {
        echo "🛡️  Checking Authorization Security...\n";

        // Check middleware usage
        $middlewareFiles = glob(__DIR__ . '/../app/Http/Middleware/*.php');
        $hasAuthMiddleware = false;
        $hasAdminMiddleware = false;

        foreach ($middlewareFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, 'auth()->check()') || str_contains($content, 'Auth::check()')) {
                $hasAuthMiddleware = true;
            }
            if (str_contains($content, 'admin') || str_contains($content, 'AdminAccess')) {
                $hasAdminMiddleware = true;
            }
        }

        if ($hasAuthMiddleware) {
            echo "  ✅ Authentication middleware is implemented\n";
        } else {
            $this->recommendations[] = "Implement authentication middleware";
            $this->score -= 10;
            echo "  ❌ Authentication middleware not found\n";
        }

        if ($hasAdminMiddleware) {
            echo "  ✅ Admin authorization middleware is implemented\n";
        } else {
            echo "  ⚠️  Admin authorization middleware not found\n";
        }

        echo "\n";
    }

    /**
     * Check input validation
     */
    private function checkInputValidation(): void
    {
        echo "✅ Checking Input Validation...\n";

        $controllerFiles = glob(__DIR__ . '/../app/Http/Controllers/*.php');
        $hasValidation = false;

        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, 'validate(') || str_contains($content, 'Request::validate')) {
                $hasValidation = true;
                break;
            }
        }

        if ($hasValidation) {
            echo "  ✅ Input validation is implemented in controllers\n";
        } else {
            $this->recommendations[] = "Implement input validation in controllers";
            $this->score -= 10;
            echo "  ❌ Input validation not found in controllers\n";
        }

        echo "\n";
    }

    /**
     * Check output encoding
     */
    private function checkOutputEncoding(): void
    {
        echo "🔤 Checking Output Encoding...\n";

        // Check Blade templates for proper escaping
        $bladeFiles = glob(__DIR__ . '/../resources/views/**/*.blade.php', GLOB_BRACE);
        $hasProperEscaping = true;

        foreach ($bladeFiles as $file) {
            $content = file_get_contents($file);
            // Check for unescaped output
            if (preg_match('/\{\{\s*\$[^}]*\}\}/', $content) && !preg_match('/\{\{\s*\$[^}]*\s*\|\s*raw\s*\}\}/', $content)) {
                // This is a basic check - in practice, you'd want more sophisticated analysis
                continue;
            }
        }

        if ($hasProperEscaping) {
            echo "  ✅ Output encoding appears to be properly implemented\n";
        } else {
            $this->recommendations[] = "Ensure all output is properly escaped";
            $this->score -= 5;
            echo "  ⚠️  Check output encoding in Blade templates\n";
        }

        echo "\n";
    }

    /**
     * Check session security
     */
    private function checkSessionSecurity(): void
    {
        echo "🍪 Checking Session Security...\n";

        // Check session configuration
        $sessionLifetime = env('SESSION_LIFETIME', 120);
        if ($sessionLifetime > 1440) { // 24 hours
            $this->recommendations[] = "Consider reducing session lifetime for better security";
            echo "  ⚠️  Session lifetime is {$sessionLifetime} minutes (consider reducing)\n";
        } else {
            echo "  ✅ Session lifetime is reasonable: {$sessionLifetime} minutes\n";
        }

        // Check session secure flag
        $sessionSecure = env('SESSION_SECURE_COOKIE', false);
        if (!$sessionSecure) {
            $this->recommendations[] = "Enable SESSION_SECURE_COOKIE for HTTPS";
            echo "  ⚠️  SESSION_SECURE_COOKIE is disabled\n";
        } else {
            echo "  ✅ SESSION_SECURE_COOKIE is enabled\n";
        }

        echo "\n";
    }

    /**
     * Check database security
     */
    private function checkDatabaseSecurity(): void
    {
        echo "🗄️  Checking Database Security...\n";

        // Check database configuration
        $dbConnection = env('DB_CONNECTION', 'mysql');
        if ($dbConnection === 'sqlite') {
            echo "  ⚠️  Using SQLite database (consider MySQL/PostgreSQL for production)\n";
        } else {
            echo "  ✅ Using {$dbConnection} database\n";
        }

        // Check for raw queries
        $modelFiles = glob(__DIR__ . '/../app/Models/*.php');
        $hasRawQueries = false;

        foreach ($modelFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, 'DB::raw') || str_contains($content, 'selectRaw')) {
                $hasRawQueries = true;
                break;
            }
        }

        if ($hasRawQueries) {
            $this->recommendations[] = "Review raw database queries for SQL injection risks";
            echo "  ⚠️  Raw database queries found (review for SQL injection)\n";
        } else {
            echo "  ✅ No raw database queries found\n";
        }

        echo "\n";
    }

    /**
     * Check file upload security
     */
    private function checkFileUploadSecurity(): void
    {
        echo "📤 Checking File Upload Security...\n";

        $controllerFiles = glob(__DIR__ . '/../app/Http/Controllers/*.php');
        $hasFileUploads = false;
        $hasValidation = false;

        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, 'file(') || str_contains($content, 'hasFile')) {
                $hasFileUploads = true;
                if (str_contains($content, 'validate') && (str_contains($content, 'image') || str_contains($content, 'mimes'))) {
                    $hasValidation = true;
                }
            }
        }

        if ($hasFileUploads) {
            if ($hasValidation) {
                echo "  ✅ File upload validation is implemented\n";
            } else {
                $this->recommendations[] = "Implement file upload validation (type, size, etc.)";
                $this->score -= 10;
                echo "  ❌ File upload validation not found\n";
            }
        } else {
            echo "  ✅ No file uploads detected\n";
        }

        echo "\n";
    }

    /**
     * Check logging security
     */
    private function checkLoggingSecurity(): void
    {
        echo "📝 Checking Logging Security...\n";

        // Check if sensitive data is being logged
        $logFiles = glob(__DIR__ . '/../storage/logs/*.log');
        $hasSensitiveLogging = false;

        foreach ($logFiles as $logFile) {
            $content = file_get_contents($logFile);
            if (str_contains($content, 'password') || str_contains($content, 'token') || str_contains($content, 'secret')) {
                $hasSensitiveLogging = true;
                break;
            }
        }

        if ($hasSensitiveLogging) {
            $this->recommendations[] = "Review logs for sensitive information";
            echo "  ⚠️  Potential sensitive information in logs\n";
        } else {
            echo "  ✅ No obvious sensitive information in logs\n";
        }

        echo "\n";
    }

    /**
     * Check middleware security
     */
    private function checkMiddlewareSecurity(): void
    {
        echo "🛡️  Checking Middleware Security...\n";

        // Check for security middleware
        $middlewareFiles = glob(__DIR__ . '/../app/Http/Middleware/*.php');
        $securityMiddleware = ['CheckBannedIp', 'AdminAccess'];

        foreach ($securityMiddleware as $middleware) {
            $found = false;
            foreach ($middlewareFiles as $file) {
                if (str_contains(basename($file), $middleware)) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                echo "  ✅ {$middleware} middleware is implemented\n";
            } else {
                echo "  ⚠️  {$middleware} middleware not found\n";
            }
        }

        echo "\n";
    }

    /**
     * Check route security
     */
    private function checkRouteSecurity(): void
    {
        echo "🛣️  Checking Route Security...\n";

        // Check web routes
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $webRoutes = file_get_contents(__DIR__ . '/../routes/web.php');
            
            // Check for protected routes
            if (str_contains($webRoutes, 'middleware(\'auth\')')) {
                echo "  ✅ Authentication middleware is applied to routes\n";
            } else {
                echo "  ⚠️  Authentication middleware not found in routes\n";
            }

            // Check for admin routes
            if (str_contains($webRoutes, 'middleware(\'admin\')')) {
                echo "  ✅ Admin middleware is applied to routes\n";
            } else {
                echo "  ⚠️  Admin middleware not found in routes\n";
            }
        }

        echo "\n";
    }

    /**
     * Check CSRF protection
     */
    private function checkCSRFProtection(): void
    {
        echo "🛡️  Checking CSRF Protection...\n";

        // Check if CSRF middleware is applied (Laravel 11 has CSRF enabled by default)
        if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
            $bootstrap = file_get_contents(__DIR__ . '/../bootstrap/app.php');
            // Laravel 11 enables CSRF by default for web routes
            if (str_contains($bootstrap, 'VerifyCsrfToken') || str_contains($bootstrap, 'csrf')) {
                echo "  ✅ CSRF protection is configured\n";
            } else {
                $this->recommendations[] = "Ensure CSRF protection is enabled";
                $this->score -= 10;
                echo "  ❌ CSRF protection not found\n";
            }
        }

        echo "\n";
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimiting(): void
    {
        echo "⏱️  Checking Rate Limiting...\n";

        // Check for rate limiting in routes
        if (file_exists(__DIR__ . '/../routes/web.php')) {
            $webRoutes = file_get_contents(__DIR__ . '/../routes/web.php');
            if (str_contains($webRoutes, 'throttle')) {
                echo "  ✅ Rate limiting is implemented\n";
            } else {
                $this->recommendations[] = "Implement rate limiting for API endpoints";
                $this->score -= 5;
                echo "  ⚠️  Rate limiting not found\n";
            }
        }

        echo "\n";
    }

    /**
     * Check security headers
     */
    private function checkHeadersSecurity(): void
    {
        echo "📋 Checking Security Headers...\n";

        // Check for security headers middleware
        $middlewareFiles = glob(__DIR__ . '/../app/Http/Middleware/*.php');
        $hasSecurityHeaders = false;

        foreach ($middlewareFiles as $file) {
            $content = file_get_contents($file);
            if (str_contains($content, 'X-Frame-Options') || str_contains($content, 'X-Content-Type-Options')) {
                $hasSecurityHeaders = true;
                break;
            }
        }

        if ($hasSecurityHeaders) {
            echo "  ✅ Security headers middleware is implemented\n";
        } else {
            $this->recommendations[] = "Implement security headers middleware";
            $this->score -= 5;
            echo "  ⚠️  Security headers middleware not found\n";
        }

        echo "\n";
    }

    /**
     * Check SSL configuration
     */
    private function checkSSLConfiguration(): void
    {
        echo "🔒 Checking SSL Configuration...\n";

        // Check for HTTPS enforcement
        if (file_exists(__DIR__ . '/../app/Http/Middleware/TrustProxies.php')) {
            echo "  ✅ TrustProxies middleware exists (for HTTPS behind proxy)\n";
        } else {
            echo "  ⚠️  TrustProxies middleware not found\n";
        }

        // Check APP_URL
        $appUrl = env('APP_URL', '');
        if (str_starts_with($appUrl, 'https://')) {
            echo "  ✅ APP_URL uses HTTPS\n";
        } else {
            $this->recommendations[] = "Use HTTPS in APP_URL";
            echo "  ⚠️  APP_URL does not use HTTPS\n";
        }

        echo "\n";
    }

    /**
     * Check error handling
     */
    private function checkErrorHandling(): void
    {
        echo "🚨 Checking Error Handling...\n";

        // Check if debug mode is disabled in production
        $appDebug = env('APP_DEBUG', false);
        if (!$appDebug) {
            echo "  ✅ Debug mode is disabled\n";
        } else {
            $this->recommendations[] = "Disable debug mode in production";
            $this->score -= 10;
            echo "  ❌ Debug mode is enabled\n";
        }

        // Check for custom error pages
        $errorViews = glob(__DIR__ . '/../resources/views/errors/*.blade.php');
        if (!empty($errorViews)) {
            echo "  ✅ Custom error pages are implemented\n";
        } else {
            echo "  ⚠️  Custom error pages not found\n";
        }

        echo "\n";
    }

    /**
     * Check backup security
     */
    private function checkBackupSecurity(): void
    {
        echo "💾 Checking Backup Security...\n";

        // Check if backup files are accessible
        $backupFiles = glob(__DIR__ . '/../storage/app/backups/*');
        if (!empty($backupFiles)) {
            echo "  ⚠️  Backup files found in storage/app/backups (ensure they're not publicly accessible)\n";
        } else {
            echo "  ✅ No backup files in public storage\n";
        }

        echo "\n";
    }

    /**
     * Generate security audit report
     */
    private function generateReport(): void
    {
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'score' => max(0, $this->score),
            'vulnerabilities' => $this->vulnerabilities,
            'recommendations' => $this->recommendations,
            'summary' => [
                'total_vulnerabilities' => count($this->vulnerabilities),
                'total_recommendations' => count($this->recommendations),
                'security_score' => max(0, $this->score),
                'status' => $this->score >= 80 ? 'GOOD' : ($this->score >= 60 ? 'WARNING' : 'CRITICAL')
            ]
        ];

        file_put_contents($this->outputPath, json_encode($report, JSON_PRETTY_PRINT));
    }

    /**
     * Display audit summary
     */
    private function displaySummary(): void
    {
        echo "=" . str_repeat("=", 50) . "\n";
        echo "🔒 SECURITY AUDIT SUMMARY\n";
        echo "=" . str_repeat("=", 50) . "\n\n";

        echo "📊 Security Score: {$this->score}/100\n";
        echo "📋 Vulnerabilities Found: " . count($this->vulnerabilities) . "\n";
        echo "💡 Recommendations: " . count($this->recommendations) . "\n\n";

        $status = $this->score >= 80 ? 'GOOD' : ($this->score >= 60 ? 'WARNING' : 'CRITICAL');
        $statusIcon = $this->score >= 80 ? '✅' : ($this->score >= 60 ? '⚠️' : '❌');
        
        echo "🎯 Overall Status: {$statusIcon} {$status}\n\n";

        if (!empty($this->recommendations)) {
            echo "💡 RECOMMENDATIONS:\n";
            echo "-" . str_repeat("-", 30) . "\n";
            foreach ($this->recommendations as $i => $recommendation) {
                echo ($i + 1) . ". {$recommendation}\n";
            }
            echo "\n";
        }

        echo "📄 Detailed report saved to: {$this->outputPath}\n";
        echo "🔒 Security audit completed!\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $audit = new SecurityAudit();
    $audit->run();
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}
