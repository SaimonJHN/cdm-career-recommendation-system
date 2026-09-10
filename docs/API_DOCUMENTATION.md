> Current setup, API workflow changes, deployment, and recovery instructions are maintained in [Operations](OPERATIONS.md). The material below describes earlier versions.

# CDM Career Recommendation System - API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
Include JWT token in Authorization header:
```
Authorization: Bearer {token}
```

---

## Endpoints

### Authentication Endpoints

#### 1. Register Student
**POST** `/auth/register`

Request:
```json
{
  "first_name": "Juan",
  "last_name": "Dela Cruz",
  "email": "juan@example.com",
  "phone": "+63 9XX XXX XXXX",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Student registered successfully",
  "student": {...},
  "token": "1|abc...",
  "student_number": "CDM-2025-00001",
  "admission_year": "2025-2026"
}
```

#### 2. Login Student
**POST** `/auth/login`

Request:
```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "student": {...},
  "token": "1|abc..."
}
```

#### 3. Google OAuth Login
**POST** `/auth/google-login`

Request:
```json
{
  "google_token": "google_oauth_token_here"
}
```

Response:
```json
{
  "success": true,
  "message": "Google login successful",
  "student": {...},
  "token": "1|abc...",
  "student_number": "CDM-2025-00001",
  "admission_year": "2025-2026"
}
```

#### 4. Logout (Protected)
**POST** `/auth/logout`

Response:
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

### Student Endpoints (Protected)

#### 1. Get Dashboard
**GET** `/student/dashboard`

Response:
```json
{
  "success": true,
  "student": {
    "id": 1,
    "first_name": "Juan",
    "last_name": "Dela Cruz",
    "email": "juan@example.com",
    "full_name": "Juan Dela Cruz"
  },
  "student_number": "CDM-2025-00001",
  "admission_year": "2025-2026",
  "exam_taken": true,
  "exam_score": 85,
  "recommendation": {...}
}
```

#### 2. Get Student Number
**GET** `/student/number`

Response:
```json
{
  "success": true,
  "student_number": "CDM-2025-00001",
  "admission_year": "2025-2026",
  "format": "CDM-YYYY-XXXXX",
  "description": "Your unique student identification number"
}
```

#### 3. Update Profile (Protected)
**PUT** `/student/profile`

Request (multipart/form-data):
```
first_name: Juan
last_name: Dela Cruz
phone: +63 9XX XXX XXXX
date_of_birth: 2005-01-15
profile_picture: [image file]
```

---

### Exam Endpoints

#### 1. Get Exam Questions
**GET** `/exam/questions`

Response:
```json
{
  "success": true,
  "total_questions": 100,
  "passing_score": 75,
  "total_score": 100,
  "time_limit": 120,
  "categories": {
    "General Mathematics": 25,
    "Science": 25,
    "Reading Comprehension": 20,
    "Logical Reasoning": 15,
    "Technical Aptitude": 15
  },
  "questions": [
    {
      "id": 1,
      "question_number": 1,
      "question_text": "What is 2 + 2?",
      "category": "General Mathematics",
      "option_a": "3",
      "option_b": "4",
      "option_c": "5",
      "option_d": "6",
      "difficulty_level": "easy"
    }
  ]
}
```

#### 2. Submit Exam (Protected)
**POST** `/exam/submit`

Request:
```json
{
  "answers": {
    "1": "B",
    "2": "A",
    "3": "D"
  },
  "time_spent": 3600
}
```

Response:
```json
{
  "success": true,
  "message": "Exam submitted successfully",
  "exam_result": {
    "id": 1,
    "student_id": 1,
    "total_score": 85,
    "percentage": 85.0,
    "time_spent": 3600,
    "is_passed": true,
    "category_scores": {
      "General Mathematics": 22,
      "Science": 20,
      "Reading Comprehension": 18,
      "Logical Reasoning": 14,
      "Technical Aptitude": 11
    }
  },
  "total_score": 85,
  "percentage": 85.0,
  "is_passed": true
}
```

#### 3. Get Exam Result (Protected)
**GET** `/exam/result`

Response:
```json
{
  "success": true,
  "exam_result": {
    "id": 1,
    "student_id": 1,
    "total_score": 85,
    "percentage": 85.0,
    "time_spent": 3600,
    "exam_date": "2025-01-15T10:30:00.000000Z",
    "is_passed": true,
    "category_scores": {...}
  }
}
```

---

### Results Endpoints (Protected)

#### 1. Get AI Recommendation
**GET** `/results/recommendation`

Response:
```json
{
  "success": true,
  "exam_score": 85,
  "percentage": 85.0,
  "category_scores": {...},
  "recommendation": {
    "primary_course_id": 1,
    "primary_course_name": "BSIT",
    "confidence_score": 92.5,
    "reasoning": "Based on your strong performance in Technical Aptitude...",
    "alternatives": [
      {
        "course_id": 2,
        "course_name": "BSE",
        "score": 78.5
      }
    ],
    "detailed_scores": {
      "bsit": 22.5,
      "bse": 19.8,
      "sba": 16.2
    }
  }
}
```

#### 2. Send Results Email (Protected)
**POST** `/results/send-email`

Response:
```json
{
  "success": true,
  "message": "Results email sent successfully",
  "email_sent_to": "juan@example.com"
}
```

#### 3. Download Certificate (Protected)
**GET** `/results/download-certificate`

Response:
```json
{
  "success": true,
  "certificate_data": {
    "student_name": "Juan Dela Cruz",
    "student_number": "CDM-2025-00001",
    "exam_date": "January 15, 2025",
    "score": "85/100",
    "percentage": "85.00%",
    "recommended_program": "BSIT",
    "status": "PASSED"
  }
}
```

---

### Courses Endpoints

#### 1. Get All Courses
**GET** `/courses`

Response:
```json
{
  "success": true,
  "total_courses": 3,
  "courses": [
    {
      "id": 1,
      "code": "BSIT",
      "name": "Bachelor of Science in Information Technology",
      "description": "A comprehensive program...",
      "duration": "4 years",
      "tuition_fee": "45000.00",
      "ideal_score_range": "80-100",
      "subjects": ["Programming", "Database", ...]
    }
  ]
}
```

#### 2. Get Course Details
**GET** `/courses/{id}`

Response:
```json
{
  "success": true,
  "course": {
    "id": 1,
    "code": "BSIT",
    "name": "Bachelor of Science in Information Technology",
    "description": "...",
    "duration": "4 years",
    "tuition_fee": "45000.00"
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### 422 Validation Error
```json
{
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 500 Server Error
```json
{
  "error": "Internal server error message"
}
```

---

## Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

**API Version:** 1.0  
**Last Updated:** January 2025
