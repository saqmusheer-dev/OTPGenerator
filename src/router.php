<?php
declare(strict_types=1);

function route_request(PDO $pdo, array $config): never {
    require_once __DIR__ . '/security.php';
    require_once __DIR__ . '/otp_service.php';
    require_once __DIR__ . '/auth.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $body = json_input();

    if ($method === 'GET' && $path === '/api/v1/health') {
        json_response(['success' => true, 'service' => 'OTPGenerator', 'status' => 'ok', 'time' => gmdate('c')]);
    }

    if ($method === 'POST' && $path === '/api/v1/auth/register') {
        register_user($pdo, $body);
    }

    if ($method === 'POST' && $path === '/api/v1/otp/send') {
        $app = authenticate_app($pdo);
        send_otp($pdo, $app, $body, $config);
    }

    if ($method === 'POST' && $path === '/api/v1/otp/verify') {
        $app = authenticate_app($pdo);
        verify_otp($pdo, $app, $body, $config);
    }

    json_response(['success' => false, 'message' => 'Route not found'], 404);
}
