<?php
declare(strict_types=1);

function send_otp(PDO $pdo, array $app, array $input, array $config): never {
    $destination = trim((string)($input['destination'] ?? ''));
    $channel = strtolower(trim((string)($input['channel'] ?? 'sms')));
    $purpose = trim((string)($input['purpose'] ?? 'verification'));

    if ($destination === '' || !in_array($channel, ['sms', 'email'], true)) {
        json_response(['success' => false, 'message' => 'destination and valid channel are required'], 422);
    }

    // Basic resend throttling per app/destination.
    $recent = $pdo->prepare('SELECT created_at FROM otp_requests WHERE app_id = ? AND destination = ? ORDER BY id DESC LIMIT 1');
    $recent->execute([$app['id'], $destination]);
    $last = $recent->fetchColumn();
    if ($last && (time() - strtotime($last)) < $config['otp']['resend_cooldown_seconds']) {
        json_response(['success' => false, 'message' => 'Please wait before requesting another OTP'], 429);
    }

    $otp = generate_otp((int)$config['otp']['length']);
    $requestId = request_id();
    $expiresAt = date('Y-m-d H:i:s', time() + (int)$config['otp']['expiry_seconds']);

    $stmt = $pdo->prepare('INSERT INTO otp_requests (app_id,destination,channel,purpose,code_hash,expires_at) VALUES (?,?,?,?,?,?)');
    $stmt->execute([
        $app['id'], $destination, $channel, $purpose,
        hash('sha256', $otp),
        $expiresAt
    ]);

    // Provider adapter is intentionally isolated from OTP state creation.
    // TODO: connect SMS/email providers and never expose the OTP in the response.
    json_response([
        'success' => true,
        'request_id' => $requestId,
        'expires_in' => (int)$config['otp']['expiry_seconds'],
        'message' => 'OTP request created'
    ], 201);
}

function verify_otp(PDO $pdo, array $app, array $input, array $config): never {
    $requestId = trim((string)($input['request_id'] ?? ''));
    $otp = trim((string)($input['otp'] ?? ''));

    if ($requestId === '' || $otp === '') {
        json_response(['success' => false, 'message' => 'request_id and otp are required'], 422);
    }

    // request_id mapping will be added to the DB before production.
    // For the first build we return an explicit integration placeholder.
    json_response([
        'success' => false,
        'verified' => false,
        'message' => 'Verification handler scaffolded; request_id persistence mapping is next.'
    ], 501);
}
