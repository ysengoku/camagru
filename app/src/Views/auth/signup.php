<div class="form-container">
    <form method="POST" action="/api/signup">
        <h2 class="text-center mb-4">Create Account</h2>
        <div class="flex-col gap-2 mb-4">
            <label for="username" class="block">Username</label>
            <input type="text" id="username" name="username" required class="form-input">
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="email" class="block">Email</label>
            <input type="email" id="email" name="email" required class="form-input">
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="password" class="block">Password</label>
            <input type="password" id="password" name="password" required class="form-input">
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="confirm_password" class="block">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="form-input">
        </div>

        <div class="my-4 pt-4 ">
          <button type="submit" class="button-primary w-100">Sign Up</button>
          <p class="mt-4 text-center">
            Already have an account? &nbsp;
            <a href="/login" class="text-blue-500 text-decoration-none">Log in</a>
          </p>
        </div>
    </form>
</div>
