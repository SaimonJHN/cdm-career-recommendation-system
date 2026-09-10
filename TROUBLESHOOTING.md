# ?? TROUBLESHOOTING REFERENCE

## Common Errors & Fixes

### Error 1: "Network Error" when logging in

```
Error: Network Error
Location: Browser login page
```

**Cause:** Backend not running

**Fix:**
```powershell
cd backend
php artisan serve
```

**Verify:**
- Terminal shows: `Serving on [http://127.0.0.1:8000]`
- Browser: `http://localhost:8000/api/courses` returns JSON

---

### Error 2: "CORS Error" in browser console

```
Error: Access to XMLHttpRequest blocked by CORS policy
```

**Cause:** Backend CORS not configured correctly

**Fix:**
Check `backend/config/cors.php` has:
```php
'allowed_origins' => ['http://localhost:5173', 'http://localhost:3000'],
```

Then restart backend:
```powershell
php artisan serve
```

---

### Error 3: "SQLSTATE[HY000]: General error"

```
SQLSTATE[HY000]: General error
PDO Exception
```

**Cause:** Database not connected or tables don't exist

**Fix:**
```powershell
# Make sure MySQL is running
net start MySQL80

# Make sure database exists
mysql -u root -p -e "CREATE DATABASE cdm_academy;"

# Run migrations
php artisan migrate
```

---

### Error 4: "Port 8000 already in use"

```
Failed to listen on 127.0.0.1:8000
```

**Cause:** Another app using that port

**Fix Option 1:** Kill the process
```powershell
netstat -ano | findstr :8000
taskkill /PID <PID_NUMBER> /F
php artisan serve
```

**Fix Option 2:** Use different port
```powershell
php artisan serve --port=8001
```

Then update `frontend/.env.local`:
```
VITE_API_URL=http://localhost:8001
```

---

### Error 5: "Class not found" error in backend

```
Class 'App\Http\Controllers\AuthController' not found
```

**Cause:** Dependencies not installed

**Fix:**
```powershell
cd backend
composer install
php artisan serve
```

---

### Error 6: "Key generation failed" error

```
RuntimeException: No application encryption key has been generated
```

**Cause:** APP_KEY not generated

**Fix:**
```powershell
cd backend
php artisan key:generate
php artisan serve
```

---

### Error 7: Frontend shows blank page

```
Browser shows: Blank white screen
Console shows: Various errors
```

**Cause:** Frontend not built or dependencies missing

**Fix:**
```powershell
cd frontend
rm node_modules package-lock.json
npm install
npm run dev
```

---

### Error 8: "Invalid credentials" when logging in

```
Error: Invalid credentials
```

**This is actually GOOD!** It means:
- Backend is working ?
- Database is connected ?
- Authentication is checking correctly ?

**Solution:** Register first
1. Go to register page
2. Create account
3. Then login with those credentials

---

### Error 9: "Cannot find module" error

```
Error: Cannot find module 'axios'
```

**Cause:** npm dependencies not installed

**Fix:**
```powershell
cd frontend
npm install
npm run dev
```

---

### Error 10: Frontend port 5173 already in use

```
EADDRINUSE: address already in use :::5173
```

**Cause:** Another app using port 5173

**Fix:**
```powershell
npm run dev -- --port 5174
```

Then open: `http://localhost:5174`

---

## Diagnostic Commands

### Check Everything

```powershell
# Check PHP version
php -v

# Check Composer
composer --version

# Check Node
node -v
npm -v

# Check MySQL
mysql -u root -p -e "SELECT 1;"

# Check if databases exist
mysql -u root -p -e "SHOW DATABASES;"

# Check database tables
mysql -u root -p -e "USE cdm_academy; SHOW TABLES;"

# Check if port 8000 is in use
netstat -ano | findstr :8000

# Test backend API
curl http://localhost:8000/api/courses

# Test with verbose output
curl -v http://localhost:8000/api/courses

# Show backend logs
tail -f backend/storage/logs/laravel.log

# Test database from Laravel
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::table('students')->count();
```

---

## Log Files to Check

### Backend Logs
```
backend/storage/logs/laravel.log
```

