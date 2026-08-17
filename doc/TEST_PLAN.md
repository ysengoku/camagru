# Test Plan

Manual test checklist to run through.

## Setup

- [ ] `make` builds and starts all containers without errors
- [ ] `make dev` builds and starts the dev environment without errors
- [ ] `make populate-db` seeds demo users and posts without errors
- [ ] `make clean-db` removes all data without errors
- [ ] No errors or warnings in any browser console on any page
- [ ] No errors or warnings in `docker logs camagru_app` / `camagru_nginx` during normal use

## Signup & email verification

- [ ] Sign up with a new username, email, and password succeeds
- [ ] Sign up with an already-taken username is rejected with a clear error
- [ ] Sign up with an already-registered, verified email is rejected
- [ ] Sign up again with the same email while the previous signup is still unverified succeeds, replacing the old attempt
- [ ] Verification email arrives and contains a working link
- [ ] Clicking the verification link marks the account verified and redirects to login
- [ ] Clicking an expired or already-used verification link shows a clear error, not a crash
- [ ] Logging in before verifying email is rejected with a clear message

## Login & logout

- [ ] Login with correct username and password succeeds
- [ ] Login with wrong password is rejected with a generic error, not one that reveals whether the username exists
- [ ] Login with a nonexistent username is rejected with the same generic error
- [ ] Logout clears the session, subsequent protected pages redirect to login
- [ ] Visiting `/login` or `/signup` while already logged in redirects away instead of showing the form

## Forgot / reset password

- [ ] Requesting a reset link for a registered, verified email sends an email
- [ ] Requesting a reset link for an unregistered email returns the same generic response, no enumeration
- [ ] Reset link works once and updates the password
- [ ] Reusing an already-used reset link is rejected
- [ ] Using an expired reset link is rejected with a clear error
- [ ] Resetting the password invalidates other active sessions for that account

## Resend email

- [ ] Resend works for a pending signup verification
- [ ] Resend works for a pending password reset
- [ ] Resending before the cooldown expires is rejected with a clear countdown, not a silent failure

## Studio

- [ ] Webcam capture works and shows a live preview before confirming
- [ ] Uploading a photo from disk works as an alternative to webcam capture
- [ ] Adding a sticker places it on the canvas and it can be moved and resized
- [ ] Adding text places it on the canvas with the selected font, color, and size
- [ ] Each filter visibly changes the preview when selected
- [ ] Sharing a photo creates a new post visible in the public feed
- [ ] The final saved image matches what was shown in the live preview, position and size included
- [ ] Own post history on the studio page shows previously created posts
- [ ] Deleting an own post removes it from both the studio gallery and the public feed

## Feed

- [ ] Public feed loads and shows posts from all users
- [ ] Scrolling down loads more posts, infinite scroll works without duplicating or skipping posts
- [ ] Liking a post updates the like count immediately, without navigating away
- [ ] Unliking a post updates the count back down
- [ ] Opening a post shows its full view with comments
- [ ] Adding a comment appears immediately without a full page reload
- [ ] Deleting an own comment removes it immediately
- [ ] Deleting someone else's comment is not possible
- [ ] Downloading an own post saves the image file
- [ ] Downloading someone else's post is not possible

## Profile

- [ ] Updating username to an available one succeeds
- [ ] Updating username to one already taken by someone else is rejected
- [ ] Updating email triggers re-verification of the new address
- [ ] Changing password requires the current password
- [ ] Changing email requires the current password
- [ ] Leaving password blank while updating other fields keeps the password unchanged
- [ ] Selecting an avatar from own posts updates it across the site
- [ ] Toggling email notifications on/off persists the setting
- [ ] Email notification for new comments is sent only when the setting is enabled

## Security

- [ ] Submitting a form without the CSRF token is rejected
- [ ] A script tag or HTML injected into a comment, username, or text overlay is displayed as literal text, not executed
- [ ] A SQL-injection-style string in any input field is handled safely, no errors, no unintended query behavior
- [ ] Session cookie has `HttpOnly`, `SameSite`, and, in production, `Secure` set
- [ ] Directly requesting another user's protected data through the API is rejected
- [ ] Passwords must be hashed before storing in database
- [ ] Uploading a non-image file, or an image with a spoofed extension, to the studio is rejected or safely fails, and is never served as executable content from `/media/`

## Cross-cutting

- [ ] Layout is usable and readable at mobile, tablet, and desktop widths
- [ ] All tested flows work in both Chrome and Firefox
- [ ] Refreshing the page mid-flow, for example during studio editing, doesn't leave the app in a broken state
