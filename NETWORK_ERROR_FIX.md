# ?? Network Error Diagnosis Checklist

## Quick Test Steps

### 1?? Check if MySQL is Running

```bash
# Windows Command Prompt
mysql -u root -p

# If it connects, great! If not, start MySQL service:
net start MySQL80
```

### 2?? Check Backend Database Connection

In backend folder:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

**Should print database connection info. If you get an error, database isn't configured.**

---

### 3?? Check if Backend Can Start

In backend folder:
```bash
php artisan serve
```

**Should output:**
```
Laravel development server started on [http://127.0.0.1:8000]
```

**If it fails with errors, check the error message.**

---

### 4?? Check if Backend Responds

In a NEW terminal:
```bash
curl http://localhost:8000/api/courses
```

**Should return JSON data. If you get "Connection refused", backend isn't running.**

---

### 5?? Check Database Tables

```bash
php artisan tinker
>>> DB::table('students')->count();
```

**Should return 0 or a number. If you get an error, migrations weren't run:**
```bash
php artisan migrate
```

---

### 6?? Test Frontend Backend Connection

Add this to browser console (F12 ? Console):

```javascript
// Test if backend is reachable
fetch('http://localhost:8000/api/courses')
  .then(r => r.json())
  .then(d => console.log('? Backend works!', d))
  .catch(e => console.error('? Backend error:', e.message))
```

---

## Most Common Issues & Fixes

### Issue 1: "Port 8000 is already in use"

**Fix:**
```bash
# Kill the process using port 8000
# Windows
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Or use different port
php artisan serve --port=8001
```

---

### Issue 2: "SQLSTATE[HY000]: General error"

**Fix:** Database not set up
```bash
# Create database
mysql -u root -p
CREATE DATABASE cdm_academy;

# Run migrations
php artisan migrate
```

---

### Issue 3: "Class not found" errors

**Fix:** Dependencies not installed
```bash
composer install
php artisan key:generate
```

---

### Issue 4: Frontend still says "Network Error"

**Fix:** Check if backend URL is correct in `frontend/.env.local`:
```
VITE_API_URL=http://localhost:8000
```

Then restart frontend:
```bash
# Kill current npm dev process (Ctrl+C)
npm run dev
```

---

## Check Logs

### Backend Error Log
```bash
# See real-time errors
tail -f storage/logs/laravel.log

# Or view entire log
cat storage/logs/laravel.log
```

### Database Error Log
- Windows: Check MySQL error log in MySQL installation folder
- Look for authentication failures or connection issues

---

## Network Error Flow

```
Frontend tries to login
        ?
Makes request to http://localhost:8000/api/auth/login
        ?
Backend must be running on port 8000 ?
        ?
Database must be connected ?
        ?
Students table must exist ?
        ?
Response sent back to frontend ?
        ?
Login works! ??
```

**If any step fails, you get "Network Error"**

---

## Complete Debug Output

Run these commands and save the output:

```bash
# Test everything
echo "=== PHP Version ==="
php -v

echo "=== Composer Version ==="
composer --version

echo "=== MySQL Connection ==="
mysql -u root -p -e "SELECT 1;"

echo "=== Database Exists ==="
mysql -u root -p -e "SHOW DATABASES LIKE 'cdm_academy';"

echo "=== Env File ==="
cat backend\.env

echo "=== Start Backend ==="
cd backend
php artisan serve
```

Copy all output and I can help you fix it! ??

---

## ?? If Still Stuck

Share these:

1. **Backend terminal output** when you run `php artisan serve`
2. **Browser console error** (F12 ? Console)
3. **Browser Network tab** request/response (F12 ? Network ? try login ? click failed request)
4. **Output of:** `mysql -u root -p -e "SHOW DATABASES;"`
5. **Output of:** `curl http://localhost:8000/api/courses`

Then I can give you exact fix! ??
