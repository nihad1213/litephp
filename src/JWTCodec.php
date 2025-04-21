<?php

class JWTCodec 
{
    private string $key;

    public function __construct(string $key) 
    {
        $this->key = $key;
    }

    public function encode(array $payload): string 
    {
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);

        $base64UrlHeader = $this->base64urlEncode($header);
        $base64UrlPayload = $this->base64urlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->key, true);
        $base64UrlSignature = $this->base64urlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    private function base64urlEncode(string $text): string 
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }

    public function decode(string $jwt): array|false 
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return false;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        $signatureCheck = hash_hmac(
            'sha256',
            "$base64UrlHeader.$base64UrlPayload",
            $this->key,
            true
        );

        if ($this->base64urlEncode($signatureCheck) !== $base64UrlSignature) {
            return false;
        }

        $payloadJson = base64_decode(strtr($base64UrlPayload, '-_', '+/'));
        return json_decode($payloadJson, true);
    }
}
