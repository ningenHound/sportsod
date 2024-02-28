<?php

namespace App\Helpers;

//use stdClass;

class JWTHelper {

    public static function isValid($jwt, $secret, $algorythm = 'SHA256'): bool {
        
        $jwt = substr($jwt, strlen("Bearer "));
        $tokenParts = explode('.', $jwt);
        $header = $tokenParts[0];
        $payload = $tokenParts[1];
        $signature = $tokenParts[2];
        //$signature2 = Base64UrlHelper::base64url_decode($signature);
        $signatureGenerated = Base64UrlHelper::base64url_encode(hash_hmac($algorythm, $header . "." . $payload, $secret));
        
        // verify if it matches the signature provided in the jwt
        $signatureValid = ($signatureGenerated === $signature);
        
        if (!$signatureValid) {
            return false;
        } 
        
        return true;
    }

    public static function generate($user, $secret, $algorythm = 'SHA256'): string {
        $header = ['alg'=>$algorythm, 'typ'=>'JWT'];
        $payload = ['iat'=>time(), 'user_id'=>$user->id, 'role_id'=>$user->role_id];
        $base64UrlHeader = Base64UrlHelper::base64url_encode(json_encode($header));
        $base64UrlPayload = Base64UrlHelper::base64url_encode(json_encode($payload));
        $signature = hash_hmac($algorythm, $base64UrlHeader . "." . $base64UrlPayload, $secret);
        $base64UrlSignature = Base64UrlHelper::base64url_encode($signature);
        return $base64UrlHeader.".".$base64UrlPayload.".".$base64UrlSignature;
    }

    public static function getClaim($jwt, $claim='role_id') {
        $jwt = substr($jwt, strlen("Bearer "));
        $tokenParts = explode('.', $jwt);
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        return $payload[$claim];
    }

}