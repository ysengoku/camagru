<?php

if (!function_exists('render_username_field')) {
    function render_username_field(?User $user, bool $required = false): string {
        $label = $required ? 'Username*' : 'Username';
        $username = htmlspecialchars($user->username ?? '');

        return <<<HTML
        <div class="flex-col gap-1 mb-4">
            <label for="username" class="block color-gray-600">{$label}</label>
            <input type="text" id="username" name="username" value="{$username}" class="form-input">
            <span id="username-error" class="error-feedback"></span>
        </div>
        HTML;
    }
}
