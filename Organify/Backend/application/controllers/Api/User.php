<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    private $users = [];

    public function __construct() {
        parent::__construct();
        $this->load->library('MyREST');

        // Dummy data user
        $this->users = [
            ["id" => 1, "name" => "Alice", "email" => "alice@example.com"],
            ["id" => 2, "name" => "Bob", "email" => "bob@example.com"],
            ["id" => 3, "name" => "Charlie", "email" => "charlie@example.com"]
        ];
    }

    // Login: Generate Token
    public function login() {
        $data = $this->myrest->get_json_input();

        if (!isset($data['email']) || !isset($data['password'])) {
            $this->myrest->response(['message' => 'Email and password required'. json_decode(file_get_contents("php://input"))], 400);
            return;
        }

        // Dummy user authentication
        $user = array_filter($this->users, function ($u) use ($data) {
            return $u['email'] === $data['email'];
        });

        if (empty($user)) {
            $this->myrest->response(['message' => 'Invalid credentials'], 401);
            return;
        }

        $user = array_values($user)[0];
        $token = JWT_Helper::generate_token(['id' => $user['id'], 'email' => $user['email']]);

        $this->myrest->response(['token' => $token], 200);
    }

    // Semua API harus pakai token!
    public function index() {
        $user_data = $this->myrest->authenticate(); // Cek token dulu!

        $method = $this->myrest->get_request_method();
        switch ($method) {
            case 'GET':
                $this->get_users();
                break;
            case 'POST':
                $this->create_user();
                break;
            case 'PUT':
                $this->update_user();
                break;
            case 'DELETE':
                $this->delete_user();
                break;
            default:
                $this->myrest->response(['message' => 'Method not allowed'], 405);
        }
    }

    // GET: Ambil semua user atau berdasarkan ID
    private function get_users() {
        $this->myrest->response($this->users, 200);
    }

    // POST: Tambah user baru
    private function create_user() {
        $data = $this->myrest->get_json_input();

        if (!isset($data['name']) || !isset($data['email'])) {
            $this->myrest->response(['message' => 'Invalid input'], 400);
            return;
        }

        $new_user = [
            "id" => count($this->users) + 1,
            "name" => $data['name'],
            "email" => $data['email']
        ];

        array_push($this->users, $new_user);
        $this->myrest->response(['message' => 'User created', 'user' => $new_user], 201);
    }

    // PUT: Update user berdasarkan ID
    private function update_user() {
        $data = $this->myrest->get_json_input();

        if (!isset($data['id']) || !isset($data['name']) || !isset($data['email'])) {
            $this->myrest->response(['message' => 'Invalid input'], 400);
            return;
        }

        foreach ($this->users as &$user) {
            if ($user['id'] == $data['id']) {
                $user['name'] = $data['name'];
                $user['email'] = $data['email'];

                $this->myrest->response(['message' => 'User updated', 'user' => $user], 200);
                return;
            }
        }

        $this->myrest->response(['message' => 'User not found'], 404);
    }

    // DELETE: Hapus user berdasarkan ID
    private function delete_user() {
        $data = $this->myrest->get_json_input();

        if (!isset($data['id'])) {
            $this->myrest->response(['message' => 'User ID is required'], 400);
            return;
        }

        foreach ($this->users as $key => $user) {
            if ($user['id'] == $data['id']) {
                unset($this->users[$key]);
                $this->myrest->response(['message' => 'User deleted'], 200);
                return;
            }
        }

        $this->myrest->response(['message' => 'User not found'], 404);
    }
}
