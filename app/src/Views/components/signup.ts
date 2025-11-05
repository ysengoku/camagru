export const signupFormComponent = `
  <dialog id="signup-modal">
    <form method="dialog" class="bg-white p-4 rounded flex flex-col items-start min-w-[320px] md:min-w-[400px] p-8">
      <button type="button" id="signup-close" class="absolute top-6 right-6 text-gray-500 hover:text-gray-800">✕</button>
      <h2 class="text-2xl font-semibold mb-8">Sign up</h2>

      <label class="mt-4">Username</label>
      <input id="signup-form-username" name="username" placeholder="Username" class="border rounded p-2 w-full">

      <label class="mt-4">Email</label>
      <input id ="signup-form-email" name="email" placeholder="Email" class="border rounded p-2 w-full">

      <label class="mt-4">Password</label>
      <input id="signup-form-password" name="password" type="password" placeholder="Password" class="border rounded p-2 w-full">

      <label class="mt-4">Confirm password</label>
      <input id="signup-form-password-repeat" name="passwordRepeat" type="password" placeholder="Password" class="border rounded p-2 w-full">

      <button id="submit-signup" type="submit" class="text-center mt-16 w-full">Sign up</button>
    </form>
  </dialog>
`;
