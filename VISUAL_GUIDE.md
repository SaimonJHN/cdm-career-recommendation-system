# ?? Visual Setup Guide - Fix Network Error

## ?? What You Need Running

```
YOUR COMPUTER
??? ?? MySQL (Database)
?   ??? Port: 3306
?       Database: cdm_academy
?
??? ?? Backend (Laravel API)
?   ??? Terminal 1
?       Command: php artisan serve
?       Port: 8000
?       URL: http://localhost:8000
?
??? ?? Frontend (Vue.js)
?   ??? Terminal 2
?       Command: npm run dev
?       Port: 5173
?       URL: http://localhost:5173
?
??? ?? Browser
    ??? Open: http://localhost:5173
```

## ?? Request Flow

### ? With Network Error (Current)

```
Browser (5173)
    ?
    ?? Tries to send login request
    ?
    ?? To: http://localhost:8000/api/auth/login
    ?
    ??> ? FAILS - "Network Error"
        (Backend not running)
```

### ? After Fix (Goal)

```
Browser (5173)
    ?
    ?? User enters email & password
    ?
    ?? Click "Login"
    ?
    ?? Sends to: http://localhost:8000/api/auth/login
    ?
    ?? Backend (8000) receives request
    ?
    ?? Checks database: Is email registered?
    ?
    ?? Checks password: Does it match?
    ?
    ?? Generates token
    ?
    ?? Returns: { student: {...}, token: "..." }
    ?
    ?? Frontend stores token in browser
    ?
    ?? Redirects to: http://localhost:5173/dashboard
    ?
    ??> ? SUCCESS - Dashboard shows!
```

## ??? Setup Steps (Visual)

### Step 1: Database Setup

```
MySQL Server
    ?
    ?? Start service: net start MySQL80
    ?
    ?? Create database:
    ?   CREATE DATABASE cdm_academy;
    ?
    ?? ? Ready to use
```

### Step 2: Backend Setup

```
Terminal 1 (Backend)
    ?
    ?? cd backend
    ?
    ?? composer install
    ?   ?? Downloads PHP packages
    ?
    ?? php artisan key:generate
    ?   ?? Creates encryption key
    ?
    ?? php artisan migrate
    ?   ?? Creates database tables
    ?
    ?? php artisan serve
    ?   ?? Starts Laravel on port 8000
    ?
    ?? ? Backend running at http://localhost:8000
       (Keep this open!)
```

### Step 3: Frontend Setup

```
Terminal 2 (Frontend)
    ?
    ?? cd frontend
    ?
    ?? npm install
    ?   ?? Downloads JavaScript packages
    ?
    ?? npm run dev
    ?   ?? Starts Vue.js on port 5173
    ?
    ?? ? Frontend running at http://localhost:5173
       (Keep this open!)
```

### Step 4: Test

```
Browser
    ?
    ?? Go to: http://localhost:5173
    ?
    ?? Click: "Register" button
    ?
    ?? Fill form:
    ?   Email: test@example.com
    ?   Password: password123
    ?   First Name: John
    ?   Last Name: Doe
    ?   Phone: 09123456789
    ?
    ?? Click: "Sign Up"
    ?
    ?? See: "Registration successful"
    ?
    ?? Redirects to: Dashboard
    ?
    ?? ? SUCCESS!
```

## ??? Terminal Layout

```
DESKTOP
??????????????????????????????????????????????????????????????
?                                                            ?
?  ????????????????????         ????????????????????        ?
?  ?  Terminal 1      ?         ?  Terminal 2      ?        ?
?  ?  (Backend)       ?         ?  (Frontend)      ?        ?
?  ?                  ?         ?                  ?        ?
?  ?  $ cd backend    ?         ?  $ cd frontend   ?        ?
?  ?  $ composer...   ?         ?  $ npm install   ?        ?
?  ?  $ php artisan.. ?         ?  $ npm run dev   ?        ?
?  ?  $ php artisan.. ?         ?                  ?        ?
?  ?  Serving on      ?         ?  Serving at      ?        ?
?  ?  :8000 ?        ?         ?  :5173 ?        ?        ?
?  ?                  ?         ?                  ?        ?
?  ?  KEEP OPEN!      ?         ?  KEEP OPEN!      ?        ?
?  ????????????????????         ????????????????????        ?
?                                                            ?
??????????????????????????????????????????????????????????????
```

