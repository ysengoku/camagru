<div class="auth-form-container">
    <form method="POST" action="/api/forgot-password" id="forgot-password-form" class="auth-form" novalidate>
        <h2>Forgot Password</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4 pb-4">
            <p class="color-primary-600">
                Enter your email address to receive a link to reset your password.
            </p>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="email" class="block">Email</label>
            <input type="email" id="email" name="email" required class="form-input">
            <span id="email-error" class="error-feedback"></span>
        </div>

        <div class="my-4 pt-4">
            <button type="submit" class="button-primary font-bold w-100">
                Reset Password
            </button>
        </div>
    </form>
</div>

