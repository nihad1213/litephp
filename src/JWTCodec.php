<?php

/*
* -----------------------------------------------------------------------------
* Class: JWTCodec
* -----------------------------------------------------------------------------
* This class provides functionality to encode and decode JSON Web Tokens (JWT)
* using the HMAC-SHA256 algorithm. It is a lightweight implementation with no 
* external dependencies, ideal for stateless authentication systems.
*
* Main Features:
*  - Generate JWTs with payload data
*  - Validate and decode JWTs securely
*  - Uses HMAC SHA-256 for signing
*  - Base64 URL-safe encoding/decoding
*
* Constructor Parameter:
*  - string $key : Secret key used for HMAC signature (must be kept private)
*
* Created by: Nihad Namatli
* -----------------------------------------------------------------------------
*/

class JWTCodec 
{
    /*
    * -------------------------------------------------------------------------
    * Property: $key
    * -------------------------------------------------------------------------
    * The secret key used for signing and verifying JWT tokens.
    *
    * Type: string
    * -------------------------------------------------------------------------
    */
    private string $key;

    /*
    * -------------------------------------------------------------------------
    * Constructor: __construct
    * -------------------------------------------------------------------------
    * Initializes the JWTCodec with a secret key used for signature generation 
    * and validation.
    *
    * Parameters:
    *  - string $key : Secret key for HMAC SHA-256 signature
    * -------------------------------------------------------------------------
    */
    public function __construct(string $key) 
    {
        $this->key = $key;
    }

    /*
    * -------------------------------------------------------------------------
    * Function: encode
    * -------------------------------------------------------------------------
    * Encodes a given payload into a JWT string using base64url and signs it 
    * with the HMAC SHA-256 algorithm.
    *
    * Parameters:
    *  - array $payload : Associative array of data to be encoded as JWT payload
    *
    * Returns:
    *  - string : Encoded JWT string
    * -------------------------------------------------------------------------
    */
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

    /*
    * -------------------------------------------------------------------------
    * Function: base64urlEncode
    * -------------------------------------------------------------------------
    * Converts a standard base64 string into a URL-safe version as required 
    * by the JWT specification.
    *
    * Parameters:
    *  - string $text : Text to be base64url encoded
    *
    * Returns:
    *  - string : Base64 URL-safe encoded string
    * -------------------------------------------------------------------------
    */
    private function base64urlEncode(string $text): string 
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }

    /*
    * -------------------------------------------------------------------------
    * Function: decode
    * -------------------------------------------------------------------------
    * Verifies the signature of a JWT and decodes its payload if valid.
    *
    * Parameters:
    *  - string $jwt : Encoded JWT token
    *
    * Returns:
    *  - array|false : Decoded payload as an associative array if valid, 
    *                  otherwise false
    * -------------------------------------------------------------------------
    */
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
