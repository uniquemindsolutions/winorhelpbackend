<?php

// Include the necessary JWT files
require_once APPPATH . 'libraries/src/BeforeValidException.php';
require_once APPPATH . 'libraries/src/ExpiredException.php';
require_once APPPATH . 'libraries/src/SignatureInvalidException.php';
require_once APPPATH . 'libraries/src/JWT.php';
require_once APPPATH . 'libraries/src/JWK.php';
require_once APPPATH . 'libraries/src/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Jwt_lib {

    private $key = '8030';

    public function generate_token($data) {
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60), // Token valid for 1 hour
            'data' => $data
        ];

        return JWT::encode($payload, $this->key, 'HS256'); // Make sure to include 'HS256' as the algorithm
    }

    public function validate_token($token) {

  
        try {
       
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return (array) $decoded->data;
        } catch (Exception $e) {
           
            return false;
        }
    }
}
