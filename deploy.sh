#!/bin/bash

# Deployment script for windemo
# This script builds the frontend and copies it to Laravel's public directory

set -e

echo "🚀 Starting deployment..."

# Get the script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Build frontend
echo "📦 Building frontend..."
cd frontend
npm install
npm run build
cd ..

# Clean old frontend files from backend/public (except Laravel files)
echo "🧹 Cleaning old frontend assets..."
cd backend/public
# Remove old assets folder if exists
rm -rf assets 2>/dev/null || true
# Remove old index.html if exists (but keep Laravel's index.php)
rm -f index.html 2>/dev/null || true
cd ../..

# Copy frontend build to backend/public
echo "📋 Copying frontend build to backend/public..."
cp -r frontend/dist/* backend/public/

# Backend setup
echo "🔧 Setting up backend..."
cd backend
composer install --no-dev --optimize-autoloader

# Clear and cache config (note: route:cache skipped due to closure routes)
php artisan config:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

echo "✅ Deployment complete!"
echo ""
echo "Make sure Apache/Nginx is pointing to: $(pwd)/public"
