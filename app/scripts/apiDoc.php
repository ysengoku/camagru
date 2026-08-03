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

function renderParams(array|bool $params): string {
    if ($params === false) {
        return 'N/A';
    }

    $bodyParams = <<<MD
        | Name | Type | Description |
        |---|---|---|

        MD;
    foreach($params as $param) {
        $parsedParam = parseParamLine($param);
        $bodyParams .= "|" . $parsedParam['name'] . "|" . $parsedParam['type'] . "|" . $parsedParam['description'] . "|\n";
    }

    return $bodyParams;
}

function parseResponseLine(string $line): array {
    // (\d+) — the status code, since it's always numeric now.
    // (\{[^}]*\}) — the literal {message}/{error} shape, matched greedily up to the closing brace.
    // (.*) — whatever's left as the description.
    preg_match('/^(\d+)\s+(\{[^}]*\})\s*(.*)$/', $line, $m);

    return [
        'status' => $m[1] ?? '',
        'body' => $m[2] ?? '',
        'description' => $m[3] ?? '',
    ]; 
}

function renderResponses(array|bool $responses): string {
    if ($responses === false) {
        return '';
    }

    $parsed = <<<MD
        | Status | Body | Description |
        |---|---|---|

        MD;
    foreach($responses as $response) {
        $parsedResponse = parseResponseLine($response);
        $parsed .= "|" . $parsedResponse['status'] . "|" . $parsedResponse['body'] . "|" . $parsedResponse['description'] . "|\n";
    }

    return $parsed;
}

$docTitle = '# API Documentation';
$content = $docTitle . "\n\n";

forEach($routes as $route) {
    if (!str_starts_with($route['path'], '/api/')) {
        continue;
    }

    $controllerName = ucfirst($route['controller']) . 'Controller';
    $methodName = $route['action'];

    $method = new ReflectionMethod($controllerName, $methodName);
    $docContent = $method->getDocComment();

    $parsed = parseDocStringComment($docContent);
    // var_dump($parsed);
    $docRoute = $parsed['tags']['route'][0] ?? '';
    $docSummary = $parsed['summary'] ?? '';

    $content .= <<<MD
        ## {$docRoute}

        {$docSummary}

        MD;

    $bodyParams = renderParams($parsed['tags']['bodyParam'] ?? false);
    $content .= "### Parameters " . "\n\n" . $bodyParams . "\n";

    $queryParams = renderParams($parsed['tags']['queryParam'] ?? false);
    $content .= "### Query Parameters" . "\n\n" . $queryParams . "\n";

    // $responses = '';
    // if ($parsed['tags']['response'] ?? false) {
    //     foreach($parsed['tags']['response'] as $response) {
    //         $responses .= '- ' . $response . "\n";
    //     }
    // }
    $responses = renderResponses($parsed['tags']['response'] ?? false);

    $content .= "### Responses" . "\n\n" . $responses . "\n\n" ;

}

file_put_contents(API_DOC_PATH, $content);
