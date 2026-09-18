> Current setup, API workflow changes, deployment, and recovery instructions are maintained in [Operations](docs/OPERATIONS.md). The material below describes earlier versions.

# CDM Career Recommendation System

An AI-based career recommendation system for Colegio de Montalban entrance exam and student admission.

## Features

- 🎓 Three Career Paths: BSIT, BSE, SBA
- 🤖 AI-Powered Career Recommendations
- 📝 100-Question Entrance Exam
- 👤 Student Authentication (Google OAuth & PNM Account)
- 📊 Automated Student Dashboard
- 🎯 Passing Score: 75-100
- 📥 Results Email Integration
- 🖼️ Download Results & Certificate
- 🎨 White & Green Design Theme

## System Requirements

- PHP 8.1+
- Node.js 16+
- MySQL 8.0+
- Composer
- npm or yarn

## Installation

### Backend Setup (Laravel)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend Setup (Vue.js)

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

## Mobile App / PWA

The mobile app is part of the existing `frontend/` application rather than a separate project. It uses the same Vue interface, student accounts, Laravel API, and database.

Build and deploy the frontend over HTTPS:

```bash
cd frontend
npm run build
```

Users can open `/download-app` or choose **Get the app** in the website navigation. Supported browsers display their native installation prompt. On iPhone or iPad, users install it from Safari using **Share > Add to Home Screen**.

Important mobile files:

- `frontend/public/manifest.webmanifest` — app identity and installation metadata
- `frontend/public/sw.js` — application-shell caching and offline fallback
- `frontend/src/pages/DownloadApp.vue` — installation screen and device instructions
- `frontend/src/components/VoiceAssistantPlaceholder.vue` — reserved AI voice-assistant interface

Set `VITE_API_URL` to the deployed HTTPS Laravel API before building. Accounts, examinations, and results require a connection to that API. Installation prompts do not work on plain HTTP except on localhost.

## Project Structure

```
capstone(final)/
├── backend/              # Laravel REST API
│   ├── app/
│   ├── routes/
│   ├── resources/
│   └── ...
├── frontend/             # Vue.js Frontend
│   ├── src/
│   ├── components/
│   ├── pages/
│   └── ...
└── docs/                 # Documentation & Exam Paper
```

## Administration Portal

The separate `admin/` Vue application provides role-based management for students, programs, exam questions, results, approved Google administrator accounts, and audit logs. It shares the Laravel API and MySQL database with the student system.

See [`docs/ADMIN_SETUP.md`](docs/ADMIN_SETUP.md) for migration, first-admin invitation, Google OAuth, development, and deployment instructions.

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new student
- `POST /api/auth/login` - Login with Google account
- `POST /api/auth/google` - Google OAuth login
- `POST /api/auth/logout` - Logout

### Student Dashboard
- `GET /api/student/dashboard` - Get student info & recommendations
- `GET /api/student/number` - Generate student number

### Exam
- `GET /api/exam/questions` - Get 100 exam questions
- `POST /api/exam/submit` - Submit exam answers
- `GET /api/exam/results` - Get exam results

### Results
- `GET /api/results/recommendation` - Get AI recommendations
- `POST /api/results/send-email` - Send results to email
- `GET /api/results/download-certificate` - Download certificate

## Entrance Exam Details

- **Total Questions**: 100
- **Subjects**:
  - General Mathematics: 25 questions
  - Science (Biology, Chemistry, Physics): 25 questions
  - Reading Comprehension: 20 questions
  - Logical Reasoning: 15 questions
  - Technical Aptitude: 15 questions

- **Passing Score**: 75-100
- **Time Limit**: 120 minutes

## Career Programs

1. **BSIT** (Bachelor of Science in Information Technology)
   - Ideal for: High technical aptitude, problem solvers
   - Recommended if: Math & Logic scores > 80

2. **BSE** (Bachelor of Science in Engineering)
   - Ideal for: Strong Science background, analytical thinkers
   - Recommended if: Science & Math scores > 75

3. **SBA** (Bachelor of Science in Business Administration)
   - Ideal for: Business-minded, verbal skills
   - Recommended if: Reading & Logical Reasoning > 70

## Configuration

### Google OAuth Setup

1. Create credentials at [Google Cloud Console](https://console.cloud.google.com/)
2. Set redirect URI: `http://localhost:8000/auth/google/callback`
3. Add credentials to `.env` file

### Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE cdm_career_recommendation;

# Run migrations
php artisan migrate
php artisan db:seed
```

## Development

### Running Tests

```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test
```

### Code Style

```bash
# Backend
cd backend
php artisan code:analyze

# Frontend
cd frontend
npm run lint
```

## Deployment

See deployment guides in `/docs` folder.

## License

MIT License - Colegio de Montalban 2025

## Support

For issues and inquiries, contact: admissions@cdm.edu.ph
