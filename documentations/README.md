# DoctorOnTap - Healthcare Consultation Platform

A comprehensive Laravel-based healthcare platform where users can consult doctors and make payments via Korapay.

## Features

- **User Consultation Forms** - Multi-step consultation with medical document uploads
- **Payment Integration** - Korapay payment gateway for secure transactions
- **Admin Dashboard** - Manage consultations, doctors, payments, and admin users
- **Email Notifications** - Automated emails for all stages (confirmation, doctor notification, payment requests)
- **Document Management** - Upload and forward medical documents to doctors
- **Doctor Management** - Maintain doctor profiles with specializations
- **Modern UI** - Tailwind CSS with responsive design

## Tech Stack

- Laravel 12
- Tailwind CSS v4
- Alpine.js
- Korapay Payment Gateway
- MySQL Database
- Vite (Asset bundler)

## Quick Start

### Local Development
```bash
# Run the quick start script
bash quick-start.sh
```

### Production Deployment
```bash
# Deploy to production server
bash deploy-production.sh
```

For detailed deployment instructions, see [PRODUCTION.md](PRODUCTION.md)

## Installation

1. **Clone and Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=campaign
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   php artisan db:seed --class=DoctorSeeder
   ```

5. **Build Assets**
   ```bash
   npm run build
   # Or for development:
   npm run dev
   ```

6. **Start Server**
   ```bash
   php artisan serve
   ```

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   ├── AuthController.php          # Admin login/logout
│   │   └── DashboardController.php     # Admin dashboard & management
│   ├── ConsultationController.php      # User consultation forms
│   └── PaymentController.php           # Korapay payment handling
├── Mail/                                # Email notifications
├── Models/                              # Database models
│   ├── AdminUser.php
│   ├── Consultation.php
│   ├── Doctor.php
│   └── Payment.php
└── Middleware/
    └── AdminAuthenticate.php            # Admin auth guard

resources/views/
├── admin/                               # Admin dashboard views
├── consultation/                        # User consultation forms
├── emails/                              # Email templates
└── payment/                             # Payment success/fail pages
```

## Key Routes

### Public Routes
- `/` - Landing page
- `/submit` - Submit consultation form
- `/payment/{consultation}` - Payment page
- `/payment/callback` - Korapay callback

### Admin Routes (requires authentication)
- `/admin/login` - Admin login
- `/admin/dashboard` - Main dashboard
- `/admin/consultations` - Manage consultations
- `/admin/doctors` - Manage doctors
- `/admin/payments` - View payments
- `/admin/users` - Manage admin users

## Configuration

### Korapay Setup
Add your Korapay credentials to `.env`:
```env
KORAPAY_PUBLIC_KEY=pk_live_xxxxx
KORAPAY_SECRET_KEY=sk_live_xxxxx
KORAPAY_ENCRYPTION_KEY=xxxxx
KORAPAY_API_URL=https://api.korapay.com/merchant/api/v1
```

### Email Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.zeptomail.com
MAIL_PORT=587
MAIL_USERNAME=emailapikey
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@doctorontap.com.ng
```

### Admin Access
Default admin credentials are seeded via `DatabaseSeeder.php`. Change them after first login.

## Development

### Run Development Server
```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite (hot reload)
npm run dev
```

### Watch for Changes
```bash
npm run dev
```

### Run Tests
```bash
php artisan test
```

## Production Deployment

See [PRODUCTION.md](PRODUCTION.md) for complete deployment guide.

**Quick deploy:**
```bash
sudo bash deploy-production.sh
```

**Fix asset loading issues:**
```bash
sudo bash fix-production.sh
```

## Database Schema

### consultations
- User information (name, email, age, gender, address)
- Consultation details (symptoms, preferred_doctor_id)
- Payment status
- Medical documents (JSON)
- Forwarding status

### doctors
- Doctor profiles
- Specializations
- Contact information

### payments
- Korapay transaction details
- Payment status tracking

### admin_users
- Admin authentication
- Role management
- Activity tracking

## Email Flow

1. **User submits consultation** → Confirmation email sent to user
2. **Admin reviews** → Can forward documents to doctor
3. **Documents forwarded** → Doctor notification email
4. **Admin requests payment** → Payment request email to user
5. **Payment completed** → Confirmation to user and admin

## Security

- Admin routes protected by authentication middleware
- CSRF protection on all forms
- Environment-based configuration
- Secure session handling
- Input validation and sanitization

## Troubleshooting

### Assets not loading (127.0.0.1:5173 error)
```bash
sudo bash fix-production.sh
```

### Database connection issues
```bash
php artisan config:clear
php artisan migrate:status
```

### Email not sending
Check logs:
```bash
tail -f storage/logs/laravel.log
```

### Cache issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Scripts

- `quick-start.sh` - Local development setup
- `deploy-production.sh` - Production deployment
- `fix-production.sh` - Fix common production issues

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers

## License

Proprietary - DoctorOnTap Healthcare Platform

## Support

For issues or questions: inquiries@doctorontap.com.ng

---

Built with Laravel + Tailwind CSS for healthcare consultation management.
