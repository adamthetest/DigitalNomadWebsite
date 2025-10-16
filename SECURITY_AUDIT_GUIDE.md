# Security Audit Guide

A comprehensive security auditing system for the Digital Nomad Website Laravel application.

## 🚀 Features

- **Comprehensive Security Analysis**: 20+ security checks covering all aspects of application security
- **Dependency Vulnerability Scanning**: Checks both Composer and NPM dependencies
- **Configuration Security**: Validates environment and application configuration
- **Code Security Analysis**: Reviews authentication, authorization, and input validation
- **Infrastructure Security**: Checks file permissions, SSL, and server configuration
- **Automated Reporting**: Generates detailed JSON reports with recommendations

## 📋 Security Checks Performed

### 1. Dependency Security
- **Composer Vulnerabilities**: Scans `composer.lock` for known security issues
- **NPM Vulnerabilities**: Checks `package-lock.json` for JavaScript vulnerabilities
- **Outdated Packages**: Identifies packages with security updates available

### 2. Configuration Security
- **APP_KEY**: Ensures application key is properly set
- **APP_ENV**: Validates environment is set to production
- **APP_DEBUG**: Confirms debug mode is disabled in production
- **Session Configuration**: Reviews session driver and security settings

### 3. File System Security
- **File Permissions**: Validates correct permissions on critical directories
- **Sensitive Files**: Checks for exposed configuration files
- **Backup Security**: Ensures backup files are not publicly accessible

### 4. Authentication Security
- **Password Hashing**: Verifies proper password hashing implementation
- **Login Throttling**: Checks for brute force protection
- **Session Management**: Validates session regeneration and security

### 5. Authorization Security
- **Middleware Implementation**: Reviews authentication and authorization middleware
- **Route Protection**: Ensures sensitive routes are properly protected
- **Admin Access Control**: Validates admin-only functionality

### 6. Input/Output Security
- **Input Validation**: Reviews form validation implementation
- **Output Encoding**: Checks for proper XSS protection
- **SQL Injection**: Scans for potential SQL injection vulnerabilities

### 7. Infrastructure Security
- **CSRF Protection**: Validates CSRF token implementation
- **Rate Limiting**: Checks for API rate limiting
- **Security Headers**: Reviews HTTP security headers
- **SSL Configuration**: Validates HTTPS implementation

### 8. Logging and Monitoring
- **Sensitive Data Logging**: Identifies potential sensitive information in logs
- **Error Handling**: Reviews error disclosure and handling
- **Security Logging**: Validates security event logging

## 🛠️ Usage

### Run Security Audit

```bash
# Using Composer script (recommended)
composer security-audit

# Or run directly
php scripts/security-audit.php
```

### CI/CD Integration

The security audit is automatically integrated into the GitHub Actions workflow:

```yaml
# .github/workflows/security.yml
comprehensive-security-audit:
  runs-on: ubuntu-latest
  steps:
    - name: Run Comprehensive Security Audit
      run: composer security-audit
    
    - name: Upload Security Audit Report
      uses: actions/upload-artifact@v4
      with:
        name: security-audit-report
        path: storage/app/security-audit-report.json
```

### Manual Workflow Trigger

You can trigger specific security scans via GitHub Actions:

1. Go to **Actions** → **Security Scan**
2. Click **Run workflow**
3. Select scan type:
   - `full` - Complete security audit
   - `security` - Comprehensive security audit only
   - `dependencies` - Dependency vulnerability scan
   - `codeql` - CodeQL static analysis
   - `secrets` - Secret scanning
   - `lighthouse` - Web security audit

## 📊 Security Scoring

The audit generates a security score from 0-100 based on:

- **Critical Issues** (-10 to -20 points each)
- **High Priority Issues** (-5 to -10 points each)
- **Medium Priority Issues** (-2 to -5 points each)
- **Low Priority Issues** (-1 to -2 points each)

### Score Interpretation

- **80-100**: GOOD - Application is secure
- **60-79**: WARNING - Some security improvements needed
- **0-59**: CRITICAL - Immediate security attention required

## 📄 Report Format

The security audit generates a detailed JSON report:

