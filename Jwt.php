<?php
$header = ['typ' => 'JWT', 'alg' => 'HS256'];
$payload = [
  'sub' => 2,
  'name' => 'heskia',
  'role' => 'admin',
  'iat' => time(),
  'exp' => time() + 3600 // Token kedaluwarsa setelah 1 jam
];

$secret = 'nF7kQ3xTzL0vM2cR8bW9sD4pH6jY1tG5>=32_chars'; // harus sama seperti di config.php

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$header64 = base64UrlEncode(json_encode($header));
$payload64 = base64UrlEncode(json_encode($payload));
$signature = base64UrlEncode(hash_hmac('sha256', "$header64.$payload64", $secret, true));

$jwt = "$header64.$payload64.$signature";

echo $jwt;
