#!/bin/bash

# 1. Install Composer Dependencies
composer install --no-interaction --no-ansi --no-dev --prefer-dist --optimize-autoloader

# 2. Generate Laravel's optimized configuration
# This is crucial for performance on Vercel
php artisan config:cache

# 3. Run Database Migrations and Seeding
# The --force flag is needed for production environments
php artisan migrate --seed --force