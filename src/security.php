<?php
declare(strict_types=1);

function random_token(int $bytes = 32): string {
    return bin2hex(random_bytes($bytes));
}

function token_hash(string $token): string {
    return hash('sha256', $token);
}

function generate_otp(int $length = 6): string {
    $max = (10 ** $length) - 1;
    return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
}

function request_id(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        random_int(0, 65535), random_int(0, 65535),
        random_int(0, 65535),
        random_int(0, 4095) | 0x4000,
        random_int(0, 16383) | 0x8000,
        random_int(0, 65535), random_int(0, 65535), random_int(0, 65535)
    );
}

function api_credentials(): array {
    return [
        'key' => 'otpk_' . random_token(16),
        'secret' => 'otps_' . random_token(32),
    ];
}

function authenticate_app(PDO $pdo): array {
    $key = trim($_SERVER['HTTP_X_API_KEY'] ?? '');
    $secret = trim($_SERVER['HTTP_X_API_SECRET'] ?? '');

    if ($key === '' || $secret === '') {
        json_response(['success' => false, 'message' => 'API credentials required'], 401);
    }

    $stmt = $pdo->prepare('SELECT * FROM apps WHERE api_key_hash = ? AND status = "active" LIMIT 1');
    $stmt->execute([token_hash($key)]);
    $app = $stmt->fetch();

    if (!$app || !hash_equals($app['api_secret_hash'], token_hash($secret))) {
        json_response(['success' => false, 'message' => 'Invalid API credentials'], 401);
    }

    return $app;
}
