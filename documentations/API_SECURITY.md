# API Security Documentation

## 🔒 Security Overview

This document outlines all security measures implemented in the DoctorOnTap API to protect against common attacks and ensure data privacy.

## ✅ Security Measures Implemented

### 1. Authentication & Authorization

#### ✅ Token-Based Authentication (Laravel Sanctum)
- **All protected endpoints** require a valid Bearer token
- Tokens are stored securely in the database
- Tokens expire after **7 days** (configurable via `SANCTUM_TOKEN_EXPIRATION`)
- Token abilities/scopes are assigned based on user type

#### ✅ Authorization Checks
- **Role-based access control** in all controllers
- Users can only access their own data
- Doctors can only access their assigned consultations
- Admins have elevated permissions
- Authorization checks are performed at multiple levels:
  - Route middleware
  - Controller methods
  - Service layer

#### ✅ User Type Validation
```php
// Example from ConsultationController
if ($userType === 'Patient' && $consultation->patient_id !== $user->id) {
    return response()->json(['error' => 'Unauthorized'], 403);
}
```

### 2. Rate Limiting

#### ✅ Public Endpoints
- **Authentication endpoints**: 5 requests per minute
  - Login: 5/min
  - Register: 3/min
  - Password reset: 3/min
- **Public consultation creation**: 10 requests per minute
- **Public doctor listing**: 30 requests per minute
- **Public reviews**: 30 requests per minute

#### ✅ Protected Endpoints
- **All authenticated endpoints**: 60 requests per minute per user
- Rate limit headers included in responses:
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests
  - `Retry-After`: Seconds until retry allowed (when exceeded)

#### ✅ Rate Limiting Implementation
```php
// Uses Laravel's built-in throttle middleware
Route::middleware(['throttle:60,1'])->group(function () {
    // Protected routes
});
```

### 3. Input Validation & Sanitization

#### ✅ Request Validation
- **Form Request classes** for complex validation
- **Laravel validation rules** for all inputs
- **Custom validation messages** for better UX
- **Input sanitization** before processing:
  - HTML tags stripped
  - SQL injection prevention
  - XSS prevention
  - Phone number format validation
  - Email format validation

#### ✅ Example: StoreConsultationRequest
```php
'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
'mobile' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
'problem' => ['required', 'string', 'min:10', 'max:2000'],
```

#### ✅ Input Sanitization
- All text inputs are trimmed
- HTML tags are stripped using `strip_tags()`
- Phone numbers are sanitized to remove invalid characters
- Email addresses are lowercased and trimmed

### 4. SQL Injection Prevention

#### ✅ Eloquent ORM
- **All database queries** use Eloquent ORM
- **Parameter binding** prevents SQL injection
- **Query builder** with proper escaping
- **No raw SQL queries** without parameter binding

#### ✅ Example Protection
```php
// ✅ SAFE - Uses parameter binding
Consultation::where('patient_id', $user->id)->get();

// ✅ SAFE - Validates input before query
if (in_array($request->status, $validStatuses)) {
    $query->where('status', $request->status);
}
```

### 5. XSS (Cross-Site Scripting) Prevention

#### ✅ Output Escaping
- **All user input** is sanitized before storage
- **JSON responses** automatically escape content
- **HTML tags stripped** from text fields
- **Content Security Policy** headers (via middleware)

#### ✅ Sanitization Examples
```php
// Strip HTML tags
$data['doctor_notes'] = strip_tags($data['doctor_notes']);

// Trim and sanitize
$data['name'] = trim(strip_tags($data['name']));
```

### 6. CSRF Protection

#### ✅ API Routes
- **API routes are exempt** from CSRF (standard practice)
- **Token-based authentication** replaces CSRF for APIs
- **Web routes** still use CSRF protection

### 7. Security Headers

#### ✅ HTTP Security Headers
All API responses include:
- `X-Content-Type-Options: nosniff` - Prevents MIME type sniffing
- `X-Frame-Options: DENY` - Prevents clickjacking
- `X-XSS-Protection: 1; mode=block` - XSS protection
- `Referrer-Policy: strict-origin-when-cross-origin` - Referrer control
- `X-API-Version: v1` - API version header
- `X-Powered-By` header removed - Hide server information

### 8. Token Security

#### ✅ Token Features
- **Expiration**: Tokens expire after 7 days (configurable)
- **Abilities/Scopes**: Tokens have specific permissions
- **Revocable**: Tokens can be revoked by user or admin
- **Encrypted storage**: Tokens stored securely in database
- **Bearer token format**: Standard HTTP Bearer authentication

#### ✅ Token Abilities by User Type
- **Patient**: `patient:read`, `patient:write`, `consultation:read`, `consultation:write`
- **Doctor**: `doctor:read`, `doctor:write`, `consultation:read`, `consultation:write`, `treatment:write`
- **Admin**: `admin:read`, `admin:write`, `consultation:read`, `consultation:write`, `user:manage`
- **Nurse**: `nurse:read`, `nurse:write`, `patient:read`, `vital-signs:write`

### 9. Password Security

#### ✅ Password Requirements
- **Minimum 8 characters** required
- **Hashed with bcrypt** (Laravel default)
- **Password confirmation** required on registration
- **Password reset** with secure tokens
- **No password in responses** (hidden in model)

