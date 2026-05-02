# Project TODO

## Setup

- Use plain PHP
- [ ] Create PHP project skeleton — add `composer.json`, `public/index.php`, `src/`, `templates/`, basic autoloading, and README notes.
- [ ] Docker & dev environment — create `Dockerfile` and `docker-compose.yml` with `php-fpm`, `nginx`, and `mysql` (reuse `database/init_db.sh` and migrations).

## Database

- [ ] Migrate DB schema — reuse `database/migrations/001_initial_schema.sql` and wire it into DB init or a migration runner.

## Server Implementation

- [ ] Implement routing & controllers — port login, signup, feed, edit, settings, verify-email routes and controllers.
- [ ] Port models — implement `User`, `Post`, `Comment`, `Like`, `Session` in PHP matching DB fields and interactions.
- [ ] Port services — implement `EmailService`, `signupService`, `loginService`, `postService`, `verifyEmailService`.
- [ ] Port middleware — implement session, auth, cookie, and CSRF middleware equivalents.

## Frontend & Assets

- [ ] Port views/templates — convert `app/src/mvc/views` to PHP templates (plain PHP or Twig) and ensure asset paths.
- [ ] Migrate client assets — keep `public/assets` (JS/CSS) and ensure client JS calls the PHP endpoints correctly.

## Media & Auth

- [ ] Implement uploads & image processing — secure file uploads and use GD/Imagick for image tasks.
- [ ] Authentication & sessions — use `password_hash`, secure sessions, CSRF protections, and optional remember-me.
- [ ] Email and verification — configure PHPMailer/SwiftMailer (SMTP) for verification flows.

## Quality & Ops

- [ ] Testing — add PHPUnit tests and integration tests for auth, posts, and DB interactions.
- [ ] CI/CD and linting — add GitHub Actions for linting, tests, and Docker build pipeline.
- [ ] Documentation — document setup, migration notes, and API differences in `README.md`.
