import { passwordResetForm } from './passwordReset';

export const loginFormComponent = `
  <dialog id="login-modal" class="rounded-lg">
    <form id="login-form" method="dialog" class="bg-white p-4 rounded flex flex-col items-start min-w-[360px] md:min-w-[440px] md:max-w-[480px] p-8" autocomplete="off">
      <button type="button" id="login-close" class="absolute top-6 right-6 text-gray-500 hover:text-gray-800">✕</button>
      <h2 class="text-2xl font-semibold mb-8">Login</h2>

      <label class="mt-4">Username</label>
      <input id="login-form-username" name="username" placeholder="Username" class="border rounded p-2 w-full">

      <label class="mt-4">Password</label>
      <input id="login-form-password" name="password" type="password" placeholder="Password" class="border rounded p-2 w-full">

      <div id="login-form-error" class="form-message flex flex-col items-start p-2 mt-4 w-full gap-1 hidden">
        <div class="flex flex-row items-center mt-4 w-full gap-1">
          <img src="/assets/icons/error.svg" alt="Error icon" class="h-6" />
          <p id="login-form-error-message" class="w-full break-words pr-6 text-red-500  font-semibold"></p>
        </div>
      </div>

      <button id="submit-login" type="submit" class="text-center mt-8 py-2 w-full bg-teal-600 text-cyan-100 rounded">Login</button>

      ${passwordResetForm}
    </form>
  </dialog>
`;
