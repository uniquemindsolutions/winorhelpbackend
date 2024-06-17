<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cors {
    public function __construct() {
        $this->handleCors();
    }

    private function handleCors() {
        header('Access-Control-Allow-Origin: '); // You can specify the domain instead of ''
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        
        // If this is a preflight request, exit without further processing
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit(0);
        }
    }
}