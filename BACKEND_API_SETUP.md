# Backend API Configuration Guide

## Current Setup

Your frontend is now configured to communicate with a backend API at:
```
http://localhost:8000
```

## Changing the API URL

If your backend runs on a different address, edit `frontend/.env.local`:

```bash
# Local development
VITE_API_URL=http://localhost:8000

# Remote server
VITE_API_URL=https://api.yourdomain.com

# Different port
VITE_API_URL=http://localhost:3000
```

**Note:** After changing the URL, restart the frontend dev server.

## Backend Requirements

## Google Sign-In (`@student.pnm.edu.ph` only)

Create a **Web application** OAuth client in Google Cloud and add the frontend URL (for example, `http://localhost:5173`) as an authorized JavaScript origin. Put the same Web Client ID in both files:

```env
# backend/.env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_ALLOWED_DOMAIN=student.pnm.edu.ph

# frontend/.env.local
VITE_GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
VITE_GOOGLE_ALLOWED_DOMAIN=student.pnm.edu.ph
```

Run `php artisan config:clear`, then restart both backend and frontend development servers. The frontend asks Google to show `student.pnm.edu.ph` accounts, while the backend provides the actual security boundary by verifying the signed ID token, its audience, its verified-email status, and its `hd=student.pnm.edu.ph` Workspace claim. Gmail and other Google accounts receive HTTP 403 and cannot create or access an account through Google sign-in.

The standard Create Account endpoint uses the same domain configuration. New email/password registrations must also use an exact `@student.pnm.edu.ph` address; Gmail, other organizations, and subdomains are rejected during backend validation. Registration and password login use a six-digit email OTP. Google sign-in does not require another OTP because the backend already verifies Google's signed Workspace identity token.

## Gmail SMTP for OTP

Email OTP delivery requires a real Gmail or Google Workspace sender. Add these values to `backend/.env` and never commit the real password:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-sender@gmail.com
MAIL_PASSWORD=your-16-character-google-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="Colegio de Montalban"

OTP_EXPIRES_MINUTES=10
OTP_RESEND_SECONDS=60
OTP_MAX_ATTEMPTS=5
OTP_MAX_RESENDS=5
OTP_MAX_SENDS_PER_HOUR=5
```

For Gmail, enable 2-Step Verification on the sender account and create a Google **App password**. Use the 16-character app password for `MAIL_PASSWORD`, not the normal Google password. A managed Google Workspace account may require administrator approval for app passwords or SMTP relay.

After editing `backend/.env`, run `php artisan config:clear` and `php artisan migrate`. An OTP proves that the user can access the institutional mailbox. Confirming whether the student is currently enrolled still requires an official PnM roster or API.

Your backend must implement these authentication endpoints:

### 1. Login Endpoint
```
POST /api/auth/login
Content-Type: application/json

Request:
{
  "email": "user@student.pnm.edu.ph",
  "password": "password123"
}

Response (202 Accepted):
{
  "success": true,
  "otp_required": true,
  "purpose": "login",
  "challenge_id": "uuid",
  "masked_email": "u***@student.pnm.edu.ph",
  "expires_in": 600,
  "resend_after": 60
}

Response (401 Unauthorized):
{
  "message": "Invalid credentials"
}
```

### 2. Register Endpoint
```
POST /api/auth/register
Content-Type: application/json

Request:
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@student.pnm.edu.ph",
  "password": "password123",
  "password_confirmation": "password123"
}

Response (202 Accepted):
{
  "success": true,
  "otp_required": true,
  "purpose": "registration",
  "challenge_id": "uuid",
  "masked_email": "j***@student.pnm.edu.ph",
  "expires_in": 600,
  "resend_after": 60
}
```

No student record or authentication token is created until the registration OTP is verified.

### 3. Verify OTP Endpoint
```
POST /api/auth/otp/verify
Content-Type: application/json

{
  "challenge_id": "uuid",
  "otp": "123456"
}
```

A successful response contains the student, Sanctum token, student number, and admission year. Each challenge is single-use, expires after 10 minutes, and locks after five incorrect attempts.

### 4. Resend OTP Endpoint
```
POST /api/auth/otp/resend
Content-Type: application/json

{
  "challenge_id": "uuid"
}
```

Resending is available after the configured cooldown and invalidates the previous code.

### 5. Logout Endpoint
```
POST /api/auth/logout
Authorization: Bearer {token}

Response (200 OK):
{
  "message": "Logged out successfully"
}
```

### 6. Profile Endpoint
```
GET /api/auth/profile
Authorization: Bearer {token}

Response (200 OK):
{
  "student": {
    "id": 1,
    "email": "user@student.pnm.edu.ph",
    "name": "John Doe"
  }
}
```

### 7. Google Login Endpoint (Optional)
```
POST /api/auth/google-login
Content-Type: application/json

Request:
{
  "google_token": "eyJhbGciOiJSUzI1NiIs..."
}

Response (200 OK):
{
  "student": {...},
  "token": "...",
  "student_number": "...",
  "admission_year": ...
}
```

## CORS Configuration

Your backend **must allow** requests from the frontend URL:

```
Allowed Origins:
- http://localhost:5173 (development)
- https://yourdomain.com (production)

Allowed Methods:
- GET
- POST
- PUT
- DELETE
- OPTIONS

Allowed Headers:
- Content-Type
- Authorization
```

## Token Format

The authentication token is a Laravel Sanctum bearer token that:
1. Can be stored in localStorage
2. Is included in all authenticated requests as: `Authorization: Bearer {token}`
3. Is verified by the backend before protected API access is allowed

## Debugging Tips

### Check Network Requests
1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Try to login
4. Check if API requests are sent to the correct URL
5. Look for error responses

### Check Browser Console
- Open browser Console (F12)
- Look for errors related to API calls
- Check CORS errors if present

### Test API Manually
```bash
# Test login endpoint
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@student.pnm.edu.ph","password":"password123"}'

# Test with token
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer {your_token}"
```

## Common Issues

### Issue: "Network Error"
- Backend server might not be running
- Check if `http://localhost:8000` is accessible
- Verify the `VITE_API_URL` in `.env.local`

### Issue: "CORS Error"
- Backend must allow requests from `http://localhost:5173`
- Enable CORS on your backend server
- Add proper CORS headers to responses

### Issue: "401 Unauthorized"
- Token might be invalid or expired
- Check if token is stored correctly in localStorage
- Verify token format is correct

### Issue: "Login fails silently"
- Check browser console for errors
- Check Network tab to see what response was received
- Verify API endpoint returns correct JSON structure
