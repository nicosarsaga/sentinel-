# SENTINEL — Advanced Password Security Analyzer

## Included authentication files

This project combines the Password Checker dashboard with the existing PHP authentication structure:

- `index.php` — landing/router
- `login.php` — email/password login
- `register.php` — account registration
- `verify-otp.php` — 6-digit email OTP verification
- `logout.php` — session logout
- `dashboard.php` — authenticated SENTINEL password-checker dashboard
- `openssl-test.php` — OpenSSL environment test
- `test-mail.php` — PHPMailer loading test
- `composer.json`
- `composer.lock`
- `vendor/` — Composer dependencies
- `includes/functions.php`
- `config/database.php`
- `config/mail.php`
- `database.sql`

## XAMPP setup

1. Put the `SENTINEL_Final` folder in:
   `C:\xampp\htdocs\`

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin and import:
   `database.sql`

4. Configure Gmail SMTP credentials as environment variables:
   `SENTINEL_MAIL_USERNAME`
   `SENTINEL_MAIL_PASSWORD`

   Do NOT place a real Gmail App Password in `config/mail.php`.

5. Open:
   `http://localhost/SENTINEL_Final/`

6. Register an account.

7. Login.

8. Enter the OTP received by email.

9. You will be redirected to the SENTINEL password analyzer dashboard.

## Important security design

The password being analyzed by the SENTINEL dashboard is processed in JavaScript in the browser. It is not submitted to the PHP backend or stored in MySQL.

The login system is separate: registration stores a password hash using PHP's `password_hash()` and login verifies it with `password_verify()`.

The 100-point analyzer score and entropy value are educational indicators, not official measures of real-world password cracking probability.
