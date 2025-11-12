<?php
namespace Src\Helpers;

class Jwt
{
    /**
     * Encode base64url
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode base64url
     */
    private static function base64UrlDecode($data)
    {
        $padding = strlen($data) % 4;
        if ($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Create/encode a JWT token
     */
    public static function encode($payload, $secret, $algorithm = 'HS256')
    {
        $header = ['typ' => 'JWT', 'alg' => $algorithm];
        
        $header64 = self::base64UrlEncode(json_encode($header));
        $payload64 = self::base64UrlEncode(json_encode($payload));
        
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "$header64.$payload64", $secret, true)
        );
        
        return "$header64.$payload64.$signature";
    }

    /**
     * Decode JWT token (without verification)
     */
    public static function decode($token)
    {
        $token = trim($token);
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        try {
            $headerJson = self::base64UrlDecode($parts[0]);
            $payloadJson = self::base64UrlDecode($parts[1]);
            
            if ($headerJson === false || $payloadJson === false) {
                return false;
            }
            
            $header = json_decode($headerJson, true);
            $payload = json_decode($payloadJson, true);
            
            if (!is_array($header) || !is_array($payload)) {
                return false;
            }
            
            return [
                'header' => $header,
                'payload' => $payload,
                'signature' => $parts[2]
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sign JWT token (alias for encode)
     */
    public static function sign($payload, $secret, $algorithm = 'HS256')
    {
        return self::encode($payload, $secret, $algorithm);
    }

    /**
     * Verify JWT token signature and expiration
     */
    public static function verify($token, $secret, $algorithm = 'HS256')
    {
        $decoded = self::decode($token);
        
        if (!$decoded) {
            return false;
        }
        
        // Verify signature
        $parts = explode('.', $token);
        $signatureInput = $parts[0] . '.' . $parts[1];
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $signatureInput, $secret, true)
        );
        
        if ($decoded['signature'] !== $expectedSignature) {
            return false;
        }
        
        // Check expiration
        $payload = $decoded['payload'];
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
}