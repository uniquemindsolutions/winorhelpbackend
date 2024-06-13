<?php
class User_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function register($data) {
        $this->db->select("*");
        $this->db->from('users');
        $this->db->where('email', $data['email']);
        $query = $this->db->get();
        if($query->row_array()){
            return false;
        }else{
            $token = bin2hex(random_bytes(50)); // Generate a token
            $data['token']=$token;
            return $this->db->insert('users', $data);
        }
    }

    public function login($username, $password) {
        
        $this->db->where('email', $username);
        $query = $this->db->get('users');
        $user = $query->row();
    
        if ($user && $password==$user->password) {
            return $user;
        } else {
            return false;
        }
    }
}