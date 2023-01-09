<?php
namespace App\Util;

class Base64Url
{
    public static function decode($data) {
        // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
        $b64 = strtr($data, '-_', '+/');
        
        // Decode Base64 string and return the original data
        return base64_decode($b64);
    }
    
    public static function encode($data) {
        // First of all you should encode $data to Base64 string
        $b64 = base64_encode($data);
        
        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) {
            return false;
        }
        
        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');
        
        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }
}

