# Authentication & Account Flows

This document describes how signup, email verification, login/logout, and password reset work in Camagru, the client/server call sequence for each flow, and the session mechanism they all share. It complements [`ARCHITECTURE.md`](./ARCHITECTURE.md), which covers the MVC/Service layering these flows are built on, and [`SECURITY.md`](./SECURITY.md), which covers CSRF protection and other cross cutting security measures that apply to these routes but are not specific to them.

## 1. Overview

Auth covers four user-facing flows, all entered through a single `AuthController`:

| Flow | Entry points | Service |
|---|---|---|
| Signup + email verification | `GET/POST /signup`, `GET /verify-email` | `SignupService` |
| Login / logout | `GET/POST /login`, `POST /api/logout` | `SessionService` |
| Forgot / reset password | `GET/POST /forgot-password`, `GET/POST /reset-password` | `ForgotPasswordService`, `ResetPasswordService` |
| Resend email (shared) | `POST /api/resend-email` | reuses `SignupService`/`ForgotPasswordService` |

Relevant paths:

- `app/src/Controllers/AuthController.php`: the single controller for every route above.
- `app/src/Services/auth/`: `SignupService`, `SessionService`, `ForgotPasswordService`, `ResetPasswordService`, `AuthInputValidator`.
- `app/src/core/SessionStore.php`: typed wrapper around PHP's native `$_SESSION`.
- `app/src/DTO/SignupData.php`, `ServiceResult.php`, `SessionKey.php`: shared value type definitions (DTO stands for Data Transfer Object).
- `app/client/js/auth/`: one JS module per page (`signup.js`, `login.js`, `forgotPassword.js`, `resetPassword.js`, `resendEmail.js`, `logout.js`) plus shared `helpers/validator.js` and `helpers/validationRules.js`.
- `app/cron/cleanup_unverified_users.php`: deletes abandoned unverified accounts (prod `cron` container).

## 2. Relationship to the MVC + Service architecture

Auth follows the same layering as the rest of the app (see `ARCHITECTURE.md` section 3): one controller fanning out to several singleton services, each concrete flow shown as a sequence diagram in sections 4, 6, and 7 below.

Every service method returns a `ServiceResult` (`success`, `errors`, `data`); `AuthController` only branches on `$result->success` and never inspects exception types. Input crossing from the controller into a service is wrapped in a DTO (`SignupData`) rather than passed as a raw array.

## 3. Session foundation

Sessions are stored in the database: `app/src/core/DatabaseSessionHandler.php` implements `SessionHandlerInterface` and stores session data in the `sessions` table. `app/src/bootstrap.php` wires it in with `session_set_save_handler(new DatabaseSessionHandler(), true)` before `session_start()`.   
This applies to every visitor, not just logged in users: signup and forgot password flows store `PendingEmail`, `ResendEmailAction`, and `LastEmailSentTime` (section 5) in the session before any `UserId` exists, so `sessions.user_id` is nullable for exactly this case.

### `SessionStore`
`SessionStore` is a typed wrapper around `$_SESSION`, keyed by the `SessionKey` enum (`UserId`, `PendingEmail`, `ResendEmailAction`, `LastEmailSentTime`) instead of raw string keys.   
   
- `SessionStore::setUserSession(int $userId)` is the single place a user becomes logged in. It calls `session_regenerate_id(true)` before storing `UserId`, so a session id issued before login can never remain valid afterward (session fixation protection). It leaves `PendingEmail`, `ResendEmailAction`, and `LastEmailSentTime` untouched: those are harmless leftovers, not sensitive data.
- `SessionStore::activeSession()` is the login check used across `AuthController`.
- `SessionStore::clearUserSession()` is called by `SessionService::processLogout()` together with `session_destroy()`, so the session row is removed immediately rather than waiting on garbage collection.

## 4. Signup & email verification flow