Read with:
```powershell
Get-Content backend/storage/logs/laravel.log -Tail 50
```

### MySQL Logs (Windows)
```
C:\ProgramData\MySQL\MySQL Server 8.0\Data\COMPUTERNAME.err
```

### Browser Console
F12 ? Console tab ? Look for red errors

### Browser Network
F12 ? Network tab ? Click failed request ? See response

---

## Testing Checklist

### Quick Health Check

```powershell
# Test 1: Is backend reachable?
$response = Invoke-WebRequest http://localhost:8000/api/courses
if ($response.StatusCode -eq 200) { "? Backend OK" } else { "? Backend Error" }

# Test 2: Is database connected?
try {
    php artisan tinker < "DB::connection()->getPdo(); exit;"
    "? Database OK"
} catch {
    "? Database Error"
}

# Test 3: Can you access frontend?
$response = Invoke-WebRequest http://localhost:5173
if ($response.StatusCode -eq 200) { "? Frontend OK" } else { "? Frontend Error" }
```

---

## Advanced Troubleshooting

### Reset Everything

If things are really messed up:

```powershell
# Backend
cd backend
rm vendor
composer install
php artisan key:generate
php artisan migrate:fresh
php artisan serve

# Frontend (new terminal)
cd frontend
rm node_modules package-lock.json
npm install
npm run dev
```

### Clear Backend Cache

```powershell
cd backend
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Fresh Database

```powershell
cd backend
php artisan migrate:fresh --seed
```

?? This deletes all data!

### Enable Debug Mode

In `backend/.env`:
```
APP_DEBUG=true
```

Restart backend. Error messages will be more detailed.

---

## Before You Submit an Issue

Gather this info:

1. **Backend Status**
   ```powershell
   # Terminal 1 output
   php artisan serve
   ```

2. **Frontend Status**
   ```powershell
   # Terminal 2 output
   npm run dev
   ```

3. **Error Message**
   - What exactly does it say?
   - Full text if possible

4. **Browser Console** (F12)
   - What red errors appear?

5. **Network Request** (F12 ? Network)
   - Click failed request
   - What's in Request? Response?

6. **Database Status**
   ```powershell
   mysql -u root -p -e "SHOW DATABASES;"
   ```

7. **PHP/Node Versions**
   ```powershell
   php -v
   node -v
   npm -v
   ```

---

## Common Solution Pattern

1. **Stop everything** - Press Ctrl+C in terminals
2. **Check error message** - What does it say?
3. **Find matching error** - In this document
4. **Follow fix steps** - Run the commands
5. **Test again** - Try logging in
6. **If still broken** - Check logs and diagnostics above

---

## When Nothing Works

Try this nuclear option:

```powershell
# Delete everything cache/build
cd backend
rm -r vendor bootstrap/cache
composer install

cd frontend
rm -r node_modules
npm install

# Recreate database
mysql -u root -p -e "DROP DATABASE cdm_academy; CREATE DATABASE cdm_academy;"

# Start fresh
php artisan migrate
php artisan serve
```

---

## Emergency Contacts

If all else fails:

1. Check documentation in project root
2. Read error messages carefully
3. Search for the error online
4. Check Laravel/Vue.js documentation
5. Restart your computer (yes, really!)

---

## Prevention Tips

? Do this to avoid issues:

- Keep both terminals open
- Don't close them accidentally
- Check console for warnings
- Clear browser cache if weird bugs
- Restart servers if connection drops
- Update npm regularly: `npm update`
- Backup your code frequently

? Don't do this:

- Close terminals randomly
- Delete `vendor` or `node_modules` folders
- Edit `backend/.env` without restarting
- Run multiple `npm run dev` processes
- Use admin access you don't need
- Ignore error messages

---

## ?? More Help

- Laravel Docs: https://laravel.com/docs
- Vue.js Docs: https://vuejs.org/
- Vite Docs: https://vitejs.dev/
- MySQL Docs: https://dev.mysql.com/doc/

---

Remember: Most errors are fixed by:
1. Reading the error message
2. Checking the logs
3. Restarting the servers
4. Trying again

Good luck! ??
