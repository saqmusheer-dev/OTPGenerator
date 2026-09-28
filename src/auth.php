<?php
declare(strict_types=1);

function register_user(PDO $pdo, array $input): never {
    $name = trim((string)($input['name'] ?? ''));
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $password = (string)($input['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        json_response(['success' => false, 'message' => 'Name, valid email and password (8+ chars) are required'], 422);
    }

    $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)');
    try {
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    } catch (PDOException $e) {
        if ((string)$e->getCode() === '23000') {
            json_response(['success' => false, 'message' => 'Email already registered'], 409);
        }
        throw $e;
    }

    json_response(['success' => true, 'message' => 'Account created'], 201);
}

function create_app(PDO $pdo, int $userId, array $input): never {
    $name = trim((string)($input['name'] ?? ''));
    $website = trim((string)($input['website_url'] ?? ''));

    if ($name === '') {
        json_response(['success' => false, 'message' => 'App/business name is required'], 422);
    }

    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    $credentials = api_credentials();

    $stmt = $pdo->prepare('INSERT INTO apps (user_id,name,slug,website_url,api_key_hash,api_key_last4,api_secret_hash,secret_last4) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $userId, $name, $slug, $website ?: null,
        token_hash($credentials['key']), substr($credentials['key'], -4),
        token_hash($credentials['secret']), substr($credentials['secret'], -4)
    ]);

    json_response([
        'success' => true,
        'app' => [
            'name' => $name,
            'website_url' => $website ?: null,
            'api_key' => $credentials['key'],
            'api_secret' => $credentials['secret'],
            'warning' => 'Store the API secret securely. It will not be shown again.'
        ]
    ], 201);
}
