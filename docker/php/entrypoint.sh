#!/bin/bash
set -e

echo "🔧 Setting up Laravel permissions..."

# Wait for database
echo "⏳ Waiting for database..."
while ! nc -z mysql 3306; do
    echo "   Waiting for MySQL..."
    sleep 2
done
echo "✅ Database is ready!"

# Create required directories if they don't exist
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions  
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions for Laravel directories
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Ensure .env file is writable (if exists)
if [ -f .env ]; then
    chmod 664 .env
    echo "✅ .env file permissions set"
fi

echo "✅ Laravel permissions set successfully!"

# Execute the main command
exec "$@"
