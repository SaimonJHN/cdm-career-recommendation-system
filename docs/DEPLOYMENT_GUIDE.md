> Current setup, API workflow changes, deployment, and recovery instructions are maintained in [Operations](OPERATIONS.md). The material below describes earlier versions.

# Deployment & Setup Guide

## Backend Setup (Laravel 10)

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Git

### Installation Steps

```bash
# Clone repository
cd backend
cp .env.example .env

# Edit .env with your database credentials
nano .env

# Install dependencies
composer install

# Generate APP_KEY
php artisan key:generate

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed

# Create storage link
php artisan storage:link

# Start server
php artisan serve
```

The backend will run on `http://localhost:8000`

## Frontend Setup (Vue 3 + Vite)

### Prerequisites
- Node.js 16+
- npm or yarn

### Installation Steps

```bash
# Navigate to frontend
cd frontend

# Copy environment file
cp .env.example .env

# Edit .env with API endpoints
nano .env

# Install dependencies
npm install

# Start development server
npm run dev
```

The frontend will run on `http://localhost:5173`

## Database Setup

### Create Database
```bash
mysql -u root -p
CREATE DATABASE cdm_career_recommendation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Run Migrations
```bash
cd backend
php artisan migrate
php artisan db:seed
```

## Configuration

### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials (Web application)
5. Add authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback`
   - `http://localhost:5173` (for development)
6. Copy credentials to `.env`:
   ```
   GOOGLE_CLIENT_ID=your_client_id
   GOOGLE_CLIENT_SECRET=your_client_secret
   ```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new student
- `POST /api/auth/login` - Login with email/password
- `POST /api/auth/google-login` - Google OAuth login
- `POST /api/auth/logout` - Logout (protected)

### Student
- `GET /api/student/dashboard` - Get dashboard data (protected)
- `GET /api/student/number` - Get student number (protected)
- `PUT /api/student/profile` - Update profile (protected)

### Exam
- `GET /api/exam/questions` - Get 100 exam questions
- `POST /api/exam/submit` - Submit exam answers (protected)
- `GET /api/exam/result` - Get exam result (protected)

### Results
- `GET /api/results/recommendation` - Get AI recommendations (protected)
- `POST /api/results/send-email` - Send results via email (protected)
- `GET /api/results/download-certificate` - Get certificate data (protected)

### Courses
- `GET /api/courses` - Get all courses
- `GET /api/courses/{id}` - Get course details

## Production Deployment

### Deploy on Heroku

```bash
# Backend
cd backend
heroku create your-app-name
git push heroku main

# Frontend
cd frontend
npm run build
# Deploy dist folder to Vercel or Firebase Hosting
```

### Environment Variables (Production)
- Update all `.env` files with production URLs
- Use HTTPS for all API calls
- Set `APP_DEBUG=false`
- Use strong JWT_SECRET

## Testing

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test
```

## Troubleshooting

### Laravel Issues
- **500 Error**: Check `storage/logs/laravel.log`
- **Database Error**: Verify MySQL is running and credentials are correct
- **CORS Error**: Check CORS configuration in `config/cors.php`

### Vue Issues
- **API not found**: Verify backend is running on correct port
- **Vite port conflict**: Change port in `vite.config.js`
- **Build errors**: Run `npm install` again

## Support

For issues, contact: support@cdm.edu.ph
