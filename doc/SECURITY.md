# Security

This document summarizes the security measures implemented in this project. It complements [`AUTH.md`](./AUTH.md), which covers the auth flows in more detail, and [`tests/Security/SecurityTest.php`](../app/tests/Security/SecurityTest.php), which verifies several of the items below automatically.

## 1. Cross Site Request Forgery (CSRF)

A token is generated once per session (`Request::getCsrfToken()`, `app/src/core/Request.php`) and stored under `SessionKey::CsrfToken`. It is rendered into every page as `<meta name="csrf-token">` (`app/src/Views/layout.php`) and read by `api.js`, which attaches it as an `X-CSRF-Token` header on every request. `Application::run()` compares that header against the session value with `hash_equals()` for every method except GET, and returns 403 on a mismatch.

## 2. Cross Site Scripting (XSS)

View templates escape user supplied content with `htmlspecialchars()` before it is echoed, for example comment text in `render_comment()` (`app/src/Views/post/comments.php`) and post/profile fields throughout `app/src/Views/`. Nothing user supplied is echoed unescaped.

## 3. SQL Injection

`Database` (`app/src/core/Database.php`) only executes parameterized queries through PDO; user input is always bound as a parameter, never concatenated into SQL text. Every model built on `Model` inherits this.

## 4. Session Handling

Sessions are stored in the database through `DatabaseSessionHandler` rather than the filesystem. `SessionStore::setUserSession()` calls `session_regenerate_id(true)` on login, so a session id issued before authentication can never remain valid afterward. `SessionService::processLogout()` calls `session_destroy()` so the session row is removed immediately rather than waiting on garbage collection. `app/src/bootstrap.php` sets the session cookie flags before `session_start()`: `HttpOnly` always true, `SameSite` set to `Lax`, and `Secure` true only when `NODE_ENV` is production, so the cookie still works over plain HTTP in dev.

## 5. Authorization Checks

Every action that mutates or exposes another resource checks ownership on the server before proceeding, regardless of what the client sent: deleting a post or a comment, downloading a photo, and changing a profile's email or password all verify the acting user first. `Controller::getAuthenticatedUser()` guarantees a resolved user for any route marked `auth: true`.

## 6. Password Handling

Passwords are hashed with `password_hash()` and checked with `password_verify()`; a plaintext password is never stored or logged. Changing the account email or password requires the current password to be supplied again, verified independently on the server (`ProfileService`), even though the UI already asks for it.

## 7. Rate Limiting and Enumeration

`/api/resend-email` and `/api/forgot-password` share a sixty second cooldown, tracked in the session and enforced in `ForgotPasswordService`/`AuthController::resendEmail()`. `ForgotPasswordService::processForgotPassword()` always returns the same success response whether or not the given email belongs to a registered account, so the response cannot be used to discover which emails are registered.

## 8. Path Traversal

`PhotoDownloadController::downloadPhoto()` resolves the stored `image_path` through `basename()` before touching the filesystem, so a value crafted to walk out of the media directory still resolves to a plain filename inside it.

## 9. Known Gaps

Tracked in `TODO.md`. Outgoing rate limiting is per session rather than per account or IP, so a client that never keeps a session cannot be throttled by this mechanism alone.
