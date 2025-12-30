<?php

namespace App\Support;

class EmailCipher
{
    public static function encrypt(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $key = config('auth_custom.email_cipher.key');
        $hmacKey = config('auth_custom.email_cipher.hmac_key');
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt($email, 'AES-256-CBC', $key, 0, $iv);

        if ($encrypted === false) {
            return null;
        }

        $hmac = hash_hmac('sha256', $encrypted, $hmacKey, true);

        return base64_encode($iv . $encrypted . $hmac);
    }

    public static function decrypt(?string $payload): ?string
    {
        if (!$payload) {
            return null;
        }

        $key = config('auth_custom.email_cipher.key');
        $hmacKey = config('auth_custom.email_cipher.hmac_key');
        $data = base64_decode($payload, true);

        if ($data === false || strlen($data) < 48) {
            return null;
        }

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16, -32);
        $hmac = substr($data, -32);

        $expectedHmac = hash_hmac('sha256', $encrypted, $hmacKey, true);

        if (!hash_equals($expectedHmac, $hmac)) {
            return null;
        }

        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv) ?: null;
    }
}

