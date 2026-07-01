# Project TODO

## Setup

- Use plain PHP
- [X] Create PHP project skeleton — add `composer.json`, `public/index.php`, `src/`, `templates/`, basic autoloading, and README notes.
- [X] Docker & dev environment — create `Dockerfile` and `docker-compose.yml` with `php-fpm`, `nginx`, and `mysql`.

## Database

- [X] Migrate DB schema — `database/migrations/001_initial_schema.sql` and wire it into DB init or a migration runner.

## Server Implementation

- [ ] Implement routing & controllers — login, signup, feed, edit, settings, verify-email routes and controllers.
- [ ] Models — implement `User`, `Post`, `Comment`, `Like`, `Session` in PHP matching DB fields and interactions.
- [ ] Port services — implement `EmailService`, `signupService`, `loginService`, `postService`, `verifyEmailService`.
- [ ] Port middleware — implement session, auth, cookie, and CSRF middleware equivalents.

## Frontend & Assets

- [ ] Port views/templates — convert `app/src/mvc/views` to PHP templates (plain PHP or Twig) and ensure asset paths.
- [ ] Migrate client assets — keep `public/assets` (JS/CSS) and ensure client JS calls the PHP endpoints correctly.

## Media & Auth

- [ ] Implement uploads & image processing — secure file uploads and use GD/Imagick for image tasks.
- [ ] Authentication & sessions — use `password_hash`, secure sessions, CSRF protections, and optional remember-me.
- [ ] Email and verification — configure PHPMailer/SwiftMailer (SMTP) for verification flows.

## User Management Implementation Order

### 1. Session foundation

- [X] Update `Session` model to match DB schema (`session_token`, `csrf_token`, `expired_at`)
- [X] Add `email_notifications TINYINT(1) DEFAULT 1` column to `users` table (migration)
- [X] Implement `SessionHandler` class (implements `SessionHandlerInterface`) for DB-backed sessions
- [X] Call `session_set_save_handler()` + `session_start()` in `bootstrap.php` with secure cookie config (`httponly`, `samesite=Strict`)

### 2. AuthController (signup → login → logout → password reset)

- [ ] Create `AuthController` with actions: `signup`, `login`, `logout`, `forgotPassword`, `resetPassword`
- [ ] Create corresponding views: `auth/signup.php`, `auth/login.php`, `auth/forgot-password.php`, `auth/reset-password.php`
- [ ] Implement signup: validate input, `password_hash()`, insert user with `email_verified=0` + `verification_token`, send verification email
- [ ] Implement email verification route (`/verify-email?token=...`) that sets `email_verified=1`
- [ ] Implement login: verify credentials, `session_regenerate_id(true)`, store `user_id` + CSRF token in `$_SESSION`
- [ ] Implement logout: destroy session in DB + `session_destroy()`
- [ ] Implement forgot-password: generate reset token, store with expiry, send email
- [ ] Implement reset-password: validate token + expiry, update `password_hash`

### 3. Route auth guard

- [X] Add `'auth' => true` to protected routes (`/studio`, `/profile`, `/profile/edit`, `/profile/settings`) in `routes.php`
- [ ] Add auth check in `Application::run()` before controller dispatch — redirect unauthenticated users to `/login`
- [ ] Add CSRF validation in `Application::run()` for POST requests

### 4. EmailService (raw SMTP via `fsockopen()`)

- [X] Implement `EmailService` class using `fsockopen()` + STARTTLS via `stream_socket_enable_crypto()`
- [X] Configure SMTP credentials via `.env` (`SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`)

### 5. ProfileController

- [ ] Create `ProfileController` with actions: `index`, `edit`, `settings`
- [ ] Implement profile edit: allow changing username, email (re-verify on change), password
- [ ] Implement settings: toggle `email_notifications` preference

### 6. Verification hardening

- [X] Resend verification email endpoint (`POST /api/resend-email`) — rate-limit per email/IP, return a generic response regardless of whether the email exists or is already verified (avoid enumeration)
- [X] Cleanup job for abandoned unverified accounts — delete `email_verified=0` users older than ~24h (decide request-driven cleanup-on-signup vs cron)
- [X] `verify-email` page: handle the expired-token response and surface a "resend verification email" action to the user

## Quality & Ops

- [ ] Testing — add PHPUnit tests and integration tests for auth, posts, and DB interactions.
- [ ] CI/CD and linting — add GitHub Actions for linting, tests, and Docker build pipeline.
- [ ] Documentation — document setup, migration notes, and API differences in `README.md`.
