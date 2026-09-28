<?php
declare(strict_types=1);
session_start();

$GLOBALS['config'] = require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/router.php';

try {
    route_request(db(), $GLOBALS['config']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    json_response(['success' => false, 'message' => 'Internal server error'], 500);
}
