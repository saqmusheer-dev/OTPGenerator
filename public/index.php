<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    ['POST', '/api/v1/otp/send'],
    ['POST', '/api/v1/otp/verify'],
    ['GET',  '/api/v1/health'],
];

if ($path === '/api/v1/health' && $method === 'GET') {
    echo json_encode([
        'success' => true,
        'service' => 'OTPGenerator',
        'status' => 'ok',
        'time' => gmdate('c'),
    ]);
    exit;
}

if ($method === 'POST' && in_array($path, ['/api/v1/otp/send', '/api/v1/otp/verify'], true)) {
    http_response_code(501);
    echo json_encode([
        'success' => false,
        'message' => 'OTP endpoint scaffold created. Database, authentication and provider adapters are next.',
        'endpoint' => $path,
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Route not found']);
