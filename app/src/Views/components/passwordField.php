<?php
require_once __DIR__ . '/../components/icon.php';

if (!function_exists('render_password_field')) {
    function render_password_field(string $label = 'Password', string $name = 'password', bool $required = false, string $autocomplete = 'current-password'): string {
        $label = $required ? $label . '*' : $label;
        $errorFieldId = $name . '-error';
        $visibleIconHtml = render_icon('visible');
        $invisibleIconHtml = render_icon('invisible');

        return <<<HTML
        <div class="flex-col gap-1 mb-4">
            <label for="{$name}" class="block color-gray-600">{$label}</label>
            <div class="password-field">
                <input type="password" id="{$name}" name="{$name}" data-sensitive="true" class="form-input" autocomplete="{$autocomplete}">
                <button type="button" class="password-toggle" data-target="{$name}" aria-label="Show password">
                    <span class="icon-visible">{$visibleIconHtml}</span>
                    <span class="icon-invisible" hidden>{$invisibleIconHtml}</span>
                </button>
            </div>
            <span id="{$errorFieldId}" class="error-feedback"></span>
        </div>
        HTML;
    }
}