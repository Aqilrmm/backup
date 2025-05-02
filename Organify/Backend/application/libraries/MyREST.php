<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyREST {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->helper('jwt_helper'); // Load helper JWT
    }

    // Ambil metode request (GET, POST, PUT, DELETE)
    public function get_request_method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    // Ambil data JSON dari body
    public function get_json_input() {
        return json_decode(file_get_contents("php://input"), true);
    }

    // Format response JSON
    public function response($data, $status = 200) {
        $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header($status)
            ->set_output(json_encode($data, JSON_PRETTY_PRINT));
    }

    // Middleware: Cek Token
    public function authenticate() {
        $headers = $this->CI->input->request_headers();
        if (!isset($headers['Authorization'])) {
            $this->response(['message' => 'Token required'], 401);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $user_data = JWT_Helper::validate_token($token);

        if (!$user_data) {
            $this->response(['message' => 'Invalid or expired token'], 401);
            exit;
        }

        return $user_data; // Return data user dari token
    }
}
