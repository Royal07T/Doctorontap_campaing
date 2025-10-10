#!/bin/bash

# DoctorOnTap Quick Start Script
# This script helps you get the application running quickly

echo "================================================"
echo "   DoctorOnTap - Healthcare Awareness Campaign"
echo "================================================"
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null
then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   Visit: https://getcomposer.org/download/"
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null
then
    echo "❌ PHP is not installed. Please install PHP 8.2+ first."
    exit 1
fi

echo "✅ PHP version: $(php -v | head -n 1)"
echo "✅ Composer version: $(composer --version | head -n 1)"
echo ""

# Check if dependencies are installed
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install --no-interaction
    echo "✅ Dependencies installed"
else
    echo "✅ Dependencies already installed"
fi
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
    echo "✅ Environment configured"
else
    echo "✅ Environment file exists"
fi
echo ""

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear > /dev/null 2>&1
php artisan view:clear > /dev/null 2>&1
echo "✅ Caches cleared"
echo ""

# Check if server is already running
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "⚠️  Port 8000 is already in use."
    echo "   The server might already be running."
    echo "   Visit: http://localhost:8000"
    echo ""
    read -p "Do you want to kill the existing process and restart? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        kill $(lsof -t -i:8000) 2>/dev/null
        echo "✅ Existing server stopped"
    else
        echo "ℹ️  Keeping existing server running"
        exit 0
    fi
fi

# Start the server
echo "🚀 Starting Laravel development server..."
echo ""
echo "================================================"
echo "   Server running at: http://localhost:8000"
echo "================================================"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php artisan serve

