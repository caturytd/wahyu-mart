<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// maintenance mode
if (file_exists($maintenance = __DIR__.'/../wahyumart.prizu.site/storage/framework/maintenance.php')) {
    require $maintenance;
}

// autoload
require __DIR__.'/../wahyumart.prizu.site/vendor/autoload.php';

// bootstrap app
$app = require_once __DIR__.'/../wahyumart.prizu.site/bootstrap/app.php';

$app->handleRequest(Request::capture());