## ?? Connections

```
Frontend                Backend                Database
(5173)                  (8000)                 (3306)
  ?                       ?                      ?
  ?  Login Request        ?                      ?
  ???????????????????????>?                      ?
  ?                       ?                      ?
  ?                       ?  Query: Find user    ?
  ?                       ??????????????????????>?
  ?                       ?                      ?
  ?                       ?  User data returned  ?
  ?                       ?<??????????????????????
  ?                       ?                      ?
  ?  Token & User         ?                      ?
  ?<???????????????????????                      ?
  ?                       ?                      ?
  ? ? Save token         ?                      ?
  ? ? Go to dashboard    ?                      ?
```

## ?? Port Assignment

```
localhost:3306
?? MySQL Database
?? Store user data
?? Authentication data

localhost:8000
?? Laravel Backend API
?? Handle requests
?? Check credentials
?? Generate tokens
?? Return responses

localhost:5173
?? Vue.js Frontend
?? User interface
?? Forms
?? Dashboard
?? Exam interface
```

## ?? Success Indicators

### ? Backend Working

Terminal 1 shows:
```
Laravel development server started on [http://127.0.0.1:8000]
```

Browser shows (http://localhost:8000/api/courses):
```json
[
  {"id":1,"name":"BSIT",...},
  {"id":2,"name":"BSE",...}
]
```

### ? Frontend Working

Terminal 2 shows:
```
VITE v4.4.9  ready in 123 ms

?  Local:   http://localhost:5173/
```

Browser shows: Login page with forms ?

### ? Connection Working

Browser console shows (F12 ? Console):
```javascript
? Connected! [{...}, {...}]
```

### ? Login Working

Can successfully:
1. Register new account
2. Login with credentials
3. See dashboard
4. Access exam
5. View results

## ?? Error States

### ? Backend Down

```
Browser
  ?
  ??> Error: "Network Error"

Terminal 1
  ??> (Shows nothing or errors)
```

**Fix:** Run `php artisan serve`

### ? Frontend Down

```
Browser
  ?
  ??> Can't access http://localhost:5173
```

**Fix:** Run `npm run dev`

### ? Database Down

```
Terminal 1
  ??> SQLSTATE error: Can't connect to MySQL
```

**Fix:** Run `net start MySQL80`

### ? Wrong API URL

```
frontend/.env.local
  ?
  ??> VITE_API_URL=http://localhost:9000  ? (Wrong!)
      Should be:
      VITE_API_URL=http://localhost:8000  ? (Correct)
```

## ?? Demo Flow

1. **Start MySQL**
   ```
   net start MySQL80
   ```

2. **Terminal 1: Start Backend**
   ```
   cd backend
   php artisan serve
   ```
   Expected: `Serving on http://127.0.0.1:8000`

3. **Terminal 2: Start Frontend**
   ```
   cd frontend
   npm run dev
   ```
   Expected: `? Local: http://localhost:5173/`

4. **Browser: Test**
   - Go to: `http://localhost:5173`
   - Click: Register
   - Fill form
   - Click: Sign Up
   - Expected: Success ?

## ?? You're Ready When...

- [ ] Two terminal windows open
- [ ] Terminal 1 shows "Serving on :8000"
- [ ] Terminal 2 shows "Local: :5173"
- [ ] Both terminals running (not exited)
- [ ] Browser shows login page
- [ ] No errors in browser console

Now test the login! ??
