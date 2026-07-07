<?php

if (!function_exists('render_email_field')) {
    function render_email_field(?User $user, bool $required = false): string {
        $label = $required ? 'Email*' : 'Email';
        $email = htmlspecialchars($user->email ?? '');

        return <<<HTML
        <div class="flex-col gap-1 mb-4">
            <label for="email" class="block color-gray-600">{$label}</label>
            <input type="email" id="email" name="email" data-sensitive="true" class="form-input" value="{$email}">
            <span id="email-error" class="error-feedback"></span>
        </div>
        HTML;
    }
}
