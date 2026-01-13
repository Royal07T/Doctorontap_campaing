# API Documentation

## Overview

This API provides access to the DoctorOnTap consultation platform. The API uses Laravel Sanctum for authentication and follows RESTful conventions.

**Base URL**: `https://your-domain.com/api/v1`

## Authentication

All protected endpoints require authentication using Bearer tokens. Include the token in the Authorization header:

```
Authorization: Bearer {your-token}
```

### Getting a Token

1. Register or login using the authentication endpoints
2. The response will include a `token` in the `data` object
3. Use this token for all subsequent API requests

## Response Format

All API responses follow this structure:

```json
{
    "success": true,
    "message": "Optional message",
    "data": {
        // Response data
    }
}
```

Error responses:

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors if applicable
    }
}
```

## Endpoints

### Health Check

**GET** `/health`

Check API status.

**Response:**
```json
{
    "status": "ok",
    "timestamp": "2024-01-01T00:00:00Z",
    "version": "1.0.0"
}
```

### Authentication

#### Patient Registration

**POST** `/auth/patient/register`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123",
    "gender": "male",
    "date_of_birth": "1990-01-01"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful. Please verify your email.",
    "data": {
        "patient": {...},
        "token": "1|abc123..."
    }
}
```

#### Patient Login

**POST** `/auth/patient/login`

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "patient": {...},
        "token": "1|abc123..."
    }
}
```

#### Doctor Login

**POST** `/auth/doctor/login`

**Request Body:**
```json
{
    "email": "doctor@example.com",
    "password": "password123"
}
```

#### Admin Login

**POST** `/auth/admin/login`

**Request Body:**
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

#### Logout

**POST** `/auth/logout`

Requires authentication.

### Consultations

#### List Consultations

**GET** `/consultations`

Requires authentication. Returns consultations based on user type:
- Patients see their own consultations
- Doctors see their assigned consultations
- Admins see all consultations

**Query Parameters:**
- `status` - Filter by status (pending, assigned, in_progress, completed, cancelled)
- `consultation_mode` - Filter by mode (voice, video, chat)
- `per_page` - Number of results per page (default: 15)

**Response:**
```json
{
    "success": true,
    "data": {
        "data": [...],
        "current_page": 1,
        "per_page": 15,
        "total": 100
    }
}
```

#### Get Consultation

**GET** `/consultations/{id}`

Requires authentication.

#### Create Consultation

**POST** `/consultations`

**Request Body:**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "mobile": "+1234567890",
    "age": 30,
    "gender": "male",
    "problem": "Fever and cough",
    "consultation_mode": "video",
    "doctor_id": 1
}
```

#### Update Consultation

**PUT** `/consultations/{id}`

Requires authentication. Only doctors, admins, and nurses can update.

**Request Body:**
```json
{
    "status": "completed",
    "doctor_notes": "Patient responded well to treatment"
}
```

#### Get Session Token

**GET** `/consultations/{id}/session/token`

Get Vonage session token for joining consultation.

#### End Session

**POST** `/consultations/{id}/session/end`

End an active consultation session.

#### Get Consultation Status

**GET** `/consultations/{id}/status`

Get current status of a consultation.

### Patients

#### List Patients

**GET** `/patients`

Requires authentication. Only doctors, admins, and nurses can access.

**Query Parameters:**
- `search` - Search by name, email, or phone
- `per_page` - Number of results per page

#### Get Patient

**GET** `/patients/{id}`

Requires authentication.

#### Update Patient

**PUT** `/patients/{id}`

Requires authentication. Patients can only update their own profile.

#### Get Patient Consultations

**GET** `/patients/{id}/consultations`

Get all consultations for a specific patient.

#### Get Patient Medical History

**GET** `/patients/{id}/medical-history`

Get medical history for a patient.

### Doctors

#### List Doctors (Public)

**GET** `/doctors`

Public endpoint. Returns approved and available doctors.

**Query Parameters:**
- `specialization` - Filter by specialization
- `search` - Search by name or specialization
- `per_page` - Number of results per page

#### Get Doctor

**GET** `/doctors/{id}`

Public endpoint.

#### Get Doctor Consultations

**GET** `/doctors/{id}/consultations`

Requires authentication. Only the doctor or admin can access.

#### Get Doctor Reviews

**GET** `/doctors/{id}/reviews`

Public endpoint. Get all approved reviews for a doctor.

#### Update Doctor Availability

**PUT** `/doctors/{id}/availability`

Requires authentication. Only the doctor can update their own availability.

**Request Body:**
```json
{
    "is_available": true
}
```

### Dashboard

#### Get Dashboard Data

**GET** `/dashboard`

Requires authentication. Returns dashboard data based on user type.

**Response (Patient):**
```json
{
    "success": true,
    "data": {
        "patient": {...},
        "stats": {
            "total_consultations": 10,
            "pending_consultations": 2,
            "completed_consultations": 8
        },
        "recent_consultations": [...]
    }
}
```

### Notifications

#### Get Notifications

**GET** `/notifications`

Requires authentication.

**Query Parameters:**
- `read` - Filter by read status (true/false)
- `limit` - Number of results (default: 20)

#### Get Unread Count

**GET** `/notifications/unread-count`

Requires authentication.

#### Mark Notification as Read

**POST** `/notifications/{id}/read`

Requires authentication.

#### Mark All as Read

**POST** `/notifications/mark-all-read`

Requires authentication.

### Payments

#### List Payments

**GET** `/payments`

Requires authentication.

#### Get Payment

**GET** `/payments/{id}`

Requires authentication.

#### Initialize Payment

**POST** `/payments/initialize`

**Request Body:**
```json
{
    "consultation_id": 1,
    "amount": 5000
}
```

#### Verify Payment

**POST** `/payments/verify`

**Request Body:**
```json
{
    "payment_reference": "PAY-1234567890"
}
```

### Reviews

#### Get Doctor Reviews (Public)

**GET** `/reviews/doctor/{doctorId}`

Public endpoint.

#### Create Review

**POST** `/reviews`

Requires authentication. Only patients can create reviews.

**Request Body:**
```json
{
    "doctor_id": 1,
    "consultation_id": 1,
    "rating": 5,
    "comment": "Excellent doctor!"
}
```

#### Get My Reviews

**GET** `/reviews/my-reviews`

Requires authentication. Returns reviews created by the authenticated patient.

## Error Codes

- `401` - Unauthorized (missing or invalid token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Rate Limiting

API requests are rate-limited. Default limits:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users

Rate limit headers are included in responses:
- `X-RateLimit-Limit` - Request limit
- `X-RateLimit-Remaining` - Remaining requests

## Pagination

List endpoints support pagination. Response includes:
- `current_page` - Current page number
- `per_page` - Items per page
- `total` - Total items
- `last_page` - Last page number
- `data` - Array of items

## Examples

### Example: Creating a Consultation

```bash
curl -X POST https://your-domain.com/api/v1/consultations \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "mobile": "+1234567890",
    "problem": "Fever and cough",
    "consultation_mode": "video"
  }'
```

### Example: Getting Patient Consultations

```bash
curl -X GET https://your-domain.com/api/v1/my-consultations \
  -H "Authorization: Bearer 1|abc123..."
```

## Support

For API support, please contact: support@doctorontap.com

