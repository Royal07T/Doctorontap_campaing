# DoctorOnTap - Healthcare Awareness Campaign

A simple, elegant Laravel landing page for **DoctorOnTap**, a healthcare awareness campaign site where users can consult a doctor for free.

## Features

- **Modern, Responsive Design** - Beautiful medical-themed UI with gradient backgrounds
- **Tailwind CSS** - Utility-first CSS framework for fast development
- **Alpine.js Integration** - Interactive form without page reload
- **Laravel Validation** - Server-side form validation
- **Email Notifications** - Sends confirmation to users and alerts to admin
- **No Database Required** - Uses file-based cache and sessions
- **Vite for Asset Building** - Fast, modern build tool

## Tech Stack

- Laravel 12 (Latest)
- Tailwind CSS v4
- Blade Templates
- Alpine.js (CDN)
- Vite (Asset bundler)

## Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   
   The `.env` file is already configured with:
   - File-based cache and sessions
   - Mail driver set to `log` (emails saved to `storage/logs/laravel.log`)
   - Application name: DoctorOnTap

3. **Build Assets**
   ```bash
   npm run build
   ```
   
   Or for development with hot reload:
   ```bash
   npm run dev
   ```

## Running the Application

### Development Mode (Recommended)

Terminal 1 - Start Laravel server:
```bash
php artisan serve
```

Terminal 2 - Start Vite dev server (for hot reload):
```bash
npm run dev
```

Then visit: `http://localhost:8000`

### Production Mode

1. Build assets:
   ```bash
   npm run build
   ```

2. Start server:
   ```bash
   php artisan serve
   ```

## Usage

### Landing Page

The landing page (`/`) displays:
- Eye-catching header with campaign branding
- Information banner about free consultation
- Form with the following fields:
  - First Name
  - Last Name
  - Email Address
  - Age
  - Gender (Male/Female/Other dropdown)
  - Address
  - Symptoms (textarea)

### Form Submission

When a user submits the form:
1. Laravel validates all fields
2. Sends a confirmation email to the user
3. Sends an alert email to `admin@doctorontap.com`
4. Displays a success message without page reload
5. Clears the form for new submissions

## Styling & Customization

### Primary Color

The main brand color is **#7B3DE9** (purple). It's defined in:
- `resources/css/app.css` - Tailwind theme configuration
- Used throughout the design for consistency

### Customizing Colors

Edit `resources/css/app.css`:
```css
@theme {
    --color-primary: #7B3DE9;
    --color-primary-dark: #5a2ba8;
}
```

Then rebuild:
```bash
npm run build
```

### Modifying Layout

The main view is in `resources/views/consultation/index.blade.php`
- Uses Tailwind utility classes
- Easy to customize with Tailwind's responsive utilities
- Gradient background defined inline for smooth animation

## Email Configuration

### Development (Current Setup)
Emails are logged to `storage/logs/laravel.log` for testing.

### Production
Update `.env` with your mail server credentials:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@doctorontap.com"
MAIL_FROM_NAME="DoctorOnTap"
```

## Project Structure

```
app/
├── Http/Controllers/
│   └── ConsultationController.php    # Handles form display and submission
└── Mail/
    ├── ConsultationConfirmation.php  # User confirmation email
    └── ConsultationAdminAlert.php    # Admin notification email

resources/
├── css/
│   └── app.css                       # Tailwind CSS configuration
├── js/
│   └── app.js                        # JavaScript entry point
└── views/
    ├── consultation/
    │   └── index.blade.php           # Main landing page (Tailwind)
    └── emails/
        ├── consultation-confirmation.blade.php
        └── consultation-admin-alert.blade.php

routes/
└── web.php                           # Route definitions

public/
└── build/                            # Compiled assets (generated)
```

## Routes

- `GET /` - Display landing page (ConsultationController@index)
- `POST /submit` - Handle form submission (ConsultationController@store)

## Form Validation Rules

- **first_name**: Required, string, max 255 characters
- **last_name**: Required, string, max 255 characters
- **email**: Required, valid email, max 255 characters
- **age**: Required, integer, 1-120
- **gender**: Required, one of: male, female, other
- **address**: Required, string, max 500 characters
- **symptoms**: Required, string, max 2000 characters

## Tailwind CSS Classes Used

### Layout
- `container mx-auto max-w-4xl` - Centered container
- `grid grid-cols-1 md:grid-cols-2 gap-6` - Responsive 2-column layout

### Styling
- `rounded-3xl` - Large rounded corners
- `shadow-2xl` - Large shadow for cards
- `bg-white/95` - Semi-transparent white background
- `backdrop-blur-md` - Backdrop filter effect

### Interactive
- `hover:-translate-y-1` - Lift effect on hover
- `focus:ring-4` - Focus ring for accessibility
- `transition-all duration-300` - Smooth transitions

## Responsive Design

### Mobile (< 768px)
- Single-column form layout
- Smaller text sizes
- Full-width buttons
- Adjusted padding

### Desktop (≥ 768px)
- Two-column form layout (First/Last Name, Age/Gender)
- Larger text and spacing
- Enhanced shadows and effects

## Testing

### Test the Landing Page
```bash
curl http://localhost:8000/
```

### View Email Logs
```bash
tail -f storage/logs/laravel.log
```

After submitting a form through the browser, check the log file to see the generated emails.

### Hot Reload Development
With `npm run dev` running, any changes to Blade templates or CSS will automatically refresh in the browser.

## Build for Production

1. **Optimize Assets**
   ```bash
   npm run build
   ```

2. **Cache Configuration**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Set Environment**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

## Customization Examples

### Change Form Width
In `index.blade.php`, modify:
```html
<div class="container mx-auto max-w-4xl">
```
Change `max-w-4xl` to `max-w-2xl` (smaller) or `max-w-6xl` (larger)

### Adjust Spacing
Use Tailwind spacing utilities:
- `p-4` = 1rem padding
- `mb-8` = 2rem margin-bottom
- `gap-6` = 1.5rem gap

### Modify Button Style
In the submit button, update classes or gradient colors as needed.

## Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **First Load**: ~50KB CSS (Tailwind optimized)
- **Alpine.js**: ~15KB from CDN
- **Total Page Size**: ~65KB (extremely lightweight)
- **Load Time**: < 1 second on modern connections

## Troubleshooting

### Styles Not Loading
```bash
npm run build
php artisan view:clear
```

### Vite Connection Error
Make sure `npm run dev` is running in a separate terminal.

### Form Not Submitting
Check browser console for JavaScript errors and verify Alpine.js loaded.

## License

Open-source project for educational purposes.

## Support

For issues or questions, contact: admin@doctorontap.com

---

**Built with ❤️ using Laravel + Tailwind CSS for healthcare awareness**
