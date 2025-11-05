# Table 4. Software Development Requirements for Event QR Ticketing Platform

## Updated Based on Actual Codebase Analysis

| Component | Tools / Technologies |
|-----------|---------------------|
| **Frontend (Web)** | Laravel Blade Templates, Tailwind CSS, Vite, Vanilla JavaScript (ES6+), Axios, Alpine.js |
| **Backend (API Services)** | Laravel 12 (PHP Framework), SQLite/MySQL, Laravel Built-in Authentication, RESTful API |
| **QR Code Generation** | Bacon QR Code (bacon/bacon-qr-code), Custom QR Code Encryption Service |
| **Payment Integration** | Xendit SDK (REST API), Xendit Webhook Handler |
| **Authentication** | Laravel Authentication (Session-based), Bcrypt Password Hashing |
| **Real-time Data & Scanning** | Pusher (WebSockets), Laravel Reverb, Laravel Echo, Custom QR Scanner (Browser-based) |
| **Analytics Dashboard** | Chart.js (via CDN), Custom Excel XML Generation |
| **Email & Notifications** | Laravel Mail (SMTP/Mailgun/SES/Postmark support), Laravel Notifications |
| **File Storage** | Laravel Storage (Local/S3-compatible), Cloudflare R2 Storage |
| **Testing & Validation** | PHPUnit, Laravel Testing Framework |
| **Version Control & CI/CD** | Git, GitHub/GitLab |
| **Design & Prototyping** | Figma (recommended), Draw.io (for flowcharts) |

---

## Detailed Technology Stack

### Frontend Technologies
- **Templating**: Laravel Blade (server-side rendering)
- **CSS Framework**: Tailwind CSS 3.x
- **Build Tool**: Vite 6.x (replaces Laravel Mix)
- **JavaScript**: Vanilla JavaScript (ES6+), Alpine.js for reactive components
- **HTTP Client**: Axios for AJAX requests
- **UI Components**: Custom Blade components with Tailwind CSS

### Backend Technologies
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: SQLite (development) / MySQL (production)
- **Authentication**: Laravel's built-in authentication system (session-based)
- **API**: RESTful API endpoints

### QR Code System
- **Library**: Bacon QR Code (bacon/bacon-qr-code v3.0)
- **Encryption**: Laravel's encryption service for secure QR code data
- **Format**: Encrypted JSON data stored in QR codes
- **Validation**: Custom validation service with event ID matching

### Payment Processing
- **Primary Gateway**: Xendit (Southeast Asia payment gateway)
- **Integration**: REST API integration with Xendit
- **Features**: Invoice generation, payment webhooks, disbursement (payouts)
- **Payment Methods**: Credit cards, bank transfers, e-wallets (via Xendit)

### Real-time Features
- **WebSocket Server**: Pusher (cloud-based) or Laravel Reverb (self-hosted)
- **Client Library**: Laravel Echo, Pusher JavaScript SDK
- **Events**: Real-time dashboard updates, ticket sales notifications, admin metrics
- **Broadcasting**: Laravel Broadcasting System

### Analytics & Reporting
- **Charts**: Chart.js 4.x (loaded via CDN)
- **Reports**: Custom Excel XML generation (no external package required)
- **Export Formats**: Excel (.xls) XML format

### Email System
- **Framework**: Laravel Mail
- **Supported Drivers**: SMTP, Mailgun, Amazon SES, Postmark, Resend, Sendmail
- **Templates**: Laravel Blade email templates
- **Notifications**: Laravel Notification system

### File Storage
- **Local Storage**: Laravel Storage (local filesystem)
- **Cloud Storage**: Cloudflare R2 (S3-compatible)
- **Usage**: Event images (posters, thumbnails), certificates, layout JSON files

### Testing
- **PHP Unit Testing**: PHPUnit 11.x
- **Framework Testing**: Laravel Testing Framework
- **Browser Testing**: Laravel Dusk (optional)

### Development Tools
- **Package Manager**: Composer (PHP), npm (JavaScript)
- **Task Runner**: Laravel Artisan commands
- **Code Quality**: Laravel Pint (code formatting)

---

## Notes

1. **No React/Vue.js**: The application uses Laravel Blade templates with vanilla JavaScript, not a separate frontend framework.

2. **No Laravel Mix**: Migrated to Vite for asset bundling.

3. **No JWT/OAuth**: Uses Laravel's session-based authentication, not API tokens.

4. **No Laravel Excel Package**: Uses custom XML Excel generation for better control and smaller file sizes.

5. **No PayMongo/PayPal**: Only Xendit is integrated for payment processing.

6. **Database Flexibility**: Supports both SQLite (development) and MySQL (production).

7. **Real-time Options**: Can use either Pusher (cloud) or Laravel Reverb (self-hosted) for WebSocket functionality.

