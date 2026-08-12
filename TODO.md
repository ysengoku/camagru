# Project TODO

## Setup

- Use plain PHP
- [X] Create PHP project skeleton — add `composer.json`, `public/index.php`, `src/`, `templates/`, basic autoloading, and README notes.
- [X] Docker & dev environment — create `Dockerfile` and `docker-compose.yml` with `php-fpm`, `nginx`, and `mysql`.

## Database

- [X] Migrate DB schema — `database/migrations/001_initial_schema.sql` and wire it into DB init or a migration runner.

## Server Implementation

- [X] Implement routing & controllers — login, signup, feed, edit, settings, verify-email routes and controllers.
- [X] Models — implement `User`, `Post`, `Comment`, `Like`, `Session` in PHP matching DB fields and interactions.
- [X] Services — implement `EmailService`, `signupService`, `loginService`, `postService`, `verifyEmailService`.

## Frontend & Assets

- [X] Port views/templates — convert `app/src/mvc/views` to PHP templates (plain PHP or Twig) and ensure asset paths.
- [X] Migrate client assets — keep `public/assets` (JS/CSS) and ensure client JS calls the PHP endpoints correctly.

## Media & Auth

- [X] Implement uploads & image processing — secure file uploads and use GD for image tasks.
- [X] Authentication & sessions: use `password_hash`, secure sessions, CSRF protections, and optional remember-me.
- [X] Email and verification flows.

## User Management Implementation Order

### 1. Session foundation

- [X] Add `email_notifications TINYINT(1) DEFAULT 1` column to `users` table (migration)
- [X] DB-backed sessions (`Session` model, `SessionHandler`, `sessions` table) were tried and removed; single non-replicated `app` container doesn't need shared session storage
- [X] Session cookie hardening: set `session.cookie_httponly=1`, `session.cookie_samesite=Strict` (or `Lax`), `session.cookie_secure=1` in prod — currently all unset

### 2. AuthController (signup → login → logout → password reset)

- [X] Create `AuthController` with actions: `signup`, `login`, `logout`, `forgotPassword`, `resetPassword`
- [X] Create corresponding views: `auth/signup.php`, `auth/login.php`, `auth/forgot-password.php`, `auth/reset-password.php`
- [X] Implement signup: validate input, `password_hash()`, insert user with `email_verified=0` + `verification_token`, send verification email
- [X] Implement email verification route (`/verify-email?token=...`) that sets `email_verified=1`
- [X] Implement login: verify credentials, `session_regenerate_id(true)`, store `user_id` + CSRF token in `$_SESSION`
- [X] Implement logout: call `session_destroy()` (currently only clears `user_id` from `$_SESSION`)
- [X] Implement forgot-password: generate reset token, store with expiry, send email
- [X] Implement reset-password: validate token + expiry, update `password_hash`

### 3. Route auth guard

- [X] Add `'auth' => true` to protected routes (`/studio`, `/profile`, `/profile/edit`, `/profile/settings`) in `routes.php`
- [X] Add auth check in `Application::run()` before controller dispatch — redirect unauthenticated users to `/login`
- [X] Security pass (after logout):
  - [X] CSRF protection:
    - [X] `Request::getCsrfToken()`: lazily generate + store in `$_SESSION['csrf_token']` if missing
    - [X] Render it as `<meta name="csrf-token" content="...">` in `layout.php`
    - [X] `api.js`: read the meta tag once, attach as `X-CSRF-Token` header on every request
    - [X] `Application::run()`: for POST, compare header vs session with `hash_equals()`; throw new `HTTPForbiddenException` (mirrors `HTTPNotFoundException`) on mismatch, render 403
  - [X] Fix email enumeration risk in forgot-password flow if any new call sites are added (already fixed in `ForgotPasswordService`, keep it that way)
  - [X] Password-reset token: invalidate (`null` out) after successful use. Invalidating the user's *other* active sessions on password reset is no longer a simple DB delete now that sessions are file-based — needs its own design if still wanted (e.g. a `password_changed_at` column checked on each request)
  - [X] Rate-limit `/api/forgot-password` itself, same cooldown pattern as `/api/resend-email`

### 4. EmailService (raw SMTP via `fsockopen()`)

- [X] Implement `EmailService` class using `fsockopen()` + STARTTLS via `stream_socket_enable_crypto()`
- [X] Configure SMTP credentials via `.env` (`SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`)

### 5. ProfileController

- [X] `ProfileController::index()` renders `profile/index.php` (username, email, new/confirm password, avatar picker)
- [X] `avatarSelection.php`: lets user pick an avatar from their own posts (`Post::findByUserId()`), live preview via `profileManager.js`
- [X] Current-password re-authentication, popup-based (step-up auth, not a static field):
  - [X]Add a `<dialog>` asking for it instead
  - [X]`profileManager.js`: on submit, detect a sensitive change via `emailInput.value !== emailInput.defaultValue` or `passwordInput.value !== ''`; if either is true, open the `<dialog>` to collect `current_password` before sending, otherwise submit directly with no `current_password` field at all
  - [X] Server-side check is independent of the popup and non-negotiable: `ProfileService` must itself verify `current_password` whenever email and/or password actually changed, regardless of what the client sent or showed
- [X] `ProfileController::update()` is still a stub — build `ProfileService::updateProfile()`:
  - [X] Username/email uniqueness checks must exclude the current user's own row (same self-exclusion pattern as `SignupService::checkAvailability()`), so resubmitting unchanged values doesn't false-positive as "already taken"
  - [X] Verify `current_password` via `password_verify()` when changing password and/or email (see popup flow above)
  - [X] New password stays optional (blank = unchanged) — `AuthInputValidator::validatePassword()` should only run when a new password is actually submitted
  - [X] Avatar: verify the submitted path belongs to one of the current user's own posts before saving (don't trust the client)
  - [X] Decide whether an email change should reset `email_verified`/reissue a verification token, matching signup's flow
- [X] Implement settings: toggle `email_notifications` preference

### 6. Verification hardening

- [X] Resend verification email endpoint (`POST /api/resend-email`) — rate-limit per email/IP, return a generic response regardless of whether the email exists or is already verified (avoid enumeration)
- [X] Cleanup job for abandoned unverified accounts — delete `email_verified=0` users older than ~24h (decide request-driven cleanup-on-signup vs cron)
- [X] `verify-email` page: handle the expired-token response and surface a "resend verification email" action to the user

## Quality & Ops

- [X] CI/CD and linting: add GitHub Actions for linting, tests, and Docker build pipeline.
- [X] Documentation — document setup, migration notes, and API differences.

## Testing

- [X] Validation logic
- [X] Service layer
- [X] Helpers:
- [X] PhotoApiController:
- [X] PostReactionsController:
- [X] AuthController
- [X] ProfileController:

- [X] User::deleteAbandonedUnverified() — cleanup logic for stale unverified signups, not exercised by any test so far (services don't call it).

{
    "baseImage": base1,
    "stickers": [
        {
            "path": "/assets/stickers/glass3.png",
            "x": 417.12659960716627,
            "y": 150.18792325056432,
            "width": 154.60076328789955,
            "height": 66.15867968397292
        },
        {
            "path": "/assets/stickers/hat2.png",
            "x": 355.027438842926,
            "y": 16.08182844243792,
            "width": 270.0834131301708,
            "height": 135.14514446952597
        },
        {
            "path": "/assets/stickers/zchoju3.png",
            "x": 604.513540860663,
            "y": 348.6213318284424,
            "width": 47.83400302362954,
            "height": 70.60925756207675
        }
    ],
    "textOverlay": null,
    "filter": "none"
}