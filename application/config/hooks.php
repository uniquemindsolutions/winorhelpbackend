<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['post_controller_constructor'][] = array(
    'class'    => 'Cors',
    'function' => '__construct',
    'filename' => 'Cors.php',
    'filepath' => 'filters'
);

$hook['post_controller_constructor'][] = [
    'class'    => '',
    'function' => 'validate_token',
    'filename' => 'token_validator.php',
    'filepath' => 'hooks',
    'params'   => []
];