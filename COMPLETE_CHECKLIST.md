# ?? Complete Checklist - Fix Network Error

## ? Prerequisites Installed?

- [ ] PHP 8.1+ installed (`php -v`)
- [ ] Composer installed (`composer --version`)
- [ ] Node.js installed (`node -v`)
- [ ] npm installed (`npm -v`)
- [ ] MySQL installed and running

**Not installed?** Download from:
- PHP: https://www.php.net/downloads
- Composer: https://getcomposer.org/download/
- Node.js: https://nodejs.org/
- MySQL: https://dev.mysql.com/downloads/mysql/

---

## ?? Backend Setup

### Terminal 1 - Backend Configuration

```powershell
# Navigate to backend
cd "C:\Users\Saimon\OneDrive\Documents\capstone(final)\backend"
```

- [ ] Database exists: `mysql -u root -p -e "CREATE DATABASE cdm_academy;"`
- [ ] Install dependencies: `composer install`
- [ ] Generate key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate`
- [ ] Start server: `php artisan serve`

**Expected output:**
```
Laravel development server started on [http://127.0.0.1:8000]
```

**Keep this terminal OPEN!**

---

## ?? Frontend Setup

### Terminal 2 - Frontend Configuration

```powershell
# Navigate to frontend
cd "C:\Users\Saimon\OneDrive\Documents\capstone(final)\frontend"
```

- [ ] `.env.local` exists with: `VITE_API_URL=http://localhost:8000`
- [ ] Install dependencies: `npm install`
- [ ] Start dev server: `npm run dev`

**Expected output:**
```
VITE v4.4.9  ready in 123 ms

?  Local:   http://localhost:5173/
```

**Keep this terminal OPEN!**

---

## ?? Test Backend

While backend is running, test these URLs:

- [ ] `http://localhost:8000/api/courses` ? Should return JSON
- [ ] Check Terminal 1 ? Should show request log

```powershell
# From Terminal 3 (new terminal)
curl http://localhost:8000/api/courses
```

---

## ?? Test Frontend

- [ ] Open `http://localhost:5173` in browser
- [ ] Page loads without errors
- [ ] Open DevTools (F12) ? Console ? No red errors
- [ ] Open DevTools ? Network ? No failed requests

---

## ?? Test API Connection

In browser console (F12 ? Console):

```javascript
fetch('http://localhost:8000/api/courses')
  .then(r => r.json())
  .then(d => console.log('? Connected!', d))
  .catch(e => console.error('? Error:', e))
```

- [ ] Should see `? Connected!` with data
- [ ] Should NOT see `? Error`

---

## ?? Test Registration

1. [ ] Go to `http://localhost:5173/register`
2. [ ] Fill in form:
   - Email: `test@example.com`
   - Password: `password123`
   - Confirm Password: `password123`
   - First Name: `John`
   - Last Name: `Doe`
   - Phone: `09123456789`
3. [ ] Click "Register"
4. [ ] Should see success message
5. [ ] Should redirect to dashboard

---

## ?? Test Login

1. [ ] Go to `http://localhost:5173/login`
2. [ ] Fill in form:
   - Email: `test@example.com` (from registration)
   - Password: `password123`
3. [ ] Click "Login"
4. [ ] Should see success message
5. [ ] Should redirect to dashboard
6. [ ] Dashboard should load with user info

---

## ?? Troubleshooting Checklist

### Backend Won't Start

- [ ] Check if MySQL is running: `net start MySQL80`
- [ ] Check if database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- [ ] Check backend/.env file exists
- [ ] Try: `php artisan key:generate`
- [ ] Try: `php artisan migrate`

### Frontend Shows Network Error

- [ ] Backend is running? (See "Terminal 1" above)
- [ ] URL in `frontend/.env.local` is `http://localhost:8000`
- [ ] Backend console shows request? (check Terminal 1)
- [ ] Try: `npm install`
- [ ] Try: Restart frontend (`npm run dev`)

### CORS Error in Console

- [ ] Backend CORS config is correct (already set up)
- [ ] Backend is running on `http://localhost:8000`
- [ ] Frontend is on `http://localhost:5173`
- [ ] Restart backend server

### Database Error

- [ ] MySQL running? `net start MySQL80`
- [ ] Database created? `mysql -u root -p -e "CREATE DATABASE cdm_academy;"`
- [ ] Run: `php artisan migrate`
- [ ] Check backend/.env has correct DB settings

### "Port already in use" Error

- [ ] Another app using port 8000 or 5173
- [ ] Kill the process or use different port
- [ ] Backend: `php artisan serve --port=8001`
- [ ] Frontend: `npm run dev -- --port 5174`

---

## ?? System Status Check

Run this to verify everything:

```powershell
# Check PHP
php -v

# Check Composer
composer --version

# Check Node
node -v
npm -v

# Check MySQL
mysql -u root -p -e "SELECT 1;"

# Check databases
mysql -u root -p -e "SHOW DATABASES;"

# Check if tables exist (with backend running)
mysql -u root -p -e "USE cdm_academy; SHOW TABLES;"
```

---

## ?? Before/After

### ? Before (Network Error)

```
Browser ? Frontend (5173)
   ?
Frontend tries to call: http://localhost:8000/api/auth/login
   ?
? Connection refused
   ?
Error message: "Network Error"
```

### ? After (Working)

```
Browser ? Frontend (5173)
   ?
Frontend calls: http://localhost:8000/api/auth/login
   ?
Backend (8000) receives request
   ?
? Database lookup
   ?
Returns: { student: {...}, token: "..." }
   ?
Frontend stores token
   ?
? Redirects to dashboard
```

---

## ?? Expected Results

After following all steps, you should be able to:

1. ? Register new account
2. ? Login with that account
3. ? Access dashboard
4. ? Take exam
5. ? View results
6. ? Download certificate

---

## ?? Quick Fixes

**If login still fails:**

```powershell
# Terminal 1 (Backend)
cd backend
php artisan tinker
>>> DB::table('students')->get();  # See all students
>>> DB::table('students')->truncate();  # Clear table
>>> exit
php artisan db:seed  # Reseed data
php artisan serve
```

**If frontend won't connect:**

```powershell
# Terminal 2 (Frontend)
rm node_modules package-lock.json  # Delete cache
npm install
npm run dev
```

---

## ?? Error Messages Guide

| Error | Cause | Fix |
|-------|-------|-----|
| Network Error | Backend not running | Run `php artisan serve` |
| CORS Error | Frontend not allowed | Check backend CORS config |
| Invalid credentials | Wrong email/password | Register new account |
| Database error | Tables don't exist | Run `php artisan migrate` |
| Port already in use | Another app on port | Use different port |
| Connection refused | Backend not reachable | Check URL & port |

---

## ? Final Checklist Before Testing

- [ ] Terminal 1: Backend running with `php artisan serve`
- [ ] Terminal 2: Frontend running with `npm run dev`
- [ ] Both terminals show "ready" messages
- [ ] Backend console shows no errors
- [ ] Frontend shows no errors in browser console
- [ ] Can access `http://localhost:8000/api/courses` in browser
- [ ] Can access `http://localhost:5173` in browser
- [ ] `.env.local` has correct API URL
- [ ] MySQL database `cdm_academy` exists
- [ ] Database tables exist (after migrate)

Once all checked ?, try registering/logging in! ??
