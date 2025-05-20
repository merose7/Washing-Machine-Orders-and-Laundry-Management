@echo off
REM Batch script to run Laravel scheduler every minute using Windows Task Scheduler

REM Change directory to Laravel project
cd /d C:\laragon\www\thedailywash

REM Run Laravel scheduler
php artisan schedule:run
