<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/splAutoload.php';
require_once __DIR__ . '/../src/helper/Path.php';
require_once __DIR__ . '/../src/helper/renderer.php';
require_once __DIR__ . '/../src/helper/token.php';

putenv('MYSQL_DATABASE=' . getenv('MYSQL_TEST_DATABASE'));
putenv('MAIL_DISABLED=true');

session_start();
