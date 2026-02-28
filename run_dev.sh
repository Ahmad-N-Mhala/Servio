#!/bin/bash

# Kill all background processes on exit
trap "trap - SIGTERM && kill -- -$$" SIGINT SIGTERM EXIT

echo "🚀 Starting Servio Development Environment..."

# 1. Start Laravel Backend
php artisan serve &

# 2. Start Vite Frontend
npm run dev &

# 3. Start Queue Worker (Crucial for SMS/Email)
echo "⌛ Starting Queue Worker..."
php artisan queue:work --tries=3 &

echo "✅ All services are starting!"
echo "--------------------------------------------------"
echo "Backend:  http://127.0.0.1:8000"
echo "Frontend: Vite HMR is running"
echo "Queue:    Monitoring for pending jobs (SMS/Email)"
echo "--------------------------------------------------"
echo "Press Ctrl+C to stop all services."

wait
