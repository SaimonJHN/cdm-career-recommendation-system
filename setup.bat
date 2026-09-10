@echo off
REM CDM Career Recommendation System - Setup Script for Windows

echo.
echo ========================================
echo CDM Career Recommendation System Setup
echo ========================================
echo.

REM Check Node.js
echo Checking Node.js installation...
node -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: Node.js is not installed. Please install Node.js 16+
    pause
    exit /b 1
)
for /f "tokens=*" %%i in ('node -v') do set NODE_VERSION=%%i
echo [OK] Node.js %NODE_VERSION% installed

REM Check PHP
echo Checking PHP installation...
php -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed. Please install PHP 8.1+
    pause
    exit /b 1
)
echo [OK] PHP installed

REM Check Composer
echo Checking Composer installation...
composer -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: Composer is not installed. Please install Composer
    pause
    exit /b 1
)
echo [OK] Composer installed

REM Backend Setup
echo.
echo Setting up Backend...
cd backend
if not exist .env (
    copy .env.example .env
    echo [OK] Created .env file
)

call composer install
echo [OK] Installed PHP dependencies

call php artisan key:generate
echo [OK] Generated APP_KEY

cd ..

REM Frontend Setup
echo.
echo Setting up Frontend...
cd frontend
if not exist .env (
    copy .env.example .env
    echo [OK] Created .env file
)

call npm install
echo [OK] Installed npm dependencies

cd ..

REM Summary
echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Update database credentials in backend\.env
echo 2. Run: cd backend ^&^& php artisan migrate
echo 3. Run: cd backend ^&^& php artisan db:seed
echo 4. Start backend: cd backend ^&^& php artisan serve
echo 5. Start frontend: cd frontend ^&^& npm run dev
echo.
echo URLs:
echo - Backend:  http://localhost:8000
echo - Frontend: http://localhost:5173
echo.
pause
