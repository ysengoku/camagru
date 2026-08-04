#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/config/routes.php';
require_once __DIR__ . '/../src/splAutoload.php';

const API_DOC_PATH = __DIR__ . '/../../doc/API_DOC.md';

function parseDocStringComment(string|false $raw): array {
    if ($raw === false) {
        return ['summary' => '', 'tags' => []];
    }

    $lines = preg_split('/\R/', $raw);
    $lines = array_map(
        fn($line) => preg_replace('/^\s*\/?\*+\/?\s?/', '', $line),
        $lines
    );

    $lines = array_filter($lines, fn($l) => trim($l) !== '' || true);

    $summary = [];
    $tags = [];
    foreach ($lines as  $line) {
        if (preg_match('/^@(\w+)\s*(.*)$/', trim($line), $m)) {
            $tags[$m[1]][] = trim($m[2]); // grouped by tag name, supports repeats (@bodyParam x3)
        } elseif (empty($tags)) {
            $summary[] = $line; // only collect summary before the first @tag
        }
    }

    return [
        'summary' => trim(implode("\n", $summary)),
        'tags' => $tags
    ];
}

function parseParamLine(string $line): array {
    preg_match('/^(\S+)\s+\$?([\w-]+)\s*(.*)$/', $line, $m);

    return [
        'type' => $m[1] ?? '',
        'name' => $m[2] ?? '',
        'description' => $m[3] ?? '',
    ];
}


function parseResponseLine(string $line): array {
    // (\d+) — the status code, since it's always numeric now.
    // (\{[^}]*\}) — the literal {message}/{error} shape, matched greedily up to the closing brace.
    // (.*) — whatever's left as the description.
    preg_match('/^(\d+)\s+(\{[^}]*\})\s*(.*)$/', $line, $m);

    return [
        'status' => $m[1] ?? '',
        'value' => $m[2] ?? '',
        'description' => $m[3] ?? '',
    ]; 
}

function slugify(string $httpMethod, string $path): string {
    $slug = strtolower($httpMethod . '-' . $path);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

    return trim($slug, '-');
}

function renderEndpointHtml(string $httpMethod, string $path, array $doc): string {
    $paramsHtml   = renderTableHtml($doc['tags']['bodyParam'] ?? [], 'Body Parameters');
    $queryHtml    = renderTableHtml($doc['tags']['queryParam'] ?? [], 'Query Parameters');
    $responseHtml = renderResponseTableHtml($doc['tags']['response'] ?? []);
    $methodBadge  = methodBadge($httpMethod);
    $anchorId     = slugify($httpMethod, $path);

    $bodyParts = implode("\n", array_filter([
        $doc['summary'],
        $paramsHtml,
        $queryHtml,
        $responseHtml,
    ], fn($part) => trim($part) !== ''));

    return <<<HTML
    <details>
        <summary>
            <a href="#{$anchorId}">{$methodBadge}</a>
            <h3>
                &nbsp{$path}
            </h3>
        </summary>
        <div>
            {$bodyParts}
        </div>
    </details>

    HTML;
}

function renderTableHtml(array $params, string $heading): string {
    if (empty($params)) {
        return '';
    }
    $rows = '';
    foreach ($params as $line) {
        $p = parseParamLine($line);
        $rows .= <<<HTML
        <tr>
            <td><code>{$p['name']}</code></td>
            <td>{$p['type']}</td>
            <td>{$p['description']}</td>
        </tr>

        HTML;
    }

    return <<<HTML
    <h4>{$heading}</h4>
    <table>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
        </tr>
        {$rows}
    </table>
    HTML;
}

function renderResponseTableHtml(array $responses): string {
    if (empty($responses)) {
        return '';
    }
    $rows = '';
    foreach ($responses as $line) {
        $r = parseResponseLine($line);
        $rows .= <<<HTML
        <tr>
            <td><code>{$r['status']}</code></td>
            <td><code>{$r['value']}</code></td>
            <td>{$r['description']}</td>
        </tr>
        
        HTML;
    }
    return <<<HTML
    <h4>
        Responses
    </h4>
    <table>
        <tr>
            <th>Status</th>
            <th>Value</th>
            <th>Description</th>
        </tr>
        {$rows}
    </table>
    HTML;
}

function methodBadge(string $method): string {
    $colors = [
        'GET'    => 'brightgreen',
        'POST'   => 'blue',
        'DELETE' => 'red',
        'PUT'    => 'orange',
        'PATCH'  => 'yellow',
    ];
    $color = $colors[$method] ?? 'lightgrey';

    return "<img src=\"https://img.shields.io/badge/{$method}-{$color}\" valign=\"middle\" />";
}

$endpointsHtml  = '';
$lastController = null;
$sectionTitles  = [
    'validationRules' => 'Validation Rules',
    'auth'            => 'Auth',
    'profile'         => 'Profile',
    'studioConfig'    => 'Studio Config',
    'photoApi'        => 'Photos',
    'postReactions'   => 'Comments & Likes',
];

forEach($routes as $route) {
    if (!str_starts_with($route['path'], '/api/')) {
        continue;
    }

    $controllerName = ucfirst($route['controller']) . 'Controller';
    $methodName = $route['action'];

    $method = new ReflectionMethod($controllerName, $methodName);
    $docContent = $method->getDocComment();

    $parsed = parseDocStringComment($docContent);
    $docRoute = $parsed['tags']['route'][0] ?? '';
    $docSummary = $parsed['summary'] ?? '';

    if ($route['controller'] !== $lastController) {
        $sectionTitle = $sectionTitles[$route['controller']] ?? ucfirst($route['controller']);
        $endpointsHtml .= <<<HTML
        <h2>{$sectionTitle}</h2>

        HTML;
        $lastController = $route['controller'];
    }

    [$httpMethod, $path] = explode(' ', $parsed['tags']['route'][0] ?? ' ', 2);
    $endpointsHtml .= renderEndpointHtml($httpMethod, $path, $parsed);
}

$generatedAt = date('Y-m-d H:i');

$html = <<<HTML
<h1>API Documentation</h1>
<p>Auto-generated from PHPDoc comments in <code>src/Controllers/</code>.</p>

>[!NOTE]
>- To regenerate this documentation, use <code>make api-doc</code>. Do not edit this file directly.
>- Any endpoint may additionally return <code>500 {error}</code> for unexpected server-side failures (e.g. a database write or filesystem operation failing).

<small><em>Last generated: {$generatedAt}</em></small>

{$endpointsHtml}
HTML;

file_put_contents(API_DOC_PATH, $html);