```mermaid
sequenceDiagram
    participant U as Browser
    participant JS as signup.js
    participant AC as AuthController
    participant SS as SignupService
    participant DB as User model

    U->>JS: submit signup form
    JS->>JS: client side validate (shared rules)
    JS->>AC: POST /api/signup {username, email, password}
    AC->>SS: processSignup(SignupData)
    SS->>SS: validate (AuthInputValidator)
    SS->>DB: findByUsername / findByEmail
    DB-->>SS: existing user or null
    SS->>DB: delete() stale unverified retry, then createNewUser()
    SS->>SS: sendVerificationLinkEmail() (issues token, session cooldown)
    SS-->>AC: ServiceResult
    AC-->>JS: 201 Created
    JS->>U: redirect to /email-sent?action=verify-email

    U->>AC: GET /verify-email?token=...
    AC->>DB: findByVerificationToken(token)
    alt token valid and not expired
        AC->>DB: email_verified = 1, clear token
        AC-->>U: redirect to /login?toast=email-verified
    else missing or expired
        AC->>DB: delete() the unverified account
        AC-->>U: 404
    end
```

Notes on `SignupService::processSignup()`:

1. Validation, then availability checks in `checkAvailability()`: a username is rejected if taken, unless it belongs to the same still unverified signup attempt (`existingByUsername->email === $this->userData->email && !email_verified`). This lets a user retry a signup that never got verified. An email is rejected only if it belongs to an already verified account.
2. `createUser()` deletes any stale unverified account for that email or username before inserting the new one, so retries do not collide on the `UNIQUE` constraints in `users`.
3. The verification token comes from `generateToken(32, 15)` (`app/src/helper/token.php`, `bin2hex(random_bytes(32))`, 15 minute expiry). `sendVerificationLinkEmail()` (`app/src/helper/mailer.php`) builds the link and calls `EmailService::getInstance()->send()`.
4. On success, `PendingEmail` and `ResendEmailAction` are written to the session so `/email-sent` and the resend button (section 5) know which email and action they are acting on.

`AuthController::verifyEmail()` looks the token up directly via `User::findByVerificationToken()`, checks `email_verification_token_expires_at` against the current time, and on expiry deletes the account outright (forcing the user to sign up again) rather than just clearing the token.

## 5. Resend email flow

`POST /api/resend-email` (`AuthController::resendEmail()`) is shared by both the verification and reset password flows. It does not take the email or action as input: it reads `SessionKey::PendingEmail` and `SessionKey::ResendEmailAction` from the session set by the signup or forgot password step, so the endpoint cannot be pointed at an arbitrary account.

1. A sixty second cooldown is enforced on the server by comparing `SessionKey::LastEmailSentTime` to `time()`, returning `429 Too Many Requests` with `time_remaining` while active.
2. `resendEmail.js` mirrors the same cooldown on the client (disables the button, counts down), and re arms the timer from the `429` response if the user's local clock drifts.
3. Depending on `ResendEmailAction`, the controller re validates the still relevant token (the verification token must not be expired or missing for `signup`/`email_change`; the email must already be verified for `reset_password`) before resending through `sendVerificationLinkEmail()` or `sendPasswordResetEmail()`.

## 6. Login / logout flow

```mermaid
sequenceDiagram
    participant U as Browser
    participant JS as login.js
    participant AC as AuthController
    participant LS as SessionService
    participant DB as User model
    participant Sess as SessionStore

    U->>JS: submit login form
    JS->>AC: POST /api/login {username, password}
    AC->>LS: processLogin(username, password)
    LS->>DB: findByUsername(username)
    alt user missing, unverified, or password mismatch
        LS-->>AC: ServiceResult.failure
        AC-->>JS: 400 {error: {general: ...}}
    else credentials valid
        LS->>Sess: setUserSession(user.id): regenerates session id
        LS-->>AC: ServiceResult.success
        AC-->>JS: 200 OK
        JS->>U: redirect to /
    end

    U->>JS: click logout
    JS->>AC: POST /api/logout
    AC->>LS: processLogout()
    LS->>Sess: clearUserSession(), session_destroy()
    AC-->>JS: 200 OK
    JS->>U: redirect to /login
```

