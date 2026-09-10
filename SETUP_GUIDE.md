# ?? COMPLETE SETUP GUIDE - Fix Network Error

## Problem
You're getting "Network Error" when trying to login because:
1. ? Backend server is not running
2. ? Database is not set up/migrated
3. ? Backend cannot communicate with frontend

## ? Solution - Complete Setup Steps

### STEP 1: Set Up Database

**Note:** You need MySQL running on your computer.

#### Option A: If MySQL is NOT running
```bash
# Install MySQL Community Server from: https://dev.mysql.com/downloads/mysql/
# Or if you have it installed, start the service

# Windows - Start MySQL service
net start MySQL80

# Or use XAMPP/WAMP if you have them
```

#### Option B: Create the Database
1. Open MySQL Command Line or PhpMyAdmin
2. Create a new database:
```sql
CREATE DATABASE cdm_academy;
```

Or use this command:
```bash
mysql -u root -p -e "CREATE DATABASE cdm_academy;"
```

---

### STEP 2: Set Up Backend

Open a **NEW Terminal** and run these commands:

```bash
# 1. Navigate to backend folder
cd "C:\Users\Saimon\OneDrive\Documents\capstone(final)\backend"

# 2. Install PHP dependencies (if not already done)
composer install

# 3. Generate Laravel app key (should already be done)
php artisan key:generate

# 4. Run database migrations (creates tables)
php artisan migrate

# 5. Seed the database (optional - creates sample data)
php artisan db:seed

# 6. Start Laravel development server
php artisan serve
```

**IMPORTANT:** Keep this terminal open! The server needs to stay running.

You should see:
```
Laravel development server started on [http://127.0.0.1:8000]
```

---

### STEP 3: Update Frontend Configuration

Make sure your frontend `.env.local` has the correct backend URL:

File: `frontend/.env.local`
```
VITE_API_URL=http://localhost:8000
```

---

### STEP 4: Run Frontend

Open a **DIFFERENT Terminal** and run:

```bash
cd "C:\Users\Saimon\OneDrive\Documents\capstone(final)\frontend"

npm install
npm run dev
```

You should see:
```
VITE v4.4.9  ready in 123 ms

?  Local:   http://localhost:5173/
```

---

### STEP 5: Test Login

1. Open browser to `http://localhost:5173`
2. Click "Login" or "Register"
3. Try to login/register
4. **Should work now!**

---

## ?? Troubleshooting

### Error: "Network Error" still appears

**Check 1: Is backend running?**
```bash
# Test if backend is accessible
curl http://localhost:8000/api/courses
```

If you get "Connection refused", backend is NOT running. Go back to STEP 2 and run:
```bash
php artisan serve
```

**Check 2: Is database connected?**
```bash
# In backend terminal, try this
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

If you get an error, your database isn't set up correctly.

**Check 3: Check browser console for errors**
1. Open browser DevTools (F12)
2. Go to "Console" tab
3. Try logging in
4. Look for errors - they'll tell you what's wrong

**Check 4: Check Network tab**
1. Open browser DevTools (F12)
2. Go to "Network" tab
3. Try logging in
4. Click on the failed request to see response

### Error: "CORS Error"

This means the backend doesn't allow requests from frontend. Make sure:
1. Backend CORS config is correct
2. Backend is configured to allow `http://localhost:5173`
3. Restart the backend server

### Error: "Invalid credentials"

This is actually a GOOD sign - it means backend is working! Just:
1. Make sure email/password are correct
2. If you never registered, click "Sign up"
3. After registering, try logging in with those credentials

### Error: "Database not found"

Run migrations:
```bash
php artisan migrate
```

### Error: "Composer not found"

PHP/Composer not installed. Download from: https://getcomposer.org/

---

## ?? Quick Start (Summary)

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```

**Browser:**
- Frontend: `http://localhost:5173`
- Backend: `http://localhost:8000`

---

## ?? Prerequisites Checklist

Before starting, make sure you have:

- ? PHP 8.1+ installed
- ? Composer installed
- ? Node.js & npm installed
- ? MySQL server running
- ? `backend/.env` file created
- ? `frontend/.env.local` file created

If something is missing, install it!

---

## ? After Everything Works

Once login/registration works:

1. Create a test account
2. Take the exam
3. See your results
4. Download certificate

All features should work now! ??

---

## ?? Still Having Issues?

Provide these details:

1. What error message do you see?
2. What does the backend terminal show?
3. What does browser console show? (F12 ? Console)
4. What's in Network tab? (F12 ? Network ? Click failed request)

I can help debug from there!
