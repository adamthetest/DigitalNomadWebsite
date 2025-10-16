#!/bin/bash

# Script to install the pre-push hook for quality checks
# This will copy the pre-push hook to .git/hooks/ and make it executable

set -e

echo "🔧 Installing Git pre-push hook for quality checks..."

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo "❌ Error: This doesn't appear to be a git repository"
    exit 1
fi

# Check if the hook script exists
if [ ! -f "scripts/pre-push-hook" ]; then
    echo "❌ Error: scripts/pre-push-hook not found"
    exit 1
fi

# Create .git/hooks directory if it doesn't exist
mkdir -p .git/hooks

# Copy the hook
cp scripts/pre-push-hook .git/hooks/pre-push

# Make it executable
chmod +x .git/hooks/pre-push

echo "✅ Pre-push hook installed successfully!"
echo ""
echo "The hook will now run before every git push and check:"
echo "  • Laravel Pint (Code Style)"
echo "  • PHPStan (Static Analysis)"
echo "  • Tests (Unit & Feature)"
echo ""
echo "If any check fails, the push will be blocked."
echo "To bypass the hook (not recommended), use: git push --no-verify"