`SessionService::processLogin()` deliberately checks `isEmailVerified()` before `password_verify()`, so an unverified account gets a distinct please verify message rather than a generic invalid credentials error. The tradeoff is that this also confirms an unverified account with that username exists; there is no equivalent enumeration guard here as there is for forgot password (section 7).

Both the GET `/login` and GET `/signup` pages redirect to `/` immediately if `SessionStore::activeSession()` is already true, and their POST handlers reject with `400 Already logged in` under the same condition.

## 7. Forgot password / reset password flow

```mermaid
sequenceDiagram
    participant U as Browser
    participant JS as forgotPassword.js
    participant AC as AuthController
    participant FPS as ForgotPasswordService
    participant DB as User model

    U->>JS: submit email
    JS->>AC: POST /api/forgot-password {email}
    AC->>FPS: processForgotPassword(email)
    FPS->>FPS: validate email format
    FPS->>FPS: check session cooldown
    FPS->>DB: findByEmail(email)
    alt user exists and is verified
        FPS->>DB: save password_reset_token + expiry
        FPS->>FPS: sendPasswordResetEmail()
    else user missing or unverified
        Note over FPS: no email sent, no error surfaced
    end
    FPS-->>AC: ServiceResult.success (once format is valid and not rate limited)
    AC-->>JS: 200 OK
    JS->>U: redirect to /email-sent?action=reset-password

    U->>AC: GET /reset-password?token=...
    AC->>DB: findByPasswordResetToken + expiry check
    AC-->>U: render form, or 404 if token invalid or expired

    U->>AC: POST /api/reset-password {token, new_password}
    AC->>DB: re validate token, then update password_hash, clear token
    AC->>DB: delete all other sessions for that user
    AC-->>U: 200 OK, redirect to /login?toast=password-reset
```

`ForgotPasswordService::processForgotPassword()` always returns `ServiceResult::success()` once the email format is valid and the cooldown has passed, regardless of whether an account exists or is verified. This is a deliberate anti enumeration measure: the client cannot distinguish an email that was sent from an email that has no matching account, from the response alone. The same sixty second session cooldown described in section 5 applies here, so repeated submissions cannot be used to probe accounts either.

`ResetPasswordService::validateToken()` is called twice in the flow: once directly by `AuthController::resetPassword()`'s GET handler, to decide whether to render the form or return 404, and again internally at the top of `processResetPassword()` before the POST actually mutates the password. The GET check is a convenience, not a substitute for re checking at write time. On a successful reset, `processResetPassword()` clears the token and expiry, then deletes every other row in `sessions` for that user, so a stolen session cannot outlive a password change.

## 8. Shared validation rules

Username, email, and password constraints are defined once, on the server, in `app/src/config/validation.php`, and used two ways.

1. Server: `AuthInputValidator` (`app/src/Services/auth/AuthInputValidator.php`) loads this file directly and is the authority. It is what actually runs in `SignupService`, `ForgotPasswordService`, and `ResetPasswordService` before anything is persisted.
2. Client: `GET /api/validation-rules` (`ValidationRulesController`) serves the same file as JSON. `auth/helpers/validationRules.js` fetches it once at module load and feeds `auth/helpers/validator.js`'s `Validator` class, which every auth form (`signup.js`, `forgotPassword.js`, `resetPassword.js`) uses for pre submit validation. If the fetch fails, `validationRules.js` falls back to a hardcoded copy of the same rules so the form does not lose validation entirely, but that fallback can silently drift from `config/validation.php` since nothing keeps the two in sync.

Client side validation here is purely a UX optimization: instant feedback, fewer round trips. It is not a security boundary. `AuthInputValidator` re validates everything on the server regardless of what the client already checked.

## 9. Remaining gaps

Tracked in `TODO.md` and `SECURITY.md`.

1. The `/api/forgot-password` and `/api/resend-email` cooldown is tracked per session, not per account or IP, so a client that never keeps a session is not throttled by it alone.
