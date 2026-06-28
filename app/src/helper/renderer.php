<?php

/**
 * Renders an email template with the given name and variables.
 * @param string $templateName The name of the email template (without the .php extension).
 * @param list<string, mixed> $vars An associative array of variables to extract and use in the template.
 * @return string The rendered email content.
 */
function renderEmailTemplate(string $templateName, array $vars = []): string {
    extract($vars);
    ob_start();
    include __DIR__ . '/../Views/emails/' . $templateName . '.php';
    return (string) ob_get_clean();
}
