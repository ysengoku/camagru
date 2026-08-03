# API Documentation

## GET /api/validation-rules

Returns the client-side validation rules (username, password, email)
used to validate signup/profile forms.
### Parameters 

N/A
### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{username, password, email}|Validation constraints and error messages per field|


## POST /api/signup

Create a new user account.
### Parameters 

| Name | Type | Description |
|---|---|---|
|username|string||
|email|string||
|password|string||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|201|{message}|User created successfully|
|400|{error}|Validation failed|


## POST /api/login

Authenticate a user by username and password, starting a new session.
### Parameters 

| Name | Type | Description |
|---|---|---|
|username|string||
|password|string||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{message}|Login successful|
|400|{error}|Authentication failed|


## POST /api/logout

Clear and destroy the current user session
### Parameters 

N/A
### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{message}|Logged out successfully|
|400|{error}|Logout failed|


## POST /api/forgot-password

Sends a password-reset email to the given address, if it belongs to a registered account.
### Parameters 

| Name | Type | Description |
|---|---|---|
|email|string||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{message}|An email has been sent successfully|
|400|{error}|Failed to send an email|


## POST /api/reset-password

Resets a user's password using a valid password-reset token.
### Parameters 

| Name | Type | Description |
|---|---|---|
|token|string||
|new_password|string||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{message}|Password reset successfully|
|400|{error}|Validation failed|


## POST /api/resend-email

Resends the pending verification or password-reset email tied to the current session.
### Parameters 

N/A
### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{message}|Email resent successfully|
|400|{error}|Verification token has expired or is missing, or invalid action|
|429|{error, time_remaining}|Too many requests, wait before resending|


## POST /api/profile

Updates the current user's profile: username, email, password, avatar, and notification preference.
### Parameters 

| Name | Type | Description |
|---|---|---|
|username|string||
|email|string||
|current-password|string|Required when changing email or password|
|password|string|New password|
|avatar|string||
|notifications|bool||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{message, emailVerificationRequired, avatarHtml}|Profile updated successfully|
|400|{error}|Validation failed|


## GET /api/avatar-options

Returns a paginated HTML fragment of the current user's posts,
used for the avatar-selection picker in profile settings.
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|page|int|Page number, default to 1|

### Responses

| Status | Body | Description |
|---|---|---|
|200|{html, page, totalPages}||


## GET /api/studio-config

Returns studio configuration used by the camera/studio UI: max sticker
count, available text fonts/sizes, and filter presets.
### Parameters 

N/A
### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|200|{maxStickerCount, text, filters}|Studio configuration object|


## POST /api/photos

Composes a base image with stickers/text/filter and saves the result as a new post.
### Parameters 

| Name | Type | Description |
|---|---|---|
|baseImage|string|Base64-encoded source image|
|stickers|array|List of {path, width, height, x, y}|
|textOverlay|array|{content, fontFamily, fontSize, color, x, y}, optional|
|filter|string|Filter name, defaults to "none"|

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|201|{message, html}|Post created successfully|
|422|{error}|Missing required elements, or post could not be saved|
|500|{error}|Media directory not writable, or image creation/save failed|


## DELETE /api/photos

Deletes a post owned by the current user.
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|postId|string||

### Responses

| Status | Body | Description |
|---|---|---|
|200|{success}|Post deleted|
|400|{error}|Invalid post ID|
|403|{error}|Unauthorized to delete this post|
|404|{error}|Post not found|
|500|{error}|Failed to delete post|


## GET /api/photos

Returns a paginated HTML fragment of all users' posts (public feed).
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|offset|int|Defaults to 0|
|limit|int|Defaults to 10, max 50|

### Responses

| Status | Body | Description |
|---|---|---|
|200|{html, count}||
|400|{error}|Invalid offset or limit|


## GET /api/photos/me

Returns a paginated HTML fragment of the current user's own posts.
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|offset|int|Defaults to 0|
|limit|int|Defaults to 10, max 50|

### Responses

| Status | Body | Description |
|---|---|---|
|200|{html, count}||
|400|{error}|Invalid offset or limit|


## POST /api/like

Adds a like from the current user to a post.
### Parameters 

| Name | Type | Description |
|---|---|---|
|postId|int||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|201|{message, likesCount}|Like added|
|400|{error}|Invalid post ID, or already liked|
|500|{error}|Failed to add like|


## DELETE /api/like

Removes the current user's like from a post.
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|postId|int||

### Responses

| Status | Body | Description |
|---|---|---|
|200|{message, likesCount}|Like removed|
|400|{error}|Invalid post ID, or not liked|
|500|{error}|Failed to remove like|


## GET /api/comments

Returns a paginated HTML fragment of a post's comments.
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|postId|int||
|offset|int|Defaults to 0|
|limit|int|Defaults to 10, max 50|

### Responses

| Status | Body | Description |
|---|---|---|
|200|{html, count}||
|400|{error}|Invalid post ID, or invalid offset/limit|


## POST /api/comments

Adds a comment to a post and notifies the post's author by email.
### Parameters 

| Name | Type | Description |
|---|---|---|
|postId|int||
|content|string||

### Query Parameters

N/A
### Responses

| Status | Body | Description |
|---|---|---|
|201|{message, html, postId, commentCount}|Comment added|
|400|{error}|Invalid post ID or empty content|
|404|{error}|Post not found|
|500|{error}|Failed to add comment|


## DELETE /api/comments

Deletes a comment owned by the current user.
### Parameters 

N/A
### Query Parameters

| Name | Type | Description |
|---|---|---|
|commentId|int||

### Responses

| Status | Body | Description |
|---|---|---|
|200|{message, postId, commentCount}|Comment deleted|
|400|{error}|Invalid comment ID|
|403|{error}|Comment not found, or user not authorized to delete it|
|500|{error}|Failed to delete comment|


