# ?? DO THIS RIGHT NOW - 3 Steps to Fix Login

## Your Problem
? "Network Error" when trying to login or register

## Why It Happens
Backend server is NOT running. Frontend tries to connect to `http://localhost:8000` but nothing is there.

## The Fix (Just 3 Steps!)

---

## ? STEP 1: Start Backend Server

Open **PowerShell** or **Command Prompt**

```powershell
cd "C:\Users\Saimon\OneDrive\Documents\capstone(final)\backend"
composer install
php artisan migrate
php artisan serve
```

**Wait until you see:**
```
Laravel development server started on [http://127.0.0.1:8000]
```

**Keep this window OPEN!** ? Very important!

---

## ? STEP 2: Start Frontend Server

Open a **SECOND** PowerShell or Command Prompt

```powershell
cd "C:\Users\Saimon\OneDrive\Documents\capstone(final)\frontend"
npm install
npm run dev
```

**Wait until you see:**
```
? Local:   http://localhost:5173/
```

**Keep this window OPEN!** ? Very important!

---

## ? STEP 3: Test in Browser

1. Open your browser
2. Go to: `http://localhost:5173`
3. Click "Register" button
4. Fill in the form:
   - Email: `test@example.com`
   - Password: `password123`
   - First Name: `John`
   - Last Name: `Doe`
   - Phone: `09123456789`
5. Click "Sign Up"
6. **Should see success message!** ?

---

## ?? That's It!

If you followed all 3 steps correctly, login/register should now work!

---

## ?? Important Notes

### Keep Both Windows Open!
- Window 1 (Backend): Show `Serving on :8000`
- Window 2 (Frontend): Show `Local: :5173`

**If you close either one, you get "Network Error" again!**

### If You Get Error in Step 1

**Error: "Command composer not found"**
- PHP/Composer not installed
- Download from: https://getcomposer.org/

**Error: "Port 8000 already in use"**
- Another app using port 8000
- Run: `php artisan serve --port=8001`

**Error: "Database connection error"**
- MySQL not running
- Run: `net start MySQL80`

### If You Get Error in Step 2

**Error: "npm: command not found"**
- Node.js not installed
- Download from: https://nodejs.org/

**Error: "Port 5173 already in use"**
- Another app using port 5173
- Run: `npm run dev -- --port 5174`

### If Login Still Doesn't Work

**Check 1: Is backend accessible?**
Open browser: `http://localhost:8000/api/courses`
Should see JSON data. If not, backend isn't running.

**Check 2: Is frontend running?**
Open browser: `http://localhost:5173`
Should see login page. If not, frontend isn't running.

**Check 3: Errors in console?**
Press F12 in browser ? Console tab ? Look for red errors

---

## ?? For More Details

If you need more help:
- [README_FIX.md](README_FIX.md) - Detailed explanation
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common errors
- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Full setup guide
- [DOCUMENTATION_INDEX.md](DOCUMENTATION_INDEX.md) - All documentation

---

## ? Success!

When it's working, you can:
- ? Register new account
- ? Login with email & password
- ? See dashboard
- ? Take exam
- ? View results
- ? Download certificate

Everything is already coded! Just need the servers running! ??

---

**Question: "Do I need to do this every time I use the app?"**

**Answer:** YES! Every time you want to use the app:
1. Open Terminal 1 ? `cd backend` ? `php artisan serve`
2. Open Terminal 2 ? `cd frontend` ? `npm run dev`
3. Both must stay open while you use the app

This is normal for development! Just keep the windows open while you work.

---

**Try it now! You got this! ??**
