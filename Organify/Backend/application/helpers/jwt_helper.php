<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JWT_Helper {
    private static $secret_key = "mysecretkey"; // Ganti dengan key yang lebih kuat

    // Buat token JWT
    public static function generate_token($data, $exp = 3600) {
        $header = base64_encode(json_encode(["alg" => "HS256", "typ" => "JWT"]));
        $payload = base64_encode(json_encode([
            "data" => $data,
            "exp" => time() + $exp // Expiry time (default 1 jam)
        ]));

        $signature = hash_hmac('sha256', "$header.$payload", self::$secret_key, true);
        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    // Validasi token JWT
    public static function validate_token($token) {
        $parts = explode(".", $token);
        if (count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;
        $valid_signature = base64_encode(hash_hmac('sha256', "$header.$payload", self::$secret_key, true));

        if ($valid_signature !== $signature) return false;

        $payload_data = json_decode(base64_decode($payload), true);
        if ($payload_data['exp'] < time()) return false; // Token expired

        return $payload_data['data']; // Return user data
    }
}
