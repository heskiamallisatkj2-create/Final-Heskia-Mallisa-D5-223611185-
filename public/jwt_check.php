<?php
$secret = 'nF7kQ3xTzL0vM2cR8bW9sD4pH6jY1tG5';

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Ambil token dari header Authorization
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["error" => "Authorization header missing"]);
    exit;
}

list($type, $jwt) = explode(" ", $authHeader);
if (strcasecmp($type, "Bearer") != 0 || empty($jwt)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid Authorization header"]);
    exit;
}

// Pisahkan bagian token
list($header64, $payload64, $signature) = explode('.', $jwt);
$payload = json_decode(base64UrlDecode($payload64));

if (!$payload) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid token structure"]);
    exit;
}

// Cek waktu kedaluwarsa
if ($payload->exp < time()) {
    http_response_code(401);
    echo json_encode([
        "valid" => false,
        "message" => "Token expired",
        "expired_at" => date('Y-m-d H:i:s', $payload->exp)
    ]);
} else {
    $remaining = $payload->exp - time();
    echo json_encode([
        "valid" => true,
        "message" => "Token still active",
        "expires_in_seconds" => $remaining,
        "will_expire_at" => date('Y-m-d H:i:s', $payload->exp)
    ]);
}
