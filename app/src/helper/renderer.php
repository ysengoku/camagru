<?php

/**
 * Renders an email template with the given name and variables.
 * @param string $templateName The name of the email template (without the .php extension).
 * @param array<string, mixed> $vars An associative array of variables to extract and use in the template.
 * @return string The rendered email content.
 */
function renderEmailTemplate(string $templateName, array $vars = []): string {
    extract($vars);
    ob_start();
    /** @psalm-suppress UnresolvableInclude - Directory is fixed; only $templateName varies */
    include __DIR__ . '/../Views/emails/' . $templateName . '.php';
    return (string) ob_get_clean();
}

/**
 * Returns the hashed CSS URL associated with a JS entry (from the Vite manifest).
 * @param string $src JS entry source path as defined in vite.config.js input (e.g. 'js/main.js').
 * @return string|null Web-root-relative URL to the bundled CSS, or null if none.
 */
function viteEntryCSS(string $src): ?string {
    static $manifest = null;

    if ($manifest === null) {
        $manifestPath = Path::join(Path::getPublicPath(), 'assets', '.vite', 'manifest.json');
        $manifest = json_decode((string) file_get_contents($manifestPath), true) ?? [];
    }

    $entry = $manifest[$src] ?? null;
    if ($entry === null) {
        return null;
    }

    // CSS directly on the entry
    if (!empty($entry['css'][0])) {
        return '/assets/' . $entry['css'][0];
    }

    // CSS on an imported shared chunk
    foreach ($entry['imports'] ?? [] as $chunkKey) {
        $cssFile = $manifest[$chunkKey]['css'][0] ?? null;
        if ($cssFile !== null) {
            return '/assets/' . $cssFile;
        }
    }

    return null;
}
