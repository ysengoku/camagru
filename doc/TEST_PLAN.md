# Test Plan

Manual test checklist to run through.

## Setup

- [X] `make` builds and starts all containers without errors
- [X] `make populate-db` seeds demo users and posts without errors
- [X] `make clean-db` removes all data without errors
- [X] No errors or warnings in any browser console on any page
- [X] No errors or warnings in `docker logs camagru_app` / `camagru_nginx` during normal use
- [X] No credentials, API keys, or env files are tracked by git

## Signup & email verification

- [X] Sign up with a new username, email, and password succeeds
- [X] Sign up with a password below the minimum complexity is rejected with a clear error
- [X] Sign up with an already-taken username is rejected with a clear error
- [X] Sign up with an already-registered, verified email is rejected
- [X] Sign up again with the same email while the previous signup is still unverified succeeds, replacing the old attempt
- [X] Verification email arrives and contains a working link
- [X] Clicking the verification link marks the account verified and redirects to login
- [X] Clicking an expired or already-used verification link shows a clear error, not a crash
- [X] Logging in before verifying email is rejected with a clear message

## Login & logout

- [X] Login with correct username and password succeeds
- [X] Login with wrong password is rejected with a generic error, not one that reveals whether the username exists
- [X] Login with a nonexistent username is rejected with the same generic error
- [X] Logout clears the session, subsequent protected pages redirect to login
- [X] A logout control is reachable in one click from every page while logged in
- [X] Visiting `/login` or `/signup` while already logged in redirects away instead of showing the form

## Forgot / reset password

- [X] Requesting a reset link for a registered, verified email sends an email
- [X] Requesting a reset link for an unregistered email returns the same generic response, no enumeration
- [X] Reset link works once and updates the password
- [X] Reusing an already-used reset link is rejected
- [X] Using an expired reset link is rejected with a clear error
- [X] Resetting the password invalidates other active sessions for that account

## Resend email

- [X] Resend works for a pending signup verification
- [X] Resend works for a pending password reset
- [X] Resending before the cooldown expires is rejected with a clear countdown, not a silent failure

## Studio

- [X] Visiting `/studio` while logged out redirects to login instead of showing the editor
- [X] The capture button is disabled until a superposable image (sticker) is selected
- [X] Webcam capture works and shows a live preview before confirming
- [X] Uploading a photo from disk works as an alternative to webcam capture
- [X] Adding a sticker places it on the canvas and it can be moved and resized
- [X] Adding text places it on the canvas with the selected font, color, and size
- [X] Each filter visibly changes the preview when selected
- [X] Sharing a photo creates a new post visible in the public feed
- [X] The final saved image matches what was shown in the live preview, position and size included
- [X] Own post history on the studio page shows previously created posts
- [X] Deleting an own post removes it from both the studio gallery and the public feed

## Feed

- [X] Public feed loads and shows posts from all users
- [X] Posts are ordered by creation date
- [X] The feed loads at least 5 posts per page/scroll batch
- [X] Scrolling down loads more posts, infinite scroll works without duplicating or skipping posts
- [X] Liking a post updates the like count immediately, without navigating away
- [X] Unliking a post updates the count back down
- [X] Liking or commenting while logged out is rejected, not silently accepted
- [X] Opening a post shows its full view with comments
- [X] Adding a comment appears immediately without a full page reload
- [X] Deleting an own comment removes it immediately
- [X] Deleting someone else's comment is not possible
- [X] Downloading an own post saves the image file
- [X] Downloading someone else's post is not possible

## Profile

- [X] Updating username to an available one succeeds
- [X] Updating username to one already taken by someone else is rejected
- [X] Updating email triggers re-verification of the new address
- [X] Changing password requires the current password
- [X] Changing email requires the current password
- [X] Leaving password blank while updating other fields keeps the password unchanged
- [X] Selecting an avatar from own posts updates it across the site
- [X] A newly signed-up user has email notifications enabled by default
- [X] Toggling email notifications on/off persists the setting
- [X] Email notification for new comments is sent only when the setting is enabled

## Security

- [X] A script tag or HTML injected into a comment, username, or text overlay is displayed as literal text, not executed
- [X] Submitting a form without the CSRF token is rejected
    ```bash
    # How to test
    # Get a Session Cookie (login from browser)
    # Execute from terminal:
    curl -i -k -X POST https://<host>/api/profile \
        -H "Cookie: <session cookie>" \
        -H "Content-Type: application/json" \
        -d '{"username":"test"}'
    ```
- [X] A SQL-injection-style string in any input field is handled safely, no errors, no unintended query behavior
    ```bash
    # How to test
    # Send one of the following values from every input field
    ' OR '1'='1
    '; DROP TABLE users; --
    " OR ""="
    ```

- [X] Directly requesting another user's protected data through the API is rejected
    ```bash
    # How to test
    # Log in as user A, grab A's session, then try IDs/paths belonging to user B
    curl -i -k -X DELETE https://<host>/api/photos\?postId\=12 \
        -H "Cookie: <A's session cookie>" \
        -H "X-CSRF-Token: <A's csrf token from meta tag>"
    ```

- [X] Session cookie has `HttpOnly`, `SameSite`, and, in production, `Secure` set
- [X] Passwords must be hashed before storing in database
- [X] Uploading a non-image file, or an image with a spoofed extension, to the studio is rejected or safely fails, and is never served as executable content from `/media/`

## Cross-cutting

- [X] Layout is usable and readable at mobile, tablet, and desktop widths
- [X] All tested flows work in both Chrome and Firefox
- [X] Refreshing the page mid-flow, for example during studio editing, doesn't leave the app in a broken state
