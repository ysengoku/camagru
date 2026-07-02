#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/core/Database.php';

const TABLES = ['likes', 'comments', 'posts', 'users'];

$db = Database::getInstance();

$db->execute('SET FOREIGN_KEY_CHECKS = 0');
foreach (TABLES as $table) {
    $db->execute("TRUNCATE TABLE `{$table}`");
    echo "Truncated {$table}\n";
}
$db->execute('SET FOREIGN_KEY_CHECKS = 1');

echo "Database cleaned.\n";
