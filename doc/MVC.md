(# MVC Guide for this Project)

This document explains the Model–View–Controller structure used for the project, where files live, and the request flow.

## Goals

- Keep server-side logic in PHP (plain PHP templates) so every runtime function can be matched to PHP stdlib.
- Preserve client assets in `app/public/assets` and serve them as static files.

## Directory mapping (recommended)

- `public/` — webroot; place `index.php`, static assets (`assets/`, `media/`).
- `src/Controllers/` — HTTP controllers (one class or file per resource, e.g. `FeedController.php`).
- `src/Models/` — models that encapsulate DB access (use PDO). Mirror DB tables from `database/migrations/`.
- `templates/` or `src/Views/` — plain PHP templates that generate HTML (examples: `feed.php`, `login.php`).
- `src/Services/` — business logic (email, image handling, signup/login orchestration).
- `src/Middleware/` — session start, auth checks, CSRF verification.
- `src/Core/` — `Database.php`, `Model.php` (DB connection and base model helpers).

## Request lifecycle (simple)

1. `nginx` → `php-fpm` executes `public/index.php`.
2. `index.php` runs middleware (start session, set `$currentUser`).
3. Router dispatches to a `Controller::action()` based on path/method.
4. Controller interacts with `Service` and `Model` classes to gather data.
5. Controller includes a PHP template from `templates/` passing variables (`$user`, `$posts`, ...).
6. Template echoes final HTML; response sent to client.

## Security basics

- Passwords: use `password_hash()` / `password_verify()`.
- Sessions: `session_start()` with secure cookie flags (`secure`, `httponly`, `samesite`).
- CSRF: include token in state-changing forms and verify in middleware.
- File uploads: validate MIME/type/size, store outside webroot or sanitize.

## Example: implement Feed page

- Controller: `src/Controllers/FeedController.php` → `index()` loads posts via `src/Services/PostService.php`.
- Model: `src/Models/Post.php` queries the DB using PDO.
- View: `templates/feed.php` — expects `$user` and `$posts` (example template added).

## Notes on tooling

- Keep frontend builds (SCSS → CSS, JS bundling) as a build step. Serve compiled assets from `public/assets/`.
- Use Composer for PHP dependencies and PHPUnit for tests.

## Migration checklist

- Add `public/index.php` and a minimal router.
- Move/convert view files to `templates/` as plain PHP templates.
- Implement `src/Core/Database.php` (PDO wrapper) and `src/Core/Model.php` helpers.
- Implement basic middleware for sessions and CSRF.

If you want, I can scaffold `public/index.php`, a minimal router, and `src/Controllers/FeedController.php` next.