### 10. HTTPS Enforcement

#### ✅ Production Security
- **HTTPS enforced** in production (via `EnforceHttps` middleware)
- **HIPAA compliance** requirement
- **Secure cookie transmission** only

### 11. Request Monitoring

#### ✅ Security Monitoring Middleware
- **All requests logged** for security analysis
- **Suspicious activity detection**
- **IP-based tracking**
- **Failed authentication attempts logged**

### 12. Data Privacy

#### ✅ HIPAA Compliance
- **Medical data access** restricted to authorized users
- **Patient data** only accessible by:
  - The patient themselves
  - Assigned doctor
  - Authorized admins/nurses
- **Audit logging** for sensitive operations
- **Data encryption** in transit (HTTPS)

## 🛡️ Protected Endpoints

### Public Endpoints (Rate Limited)
- `GET /api/v1/health` - Health check
- `POST /api/v1/auth/*` - Authentication (5 req/min)
- `POST /api/v1/consultations` - Create consultation (10 req/min)
- `GET /api/v1/doctors` - List doctors (30 req/min)
- `GET /api/v1/reviews/doctor/{id}` - Doctor reviews (30 req/min)

### Protected Endpoints (Authentication Required)
**All other endpoints** require:
- Valid Bearer token
- Rate limiting (60 req/min)
- Role-based authorization
- Input validation

## 🔐 Authorization Matrix

| Endpoint | Patient | Doctor | Admin | Nurse | Canvasser |
|----------|---------|--------|-------|-------|-----------|
| View own consultations | ✅ | ❌ | ✅ | ❌ | ❌ |
| View assigned consultations | ❌ | ✅ | ✅ | ✅ | ❌ |
| Create consultation | ✅ | ❌ | ✅ | ❌ | ✅ |
| Update consultation | ❌ | ✅ | ✅ | ✅ | ❌ |
| View patient data | Own only | Assigned only | ✅ | ✅ | Own only |
| Create treatment plan | ❌ | ✅ | ✅ | ❌ | ❌ |
| View vital signs | Own only | ❌ | ✅ | ✅ | ❌ |
| Create vital signs | ❌ | ❌ | ✅ | ✅ | ❌ |

## 🚨 Security Best Practices

### For API Consumers

1. **Store tokens securely**
   - Never commit tokens to version control
   - Use environment variables
   - Rotate tokens regularly

2. **Use HTTPS only**
   - Never send tokens over HTTP
   - Verify SSL certificates

3. **Handle errors gracefully**
   - Don't expose tokens in error messages
   - Implement retry logic with exponential backoff

4. **Respect rate limits**
   - Implement client-side rate limiting
   - Handle 429 responses appropriately

### For Developers

1. **Always validate input**
   - Use Form Request classes
   - Sanitize user input
   - Validate data types

2. **Check authorization**
   - Verify user permissions
   - Check resource ownership
   - Log unauthorized access attempts

3. **Use parameterized queries**
   - Never use raw SQL with user input
   - Use Eloquent ORM
   - Validate query parameters

4. **Log security events**
   - Failed authentication attempts
   - Unauthorized access attempts
   - Rate limit violations

## 🔍 Security Audit Checklist

- [x] All endpoints require authentication (except public ones)
- [x] Rate limiting on all endpoints
- [x] Input validation on all inputs
- [x] SQL injection prevention (Eloquent ORM)
- [x] XSS prevention (input sanitization)
- [x] CSRF protection (not needed for API, but handled)
- [x] Token expiration configured
- [x] HTTPS enforcement in production
- [x] Security headers added
- [x] Authorization checks in controllers
- [x] Password hashing
- [x] Audit logging
- [x] Error handling (no sensitive data in errors)

## 📊 Security Monitoring

### Logged Events
- Failed login attempts
- Rate limit violations
- Unauthorized access attempts
- Token generation/revocation
- Sensitive data access

### Monitoring Endpoints
- Check logs: `storage/logs/laravel.log`
- Security events: `storage/logs/security.log` (if configured)

## 🆘 Security Incident Response

If you suspect a security breach:

1. **Revoke all tokens** for affected users
2. **Check access logs** for suspicious activity
3. **Review rate limit violations**
4. **Check for unauthorized data access**
5. **Update passwords** if credentials compromised
6. **Notify affected users** if required by law

## 📝 Configuration

### Environment Variables
```env
# Sanctum Token Expiration (in minutes)
SANCTUM_TOKEN_EXPIRATION=10080  # 7 days

# Rate Limiting
API_RATE_LIMIT=60  # Requests per minute

# Security
APP_ENV=production
APP_DEBUG=false
```

## 🔄 Regular Security Updates

1. **Keep Laravel updated** - Security patches
2. **Update dependencies** - `composer update`
3. **Review access logs** - Weekly
4. **Audit user permissions** - Monthly
5. **Review rate limit settings** - As needed
6. **Test security measures** - Quarterly

## ✅ Conclusion

Your API is protected with multiple layers of security:
- ✅ Authentication (Sanctum tokens)
- ✅ Authorization (Role-based)
- ✅ Rate limiting
- ✅ Input validation
- ✅ SQL injection prevention
- ✅ XSS prevention
- ✅ Security headers
- ✅ HTTPS enforcement
- ✅ Audit logging

**Your API is secure and ready for production use!** 🎉

