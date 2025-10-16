# Git Pre-Push Hook for Quality Checks

This project includes a Git pre-push hook that automatically runs quality checks before allowing code to be pushed to the repository. This ensures code quality standards are maintained and prevents broken code from reaching the CI/CD pipeline.

## What the Hook Does

The pre-push hook runs four essential quality checks in sequence:

1. **Laravel Pint** - Code style formatting and validation
2. **PHPStan** - Static analysis for type safety and error detection
3. **Security Audit** - Comprehensive security vulnerability scanning
4. **Tests** - Unit and feature test suite execution

If any of these checks fail, the push is blocked and you'll need to fix the issues before pushing.

## Installation

### For New Team Members

Run the installation script:

```bash
./scripts/install-pre-push-hook.sh
```

### Manual Installation

1. Copy the hook script to the Git hooks directory:
   ```bash
   cp scripts/pre-push-hook .git/hooks/pre-push
   ```

2. Make it executable:
   ```bash
   chmod +x .git/hooks/pre-push
   ```

## Usage

The hook runs automatically before every `git push`. You don't need to do anything special - just push as normal:

```bash
git push origin main
```

### What You'll See

When you push, you'll see output like this:

```
🚀 Running pre-push quality checks...
==================================
[INFO] Running Laravel Pint (Code Style Check)...
[SUCCESS] Laravel Pint passed ✅
[INFO] Running PHPStan (Static Analysis)...
[SUCCESS] PHPStan passed ✅
[INFO] Running Security Audit...
[SUCCESS] Security audit passed ✅
[INFO] Running Tests...
[SUCCESS] Tests passed ✅

[SUCCESS] All quality checks passed! 🎉
[INFO] Proceeding with push...
==================================
```

### If Checks Fail

If any check fails, you'll see error messages and the push will be blocked:

```
[ERROR] Laravel Pint failed ❌
[WARNING] Run './vendor/bin/pint' to fix style issues
```

Fix the issues and try pushing again.

## Security Audit Details

The security audit performs comprehensive security checks including:

- **Dependency Vulnerabilities**: Scans Composer and NPM packages for known security issues
- **Configuration Security**: Validates environment and application security settings
- **Authentication & Authorization**: Checks password hashing, session management, and access controls
- **Input/Output Security**: Validates input validation and XSS protection
- **Infrastructure Security**: Checks file permissions, CSRF protection, and security headers
- **Code Security**: Reviews for SQL injection, file upload security, and other vulnerabilities

The audit generates a security score (0-100) and provides actionable recommendations for improvement.

## Bypassing the Hook (Not Recommended)

In emergency situations, you can bypass the hook using:

```bash
git push --no-verify
```

**⚠️ Warning**: Only use this in true emergencies. The hook exists to maintain code quality.

## Troubleshooting

### "Composer dependencies not installed"
```bash
composer install
```

### "This doesn't appear to be a Laravel project"
Make sure you're in the project root directory where the `artisan` file is located.

### Hook not running
1. Check if the hook is executable: `ls -la .git/hooks/pre-push`
2. Reinstall the hook: `./scripts/install-pre-push-hook.sh`

## Benefits

- **Prevents broken code** from reaching the repository
- **Catches issues early** before CI/CD pipeline runs
- **Maintains consistent code quality** across the team
- **Saves CI/CD resources** by catching issues locally
- **Enforces coding standards** automatically

## Team Workflow

1. Make your changes
2. Commit your changes: `git commit -m "Your message"`
3. Push your changes: `git push`
4. The hook automatically runs quality checks
5. If all checks pass, push succeeds
6. If checks fail, fix issues and push again

This ensures that only high-quality, tested code reaches the main branch and CI/CD pipeline.
