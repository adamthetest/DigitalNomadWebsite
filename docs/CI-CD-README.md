# CI/CD Documentation

This project includes comprehensive CI/CD workflows using GitHub Actions for automated testing, security scanning, and deployment.

## Workflows Overview

### 1. CI Workflow (`.github/workflows/ci.yml`)
**Triggers:** Push to `main`/`develop`, Pull Requests

**Features:**
- PHP 8.2 testing with MySQL service
- Node.js 20 for frontend asset building
- Unit and Feature tests via PHPUnit
- Code style checking with Laravel Pint
- Asset building with Vite
- Lighthouse performance testing for PRs
- Security auditing with Composer audit

**Jobs:**
- `test`: Runs all tests and code quality checks
- `security`: Performs dependency security audits
- `lighthouse`: Performance and accessibility testing

### 2. CD Workflow (`.github/workflows/cd.yml`)
**Triggers:** Push to `main`, Manual dispatch

**Features:**
- Production-ready deployment pipeline
- Asset optimization and caching
- Deployment artifact creation
- Post-deployment health checks
- Flexible deployment targets (SSH, Docker, Cloud platforms)

**Jobs:**
- `deploy`: Main deployment process
- `post-deploy`: Health checks and notifications

### 3. Security Workflow (`.github/workflows/security.yml`)
**Triggers:** Daily schedule, Push to `main`, Pull Requests

**Features:**
- Composer dependency vulnerability scanning
- NPM package security auditing
- CodeQL static analysis for PHP and JavaScript
- Secret scanning with TruffleHog
- Lighthouse security audits

**Jobs:**
- `dependency-scan`: PHP dependency security
- `npm-audit`: JavaScript dependency security
- `codeql-analysis`: Static code analysis
- `secret-scan`: Secret detection
- `lighthouse-security`: Web security audits

## Configuration Files

### Dependabot (`.github/dependabot.yml`)
Automated dependency updates for:
- Composer packages (weekly)
- NPM packages (weekly)
- GitHub Actions (weekly)

### Lighthouse Configuration
- `.lighthouserc.json`: Performance and accessibility thresholds
- `.lighthouserc-security.json`: Security-focused audits

### Docker Support
- `Dockerfile`: Production-ready containerization
- `docker/apache/000-default.conf`: Apache configuration

## Setup Instructions

### 1. Repository Secrets
Configure these secrets in your GitHub repository:

```
# For deployment (examples)
HOST=your-server.com
USERNAME=deploy-user
SSH_KEY=your-private-ssh-key
CLOUD_API_KEY=your-cloud-provider-key
```

### 2. Environment Configuration
- Ensure `.env.example` exists with all required variables
- The CI workflow will copy `.env.example` to `.env` for testing

### 3. Database Setup
- CI uses SQLite for testing (in-memory database)
- Production deployment should use MySQL/PostgreSQL

### 4. Deployment Configuration
Edit `.github/workflows/cd.yml` and uncomment the deployment method that matches your infrastructure:

- **SSH Deployment**: Uncomment the SSH action section
- **Docker Deployment**: Uncomment the Docker build/push section
- **Cloud Platform**: Uncomment the cloud provider section

## Workflow Features

### Testing
- PHPUnit for unit and feature tests
- Laravel Pint for code style enforcement
- Lighthouse CI for performance monitoring
- MySQL service for database testing

### Security
- Daily security scans
- Dependency vulnerability detection
- Static code analysis
- Secret scanning
- Web security audits

### Deployment
- Production-optimized builds
- Asset compilation and optimization
- Database migrations
- Cache optimization
- Health checks

### Monitoring
- Performance metrics via Lighthouse
- Security audit reports
- Deployment status notifications
- Health check monitoring

## Customization

### Adding New Tests
1. Add test files to `tests/` directory
2. Tests will automatically run in the CI workflow

### Modifying Security Thresholds
1. Edit `.lighthouserc.json` for performance thresholds
2. Edit `.lighthouserc-security.json` for security thresholds

### Custom Deployment
1. Modify `.github/workflows/cd.yml`
2. Add your deployment logic in the "Deploy to production" step
3. Configure necessary secrets

## Troubleshooting

### Common Issues
1. **Permission Errors**: Ensure proper file permissions in Docker
2. **Database Connection**: Verify database service configuration
3. **Asset Building**: Check Node.js version compatibility
4. **Security Failures**: Review and update vulnerable dependencies

### Debug Mode
Enable debug output by adding `--debug` flags to commands in workflows.

## Best Practices

1. **Keep Dependencies Updated**: Use Dependabot for automated updates
2. **Monitor Security**: Review security scan results regularly
3. **Test Before Deploy**: All tests must pass before deployment
4. **Use Environments**: Configure separate environments for staging/production
5. **Monitor Performance**: Track Lighthouse scores over time

## Support

For issues with CI/CD workflows:
1. Check GitHub Actions logs
2. Review workflow configuration
3. Verify repository secrets
4. Test locally with same PHP/Node versions
