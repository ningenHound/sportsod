<?php

namespace App\Helpers;

//use stdClass;

class JWTHelper {

    public static function isValid($jwt, $secret, $algorythm = 'SHA256'): bool {
        // split the jwt
        $jwt = substr($jwt, strlen("Bearer "));
        //dd($jwt);
        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        //dd($header);
        $payload = base64_decode($tokenParts[1]);
        $signature = $tokenParts[2];
    
        $tokenExpired = false;
        // check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
        //if(property_exist(json_decode($payload), 'exp')) {
            isset(json_decode($payload)->$exp) ? $expiration = json_decode($payload)->$exp : $expiration = null;
        //}
        if($expiration) {
            return ($expiration - time()) < 0;
        }
    
        // build a signature based on the header and payload using the secret
        $base64UrlHeader = Base64UrlHelper::base64url_encode($header);
        $base64UrlPayload = Base64UrlHelper::base64url_encode($payload);
        $signature = hash_hmac($algorythm, $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = Base64UrlHelper::base64url_encode($signature);
    
        // verify if it matches the signature provided in the jwt
        $signatureValid = ($base64_url_signature === $signature);
        
        if ($tokenExpired || !$signatureValid) {
            return false;
        } 
        
        return true;
    }

    /**
     * @return true if expired
     */
    public static function isExpired($jwt, $expirationField = 'exp') {
        $tokenParts = explode('.', $jwt);
        $payload = base64_decode($tokenParts[1]);
        // if(isset(json_decode($payload, true)[$expirationField])) {
            isset(json_decode($payload, true)[$expirationField]) ? $expiration = json_decode($payload)->exp : $expiration = null;
        // }
        if($expiration) {
            return ($expiration - time()) < 0;
        }

        return false;
    }

}