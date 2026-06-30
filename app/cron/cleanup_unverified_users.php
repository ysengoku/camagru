#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/core/Database.php';
require_once __DIR__ . '/../src/core/Model.php';
require_once __DIR__ . '/../src/Models/User.php';

$success = User::deleteAbandonedUnverified(24);

echo $success
    ? '[' . date('Y-m-d H:i:s') . "] Cleanup of abandoned unverified accounts completed.\n"
    : '[' . date('Y-m-d H:i:s') . "] Cleanup of abandoned unverified accounts failed.\n";