```json
{
  "timestamp": "2024-01-15 10:30:00",
  "score": 85,
  "vulnerabilities": {
    "dependencies": [],
    "npm": []
  },
  "recommendations": [
    "Enable SESSION_SECURE_COOKIE for HTTPS",
    "Implement rate limiting for API endpoints"
  ],
  "summary": {
    "total_vulnerabilities": 0,
    "total_recommendations": 2,
    "security_score": 85,
    "status": "GOOD"
  }
}
```

## 🔧 Customization

### Adding Custom Checks

To add custom security checks, modify `scripts/security-audit.php`:

```php
private function checkCustomSecurity(): void
{
    echo "🔍 Checking Custom Security...\n";
    
    // Your custom security check logic here
    if ($someCondition) {
        $this->recommendations[] = "Custom security recommendation";
        $this->score -= 5;
        echo "  ⚠️  Custom security issue found\n";
    } else {
        echo "  ✅ Custom security check passed\n";
    }
    
    echo "\n";
}
```

### Modifying Scoring

Adjust the scoring system by modifying point deductions:

```php
// Critical issue
$this->score -= 20;

// High priority issue
$this->score -= 10;

// Medium priority issue
$this->score -= 5;

// Low priority issue
$this->score -= 2;
```

## 🚨 Common Security Issues and Fixes

### 1. Missing APP_KEY
```bash
# Generate application key
php artisan key:generate
```

### 2. Incorrect File Permissions
```bash
# Set correct permissions
chmod 755 storage bootstrap/cache
chmod 644 .env
```

### 3. Enable CSRF Protection
```php
// In bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\VerifyCsrfToken::class,
    ]);
})
```

### 4. Implement Rate Limiting
```php
// In routes/web.php
Route::middleware(['throttle:60,1'])->group(function () {
    // Your routes here
});
```

### 5. Enable Security Headers
```php
// Create app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    
    return $response;
}
```

## 📈 Continuous Security Monitoring

### Automated Scanning

The security audit runs automatically:
- **Daily**: Scheduled security scans at 2 AM UTC
- **On Push**: Triggered on every push to main branch
- **On PR**: Runs on pull requests for security review

### Security Alerts

Set up notifications for critical security issues:

```yaml
# In .github/workflows/security.yml
- name: Notify on Critical Issues
  if: ${{ steps.audit.outputs.score < 60 }}
  run: |
    echo "🚨 CRITICAL SECURITY ISSUES DETECTED"
    echo "Score: ${{ steps.audit.outputs.score }}/100"
```

## 🔍 Advanced Security Analysis

### Integration with External Tools

The security audit can be extended to integrate with:

- **OWASP ZAP**: Web application security scanner
- **Snyk**: Vulnerability database and scanning
- **SonarQube**: Code quality and security analysis
- **Bandit**: Python security linter (if applicable)

### Custom Security Policies

Define organization-specific security policies:

```php
private function checkCustomPolicies(): void
{
    // Check for custom security policies
    $policies = [
        'no_hardcoded_secrets' => $this->checkHardcodedSecrets(),
        'encryption_required' => $this->checkEncryption(),
        'audit_logging' => $this->checkAuditLogging(),
    ];
    
    foreach ($policies as $policy => $result) {
        if (!$result) {
            $this->recommendations[] = "Policy violation: {$policy}";
            $this->score -= 10;
        }
    }
}
```

## 📚 Security Best Practices

### Development

1. **Regular Audits**: Run security audits before each release
2. **Dependency Updates**: Keep all dependencies up to date
3. **Code Reviews**: Include security review in pull request process
4. **Testing**: Include security tests in test suite

### Production

1. **Environment Hardening**: Secure server configuration
2. **Monitoring**: Implement security monitoring and alerting
3. **Incident Response**: Have a plan for security incidents
4. **Regular Updates**: Keep system and dependencies updated

### Team Training

1. **Security Awareness**: Regular security training for developers
2. **Secure Coding**: Follow secure coding practices
3. **Threat Modeling**: Understand potential threats and mitigations
4. **Incident Response**: Know how to respond to security incidents

## 🆘 Getting Help

### Security Issues

If you discover a security vulnerability:

1. **Do NOT** create a public issue
2. Email security concerns to: security@digitalnomad.com
3. Include detailed information about the vulnerability
4. Allow time for the issue to be addressed

### Audit Issues

For questions about the security audit:

1. Check this documentation
2. Review the audit report for specific recommendations
3. Consult Laravel security documentation
4. Contact the development team

---

**Security is everyone's responsibility. Stay vigilant and keep your application secure!** 🔒
