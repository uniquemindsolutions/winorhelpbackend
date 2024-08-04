<?php
// require_once 'vendor/autoload.php';
require_once APPPATH . 'libraries/src/BeforeValidException.php';
require_once APPPATH . 'libraries/src/ExpiredException.php';
require_once APPPATH . 'libraries/src/SignatureInvalidException.php';
require_once APPPATH . 'libraries/src/JWT.php';
require_once APPPATH . 'libraries/src/JWK.php';
require_once APPPATH . 'libraries/src/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validate_token() {
    $CI =& get_instance();

    // Exclude specific URIs from token validation (e.g., login, register)
    $excluded_uris = [
        'auth/login',
        'auth/register',
        'auth/login',
        'admin/roomList',
        'auth/logout',
        'admin/getRoomUsersList',
        'websocket/*'
    ];

    $current_uri = $CI->uri->uri_string();

    if (!in_array($current_uri, $excluded_uris)) {
        // Get the Authorization header
        $authorizationHeader = $CI->input->get_request_header('Authorization', TRUE);

        if ($authorizationHeader) {
            // Remove "Bearer " from the token
            $token = str_replace('Bearer ', '', $authorizationHeader);

            try {
                //$token='234234';
                // Validate the token
                $key = '8030'; // Replace with your actual secret key
                JWT::decode($token, new Key($key, 'HS256'));

                $decoded = JWT::decode($token, new Key($key, 'HS256'));

                // Token is valid, continue processing
                return;
            } catch (Exception $e) {
                // Token is invalid, return error response
                $CI->output
                    ->set_content_type('application/json')
                    ->set_status_header(401)
                    ->set_output(json_encode(['error' => 'Invalid token: ' . $e->getMessage()]))
                    ->_display();
                exit;
            }
        } else {
            // No Authorization header provided
            $CI->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode(['error' => 'Authorization header missing']))
                ->_display();
            exit;
        }
    }
}
