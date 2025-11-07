export const signupFormComponent = `
  <dialog id="signup-modal" class="rounded-lg">
    <form id="signup-form" method="dialog" class="bg-white p-4 rounded flex flex-col items-start min-w-[360px] md:min-w-[480px] p-8" autocomplete="off">
      <button type="button" id="signup-close" class="absolute top-6 right-6 text-gray-500 hover:text-gray-800">✕</button>
      <h2 class="text-2xl font-semibold mb-8">Sign up</h2>

      <label class="mt-4">Username</label>
      <input id="signup-form-username" name="username" placeholder="Username" class="border rounded p-2 w-full" required>

      <label class="mt-4">Email</label>
      <input id ="signup-form-email" name="email" placeholder="Email" class="border rounded p-2 w-full" required>

      <label class="mt-4">Password</label>
      <input id="signup-form-password" name="password" type="password" placeholder="Password" class="border rounded p-2 w-full" required>

      <label class="mt-4">Confirm password</label>
      <input id="signup-form-password-repeat" name="passwordRepeat" type="password" placeholder="Password" class="border rounded p-2 w-full" required>

      <div id="signup-form-error" class="form-error flex flex-row items-center p-2 mt-4 w-full gap-1 hidden">
        <img src="/assets/icons/error.svg" alt="Error icon" class="h-6" />
        <p id="signup-form-error-message" class="w-full break-words pr-8 text-red-500 font-semibold"></p>
      </div>

      <button id="submit-signup" type="submit" class="text-center mt-8 py-2 w-full bg-teal-600 text-cyan-100 rounded">Sign up</button>
    </form>
  </dialog>
`;
