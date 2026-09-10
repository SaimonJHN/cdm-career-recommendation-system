# Getting Started Guide

## Quick Start

### 1. Backend Setup (5 minutes)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

**Backend running on:** `http://localhost:8000`

### 2. Frontend Setup (5 minutes)

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

**Frontend running on:** `http://localhost:5173`

## Features Overview

### 🎓 Student Registration & Login
- Create account with email or Google
- Get auto-generated student number (CDM-YYYY-XXXXX)
- Secure JWT authentication

### 📝 Entrance Exam
- 100 questions across 5 subjects
- Real-time timer (120 minutes)
- Auto-save answers
- View progress and skip functionality

### 🤖 AI-Powered Recommendations
- Instant results after exam submission
- Personalized program recommendation (BSIT, BSE, SBA)
- Confidence score based on subject performance
- Alternative program suggestions

### 📊 Results & Downloads
- Detailed score breakdown by subject
- Email results to registered email
- Download certificate as PDF
- Download results as image

### 🎨 Design
- White and green color scheme
- Responsive design (mobile, tablet, desktop)
- Smooth animations and transitions
- Modern Tailwind CSS styling

## File Structure

```
capstone(final)/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/   # API Controllers
│   │   └── Models/             # Database Models
│   ├── database/
│   │   ├── migrations/         # Database Structure
│   │   └── seeders/            # Sample Data
│   ├── routes/
│   │   └── api.php             # API Routes
│   └── .env.example
│
├── frontend/                   # Vue 3 + Vite
│   ├── src/
│   │   ├── pages/              # Vue Pages
│   │   ├── components/         # Vue Components
│   │   ├── stores/             # Pinia State Management
│   │   ├── utils/              # Helper Functions
│   │   ├── router/             # Vue Router
│   │   ├── App.vue             # Root Component
│   │   └── main.js             # Entry Point
│   ├── vite.config.js
│   ├── tailwind.config.js
│   └── package.json
│
├── docs/                       # Documentation
│   ├── ENTRANCE_EXAM_QUESTIONS.md
│   ├── DEPLOYMENT_GUIDE.md
│   └── GETTING_STARTED.md
│
└── README.md
```

## Important Notes

### Student Number Format
- Format: `CDM-YYYY-XXXXX`
- Example: `CDM-2025-00001`
- Generated automatically at registration
- Based on admission year (2025-2026, etc.)

### Passing Scores
- **Minimum:** 75/100
- **Excellent:** 90-100
- **Very Good:** 80-89
- **Good:** 75-79

### Exam Duration
- **Total Time:** 120 minutes (2 hours)
- **Questions:** 100
- **Categories:** 5 subjects

## Next Steps

1. **Configure Database**
   - Create MySQL database
   - Update `.env` credentials
   - Run migrations

2. **Set Up Google OAuth**
   - Get credentials from Google Cloud Console
   - Add to `.env` files
   - Test Google login

3. **Seed Courses**
   - Run `php artisan db:seed`
   - Loads BSIT, BSE, SBA courses

4. **Test Exam Questions**
   - All 100 questions pre-loaded
   - Can modify in database seeder
   - Supports different difficulty levels

## Useful Commands

```bash
# Backend
php artisan tinker                 # Interactive shell
php artisan migrate:fresh --seed  # Reset & seed database
php artisan make:model ModelName   # Create new model
php artisan make:controller CtrlName  # Create controller

# Frontend
npm run build                      # Build for production
npm run preview                    # Preview production build
npm run lint                       # Check code quality
```

## Credentials for Testing

```
Email: test@cdm.edu.ph
Password: password123
Student Number: CDM-2025-00001
Admission Year: 2025-2026
```

## Support

For questions or issues:
- Check DEPLOYMENT_GUIDE.md
- Review ENTRANCE_EXAM_QUESTIONS.md
- Contact: support@cdm.edu.ph

---

**Happy coding! 🚀**
