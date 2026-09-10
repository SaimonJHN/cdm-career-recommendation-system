# Installation & Quick Reference

## One-Time Setup

### Option 1: Automatic Setup (Recommended)

**Windows:**
```bash
setup.bat
```

**Linux/Mac:**
```bash
bash setup.sh
chmod +x setup.sh
./setup.sh
```

### Option 2: Manual Setup

**Backend:**
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

**Frontend:**
```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

## Running the Application

### Terminal 1 - Backend
```bash
cd backend
php artisan serve
# Runs on http://localhost:8000
```

### Terminal 2 - Frontend
```bash
cd frontend
npm run dev
# Runs on http://localhost:5173
```

## Testing the System

### 1. Register New Student
- Go to `http://localhost:5173/register`
- Fill in details
- Will receive auto-generated student number

### 2. Login
- Use registered email and password
- Or login with Google

### 3. Take Exam
- Click "Start Exam" on dashboard
- Answer 100 questions in 120 minutes
- Submit when done

### 4. View Results
- See score breakdown by category
- Get AI career recommendation
- Download certificate and results

## Environment Configuration

### Backend (.env)
```
APP_NAME="CDM Career Recommendation"
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=cdm_career_recommendation
DB_USERNAME=root
DB_PASSWORD=
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
```

### Frontend (.env)
```
VITE_API_URL=http://localhost:8000/api
VITE_GOOGLE_CLIENT_ID=your_google_client_id
```

## Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE cdm_career_recommendation;
EXIT;

# Run migrations
cd backend
php artisan migrate

# Seed initial data (BSIT, BSE, SBA)
php artisan db:seed --class=CourseSeeder
```

## Troubleshooting

### "Connection refused" error
- Make sure Laravel server is running: `php artisan serve`
- Check port 8000 is not in use

### "Module not found" error (Frontend)
- Delete node_modules: `rm -rf node_modules`
- Reinstall: `npm install`

### Database connection error
- Check MySQL is running
- Verify credentials in .env
- Ensure database exists

### Google OAuth not working
- Get credentials from Google Cloud Console
- Update .env with CLIENT_ID and SECRET
- Set redirect URI to `http://localhost:8000/auth/google/callback`

## Useful Commands

```bash
# Backend
php artisan tinker                    # Interactive shell
php artisan migrate:fresh --seed     # Reset all data
php artisan make:model ModelName      # Create new model
php artisan route:list                # List all routes
php artisan cache:clear               # Clear cache

# Frontend
npm run build                         # Build for production
npm run preview                       # Preview production build
npm run lint                          # Code quality check
npm run dev                           # Development server
```

## Production Deployment

See `DEPLOYMENT_GUIDE.md` for complete production setup.

## Support

- 📧 Email: support@cdm.edu.ph
- 📖 Docs: See /docs folder
- 🐛 Issues: Check DEPLOYMENT_GUIDE.md troubleshooting

---

Happy coding! 🚀
